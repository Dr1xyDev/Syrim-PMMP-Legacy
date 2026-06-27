<?php
/*
 * Syrim - Port de Nukkit 1.1.5 MountainsBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class MountainsBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $tree = new \pocketmine\level\generator\nukkit\populator\PopulatorTree();
        $tree->setBaseAmount(1);
        $this->addPopulator($tree);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(30);
        $this->addPopulator($grass);

        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(1);
        $this->addPopulator($tallGrass);


        $this->setElevation(63, 127);

        $this->temperature = 0.400000;
        $this->rainfall = 0.500000;
    }

    public function getName() : string {
        return "Mountains";
    }


}
