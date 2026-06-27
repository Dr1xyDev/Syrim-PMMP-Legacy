<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectSpruceTree
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class ObjectSpruceTree extends ObjectTree {

    /** @var int */
    private $treeHeight = 15;

    public function getTrunkBlock() : int {
        return Block::LOG;
    }

    public function getLeafBlock() : int {
        return Block::LEAVES;
    }

    public function getType() : int {
        return Wood::SPRUCE;
    }

    public function getTreeHeight() : int {
        return $this->treeHeight;
    }

    public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random) {
        $this->treeHeight = $random->nextBoundedInt(4) + 6;

        $topSize = $this->getTreeHeight() - (1 + $random->nextBoundedInt(2));
        $lRadius = 2 + $random->nextBoundedInt(2);

        $this->placeTrunk($level, $x, $y, $z, $random, $this->getTreeHeight() - $random->nextBoundedInt(3));

        $radius = $random->nextBoundedInt(2);
        $maxR = 1;
        $minR = 0;

        for($yy = 0; $yy <= $topSize; ++$yy) {
            $yyy = $y + $this->treeHeight - $yy;

            for($xx = $x - $radius; $xx <= $x + $radius; ++$xx) {
                $xOff = abs($xx - $x);
                for($zz = $z - $radius; $zz <= $z + $radius; ++$zz) {
                    $zOff = abs($zz - $z);
                    if($xOff === $radius && $zOff === $radius && $radius > 0) {
                        continue;
                    }

                    if(!Block::$solid[$level->getBlockIdAt($xx, $yyy, $zz)]) {
                        $level->setBlockIdAt($xx, $yyy, $zz, $this->getLeafBlock());
                        $level->setBlockDataAt($xx, $yyy, $zz, $this->getType());
                    }
                }
            }

            if($radius >= $maxR) {
                $radius = $minR;
                $minR = 1;
                if(++$maxR > $lRadius) {
                    $maxR = $lRadius;
                }
            } else {
                ++$radius;
            }
        }
    }
}
