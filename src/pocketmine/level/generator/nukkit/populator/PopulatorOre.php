<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorOre
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\object\ore\ObjectOre;
use pocketmine\level\generator\nukkit\object\ore\OreType;
use pocketmine\utils\Random;

class PopulatorOre extends Populator {

    /** @var int */
    private $replaceId;
    /** @var OreType[] */
    private $oreTypes = [];

    public function __construct(int $id = Block::STONE) {
        $this->replaceId = $id;
    }

    public function setOreTypes(array $oreTypes) {
        $this->oreTypes = $oreTypes;
    }

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        foreach($this->oreTypes as $type) {
            $ore = new ObjectOre($random, $type, $this->replaceId);
            for($i = 0; $i < $type->clusterCount; ++$i) {
                $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
                $y = $random->nextRange($type->minHeight, $type->maxHeight);
                $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
                if($ore->canPlaceObject($level, $x, $y, $z)) {
                    $ore->placeObject($level, $x, $y, $z);
                }
            }
        }
    }
}
