<?php

/*
 *  ░█▀▀░█░█░█▀▄░▀█▀░█▄█
 *  ░▀▀█░░█░░█▀▄░░█░░█░█
 *  ░▀▀▀░░▀░░▀░▀░▀▀▀░▀░▀
 *
 *  Syrim - PocketMine-MP based core
 *  Version : 1.0.6
 *  Author  : Dr1xy dev
 *  API     : 3.0.1
 *
 */

namespace pocketmine\block;

use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\item\Item;
use pocketmine\level\generator\object\ObjectDarkOakTree;
use pocketmine\level\generator\object\ObjectJungleBigTree;
use pocketmine\level\generator\object\Tree;
use pocketmine\level\generator\nukkit\object\tree\NewJungleTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectBirchTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectJungleTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectOakTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectSavannaTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectSpruceTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectTallBirchTree;
use pocketmine\level\generator\nukkit\object\tree\ObjectTree;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;

class Sapling extends Flowable{
        const OAK = 0;
        const SPRUCE = 1;
        const BIRCH = 2;
        const JUNGLE = 3;
        const ACACIA = 4;
        const DARK_OAK = 5;

        protected $id = self::SAPLING;

        public function __construct($meta = 0){
                $this->meta = $meta;
        }

        public function getName() : string{
                static $names = [
                        0 => "Oak Sapling",
                        1 => "Spruce Sapling",
                        2 => "Birch Sapling",
                        3 => "Jungle Sapling",
                        4 => "Acacia Sapling",
                        5 => "Dark Oak Sapling"
                ];
                return $names[$this->meta & 0x07] ?? "Unknown";
        }

        public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
                $down = $this->getSide(0);
                if($down->getId() === self::GRASS or $down->getId() === self::DIRT or $down->getId() === self::FARMLAND or $down->getId() === self::PODZOL){
                        $this->getLevel()->setBlock($block, $this, true, true);
                        return true;
                }
                return false;
        }

        public function onActivate(Item $item, Player $player = null){
                if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
                        $type = $this->meta & 0x07;
                        $level = $this->getLevel();
                        $rand = new Random(mt_rand());

                        $tree = $this->createNukkitTree($type, $rand);
                        if($tree === null){
                                Tree::growTree($level, $this->x, $this->y, $this->z, $rand, $type);
                                $item->pop();
                                return true;
                        }

                        for($cx = $this->x - 3; $cx <= $this->x + 3; ++$cx){
                                for($cz = $this->z - 3; $cz <= $this->z + 3; ++$cz){
                                        for($yy = $this->y; $yy < $this->y + 30; ++$yy){
                                                $level->setBlockIdAt($cx, $yy, $cz, Block::AIR);
                                                $level->setBlockDataAt($cx, $yy, $cz, 0);
                                        }
                                }
                        }

                        $level->setBlockIdAt($this->x, $this->y - 1, $this->z, Block::DIRT);
                        $level->setBlockDataAt($this->x, $this->y - 1, $this->z, 0);

                        try{
                                if(method_exists($tree, "placeObject")){
                                        $tree->placeObject($level, $this->x, $this->y, $this->z, $rand);
                                }elseif(method_exists($tree, "generate")){
                                        $tree->generate($level, $rand, new Vector3($this->x, $this->y, $this->z));
                                }
                        }catch(\Throwable $e){
                                if($player !== null){
                                        $player->sendMessage(TextFormat::RED . "Error al crecer el brote: " . $e->getMessage());
                                }
                        }

                        $item->pop();
                        return true;
                }

                return false;
        }

        private function createNukkitTree(int $type, Random $rand){
                switch($type){
                        case Sapling::SPRUCE:
                                return new ObjectSpruceTree();

                        case Sapling::BIRCH:
                                if($rand->nextBoundedInt(39) === 0){
                                        return new ObjectTallBirchTree();
                                }
                                return new ObjectBirchTree();

                        case Sapling::JUNGLE:
                                $r = $rand->nextBoundedInt(20);
                                if($r < 16){
                                        return new NewJungleTree(4);
                                }elseif($r < 19){
                                        return new ObjectJungleBigTree(5, 5, Wood::JUNGLE, Leaves::JUNGLE);
                                }
                                return new ObjectJungleTree();

                        case Sapling::ACACIA:
                                return new ObjectSavannaTree();

                        case Sapling::DARK_OAK:
                                return new ObjectDarkOakTree();

                        case Sapling::OAK:
                        default:
                                return new ObjectOakTree();
                }
        }

        public function onUpdate($type){
                if($type === Level::BLOCK_UPDATE_NORMAL){
                        if($this->getSide(0)->isTransparent() === true){
                                $this->getLevel()->useBreakOn($this);
                                return Level::BLOCK_UPDATE_NORMAL;
                        }
                }elseif($type === Level::BLOCK_UPDATE_RANDOM){
                        if($this->getLevel()->getFullLightAt($this->x, $this->y, $this->z) >= 8 and mt_rand(1, 7) === 1){
                                if(($this->meta & 0x08) === 0x08){
                                        Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->meta & 0x07);
                                }else{
                                        $this->meta |= 0x08;
                                        $this->getLevel()->setBlock($this, $this, true);
                                        return Level::BLOCK_UPDATE_RANDOM;
                                }
                        }else{
                                return Level::BLOCK_UPDATE_RANDOM;
                        }
                }
                return false;
        }

        public function getDrops(Item $item) : array{
                return [
                        [$this->id, $this->meta & 0x07, 1],
                ];
        }
}
