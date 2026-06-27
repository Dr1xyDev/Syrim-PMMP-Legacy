<?php
/*
 * Syrim - Port de Nukkit 1.1.5 OceanBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class OceanBiome extends WateryBiome {

    public function __construct() {
        parent::__construct();

        
        $sugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorSugarcane();
        $sugarcane->setBaseAmount(6);
        $tallSugarcane = new \pocketmine\level\generator\nukkit\populator\PopulatorTallSugarcane();
        $tallSugarcane->setBaseAmount(60);
        $this->addPopulator($sugarcane);
        $this->addPopulator($tallSugarcane);


        $this->setElevation(46, 58);

        $this->temperature = 0.500000;
        $this->rainfall = 0.500000;
    }

    public function getName() : string {
        return "Ocean";
    }


}
