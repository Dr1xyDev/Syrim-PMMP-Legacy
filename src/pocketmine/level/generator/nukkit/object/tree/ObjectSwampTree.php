<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectSwampTree
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class ObjectSwampTree extends TreeGenerator {

    /** @var int */
    private $metaWood = Wood::OAK;
    /** @var int */
    private $metaLeaves = Leaves::OAK;

    public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool {
        $px = (int) $position->x;
        $py = (int) $position->y;
        $pz = (int) $position->z;

        $i = $rand->nextBoundedInt(4) + 5;
        $flag = true;

        if($py >= 1 && $py + $i + 1 <= 256) {
            for($j = $py; $j <= $py + 1 + $i; ++$j) {
                $k = 1;
                if($j === $py) $k = 0;
                if($j >= $py + 1 + $i - 2) $k = 3;

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

            if(!$flag) return false;

            $block = $level->getBlockIdAt($px, $py - 1, $pz);
            if(($block === Block::GRASS || $block === Block::DIRT) && $py < 256 - $i - 1) {
                $this->setDirtAt($level, new Vector3($px, $py - 1, $pz));

                // Capa de hojas
                for($k1 = $py - 3 + $i; $k1 <= $py + $i; ++$k1) {
                    $j2 = $k1 - ($py + $i);
                    $l2 = 2 - (int)($j2 / 2);

                    for($j3 = $px - $l2; $j3 <= $px + $l2; ++$j3) {
                        $k3 = $j3 - $px;
                        for($i4 = $pz - $l2; $i4 <= $pz + $l2; ++$i4) {
                            $j1 = $i4 - $pz;
                            if(abs($k3) !== $l2 || abs($j1) !== $l2 || ($rand->nextBoundedInt(2) !== 0 && $j2 !== 0)) {
                                $id = $level->getBlockIdAt($j3, $k1, $i4);
                                if($id === Block::AIR || $id === Block::LEAVES || $id === Block::VINE) {
                                    $level->setBlockIdAt($j3, $k1, $i4, Block::LEAVES);
                                    $level->setBlockDataAt($j3, $k1, $i4, $this->metaLeaves);
                                }
                            }
                        }
                    }
                }

                // Tronco
                for($l1 = 0; $l1 < $i; ++$l1) {
                    $id = $level->getBlockIdAt($px, $py + $l1, $pz);
                    if($id === Block::AIR || $id === Block::LEAVES || $id === Block::WATER || $id === Block::STILL_WATER) {
                        $level->setBlockIdAt($px, $py + $l1, $pz, Block::LOG);
                        $level->setBlockDataAt($px, $py + $l1, $pz, $this->metaWood);
                    }
                }

                // Vines colgantes
                for($i2 = $py - 3 + $i; $i2 <= $py + $i; ++$i2) {
                    $k2 = $i2 - ($py + $i);
                    $i3 = 2 - (int)($k2 / 2);
                    for($l3 = $px - $i3; $l3 <= $px + $i3; ++$l3) {
                        for($j4 = $pz - $i3; $j4 <= $pz + $i3; ++$j4) {
                            if($level->getBlockIdAt($l3, $i2, $j4) === Block::LEAVES) {
                                if($rand->nextBoundedInt(4) === 0 && $level->getBlockIdAt($l3 - 1, $i2, $j4) === Block::AIR) {
                                    $this->addHangingVine($level, $l3 - 1, $i2, $j4, 8);
                                }
                                if($rand->nextBoundedInt(4) === 0 && $level->getBlockIdAt($l3 + 1, $i2, $j4) === Block::AIR) {
                                    $this->addHangingVine($level, $l3 + 1, $i2, $j4, 2);
                                }
                                if($rand->nextBoundedInt(4) === 0 && $level->getBlockIdAt($l3, $i2, $j4 - 1) === Block::AIR) {
                                    $this->addHangingVine($level, $l3, $i2, $j4 - 1, 1);
                                }
                                if($rand->nextBoundedInt(4) === 0 && $level->getBlockIdAt($l3, $i2, $j4 + 1) === Block::AIR) {
                                    $this->addHangingVine($level, $l3, $i2, $j4 + 1, 4);
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

    private function addVine(ChunkManager $level, int $x, int $y, int $z, int $meta) {
        $level->setBlockIdAt($x, $y, $z, Block::VINE);
        $level->setBlockDataAt($x, $y, $z, $meta);
    }

    private function addHangingVine(ChunkManager $level, int $x, int $y, int $z, int $meta) {
        $this->addVine($level, $x, $y, $z, $meta);
        $i = 4;
        --$y;
        while($i > 0 && $level->getBlockIdAt($x, $y, $z) === Block::AIR) {
            $this->addVine($level, $x, $y, $z, $meta);
            --$y;
            --$i;
        }
    }
}
