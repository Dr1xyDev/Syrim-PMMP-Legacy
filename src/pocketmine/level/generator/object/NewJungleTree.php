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
 *  Nota : Port de NewJungleTree de Nukkit (Java) a PHP/Syrim.
 *         Es la versión "mejorada" del JungleTree original de PMMP:
 *         tronco de 1x1 con vines colgantes y, a veces, vainas de cacao.
 */

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class NewJungleTree extends Tree{

        /** @var int Altura mínima del tronco */
        private $minTreeHeight;

        /** @var int Metadata del tronco (Wood::JUNGLE = 3) */
        private $metaWood = Wood::JUNGLE;

        /** @var int Metadata de las hojas (Leaves::JUNGLE = 3) */
        private $metaLeaves = Leaves::JUNGLE;

        /**
         * NewJungleTree constructor.
         *
         * Syrim: NO se llama a parent::__construct() porque la clase base
         * Tree es abstracta y no define constructor. Las propiedades
         * públicas ($trunkBlock, $leafBlock, $type, $treeHeight) ya están
         * inicializadas inline en la declaración de Tree, así que se
         * sobreescriben directamente aquí. Igual que OakTree/JungleTree.
         *
         * @param int $minTreeHeight
         */
        public function __construct(int $minTreeHeight = 4){
                $this->trunkBlock = Block::LOG;
                $this->leafBlock = Block::LEAVES;
                $this->type = Wood::JUNGLE;
                $this->minTreeHeight = $minTreeHeight;
        }

        public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
                // Se confía en el check interno de generate()
                return true;
        }

        /**
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         * @param Random       $rand
         * @return bool
         */
        public function placeObject(ChunkManager $level, $x, $y, $z, Random $rand){
                $position = new Vector3($x, $y, $z);
                return $this->generate($level, $rand, $position);
        }

        /**
         * Genera el árbol completo.
         *
         * @param ChunkManager $level
         * @param Random       $rand
         * @param Vector3      $position
         * @return bool
         */
        public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool{
                $px = (int) $position->x;
                $py = (int) $position->y;
                $pz = (int) $position->z;

                $i = $rand->nextBoundedInt(3) + $this->minTreeHeight;
                $flag = true;

                if($py >= 1 and $py + $i + 1 <= 256){
                        // Validación de espacio
                        for($j = $py; $j <= $py + 1 + $i; ++$j){
                                $k = 1;
                                if($j === $py) $k = 0;
                                if($j >= $py + 1 + $i - 2) $k = 2;

                                for($l = $px - $k; $l <= $px + $k and $flag; ++$l){
                                        for($i1 = $pz - $k; $i1 <= $pz + $k and $flag; ++$i1){
                                                if($j >= 0 and $j < 256){
                                                        if(!$this->canGrowInto($level->getBlockIdAt($l, $j, $i1))){
                                                                $flag = false;
                                                        }
                                                }else{
                                                        $flag = false;
                                                }
                                        }
                                }
                        }

                        if(!$flag){
                                return false;
                        }

                        $downBlock = $level->getBlockIdAt($px, $py - 1, $pz);
                        if(($downBlock === Block::GRASS or $downBlock === Block::DIRT or $downBlock === Block::FARMLAND) and $py < 256 - $i - 1){
                                // Poner dirt bajo el tronco
                                $level->setBlockIdAt($px, $py - 1, $pz, Block::DIRT);

                                // Capa de hojas superior
                                for($i3 = $py - 3 + $i; $i3 <= $py + $i; ++$i3){
                                        $i4 = $i3 - ($py + $i);
                                        $j1 = 1 - $i4 / 2;

                                        for($k1 = $px - $j1; $k1 <= $px + $j1; ++$k1){
                                                $l1 = $k1 - $px;
                                                for($i2 = $pz - $j1; $i2 <= $pz + $j1; ++$i2){
                                                        $j2 = $i2 - $pz;
                                                        if(abs($l1) !== $j1 or abs($j2) !== $j1 or ($rand->nextBoundedInt(2) !== 0 and $i4 !== 0)){
                                                                $id = $level->getBlockIdAt($k1, $i3, $i2);
                                                                if($id === Block::AIR or $id === Block::LEAVES or $id === Block::VINE){
                                                                        $level->setBlockIdAt($k1, $i3, $i2, Block::LEAVES);
                                                                        $level->setBlockDataAt($k1, $i3, $i2, $this->metaLeaves);
                                                                }
                                                        }
                                                }
                                        }
                                }

                                // Tronco
                                for($j3 = 0; $j3 < $i; ++$j3){
                                        $id = $level->getBlockIdAt($px, $py + $j3, $pz);
                                        if($id === Block::AIR or $id === Block::LEAVES or $id === Block::VINE){
                                                $level->setBlockIdAt($px, $py + $j3, $pz, Block::LOG);
                                                $level->setBlockDataAt($px, $py + $j3, $pz, $this->metaWood);

                                                if($j3 > 0){
                                                        // Vines en las 4 caras del tronco
                                                        if($rand->nextBoundedInt(3) > 0 and $level->getBlockIdAt($px - 1, $py + $j3, $pz) === Block::AIR){
                                                                $this->addVine($level, $px - 1, $py + $j3, $pz, 8);
                                                        }
                                                        if($rand->nextBoundedInt(3) > 0 and $level->getBlockIdAt($px + 1, $py + $j3, $pz) === Block::AIR){
                                                                $this->addVine($level, $px + 1, $py + $j3, $pz, 2);
                                                        }
                                                        if($rand->nextBoundedInt(3) > 0 and $level->getBlockIdAt($px, $py + $j3, $pz - 1) === Block::AIR){
                                                                $this->addVine($level, $px, $py + $j3, $pz - 1, 1);
                                                        }
                                                        if($rand->nextBoundedInt(3) > 0 and $level->getBlockIdAt($px, $py + $j3, $pz + 1) === Block::AIR){
                                                                $this->addVine($level, $px, $py + $j3, $pz + 1, 4);
                                                        }
                                                }
                                        }
                                }

                                // Vines colgantes bajo las hojas
                                for($k3 = $py - 3 + $i; $k3 <= $py + $i; ++$k3){
                                        $j4 = $k3 - ($py + $i);
                                        $k4 = 2 - $j4 / 2;
                                        for($l4 = $px - $k4; $l4 <= $px + $k4; ++$l4){
                                                for($i5 = $pz - $k4; $i5 <= $pz + $k4; ++$i5){
                                                        if($level->getBlockIdAt($l4, $k3, $i5) === Block::LEAVES){
                                                                if($rand->nextBoundedInt(4) === 0 and $level->getBlockIdAt($l4 - 1, $k3, $i5) === Block::AIR){
                                                                        $this->addHangingVine($level, $l4 - 1, $k3, $i5, 8);
                                                                }
                                                                if($rand->nextBoundedInt(4) === 0 and $level->getBlockIdAt($l4 + 1, $k3, $i5) === Block::AIR){
                                                                        $this->addHangingVine($level, $l4 + 1, $k3, $i5, 2);
                                                                }
                                                                if($rand->nextBoundedInt(4) === 0 and $level->getBlockIdAt($l4, $k3, $i5 - 1) === Block::AIR){
                                                                        $this->addHangingVine($level, $l4, $k3, $i5 - 1, 1);
                                                                }
                                                                if($rand->nextBoundedInt(4) === 0 and $level->getBlockIdAt($l4, $k3, $i5 + 1) === Block::AIR){
                                                                        $this->addHangingVine($level, $l4, $k3, $i5 + 1, 4);
                                                                }
                                                        }
                                                }
                                        }
                                }

                                // Vainas de cacao (raras)
                                if($rand->nextBoundedInt(5) === 0 and $i > 5){
                                        for($l3 = 0; $l3 < 2; ++$l3){
                                                // Recorrer las 4 caras horizontales
                                                $faces = [[-1, 0], [1, 0], [0, -1], [0, 1]];
                                                foreach($faces as $face){
                                                        if($rand->nextBoundedInt(4 - $l3) === 0){
                                                                // side index: -X=4, +X=5, -Z=2, +Z=3
                                                                $sideIdx = ($face[0] === -1) ? 4 : (($face[0] === 1) ? 5 : (($face[1] === -1) ? 2 : 3));
                                                                $this->placeCocoa($level, $rand->nextBoundedInt(3), $px + $face[0], $py + $i - 5 + $l3, $pz + $face[1], $sideIdx);
                                                        }
                                                }
                                        }
                                }

                                return true;
                        }
                }
                return false;
        }

        /**
         * Indica si el árbol puede crecer "dentro" del bloque dado.
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
         * Coloca una vine en la posición indicada.
         *
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         * @param int          $meta
         */
        private function addVine(ChunkManager $level, int $x, int $y, int $z, int $meta){
                $level->setBlockIdAt($x, $y, $z, Block::VINE);
                $level->setBlockDataAt($x, $y, $z, $meta);
        }

        /**
         * Coloca una vine colgante: la extiende hacia abajo hasta 4 bloques
         * mientras haya aire.
         *
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         * @param int          $meta
         */
        private function addHangingVine(ChunkManager $level, int $x, int $y, int $z, int $meta){
                $this->addVine($level, $x, $y, $z, $meta);
                $remaining = 4;
                --$y;
                while($remaining > 0 and $level->getBlockIdAt($x, $y, $z) === Block::AIR){
                        $this->addVine($level, $x, $y, $z, $meta);
                        --$y;
                        --$remaining;
                }
        }

        /**
         * Coloca una vaina de cacao con la edad y orientación dadas.
         *
         * @param ChunkManager $level
         * @param int          $age   0..2 (estado de crecimiento)
         * @param int          $x
         * @param int          $y
         * @param int          $z
         * @param int          $side  2=S, 3=N, 4=E, 5=W (índice BlockFace de Nukkit)
         */
        private function placeCocoa(ChunkManager $level, int $age, int $x, int $y, int $z, int $side){
                $meta = $this->getCocoaMeta($age, $side);
                $level->setBlockIdAt($x, $y, $z, Block::COCOA_BLOCK);
                $level->setBlockDataAt($x, $y, $z, $meta);
        }

        /**
         * Calcula la metadata del bloque de cacao según edad y orientación.
         *
         * Layout del meta (4 bits):
         *   bits 0-1: dirección (0=North, 1=East, 2=South, 3=West)
         *   bits 2-3: edad (0..2)
         *
         * Mapeo Nukkit side -> Syrim dir:
         *   side 2 (NORTH) -> dir 2 (South, mirando al norte)
         *   side 3 (SOUTH) -> dir 0 (North, mirando al sur)
         *   side 4 (EAST)  -> dir 1
         *   side 5 (WEST)  -> dir 3
         *
         * @param int $age
         * @param int $side
         * @return int
         */
        private function getCocoaMeta(int $age, int $side) : int{
                $dir = 0;
                switch($side){
                        case 4: $dir = 0; break; // WEST  -> 0
                        case 2: $dir = 1; break; // NORTH -> 1
                        case 5: $dir = 2; break; // EAST  -> 2
                        case 3: $dir = 3; break; // SOUTH -> 3
                }
                return ($age << 2) | $dir;
        }
}
