<?php
/*
 * Syrim - Port de Nukkit 1.1.5 HellBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class HellBiome extends Biome {

    public function __construct() {
        

        

        $this->setElevation(0, 0);

        $this->temperature = 0.500000;
        $this->rainfall = 0.500000;
    }

    public function getName() : string {
        return "Hell";
    }

    public function getColor() : int {
        return -3394765;
    }


}
