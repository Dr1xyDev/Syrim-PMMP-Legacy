<?php
/*
 * Syrim - Port de Nukkit 1.1.5 SwampBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class SwampBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $lilypad = new \pocketmine\level\generator\nukkit\populator\PopulatorLilyPad();
        $lilypad->setBaseAmount(4);

        $trees = new \pocketmine\level\generator\nukkit\populator\tree\SwampTreePopulator();
        $trees->setBaseAmount(2);

        $flower = new \pocketmine\level\generator\nukkit\populator\PopulatorFlower();
        $flower->setBaseAmount(2);
        $flower->addType(\pocketmine\block\Block::RED_FLOWER, 1); // Blue Orchid

        $this->addPopulator($trees);
        $this->addPopulator($flower);
        $this->addPopulator($lilypad);


        $this->setElevation(62, 63);

        $this->temperature = 0.800000;
        $this->rainfall = 0.900000;
    }

    public function getName() : string {
        return "Swamp";
    }

    public function getColor() : int {
        return 0x6a7039;
    }


}
