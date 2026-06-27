<?php
/*
 * Syrim - Port de Nukkit 1.1.5 MushroomIsland
 */

namespace pocketmine\level\generator\nukkit\biome;

class MushroomIsland extends NormalBiome implements CaveBiome {

    public function __construct() {
        // Syrim: NO llamar parent::__construct() porque NormalBiome/Biome no
        // definen constructor. En PHP 7.3 esto da "Cannot call constructor".
        $this->setGroundCover([\pocketmine\block\Block::get(\pocketmine\block\Block::MYCELIUM, 0), \pocketmine\block\Block::get(\pocketmine\block\Block::DIRT, 0), \pocketmine\block\Block::get(\pocketmine\block\Block::DIRT, 0), \pocketmine\block\Block::get(\pocketmine\block\Block::DIRT, 0), \pocketmine\block\Block::get(\pocketmine\block\Block::DIRT, 0)]);

        
        $mushroomPopulator = new \pocketmine\level\generator\nukkit\populator\MushroomPopulator();
        $mushroomPopulator->setBaseAmount(1);

        $this->addPopulator($mushroomPopulator);


        $this->setElevation(60, 70);

        $this->temperature = 0.900000;
        $this->rainfall = 1.000000;
    }

    public function getName() : string {
        return "Mushroom Island";
    }


    public function getSurfaceBlock() : int {
        return \pocketmine\block\Block::MYCELIUM;
    }

    public function getGroundBlock() : int {
        return \pocketmine\block\Block::DIRT;
    }

    public function getStoneBlock() : int {
        return \pocketmine\block\Block::STONE;
    }

}
