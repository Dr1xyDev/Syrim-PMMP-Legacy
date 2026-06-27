<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectBirchTree
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class ObjectBirchTree extends ObjectTree {

    /** @var int */
    protected $treeHeight = 7;

    public function getTrunkBlock() : int {
        return Block::LOG;
    }

    public function getLeafBlock() : int {
        return Block::LEAVES;
    }

    public function getType() : int {
        return Wood::BIRCH;
    }

    public function getTreeHeight() : int {
        return $this->treeHeight;
    }

    public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random) {
        $this->treeHeight = $random->nextBoundedInt(3) + 5;
        parent::placeObject($level, $x, $y, $z, $random);
    }
}
