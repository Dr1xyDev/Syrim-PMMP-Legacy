<?php
/*
 * Syrim - Port de Nukkit 1.1.5 NormalBiome
 */

namespace pocketmine\level\generator\nukkit\biome;

abstract class NormalBiome extends Biome {

    public function getColor() : int {
        return $this->grassColor;
    }
}
