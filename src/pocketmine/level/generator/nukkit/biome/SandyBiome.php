<?php
/*
 * Syrim - Port de Nukkit 1.1.5 SandyBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;

abstract class SandyBiome extends NormalBiome implements CaveBiome {

    public function __construct() {
        $cactus = new \pocketmine\level\generator\nukkit\populator\PopulatorCactus();
        $cactus->setBaseAmount(2);

        $deadbush = new \pocketmine\level\generator\nukkit\populator\PopulatorDeadBush();
        $deadbush->setBaseAmount(2);

        $this->addPopulator($cactus);
        $this->addPopulator($deadbush);

        $this->setGroundCover([
            Block::get(Block::SAND, 0),
            Block::get(Block::SAND, 0),
            Block::get(Block::SANDSTONE, 0),
            Block::get(Block::SANDSTONE, 0),
            Block::get(Block::SANDSTONE, 0),
        ]);
    }

    public function getSurfaceBlock() : int {
        return Block::SAND;
    }

    public function getGroundBlock() : int {
        return Block::SAND;
    }

    public function getStoneBlock() : int {
        return Block::SANDSTONE;
    }
}
