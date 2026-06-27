<?php
/*
 * Syrim - Port de Nukkit 1.1.5 IcePlainsBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

class IcePlainsBiome extends SnowyBiome {

    public function __construct() {
        parent::__construct();

        
        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(5);

        $trees = new \pocketmine\level\generator\nukkit\populator\PopulatorTree(\pocketmine\block\Sapling::SPRUCE);
        $trees->setBaseAmount(1);
        $trees->setRandomAmount(1);

        $this->addPopulator($tallGrass);
        $this->addPopulator($trees);


        $this->setElevation(63, 74);

        $this->temperature = 0.000000;
        $this->rainfall = 0.500000;
    }

    public function getName() : string {
        return "Ice Plains";
    }


}
