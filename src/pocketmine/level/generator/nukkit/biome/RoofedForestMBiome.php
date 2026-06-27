<?php
/*
 * Syrim - Port de Nukkit 1.1.5 RoofedForestMBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class RoofedForestMBiome extends GrassyBiome {

    public function __construct() {
        parent::__construct();

        
        $tree = new \pocketmine\level\generator\nukkit\populator\tree\DarkOakTreePopulator();
        $tree->setBaseAmount(20);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(10);

        $flower = new \pocketmine\level\generator\nukkit\populator\PopulatorFlower();
        $flower->setBaseAmount(2);

        $mushroom = new \pocketmine\level\generator\nukkit\populator\MushroomPopulator();
        $mushroom->setBaseAmount(1);
        $mushroom->setRandomAmount(1);

        $this->addPopulator($grass);
        $this->addPopulator($tree);
        $this->addPopulator($flower);
        $this->addPopulator($mushroom);


        $this->setElevation(63, 127);

        $this->temperature = 0.700000;
        $this->rainfall = 0.800000;
    }

    public function getName() : string {
        return "Roofed ForestM";
    }


}
