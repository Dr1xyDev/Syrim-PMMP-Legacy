<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectSavannaTree (Acacia)
 * Porteado desde el source exacto de CreeperFace.
 * Árbol de acacia con tronco curvo característico y dos copas de hojas.
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\block\Leaves2;
use pocketmine\block\Wood2;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class ObjectSavannaTree extends TreeGenerator {

    /** @var int */
    private $trunkId = Block::LOG2;
    /** @var int */
    private $trunkMeta = Wood2::ACACIA;
    /** @var int */
    private $leafId = Block::LEAVES2;
    /** @var int */
    private $leafMeta = Leaves2::ACACIA;

    public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool {
        $px = (int) $position->x;
        $py = (int) $position->y;
        $pz = (int) $position->z;

        $i = $rand->nextBoundedInt(3) + $rand->nextBoundedInt(3) + 5;
        $flag = true;

        if($py >= 1 && $py + $i + 1 <= 256) {
            for($j = $py; $j <= $py + 1 + $i; ++$j) {
                $k = 1;
                if($j === $py) $k = 0;
                if($j >= $py + 1 + $i - 2) $k = 2;

                for($l = $px - $k; $l <= $px + $k && $flag; ++$l) {
                    for($i1 = $pz - $k; $i1 <= $pz + $k && $flag; ++$i1) {
                        if($j >= 0 && $j < 256) {
                            if(!$this->canGrowInto($level->getBlockIdAt($l, $j, $i1))) {
                                $flag = false;
                            }
                        } else {
                            $flag = false;
                        }
                    }
                }
            }

            if(!$flag) {
                return false;
            }

            $block = $level->getBlockIdAt($px, $py - 1, $pz);
            if(($block === Block::GRASS || $block === Block::DIRT) && $py < 256 - $i - 1) {
                $this->setDirtAt($level, new Vector3($px, $py - 1, $pz));

                // Syrim: BlockFace.Plane.HORIZONTAL.random(rand) — elegir direccion aleatoria
                // 0=N(-Z), 1=E(+X), 2=S(+Z), 3=W(-X)
                $face = $rand->nextBoundedInt(4);
                $faceX = [0, 1, 0, -1][$face];
                $faceZ = [-1, 0, 1, 0][$face];

                $k2 = $i - $rand->nextBoundedInt(4) - 1;
                $l2 = 3 - $rand->nextBoundedInt(3);
                $i3 = $px;
                $j1 = $pz;
                $k1 = 0;

                // Tronco principal con curva
                for($l1 = 0; $l1 < $i; ++$l1) {
                    $i2 = $py + $l1;

                    if($l1 >= $k2 && $l2 > 0) {
                        $i3 += $faceX;
                        $j1 += $faceZ;
                        --$l2;
                    }

                    $material = $level->getBlockIdAt($i3, $i2, $j1);
                    if($material === Block::AIR || $material === Block::LEAVES) {
                        $this->placeLogAt($level, new Vector3($i3, $i2, $j1));
                        $k1 = $i2;
                    }
                }

                // Copa de hojas principal (7x7 sin esquinas)
                $blockpos2 = new Vector3($i3, $k1, $j1);
                for($j3 = -3; $j3 <= 3; ++$j3) {
                    for($i4 = -3; $i4 <= 3; ++$i4) {
                        if(abs($j3) !== 3 || abs($i4) !== 3) {
                            $this->placeLeafAt($level, $blockpos2->add($j3, 0, $i4));
                        }
                    }
                }

                // Capa superior de la copa (3x3)
                $blockpos2 = $blockpos2->add(0, 1, 0);
                for($k3 = -1; $k3 <= 1; ++$k3) {
                    for($j4 = -1; $j4 <= 1; ++$j4) {
                        $this->placeLeafAt($level, $blockpos2->add($k3, 0, $j4));
                    }
                }

                // Extensiones de hojas en cruz (+2 bloques)
                $this->placeLeafAt($level, $blockpos2->add(2, 0, 0));
                $this->placeLeafAt($level, $blockpos2->add(-2, 0, 0));
                $this->placeLeafAt($level, $blockpos2->add(0, 0, 2));
                $this->placeLeafAt($level, $blockpos2->add(0, 0, -2));

                // Segunda rama (si la direccion es diferente a la primera)
                $i3 = $px;
                $j1 = $pz;
                $face1 = $rand->nextBoundedInt(4);
                $face1X = [0, 1, 0, -1][$face1];
                $face1Z = [-1, 0, 1, 0][$face1];

                if($face1 !== $face) {
                    $l3 = $k2 - $rand->nextBoundedInt(2) - 1;
                    $k4 = 1 + $rand->nextBoundedInt(3);
                    $k1 = 0;

                    for($l4 = $l3; $l4 < $i && $k4 > 0; --$k4) {
                        if($l4 >= 1) {
                            $j2 = $py + $l4;
                            $i3 += $face1X;
                            $j1 += $face1Z;

                            $material1 = $level->getBlockIdAt($i3, $j2, $j1);
                            if($material1 === Block::AIR || $material1 === Block::LEAVES) {
                                $this->placeLogAt($level, new Vector3($i3, $j2, $j1));
                                $k1 = $j2;
                            }
                        }
                        ++$l4;
                    }

                    if($k1 > 0) {
                        // Copa de la segunda rama (5x5 sin esquinas)
                        $blockpos3 = new Vector3($i3, $k1, $j1);
                        for($i5 = -2; $i5 <= 2; ++$i5) {
                            for($k5 = -2; $k5 <= 2; ++$k5) {
                                if(abs($i5) !== 2 || abs($k5) !== 2) {
                                    $this->placeLeafAt($level, $blockpos3->add($i5, 0, $k5));
                                }
                            }
                        }

                        // Capa superior de la segunda copa (3x3)
                        $blockpos3 = $blockpos3->add(0, 1, 0);
                        for($j5 = -1; $j5 <= 1; ++$j5) {
                            for($l5 = -1; $l5 <= 1; ++$l5) {
                                $this->placeLeafAt($level, $blockpos3->add($j5, 0, $l5));
                            }
                        }
                    }
                }

                return true;
            }
        }
        return false;
    }

    private function placeLogAt(ChunkManager $level, Vector3 $pos) {
        $level->setBlockIdAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $this->trunkId);
        $level->setBlockDataAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $this->trunkMeta);
    }

    private function placeLeafAt(ChunkManager $level, Vector3 $pos) {
        $material = $level->getBlockIdAt((int) $pos->x, (int) $pos->y, (int) $pos->z);
        if($material === Block::AIR || $material === Block::LEAVES) {
            $level->setBlockIdAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $this->leafId);
            $level->setBlockDataAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $this->leafMeta);
        }
    }
}
