<?php
/*
 * Syrim - Port de Nukkit 1.1.5 CaveBiome (interface)
 */

namespace pocketmine\level\generator\nukkit\biome;

interface CaveBiome {

    public function getStoneBlock() : int;

    public function getSurfaceBlock() : int;

    public function getGroundBlock() : int;
}
