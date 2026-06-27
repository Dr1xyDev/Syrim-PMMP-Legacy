<?php
/*
 * Syrim - Port de Nukkit 1.1.5 SmallMountainsBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class SmallMountainsBiome extends MountainsBiome {

    public function __construct() {
        parent::__construct();

        

        $this->setElevation(63, 97);

        $this->temperature = 0.400000;
        $this->rainfall = 0.500000;
    }

    public function getName() : string {
        return "Small Mountains";
    }


}
