<?php
/*
 * Syrim - Port de Nukkit 1.1.5 BigMushroom
 */

namespace pocketmine\level\generator\nukkit\object\mushroom;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\object\BasicGenerator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class BigMushroom extends BasicGenerator {

    const NORTH_WEST = 1;
    const NORTH = 2;
    const NORTH_EAST = 3;
    const WEST = 4;
    const CENTER = 5;
    const EAST = 6;
    const SOUTH_WEST = 7;
    const SOUTH = 8;
    const SOUTH_EAST = 9;
    const STEM = 10;
    const ALL_INSIDE = 0;
    const ALL_OUTSIDE = 14;
    const ALL_STEM = 15;

    const BROWN = 0;
    const RED = 1;

    /** @var int */
    private $mushroomType;

    public function __construct(int $mushroomType = -1) {
        $this->mushroomType = $mushroomType;
    }

    public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool {
        $block = $this->mushroomType;
        if($block < 0) {
            $block = $rand->nextBoundedInt(2) === 0 ? self::RED : self::BROWN;
        }

        // Syrim: Block::BROWN_MUSHROOM_BLOCK = 99, RED_MUSHROOM_BLOCK = 100
        $mushroomId = $block === 0 ? Block::BROWN_MUSHROOM_BLOCK : Block::RED_MUSHROOM_BLOCK;

        $i = $rand->nextBoundedInt(3) + 4;
        if($rand->nextBoundedInt(12) === 0) {
            $i *= 2;
        }

        $flag = true;

        if($position->y >= 1 && $position->y + $i + 1 < 256) {
            for($j = (int) $position->y; $j <= $position->y + 1 + $i; ++$j) {
                $k = 3;
                if($j <= $position->y + 3) {
                    $k = 0;
                }
                for($l = (int) $position->x - $k; $l <= $position->x + $k && $flag; ++$l) {
                    for($i1 = (int) $position->z - $k; $i1 <= $position->z + $k && $flag; ++$i1) {
                        if($j >= 0 && $j < 256) {
                            $material = $level->getBlockIdAt($l, $j, $i1);
                            if($material !== Block::AIR && $material !== Block::LEAVES) {
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

            $block1 = $level->getBlockIdAt((int) $position->x, (int) $position->y - 1, (int) $position->z);
            if($block1 !== Block::DIRT && $block1 !== Block::GRASS && $block1 !== Block::MYCELIUM) {
                return false;
            }

            $k2 = (int) $position->y + $i;
            if($block === self::RED) {
                $k2 = (int) $position->y + $i - 3;
            }

            for($l2 = $k2; $l2 <= $position->y + $i; ++$l2) {
                $j3 = 1;
                if($l2 < $position->y + $i) {
                    ++$j3;
                }
                if($block === self::BROWN) {
                    $j3 = 3;
                }

                $k3 = (int) $position->x - $j3;
                $l3 = (int) $position->x + $j3;
                $j1 = (int) $position->z - $j3;
                $k1 = (int) $position->z + $j3;

                for($l1 = $k3; $l1 <= $l3; ++$l1) {
                    for($i2 = $j1; $i2 <= $k1; ++$i2) {
                        $j2 = 5;

                        if($l1 === $k3) {
                            --$j2;
                        } elseif($l1 === $l3) {
                            ++$j2;
                        }

                        if($i2 === $j1) {
                            $j2 -= 3;
                        } elseif($i2 === $k1) {
                            $j2 += 3;
                        }

                        $meta = $j2;

                        if($block === self::BROWN || $l2 < $position->y + $i) {
                            if(($l1 === $k3 || $l1 === $l3) && ($i2 === $j1 || $i2 === $k1)) {
                                continue;
                            }
                            if($l1 === $position->x - ($j3 - 1) && $i2 === $j1) $meta = self::NORTH_WEST;
                            if($l1 === $k3 && $i2 === $position->z - ($j3 - 1)) $meta = self::NORTH_WEST;
                            if($l1 === $position->x + ($j3 - 1) && $i2 === $j1) $meta = self::NORTH_EAST;
                            if($l1 === $l3 && $i2 === $position->z - ($j3 - 1)) $meta = self::NORTH_EAST;
                            if($l1 === $position->x - ($j3 - 1) && $i2 === $k1) $meta = self::SOUTH_WEST;
                            if($l1 === $k3 && $i2 === $position->z + ($j3 - 1)) $meta = self::SOUTH_WEST;
                            if($l1 === $position->x + ($j3 - 1) && $i2 === $k1) $meta = self::SOUTH_EAST;
                            if($l1 === $l3 && $i2 === $position->z + ($j3 - 1)) $meta = self::SOUTH_EAST;
                        }

                        if($meta === self::CENTER && $l2 < $position->y + $i) {
                            $meta = self::ALL_INSIDE;
                        }

                        if($position->y >= $position->y + $i - 1 || $meta !== self::ALL_INSIDE) {
                            $blockPos = new Vector3($l1, $l2, $i2);
                            // Syrim: set mushroom block with metadata
                            $level->setBlockIdAt($l1, $l2, $i2, $mushroomId);
                            $level->setBlockDataAt($l1, $l2, $i2, $meta);
                        }
                    }
                }
            }

            // Tronco (stem) del mushroom
            for($l1 = 0; $l1 < $i; ++$l1) {
                $blockPos = new Vector3($position->x, $position->y + $l1, $position->z);
                $material = $level->getBlockIdAt((int) $position->x, (int) ($position->y + $l1), (int) $position->z);
                if($material === Block::AIR || $material === Block::LEAVES) {
                    $level->setBlockIdAt((int) $position->x, (int) ($position->y + $l1), (int) $position->z, $mushroomId);
                    $level->setBlockDataAt((int) $position->x, (int) ($position->y + $l1), (int) $position->z, self::STEM);
                }
            }

            return true;
        }

        return false;
    }
}
