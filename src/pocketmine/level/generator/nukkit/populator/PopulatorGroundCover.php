<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorGroundCover
 *
 * Syrim FIX: getBlockIdColumn devuelve un string en PMMP, no un array.
 * Se reescribio para usar getBlockIdAt() del chunk directamente.
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\biome\Biome;
use pocketmine\utils\Random;

class PopulatorGroundCover extends Populator {

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        /** @var Chunk $chunk */
        $chunk = $level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($x = 0; $x < 16; ++$x) {
            for($z = 0; $z < 16; ++$z) {
                $biome = Biome::getBiome($chunk->getBiomeId($x, $z));
                $cover = $biome->getGroundCover();
                if($cover !== null && count($cover) > 0) {
                    $diffY = 0;
                    if(!$cover[0]->isSolid()) {
                        $diffY = 1;
                    }

                    // Syrim: usar getBlockIdAt en vez de getBlockIdColumn
                    // porque getBlockIdColumn devuelve string en PMMP.
                    $y = 127;
                    for(; $y > 0; --$y) {
                        $blockId = $chunk->getBlockId($x, $y, $z);
                        if($blockId !== 0x00 && !Block::get($blockId)->isTransparent()) {
                            break;
                        }
                    }
                    $startY = min(127, $y + $diffY);
                    $endY = $startY - count($cover);
                    for($y = $startY; $y > $endY && $y >= 0; --$y) {
                        $b = $cover[$startY - $y];
                        $currentBlockId = $chunk->getBlockId($x, $y, $z);
                        if($currentBlockId === 0x00 && $b->isSolid()) {
                            break;
                        }
                        if($b->getDamage() === 0) {
                            $chunk->setBlockId($x, $y, $z, $b->getId());
                        } else {
                            $chunk->setBlock($x, $y, $z, $b->getId(), $b->getDamage());
                        }
                    }
                }
            }
        }
    }
}
