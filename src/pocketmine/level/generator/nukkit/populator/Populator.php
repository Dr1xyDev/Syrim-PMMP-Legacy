<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Populator (base)
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

abstract class Populator {

    abstract public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random);
}
