<?php
/*
 * Syrim - Port de Nukkit 1.1.5 RiverBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class RiverBiome extends WateryBiome {

    public function __construct() {
        parent::__construct();

        
        $sugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorSugarcane();
        $sugarcane->setBaseAmount(6);
        $tallSugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorTallSugarcane();
        $tallSugarcane->setBaseAmount(60);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(30);
        $this->addPopulator($grass);

        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(5);

        $this->addPopulator($tallGrass);
        $this->addPopulator($sugarcane);
        $this->addPopulator($tallSugarcane);


        $this->setElevation(58, 62);

        $this->temperature = 0.500000;
        $this->rainfall = 0.700000;
    }

    public function getName() : string {
        return "River";
    }


}
