<?php
/*
 * Syrim - Port de Nukkit 1.1.5 BasicGenerator
 */

namespace pocketmine\level\generator\nukkit\object;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class BasicGenerator {

    abstract public function generate(ChunkManager $level, Random $rand, Vector3 $position) : bool;

    public function setDecorationDefaults() {
    }

    protected function setBlockAndNotifyAdequately(ChunkManager $level, Vector3 $pos, Block $state) {
        $level->setBlockIdAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $state->getId());
        $level->setBlockDataAt((int) $pos->x, (int) $pos->y, (int) $pos->z, $state->getDamage());
    }
}
