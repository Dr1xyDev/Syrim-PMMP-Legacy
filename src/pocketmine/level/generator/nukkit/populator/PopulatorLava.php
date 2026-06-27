<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorLava (nether)
 * Versión simplificada - solo coloca lava en pozos.
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\utils\Random;

class PopulatorLava extends Populator {

    /** @var int */
    private $randomAmount = 0;
    /** @var int */
    private $baseAmount = 0;

    public function setRandomAmount(int $amount) {
        $this->randomAmount = $amount;
    }

    public function setBaseAmount(int $amount) {
        $this->baseAmount = $amount;
    }

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        if($random->nextRange(0, 100) >= 5) {
            return;
        }
        $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
        /** @var Chunk $chunk */
        $chunk = $level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($i = 0; $i < $amount; ++$i) {
            $x = $random->nextRange(0, 15);
            $z = $random->nextRange(0, 15);
            $y = $this->getHighestWorkableBlock($chunk, $x, $z);
            if($y !== -1 && $chunk->getBlockId($x, $y, $z) === Block::AIR) {
                $chunk->setBlockId($x, $y, $z, Block::LAVA);
                $chunk->setBlockLight($x, $y, $z, 15);
            }
        }
    }

    private function getHighestWorkableBlock(Chunk $chunk, int $x, int $z) : int {
        $y;
        for($y = 0; $y <= 127; ++$y) {
            $b = $chunk->getBlockId($x, $y, $z);
            if($b === Block::AIR) {
                break;
            }
        }
        return $y === 0 ? -1 : $y;
    }
}
