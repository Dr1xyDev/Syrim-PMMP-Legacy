<?php
/*
 * Syrim - Port de Nukkit 1.1.5 BeachBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class BeachBiome extends SandyBiome {

    public function __construct() {
        parent::__construct();

        

        $this->setElevation(62, 65);

        $this->temperature = 2.000000;
        $this->rainfall = 0.000000;
    }

    public function getName() : string {
        return "Beach";
    }


}
