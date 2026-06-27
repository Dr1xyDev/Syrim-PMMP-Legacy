<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectOre
 */

namespace pocketmine\level\generator\nukkit\object\ore;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class ObjectOre {

    /** @var Random */
    private $random;
    /** @var OreType */
    public $type;
    /** @var int */
    private $replaceId;

    public function __construct(Random $random, OreType $type, int $replaceId = Block::STONE) {
        $this->type = $type;
        $this->random = $random;
        $this->replaceId = $replaceId;
    }

    public function getType() : OreType {
        return $this->type;
    }

    public function canPlaceObject(ChunkManager $level, int $x, int $y, int $z) : bool {
        return $level->getBlockIdAt($x, $y, $z) === $this->replaceId;
    }

    public function placeObject(ChunkManager $level, int $x, int $y, int $z) {
        $clusterSize = $this->type->clusterSize;
        $angle = $this->random->nextFloat() * M_PI;
        $offsetX = cos($angle) * $clusterSize / 8.0;
        $offsetZ = sin($angle) * $clusterSize / 8.0;
        $x1 = $x + 8 + $offsetX;
        $x2 = $x + 8 - $offsetX;
        $z1 = $z + 8 + $offsetZ;
        $z2 = $z + 8 - $offsetZ;
        $y1 = $y + $this->random->nextBoundedInt(3) + 2;
        $y2 = $y + $this->random->nextBoundedInt(3) + 2;

        for($count = 0; $count <= $clusterSize; ++$count) {
            $seedX = $x1 + ($x2 - $x1) * $count / $clusterSize;
            $seedY = $y1 + ($y2 - $y1) * $count / $clusterSize;
            $seedZ = $z1 + ($z2 - $z1) * $count / $clusterSize;
            $size = ((sin($count * (M_PI / $clusterSize)) + 1) * $this->random->nextFloat() * $clusterSize / 16 + 1) / 2;

            $startX = (int) ($seedX - $size);
            $startY = (int) ($seedY - $size);
            $startZ = (int) ($seedZ - $size);
            $endX = (int) ($seedX + $size);
            $endY = (int) ($seedY + $size);
            $endZ = (int) ($seedZ + $size);

            for($xx = $startX; $xx <= $endX; ++$xx) {
                $sizeX = ($xx + 0.5 - $seedX) / $size;
                $sizeX *= $sizeX;

                if($sizeX < 1) {
                    for($yy = $startY; $yy <= $endY; ++$yy) {
                        $sizeY = ($yy + 0.5 - $seedY) / $size;
                        $sizeY *= $sizeY;

                        if($yy > 0 && ($sizeX + $sizeY) < 1) {
                            for($zz = $startZ; $zz <= $endZ; ++$zz) {
                                $sizeZ = ($zz + 0.5 - $seedZ) / $size;
                                $sizeZ *= $sizeZ;

                                if(($sizeX + $sizeY + $sizeZ) < 1 && $level->getBlockIdAt($xx, $yy, $zz) === $this->replaceId) {
                                    $level->setBlockIdAt($xx, $yy, $zz, $this->type->material->getId());
                                    if($this->type->material->getDamage() !== 0) {
                                        $level->setBlockDataAt($xx, $yy, $zz, $this->type->material->getDamage());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
