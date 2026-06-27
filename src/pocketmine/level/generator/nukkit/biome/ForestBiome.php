<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ForestBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Sapling;

class ForestBiome extends GrassyBiome {

    public const TYPE_NORMAL = 0;
    public const TYPE_BIRCH = 1;

    /** @var int */
    public $type;

    public function __construct(int $type = self::TYPE_NORMAL) {
        parent::__construct();
        $this->type = $type;

        if($type === self::TYPE_BIRCH) {
            
        $trees = new \pocketmine\level\generator\nukkit\populator\PopulatorTree(\pocketmine\block\Sapling::BIRCH);
        $trees->setBaseAmount(5);
        $this->addPopulator($trees);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(30);
        $this->addPopulator($grass);

        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(3);
        $this->addPopulator($tallGrass);

            $this->temperature = 0.5;
            $this->rainfall = 0.5;
        } else {
            
        $trees = new \pocketmine\level\generator\nukkit\populator\PopulatorTree();
        $trees->setBaseAmount(5);
        $this->addPopulator($trees);

        $grass = new \pocketmine\level\generator\nukkit\populator\PopulatorGrass();
        $grass->setBaseAmount(30);
        $this->addPopulator($grass);

        $tallGrass = new \pocketmine\level\generator\nukkit\populator\PopulatorTallGrass();
        $tallGrass->setBaseAmount(3);
        $this->addPopulator($tallGrass);

            $this->temperature = 0.7;
            $this->rainfall = 0.8;
        }

        $this->setElevation(63, 81);
    }

    public function getName() : string {
        return $this->type === self::TYPE_BIRCH ? "Birch Forest" : "Forest";
    }
}
