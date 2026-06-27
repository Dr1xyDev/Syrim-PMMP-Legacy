<?php
/*
 * Syrim - Port de Nukkit 1.1.5 JungleBigTreePopulator
 * Usa el ObjectJungleBigTree ya porteado (en pocketmine\level\generator\object)
 */

namespace pocketmine\level\generator\nukkit\populator\tree;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Wood;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\populator\Populator;
use pocketmine\level\generator\object\ObjectJungleBigTree;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class JungleBigTreePopulator extends Populator {

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
            $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
            $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
            $y = $this->getHighestWorkableBlock($x, $z);
            if($y === -1) {
                continue;
            }
            // Syrim: usa el ObjectJungleBigTree ya porteado
            $tree = new ObjectJungleBigTree(10, 20, Wood::JUNGLE, Leaves::JUNGLE);
            $tree->placeObject($level, $x, $y, $z, $random);
        }
    }

    private function getHighestWorkableBlock(int $x, int $z) : int {
        $y;
        for($y = 127; $y > 0; --$y) {
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if($b === Block::DIRT || $b === Block::GRASS) {
                break;
            } elseif($b !== Block::AIR && $b !== Block::SNOW_LAYER) {
                return -1;
            }
        }
        return ++$y;
    }
}
