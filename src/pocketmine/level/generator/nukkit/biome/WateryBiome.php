<?php
/*
 * Syrim - Port de Nukkit 1.1.5 WateryBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;

abstract class WateryBiome extends NormalBiome implements CaveBiome {

    public function __construct() {
        $this->setGroundCover([
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
        ]);
    }

    public function getSurfaceBlock() : int {
        return Block::DIRT;
    }

    public function getGroundBlock() : int {
        return Block::DIRT;
    }

    public function getStoneBlock() : int {
        return Block::STONE;
    }
}
