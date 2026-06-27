<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorGroundFire (nether)
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\utils\Random;

class PopulatorGroundFire extends Populator {

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
        /** @var Chunk $chunk */
        $chunk = $level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;
        $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
        for($i = 0; $i < $amount; ++$i) {
            $x = $random->nextRange(0, 15);
            $z = $random->nextRange(0, 15);
            $y = $this->getHighestWorkableBlock($chunk, $x, $z);
            if($y !== -1 && $this->canGroundFireStay($chunk, $x, $y, $z)) {
                $chunk->setBlockId($x, $y, $z, Block::FIRE);
                $chunk->setBlockLight($x, $y, $z, Block::$light[Block::FIRE] ?? 15);
            }
        }
    }

    private function canGroundFireStay(Chunk $chunk, int $x, int $y, int $z) : bool {
        $b = $chunk->getBlockId($x, $y, $z);
        return $b === Block::AIR && $chunk->getBlockId($x, $y - 1, $z) === Block::NETHERRACK;
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
