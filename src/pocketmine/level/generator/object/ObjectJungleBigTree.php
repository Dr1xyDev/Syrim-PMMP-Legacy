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
 *  Nota : Port exacto de ObjectJungleBigTree de Nukkit (Java) a PHP/Syrim.
 *         Genera un "2x2 jungle tree": tronco de 4 bloques, con
 *         ramas radiales que sostienen coronas de hojas, y vines.
 *
 *  Syrim BUGFIX: orden de generacion tronco->ramas->hojas->corona.
 *  Syrim BUGFIX: tronco 2x2 siempre se coloca (sin canGrowInto).
 *  Syrim BUGFIX: hojas no sobrescriben LOG (placeLeafIfAir).
 *  Syrim BUGFIX: copa circular euclidiana con domo (sin esquinas).
 *  Syrim BUGFIX: luz se recalcula en cada bloque colocado.
 *  Syrim: altura minima vanilla 12 bloques.
 */

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class ObjectJungleBigTree extends HugeTreesGenerator{

        /**
         * ObjectJungleBigTree constructor.
         *
         * @param int $baseHeightIn
         * @param int $extraRandomHeight
         * @param int $woodMetadata    Pasar Wood::JUNGLE (=3)
         * @param int $leavesMetadata  Pasar Leaves::JUNGLE (=3)
         */
        public function __construct(int $baseHeightIn, int $extraRandomHeight, int $woodMetadata, int $leavesMetadata){
                parent::__construct($baseHeightIn, $extraRandomHeight, $woodMetadata, $leavesMetadata);
        }

        /**
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         * @param Random       $random
         * @return bool
         */
        public function placeObject(ChunkManager $level, $x, $y, $z, Random $random){
                $position = new Vector3($x, $y, $z);
                return $this->generate($level, $random, $position);
        }

        public function canPlaceObject(ChunkManager $level, $x, $y, $z, Random $random){
                return true;
        }

        /**
         * Genera el árbol completo.
         *
         * Syrim: orden de generación:
         *   1. Tronco 2x2 (siempre, sin canGrowInto)
         *   2. Ramas laterales
         *   3. Hojas de ramas (solo en la punta, placeLeafIfAir)
         *   4. Corona superior de hojas (circular euclidiana con domo)
         *
         * @param ChunkManager $level
         * @param Random       $rand
         * @param Vector3      $position
         * @return bool
         */
        public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool{
                $height = $this->getHeight($rand);

                // Syrim: garantizar altura mínima vanilla (12 bloques)
                if($height < 12){
                        $height = 12 + $rand->nextBoundedInt(13); // 12..24
                }

                if(!$this->ensureGrowable($level, $rand, $position, $height)){
                        return false;
                }

                // ============ PASO 1: TRONCO 2x2 (siempre, sin canGrowInto) ============
                for($i2 = 0; $i2 < $height; ++$i2){
                        $y = $position->y + $i2;
                        $x = $position->x;
                        $z = $position->z;

                        // Columna principal (NW)
                        $this->setBlockAndNotifyAdequately($level, new Vector3($x, $y, $z), Block::LOG, $this->woodMeta);

                        if($i2 < $height - 1){
                                // Columna NE
                                $this->setBlockAndNotifyAdequately($level, new Vector3($x + 1, $y, $z), Block::LOG, $this->woodMeta);
                                // Columna SW
                                $this->setBlockAndNotifyAdequately($level, new Vector3($x, $y, $z + 1), Block::LOG, $this->woodMeta);
                                // Columna SE
                                $this->setBlockAndNotifyAdequately($level, new Vector3($x + 1, $y, $z + 1), Block::LOG, $this->woodMeta);
                        }

                        // Vines en las caras externas del tronco
                        if($i2 > 0){
                                $this->placeVine($level, $rand, new Vector3($x - 1, $y, $z), 8);
                                $this->placeVine($level, $rand, new Vector3($x, $y, $z - 1), 1);
                                $this->placeVine($level, $rand, new Vector3($x + 2, $y, $z), 2);
                                $this->placeVine($level, $rand, new Vector3($x + 1, $y, $z - 1), 1);
                                $this->placeVine($level, $rand, new Vector3($x - 1, $y, $z + 1), 8);
                                $this->placeVine($level, $rand, new Vector3($x, $y, $z + 2), 4);
                                $this->placeVine($level, $rand, new Vector3($x + 2, $y, $z + 1), 2);
                                $this->placeVine($level, $rand, new Vector3($x + 1, $y, $z + 2), 4);
                        }
                }

                // ============ PASO 2: RAMAS LATERALES ============
                $startJ = (int) $position->y + $height - 2 - $rand->nextBoundedInt(4);
                $endJ   = (int) $position->y + ($height >> 1);
                $branchTips = [];
                for($j = $startJ; $j > $endJ; $j -= 2 + $rand->nextBoundedInt(4)){
                        $f = $rand->nextFloat() * (M_PI * 2.0);

                        // Rama diagonal de 5 bloques
                        $lastK = (int) $position->x;
                        $lastL = (int) $position->z;
                        for($i1 = 0; $i1 < 5; ++$i1){
                                $k = (int) ($position->x + (1.5 + cos($f) * (float) $i1));
                                $l = (int) ($position->z + (1.5 + sin($f) * (float) $i1));
                                $branchY = $j - 3 + ($i1 >> 1);

                                // Solo colocar la rama si está FUERA del tronco 2x2
                                if(!($k >= $position->x and $k <= $position->x + 1
                                        and $l >= $position->z and $l <= $position->z + 1)){
                                        $this->setBlockAndNotifyAdequately($level, new Vector3($k, $branchY, $l), Block::LOG, $this->woodMeta);
                                }
                                $lastK = $k;
                                $lastL = $l;
                        }

                        $branchTips[] = [$lastK, $j, $lastL];
                }

                // ============ PASO 3: HOJAS DE RAMAS ============
                foreach($branchTips as $tip){
                        $bk = $tip[0];
                        $by = $tip[1];
                        $bl = $tip[2];
                        // Capa de hojas 3x3 centrada en la punta de la rama
                        for($dx = -1; $dx <= 1; ++$dx){
                                for($dz = -1; $dz <= 1; ++$dz){
                                        $this->placeLeafIfAir($level, $bk + $dx, $by, $bl + $dz);
                                }
                        }
                        // 1 capa justo arriba de la punta
                        for($dx = -1; $dx <= 1; ++$dx){
                                for($dz = -1; $dz <= 1; ++$dz){
                                        if(abs($dx) + abs($dz) <= 1){
                                                $this->placeLeafIfAir($level, $bk + $dx, $by + 1, $bl + $dz);
                                        }
                                }
                        }
                }

                // ============ PASO 4: CORONA SUPERIOR DE HOJAS ============
                $crownCenterX = $position->x;
                $crownCenterZ = $position->z;
                $crownY = $position->y + $height;

                // Capa inferior de la corona: círculo radio 5 (Y-2)
                $this->placeLeafCircle($level, $crownCenterX, $crownY - 2, $crownCenterZ, 5);
                // Capa media: círculo radio 4 (Y-1)
                $this->placeLeafCircle($level, $crownCenterX, $crownY - 1, $crownCenterZ, 4);
                // Capa superior: círculo radio 3 (Y)
                $this->placeLeafCircle($level, $crownCenterX, $crownY, $crownCenterZ, 3);
                // Remate del domo: círculo radio 1 (Y+1)
                $this->placeLeafCircle($level, $crownCenterX, $crownY + 1, $crownCenterZ, 1);

                return true;
        }

        /**
         * Syrim: coloca una hoja (LEAVES + metadata) sólo si la posición
         * es AIR, VINE u otra hoja. NO sobrescribe LOG.
         *
         * @param ChunkManager $level
         * @param int          $x
         * @param int          $y
         * @param int          $z
         */
        private function placeLeafIfAir(ChunkManager $level, int $x, int $y, int $z){
                if($y < 0 or $y >= 256) return;
                $id = $level->getBlockIdAt($x, $y, $z);
                if($id === Block::AIR or $id === Block::VINE or $id === Block::LEAVES or $id === Block::LEAVES2){
                        $level->setBlockIdAt($x, $y, $z, Block::LEAVES);
                        $level->setBlockDataAt($x, $y, $z, $this->leavesMeta);

                        if($level instanceof Level){
                                $level->updateAllLight(new Vector3($x, $y, $z));
                        }
                }
        }

        /**
         * Syrim: coloca un círculo de hojas con distancia euclidiana.
         * Sin esquinas, forma circular perfecta.
         *
         * @param ChunkManager $level
         * @param int          $cx
         * @param int          $cy
         * @param int          $cz
         * @param int          $radius
         */
        private function placeLeafCircle(ChunkManager $level, int $cx, int $cy, int $cz, int $radius){
                $radiusSq = $radius * $radius;
                for($dx = -$radius; $dx <= $radius; ++$dx){
                        for($dz = -$radius; $dz <= $radius; ++$dz){
                                $distSq = $dx * $dx + $dz * $dz;
                                if($distSq <= $radiusSq){
                                        $this->placeLeafIfAir($level, $cx + $dx, $cy, $cz + $dz);
                                }
                        }
                }
        }

        /**
         * Coloca una vine en pos si el azar lo permite y la posición es aire.
         *
         * @param ChunkManager $level
         * @param Random       $random
         * @param Vector3      $pos
         * @param int          $meta  Metadata de Vine (north/south/east/west flags)
         */
        private function placeVine(ChunkManager $level, Random $random, Vector3 $pos, int $meta){
                if($random->nextBoundedInt(3) > 0 and $level->getBlockIdAt($pos->x, $pos->y, $pos->z) === Block::AIR){
                        $this->setBlockAndNotifyAdequately($level, $pos, Block::VINE, $meta);
                }
        }
}
