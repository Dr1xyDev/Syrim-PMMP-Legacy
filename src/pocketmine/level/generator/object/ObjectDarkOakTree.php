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
 *  Nota : Port de ObjectDarkOakTree de Nukkit (Java) a PHP/Syrim.
 *         Genera un "dark oak tree" 2x2 vanilla con tronco cuádruple,
 *         copa de hojas robusta y, a veces, ramas extra con hojas
 *         adicionales.
 */

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves2;
use pocketmine\block\Wood2;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class ObjectDarkOakTree extends Tree{

        /** @var int Metadata del tronco (Wood2::DARK_OAK = 1) */
        private $metaWood = Wood2::DARK_OAK;

        /** @var int Metadata de las hojas (Leaves2::DARK_OAK = 1) */
        private $metaLeaves = Leaves2::DARK_OAK;

        public function __construct(){
                // NO se llama a parent::__construct() porque Tree no define
                // constructor (las propiedades publicas ya estan inicializadas).
                $this->trunkBlock = Block::LOG2;
                $this->leafBlock  = Block::LEAVES2;
                $this->type       = Wood2::DARK_OAK;
                $this->treeHeight = 8;
        }

        public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
                // Syrim: el check real lo hace placeTreeOfHeight() dentro de generate().
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
                $i = $rand->nextBoundedInt(3) + $rand->nextBoundedInt(2) + 6;
                $j = (int) $position->x;
                $k = (int) $position->y;
                $l = (int) $position->z;

                if($k >= 1 and $k + $i + 1 < 256){
                        $blockpos = new Vector3($j, $k - 1, $l);
                        $block = $level->getBlockIdAt($j, $k - 1, $l);

                        if($block !== Block::GRASS and $block !== Block::DIRT){
                                return false;
                        }elseif(!$this->placeTreeOfHeight($level, $position, $i)){
                                return false;
                        }else{
                                // Poner dirt bajo las 4 columnas del tronco 2x2
                                $this->setDirtAt($level, $blockpos);
                                $this->setDirtAt($level, new Vector3($j + 1, $k - 1, $l));
                                $this->setDirtAt($level, new Vector3($j, $k - 1, $l + 1));
                                $this->setDirtAt($level, new Vector3($j + 1, $k - 1, $l + 1));

                                // Dirección horizontal aleatoria
                                $face = $rand->nextBoundedInt(4); // 0=N, 1=E, 2=S, 3=W
                                $i1 = $i - $rand->nextBoundedInt(4);
                                $j1 = 2 - $rand->nextBoundedInt(3);
                                $k1 = $j;
                                $l1 = $l;
                                $i2 = $k + $i - 1;

                                // Tronco 2x2 (con curva opcional)
                                for($j2 = 0; $j2 < $i; ++$j2){
                                        if($j2 >= $i1 and $j1 > 0){
                                                // Avanzar según la dirección elegida
                                                switch($face){
                                                        case 0: --$l1; break; // North (-Z)
                                                        case 1: ++$k1; break; // East (+X)
                                                        case 2: ++$l1; break; // South (+Z)
                                                        case 3: --$k1; break; // West (-X)
                                                }
                                                --$j1;
                                        }

                                        $k2 = $k + $j2;
                                        $bpos = new Vector3($k1, $k2, $l1);
                                        $material = $level->getBlockIdAt($k1, $k2, $l1);

                                        // Syrim BUGFIX: forzar siempre las 4 columnas del tronco 2x2.
                                        // Antes solo se generaba si la posición central era AIR/LEAVES,
                                        // lo que causaba troncos partidos cuando la posición ya tenía
                                        // un bloque de rama o similar.
                                        if($material === Block::AIR or $material === Block::LEAVES or $material === Block::LEAVES2 or $material === Block::LOG or $material === Block::LOG2){
                                                $this->placeLogForce($level, $bpos);
                                                $this->placeLogForce($level, new Vector3($k1 + 1, $k2, $l1));
                                                $this->placeLogForce($level, new Vector3($k1, $k2, $l1 + 1));
                                                $this->placeLogForce($level, new Vector3($k1 + 1, $k2, $l1 + 1));
                                        }
                                }

                                // Capa de hojas superior (-1 y +1)
                                for($i3 = -2; $i3 <= 0; ++$i3){
                                        for($l3 = -2; $l3 <= 0; ++$l3){
                                                $k4 = -1;
                                                $this->placeLeafAt($level, $k1 + $i3, $i2 + $k4, $l1 + $l3);
                                                $this->placeLeafAt($level, 1 + $k1 - $i3, $i2 + $k4, $l1 + $l3);
                                                $this->placeLeafAt($level, $k1 + $i3, $i2 + $k4, 1 + $l1 - $l3);
                                                $this->placeLeafAt($level, 1 + $k1 - $i3, $i2 + $k4, 1 + $l1 - $l3);

                                                if(($i3 > -2 or $l3 > -1) and ($i3 !== -1 or $l3 !== -2)){
                                                        $k4 = 1;
                                                        $this->placeLeafAt($level, $k1 + $i3, $i2 + $k4, $l1 + $l3);
                                                        $this->placeLeafAt($level, 1 + $k1 - $i3, $i2 + $k4, $l1 + $l3);
                                                        $this->placeLeafAt($level, $k1 + $i3, $i2 + $k4, 1 + $l1 - $l3);
                                                        $this->placeLeafAt($level, 1 + $k1 - $i3, $i2 + $k4, 1 + $l1 - $l3);
                                                }
                                        }
                                }

                                // 4 hojas en la cima (50% probabilidad)
                                if($rand->nextBoundedInt(2) === 0){
                                        $this->placeLeafAt($level, $k1, $i2 + 2, $l1);
                                        $this->placeLeafAt($level, $k1 + 1, $i2 + 2, $l1);
                                        $this->placeLeafAt($level, $k1 + 1, $i2 + 2, $l1 + 1);
                                        $this->placeLeafAt($level, $k1, $i2 + 2, $l1 + 1);
                                }

                                // Capa de hojas intermedia (cuadrado 8x8 con esquinas recortadas)
                                for($j3 = -3; $j3 <= 4; ++$j3){
                                        for($i4 = -3; $i4 <= 4; ++$i4){
                                                if(($j3 !== -3 or $i4 !== -3)
                                                        and ($j3 !== -3 or $i4 !== 4)
                                                        and ($j3 !== 4 or $i4 !== -3)
                                                        and ($j3 !== 4 or $i4 !== 4)
                                                        and (abs($j3) < 3 or abs($i4) < 3)){
                                                        $this->placeLeafAt($level, $k1 + $j3, $i2, $l1 + $i4);
                                                }
                                        }
                                }

                                // Ramas adicionales con hojas alrededor (a veces)
                                for($k3 = -1; $k3 <= 2; ++$k3){
                                        for($j4 = -1; $j4 <= 2; ++$j4){
                                                if(($k3 < 0 or $k3 > 1 or $j4 < 0 or $j4 > 1) and $rand->nextBoundedInt(3) <= 0){
                                                        $l4 = $rand->nextBoundedInt(3) + 2;

                                                        for($i5 = 0; $i5 < $l4; ++$i5){
                                                                $this->placeLogAt($level, new Vector3($j + $k3, $i2 - $i5 - 1, $l + $j4));
                                                        }

                                                        for($j5 = -1; $j5 <= 1; ++$j5){
                                                                for($l2 = -1; $l2 <= 1; ++$l2){
                                                                        $this->placeLeafAt($level, $k1 + $k3 + $j5, $i2, $l1 + $j4 + $l2);
                                                                }
                                                        }

                                                        for($k5 = -2; $k5 <= 2; ++$k5){
                                                                for($l5 = -2; $l5 <= 2; ++$l5){
                                                                        if(abs($k5) !== 2 or abs($l5) !== 2){
                                                                                $this->placeLeafAt($level, $k1 + $k3 + $k5, $i2 - 1, $l1 + $j4 + $l5);
                                                                        }
                                                                }
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
         * Comprueba que haya espacio suficiente para crecer.
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         * @param int          $height
         * @return bool
         */
        private function placeTreeOfHeight(ChunkManager $level, Vector3 $pos, int $height) : bool{
                $i = (int) $pos->x;
                $j = (int) $pos->y;
                $k = (int) $pos->z;

                for($l = 0; $l <= $height + 1; ++$l){
                        $i1 = 1;
                        if($l === 0) $i1 = 0;
                        if($l >= $height - 1) $i1 = 2;

                        for($j1 = -$i1; $j1 <= $i1; ++$j1){
                                for($k1 = -$i1; $k1 <= $i1; ++$k1){
                                        if(!$this->canGrowInto($level->getBlockIdAt($i + $j1, $j + $l, $k + $k1))){
                                                return false;
                                        }
                                }
                        }
                }
                return true;
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
         * Coloca un bloque de tronco (Wood2 DARK_OAK).
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         */
        private function placeLogAt(ChunkManager $level, Vector3 $pos){
                if($this->canGrowInto($level->getBlockIdAt($pos->x, $pos->y, $pos->z))){
                        $level->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::LOG2);
                        $level->setBlockDataAt($pos->x, $pos->y, $pos->z, $this->metaWood);
                }
        }

        /**
         * Syrim: fuerza la colocación del tronco sin verificar canGrowInto.
         * Se usa para el tronco 2x2 principal, que debe generarse siempre.
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         */
        private function placeLogForce(ChunkManager $level, Vector3 $pos){
                $level->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::LOG2);
                $level->setBlockDataAt($pos->x, $pos->y, $pos->z, $this->metaWood);
        }

        /**
         * Coloca una hoja (Leaves2 DARK_OAK) sólo si la posición es aire.
         *
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         */
        private function placeLeafAt(ChunkManager $level, int $x, int $y, int $z){
                $material = $level->getBlockIdAt($x, $y, $z);
                if($material === Block::AIR){
                        $level->setBlockIdAt($x, $y, $z, Block::LEAVES2);
                        $level->setBlockDataAt($x, $y, $z, $this->metaLeaves);
                }
        }

        /**
         * Coloca dirt bajo el tronco (compatibilidad con la clase base Tree).
         *
         * @param ChunkManager $level
         * @param Vector3      $pos
         */
        private function setDirtAt(ChunkManager $level, Vector3 $pos){
                $level->setBlockIdAt($pos->x, $pos->y, $pos->z, Block::DIRT);
                $level->setBlockDataAt($pos->x, $pos->y, $pos->z, 0);
        }
}
