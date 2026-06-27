<?php
/*
 * Syrim - Port de Nukkit 1.1.5 TaigaBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class TaigaBiome extends SnowyBiome {

    public function __construct() {
        parent::__construct();

        
        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(6);
        $this->addPopulator($grass);

        $trees = new \pocketmine\level\generator\nukkit\populator\PopulatorTree(\pocketmine\block\Sapling::SPRUCE);
        $trees->setBaseAmount(10);
        $this->addPopulator($trees);

        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(1);
        $this->addPopulator($tallGrass);


        $this->setElevation(63, 81);

        $this->temperature = 0.050000;
        $this->rainfall = 0.800000;
    }

    public function getName() : string {
        return "Taiga";
    }


}
