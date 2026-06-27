<?php
/*
 * Syrim - Port de Nukkit 1.1.5 TreeGenerator
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\object\BasicGenerator;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class TreeGenerator extends BasicGenerator {

    protected function canGrowInto(int $id) : bool {
        return $id === Item::AIR
            || $id === Item::LEAVES
            || $id === Item::GRASS
            || $id === Item::DIRT
            || $id === Item::LOG
            || $id === Item::LOG2
            || $id === Item::SAPLING
            || $id === Item::VINE;
    }

    public function generateSaplings(Level $level, Random $random, Vector3 $pos) {
    }

    protected function setDirtAt(ChunkManager $level, Vector3 $pos) {
        if($level->getBlockIdAt((int) $pos->x, (int) $pos->y, (int) $pos->z) !== Item::DIRT) {
            $this->setBlockAndNotifyAdequately($level, $pos, Block::get(Block::DIRT));
        }
    }
}
