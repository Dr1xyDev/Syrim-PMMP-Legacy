<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PlainBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class PlainBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $sugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorSugarcane();
        $sugarcane->setBaseAmount(6);
        $tallSugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorTallSugarcane();
        $tallSugarcane->setBaseAmount(60);
        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(40);
        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(7);
        $flower = new \pocketmine\level\generator\nukkit\populator\PopulatorFlower();
        $flower->setBaseAmount(10);
        $flower->addType(\pocketmine\block\Block::DANDELION, 0);
        $flower->addType(\pocketmine\block\Block::RED_FLOWER, 0); // Poppy
        $flower->addType(\pocketmine\block\Block::RED_FLOWER, 3); // Azure Bluet
        $flower->addType(\pocketmine\block\Block::RED_FLOWER, 5); // Red Tulip
        $flower->addType(\pocketmine\block\Block::RED_FLOWER, 6); // Orange Tulip

        $this->addPopulator($sugarcane);
        $this->addPopulator($tallSugarcane);
        $this->addPopulator($grass);
        $this->addPopulator($tallGrass);
        $this->addPopulator($flower);


        $this->setElevation(63, 74);

        $this->temperature = 0.800000;
        $this->rainfall = 0.400000;
    }

    public function getName() : string {
        return "Plains";
    }


}
