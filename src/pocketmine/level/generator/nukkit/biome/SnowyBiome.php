<?php
/*
 * Syrim - Port de Nukkit 1.1.5 SnowyBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;

abstract class SnowyBiome extends NormalBiome {

    public function __construct() {
        $this->setGroundCover([
            Block::get(Block::SNOW_LAYER, 0),
            Block::get(Block::GRASS, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
            Block::get(Block::DIRT, 0),
        ]);
    }
}
