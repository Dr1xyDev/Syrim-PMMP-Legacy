<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorDeadBush
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class PopulatorDeadBush extends Populator {

    /** @var ChunkManager */
    private $level;
    /** @var int */
    private $randomAmount = 0;
    /** @var int */
    private $baseAmount = 0;

    public function setRandomAmount(int $randomAmount) {
        $this->randomAmount = $randomAmount;
    }

    public function setBaseAmount(int $baseAmount) {
        $this->baseAmount = $baseAmount;
    }

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        $this->level = $level;
        $amount = $random->nextBoundedInt($this->randomAmount + 1) + $this->baseAmount;
        for($i = 0; $i < $amount; ++$i) {
            $x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
            $z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
            $y = $this->getHighestWorkableBlock($x, $z);

            if($y !== -1 && $this->canStay($x, $y, $z)) {
                $this->level->setBlockIdAt($x, $y, $z, Block::DEAD_BUSH);
                $this->level->setBlockDataAt($x, $y, $z, 1);

            }
        }
    }

    private function canStay(int $x, int $y, int $z) : bool {
        $b = $this->level->getBlockIdAt($x, $y, $z);

        return ($b === Block::AIR && $this->level->getBlockIdAt($x, $y - 1, $z) === Block::SAND);
    }

    private function getHighestWorkableBlock(int $x, int $z) : int {
        $y;
        for($y = 127; $y >= 0; --$y) {
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if($b !== Block::AIR && $b !== Block::LEAVES && $b !== Block::LEAVES2) {
                break;
            }
        }

        return $y === 0 ? -1 : ++$y;
    }

}
