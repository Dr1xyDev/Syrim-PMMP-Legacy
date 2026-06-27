<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorGlowStone (nether)
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\utils\Random;

class PopulatorGlowStone extends Populator {

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
        $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
        /** @var Chunk $chunk */
        $chunk = $level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($i = 0; $i < $amount; ++$i) {
            $x = $random->nextRange(1, 14);
            $z = $random->nextRange(1, 14);
            $y = $random->nextRange(60, 126);

            // Solo colocar glowstone si hay netherrack arriba (techo del nether)
            if($chunk->getBlockId($x, $y + 1, $z) === Block::NETHERRACK && $chunk->getBlockId($x, $y, $z) === Block::AIR) {
                $chunk->setBlockId($x, $y, $z, Block::GLOWSTONE_BLOCK);
                $chunk->setBlockLight($x, $y, $z, 15);

                // A veces extenderlo hacia abajo formando estalactitas
                if($random->nextRange(0, 3) === 0 && $chunk->getBlockId($x, $y - 1, $z) === Block::AIR) {
                    $chunk->setBlockId($x, $y - 1, $z, Block::GLOWSTONE_BLOCK);
                    $chunk->setBlockLight($x, $y - 1, $z, 15);
                }
            }
        }
    }
}
