<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\block\Block;

class Farmland extends Transparent{

        protected $id = self::FARMLAND;

        /**
         * Farmland constructor.
         *
         * @param int $meta
         */
        public function __construct($meta = 0){
                $this->meta = $meta;
        }

        /**
         * @return string
         */
        public function getName() : string{
                return "Farmland";
        }

        /**
         * @return float
         */
        public function getHardness(){
                return 0.6;
        }

        /**
         * @return int
         */
        public function getToolType(){
                return Tool::TYPE_SHOVEL;
        }

        /**
         * @return AxisAlignedBB
         */
        protected function recalculateBoundingBox(){
                // Syrim: MCPE v113 trata el farmland como bloque completo (bug MCPE-12109).
                // La altura vanilla sería 0.9375, pero usarla provoca glitch visual
                // donde el jugador "flota" sobre el farmland. Se mantiene y + 1.
                return new AxisAlignedBB(
                        $this->x,
                        $this->y,
                        $this->z,
                        $this->x + 1,
                        $this->y + 1,
                        $this->z + 1
                );
        }

        public function onUpdate($type){
                if($type === Level::BLOCK_UPDATE_NORMAL and $this->getSide(Vector3::SIDE_UP)->isSolid()){
                        $this->level->setBlock($this, Block::get(Block::DIRT), true);
                        return $type;
                }elseif($type === Level::BLOCK_UPDATE_RANDOM){
                        // Syrim: hidratación del farmland.
                        // Si hay agua a 4 bloques a la redonda (inclusive diagonales),
                        // el meta sube hasta 7 (máxima hidratación). Si no hay agua,
                        // el meta baja gradualmente y,eventualmente, vuelve a DIRT.
                        if($this->meta < 7){
                                for($x = -4; $x <= 4; ++$x){
                                        for($z = -4; $z <= 4; ++$z){
                                                $b = $this->level->getBlock($this->add($x, 0, $z));
                                                if($b->getId() === Block::WATER or $b->getId() === Block::STILL_WATER){
                                                        $this->meta = 7;
                                                        $this->level->setBlock($this, $this, true);
                                                        return $type;
                                                }
                                        }
                                }
                                // Sin agua cercana: deshidratar.
                                if(mt_rand(0, 3) === 0){
                                        if($this->meta > 0){
                                                --$this->meta;
                                                $this->level->setBlock($this, $this, true);
                                        }else{
                                                $this->level->setBlock($this, Block::get(Block::DIRT), true);
                                        }
                                }
                        }
                }

                return false;
        }

        /**
         * @param Item $item
         *
         * @return array
         */
        public function getDrops(Item $item) : array{
                return [
                        [Item::DIRT, 0, 1],
                ];
        }
}