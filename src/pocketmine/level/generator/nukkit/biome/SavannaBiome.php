<?php
/*
 * Syrim - Port de Nukkit 1.1.5 SavannaBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class SavannaBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $tree = new \pocketmine\level\generator\nukkit\populator\tree\SavannaTreePopulator();
        $tree->setBaseAmount(1);
        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(20);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(20);

        $flower = new \pocketmine\level\generator\nukkit\populator\PopulatorFlower();
        $flower->setBaseAmount(4);

        $this->addPopulator($tallGrass);
        $this->addPopulator($grass);
        $this->addPopulator($tree);
        $this->addPopulator($flower);


        $this->setElevation(62, 68);

        $this->temperature = 1.200000;
        $this->rainfall = 0.000000;
    }

    public function getName() : string {
        return "Savanna";
    }


}
