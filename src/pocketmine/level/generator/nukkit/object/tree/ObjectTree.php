<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectTree (dispatcher)
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

abstract class ObjectTree {

    /** @var bool[] */
    public $overridable = [];

    public function __construct() {
        $this->overridable = [
            Block::AIR => true,
            Block::SAPLING => true,
            Block::LOG => true,
            Block::LEAVES => true,
            Block::SNOW_LAYER => true,
            Block::LOG2 => true,
            Block::LEAVES2 => true
        ];
    }

    public function getType() : int {
        return 0;
    }

    public function getTrunkBlock() : int {
        return Block::LOG;
    }

    public function getLeafBlock() : int {
        return Block::LEAVES;
    }

    public function getTreeHeight() : int {
        return 7;
    }

    public static function growTree(ChunkManager $level, int $x, int $y, int $z, Random $random, int $type = 0) {
        $tree = null;
        switch($type) {
            case Sapling::SPRUCE:
                $tree = new ObjectSpruceTree();
                break;
            case Sapling::BIRCH:
                if($random->nextBoundedInt(39) === 0) {
                    $tree = new ObjectTallBirchTree();
                } else {
                    $tree = new ObjectBirchTree();
                }
                break;
            case Sapling::JUNGLE:
                $tree = new ObjectJungleTree();
                break;
            case Sapling::OAK:
            default:
                $tree = new ObjectOakTree();
                break;
        }

        if($tree !== null and $tree->canPlaceObject($level, $x, $y, $z, $random)) {
            $tree->placeObject($level, $x, $y, $z, $random);
        }
    }

    public function canPlaceObject(ChunkManager $level, int $x, int $y, int $z, Random $random) : bool {
        $radiusToCheck = 0;
        for($yy = 0; $yy < $this->getTreeHeight() + 3; ++$yy) {
            if($yy === 1 || $yy === $this->getTreeHeight()) {
                ++$radiusToCheck;
            }
            for($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx) {
                for($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz) {
                    if(!isset($this->overridable[$level->getBlockIdAt($x + $xx, $y + $yy, $z + $zz)])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random) {
        $this->placeTrunk($level, $x, $y, $z, $random, $this->getTreeHeight() - 1);

        for($yy = $y - 3 + $this->getTreeHeight(); $yy <= $y + $this->getTreeHeight(); ++$yy) {
            $yOff = $yy - ($y + $this->getTreeHeight());
            $mid = (int) (1 - $yOff / 2);
            for($xx = $x - $mid; $xx <= $x + $mid; ++$xx) {
                $xOff = abs($xx - $x);
                for($zz = $z - $mid; $zz <= $z + $mid; ++$zz) {
                    $zOff = abs($zz - $z);
                    if($xOff === $mid && $zOff === $mid && ($yOff === 0 || $random->nextBoundedInt(2) === 0)) {
                        continue;
                    }
                    if(!Block::$solid[$level->getBlockIdAt($xx, $yy, $zz)]) {
                        $level->setBlockIdAt($xx, $yy, $zz, $this->getLeafBlock());
                        $level->setBlockDataAt($xx, $yy, $zz, $this->getType());
                    }
                }
            }
        }
    }

    protected function placeTrunk(ChunkManager $level, int $x, int $y, int $z, Random $random, int $trunkHeight) {
        $level->setBlockIdAt($x, $y - 1, $z, Block::DIRT);

        for($yy = 0; $yy < $trunkHeight; ++$yy) {
            $blockId = $level->getBlockIdAt($x, $y + $yy, $z);
            if(isset($this->overridable[$blockId])) {
                $level->setBlockIdAt($x, $y + $yy, $z, $this->getTrunkBlock());
                $level->setBlockDataAt($x, $y + $yy, $z, $this->getType());
            }
        }
    }
}
