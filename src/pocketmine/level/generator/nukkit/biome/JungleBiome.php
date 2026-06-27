<?php
/*
 * Syrim - Port de Nukkit 1.1.5 JungleBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class JungleBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $trees = new \pocketmine\level\generator\nukkit\populator\tree\JungleTreePopulator();
        $bigTrees = new \pocketmine\level\generator\nukkit\populator\tree\JungleBigTreePopulator();
        // Syrim: mas arboles pequeños, menos big trees
        $trees->setBaseAmount(15);
        $bigTrees->setBaseAmount(2);
        $bigTrees->setRandomAmount(1);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(20);

        $this->addPopulator($grass);
        $this->addPopulator($bigTrees);
        $this->addPopulator($trees);


        $this->setElevation(62, 63);

        $this->temperature = 1.200000;
        $this->rainfall = 0.900000;
    }

    public function getName() : string {
        return "Jungle";
    }


}
