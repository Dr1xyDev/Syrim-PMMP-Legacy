<?php
/*
 * Syrim - Port de Nukkit 1.1.5 GrassyBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;

abstract class GrassyBiome extends NormalBiome implements CaveBiome {

    public function __construct() {
        $this->setGroundCover([
            Block::get(Block::GRASS, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
        ]);
    }

    public function getSurfaceBlock() : int {
        return Block::GRASS;
    }

    public function getGroundBlock() : int {
        return Block::DIRT;
    }

    public function getStoneBlock() : int {
        return Block::STONE;
    }
}
