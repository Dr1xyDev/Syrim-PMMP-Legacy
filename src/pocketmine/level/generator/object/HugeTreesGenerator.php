<?php

/*
 *  ░█▀▀░█░█░█▀▄░▀█▀░█▄█
 *  ░▀▀█░░█░░█▀▄░░█░░█░█
 *  ░▀▀▀░░▀░░▀░▀░▀▀▀░▀░▀
 *
 *  Syrim - PocketMine-MP based core
 *  Version : 1.0.5
 *  Author  : Dr1xy dev
 *  API     : 3.0.1 (modified)  |  Protocol : v113  |  MultiPHP : 7.3 / 7.4 / 8.0
 *
 *  Nota : Port de HugeTreesGenerator de Nukkit (Java) a PHP/Syrim.
 *         Es la clase base para los "big trees" (jungle big tree, dark oak big tree).
 */

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class HugeTreesGenerator extends Tree{

        /** @var int Altura base del tronco */
        protected $baseHeight = 0;

        /** @var int Altura extra aleatoria (se suma a baseHeight) */
        protected $extraRandomHeight = 0;

        /** @var int Metadata del tronco (Wood::JUNGLE, etc) */
        protected $woodMeta = 0;

        /** @var int Metadata de las hojas (Leaves::JUNGLE, etc) */
        protected $leavesMeta = 0;

        /**
         * HugeTreesGenerator constructor.
         *
         * @param int $baseHeightIn
         * @param int $extraRandomHeight
         * @param int $woodMetadata   Block metadata (no Block object como en Nukkit)
         * @param int $leavesMetadata Block metadata
         */
        public function __construct(int $baseHeightIn, int $extraRandomHeight, int $woodMetadata, int $leavesMetadata){
                $this->baseHeight = $baseHeightIn;
                $this->extraRandomHeight = $extraRandomHeight;
                $this->woodMeta = $woodMetadata;
                $this->leavesMeta = $leavesMetadata;

                // Compatibilidad con la clase base Tree
                $this->trunkBlock = Block::LOG;
                $this->leafBlock = Block::LEAVES;
                $this->type = $woodMetadata;
        }

        /**
         * Calcula la altura final del árbol = baseHeight + random(0..extraRandomHeight).
         *
         * @param Random $rand
         * @return int
         */
        protected function getHeight(Random $rand) : int{
                return $this->baseHeight + $rand->nextBoundedInt($this->extraRandomHeight + 1);
        }

        /**
         * Comprueba que haya espacio suficiente para crecer (radio 1, 2 niveles por encima).
         *
         * @param ChunkManager $level
         * @param Random       $rand
         * @param Vector3      $position
         * @param int          $height
         * @return bool
         */
        protected function ensureGrowable(ChunkManager $level, Random $rand, Vector3 $position, int $height) : bool{
                if($position->y >= 1 and $position->y + $height + 1 <= 256){
                        for($j = $position->y; $j <= $position->y + 1 + $height; ++$j){
                                $k = 1;
                                if($j === $position->y){
                                        $k = 0;
                                }
                                if($j >= $position->y + 1 + $height - 2){
                                        $k = 2;
                                }
                                for($l = $position->x - $k; $l <= $position->x + $k; ++$l){
                                        for($i1 = $position->z - $k; $i1 <= $position->z + $k; ++$i1){
                                                if($j < 0 or $j >= 256){
                                                        return false;
                                                }
                                                if(!$this->canGrowInto($level->getBlockIdAt($l, $j, $i1))){
                                                        return false;
                                                }
                                        }
                                }
                        }
                        $down = $level->getBlockIdAt($position->x, $position->y - 1, $position->z);
                        if($down === Block::GRASS or $down === Block::DIRT or $down === Block::FARMLAND){
                                return true;
                        }
                }
                return false;
        }

        /**
         * Indica si el árbol puede crecer "dentro" del bloque dado.
         * Equivalente a canGrowInto de Nukkit.
         *
         * @param int $blockId
         * @return bool
         */
        protected function canGrowInto(int $blockId) : bool{
                return $blockId === Block::AIR
                        or $blockId === Block::LEAVES
                        or $blockId === Block::VINE
                        or $blockId === Block::LEAVES2
                        or $blockId === Block::LOG
                        or $blockId === Block::LOG2
                        or $blockId === Block::SNOW_LAYER;
        }

        /**
         * Coloca un bloque y notifica al ChunkManager.
         * Equivalente a setBlockAndNotifyAdequately de Nukkit.
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         * @param int          $blockId
         * @param int          $meta
         */
        protected function setBlockAndNotifyAdequately(ChunkManager $level, Vector3 $pos, int $blockId, int $meta = 0){
                $level->setBlockIdAt($pos->x, $pos->y, $pos->z, $blockId);
                $level->setBlockDataAt($pos->x, $pos->y, $pos->z, $meta);
        }

        /**
         * Pone suelo de dirt bajo el árbol (sustituye grass).
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         */
        protected function setDirtAt(ChunkManager $level, Vector3 $pos){
                $this->setBlockAndNotifyAdequately($level, $pos, Block::DIRT, 0);
        }

        /**
         * Capa estricta de hojas: cuadrado completo de lado (2*radius+1).
         *
         * @param ChunkManager $level
         * @param Vector3      $pos   Centro de la capa (Y)
         * @param int          $radius  Radio (1 = 3x3, 2 = 5x5)
         */
        protected function growLeavesLayerStrict(ChunkManager $level, Vector3 $pos, int $radius){
                $j1 = $radius;
                for($k1 = $pos->x - $j1; $k1 <= $pos->x + $j1; ++$k1){
                        for($i2 = $pos->z - $j1; $i2 <= $pos->z + $j1; ++$i2){
                                $id = $level->getBlockIdAt($k1, $pos->y, $i2);
                                if($id === Block::AIR or $id === Block::LEAVES or $id === Block::VINE){
                                        $this->setBlockAndNotifyAdequately($level, new Vector3($k1, $pos->y, $i2), Block::LEAVES, $this->leavesMeta);
                                }
                        }
                }
        }

        /**
         * Capa de hojas "no estricta": cuadrado con esquinas opcionales.
         *
         * @param ChunkManager $level
         * @param Vector3      $pos   Centro de la capa (Y)
         * @param int          $radius Radio (1 = 3x3, 2 = 5x5)
         */
        protected function growLeavesLayer(ChunkManager $level, Vector3 $pos, int $radius){
                $j1 = $radius;
                for($k1 = $pos->x - $j1; $k1 <= $pos->x + $j1; ++$k1){
                        for($i2 = $pos->z - $j1; $i2 <= $pos->z + $j1; ++$i2){
                                $l1 = abs($k1 - $pos->x);
                                $j2 = abs($i2 - $pos->z);
                                if($l1 + $j2 <= $j1){
                                        $id = $level->getBlockIdAt($k1, $pos->y, $i2);
                                        if($id === Block::AIR or $id === Block::LEAVES or $id === Block::VINE){
                                                $this->setBlockAndNotifyAdequately($level, new Vector3($k1, $pos->y, $i2), Block::LEAVES, $this->leavesMeta);
                                        }
                                }
                        }
                }
        }
}
