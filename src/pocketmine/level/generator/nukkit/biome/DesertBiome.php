<?php
/*
 * Syrim - Port de Nukkit 1.1.5 DesertBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class DesertBiome extends SandyBiome {

    public function __construct() {
        parent::__construct();

        

        $this->setElevation(63, 74);

        $this->temperature = 2.000000;
        $this->rainfall = 0.000000;
    }

    public function getName() : string {
        return "Desert";
    }


}
