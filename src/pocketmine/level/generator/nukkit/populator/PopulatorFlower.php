<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorFlower
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class PopulatorFlower extends Populator {

    /** @var ChunkManager */
    private $level;
    /** @var int */
    private $randomAmount = 0;
    /** @var int */
    private $baseAmount = 0;
    /** @var int[][] */
    private $flowerTypes = [];

    public function setRandomAmount(int $randomAmount) {
        $this->randomAmount = $randomAmount;
    }

    public function setBaseAmount(int $baseAmount) {
        $this->baseAmount = $baseAmount;
    }

    public function addType(int $a, int $b) {
        $this->flowerTypes[] = [$a, $b];
    }

    public function getTypes() : array {
        return $this->flowerTypes;
    }

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        $this->level = $level;
        $amount = $random->nextBoundedInt($this->randomAmount + 1) + $this->baseAmount;

        if(count($this->flowerTypes) === 0) {
            $this->addType(Block::RED_FLOWER, 0); // Poppy
            $this->addType(Block::DANDELION, 0);
        }

        $endNum = count($this->flowerTypes);

        for($i = 0; $i < $amount; ++$i) {
            $x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
            $z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
            $y = $this->getHighestWorkableBlock($x, $z);

            if($y !== -1 && $this->canFlowerStay($x, $y, $z)) {
                $type = $this->flowerTypes[$random->nextRange(0, $endNum - 1)];
                $this->level->setBlockIdAt($x, $y, $z, $type[0]);
                $this->level->setBlockDataAt($x, $y, $z, $type[1]);
            }
        }
    }

    private function canFlowerStay(int $x, int $y, int $z) : bool {
        $b = $this->level->getBlockIdAt($x, $y, $z);
        return ($b === Block::AIR || $b === Block::SNOW_LAYER) && $this->level->getBlockIdAt($x, $y - 1, $z) === Block::GRASS;
    }

    private function getHighestWorkableBlock(int $x, int $z) : int {
        $y;
        for($y = 127; $y >= 0; --$y) {
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if($b !== Block::AIR && $b !== Block::LEAVES && $b !== Block::LEAVES2 && $b !== Block::SNOW_LAYER) {
                break;
            }
        }
        return $y === 0 ? -1 : ++$y;
    }
}
