<?php
/*
 * Syrim - Port de Nukkit 1.1.5 BiomeSelector
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\level\generator\nukkit\noise\Simplex;
use pocketmine\utils\Random;

class BiomeSelector {

    /** @var Biome */
    private $fallback;
    /** @var Simplex */
    private $temperature;
    /** @var Simplex */
    private $rainfall;
    /** @var Biome[] */
    private $biomes = [];
    /** @var int[] */
    private $map = [];

    public function __construct(Random $random, Biome $fallback) {
        $this->fallback = $fallback;
        $this->temperature = new Simplex($random, 2.0, 1.0 / 8.0, 1.0 / 1024.0);
        $this->rainfall = new Simplex($random, 2.0, 1.0 / 8.0, 1.0 / 1024.0);
    }

    public function lookup(float $temperature, float $rainfall) : int {
        // Syrim: lookup con Mesa = misma rareza que Swamp
        // Ambos comparten la zona "muy seca" (rainfall < 0.25) a partes iguales:
        //   - Temp > 0.50 → Mesa (cálido + seco, junto a desierto/sabana)
        //   - Temp < 0.50 → Swamp (frío + seco)
        // Esto da a Mesa exactamente la misma área que Swamp en el mapa.

        if($rainfall < 0.25) {
            // Zona muy seca — dividir entre Mesa y Swamp
            if($temperature >= 0.50) {
                return Biome::MESA;
            }
            return Biome::SWAMP;
        } elseif($rainfall < 0.60) {
            // Zona seca-moderada
            if($temperature < 0.20) {
                return Biome::ICE_PLAINS;
            } elseif($temperature < 0.40) {
                return Biome::MOUNTAINS;
            } elseif($temperature < 0.75) {
                return Biome::DESERT;
            } else {
                return Biome::SAVANNA;
            }
        } elseif($rainfall < 0.80) {
            // Zona moderada-húmeda
            if($temperature < 0.25) {
                return Biome::TAIGA;
            } elseif($temperature < 0.45) {
                // Syrim NEW: bosque de abedul en zonas frescas-moderadas
                return Biome::BIRCH_FOREST;
            } elseif($temperature < 0.65) {
                return Biome::FOREST;
            } else {
                // Syrim NEW: bosque oscuro (dark oak) en zonas cálidas-moderadas
                return Biome::ROOFED_FOREST;
            }
        } else {
            // Zona muy húmeda
            if($rainfall < 0.90) {
                if($temperature < 0.15) {
                    // Syrim NEW: isla de hongos - muy rara, solo en frío extremo + húmedo
                    return Biome::MUSHROOM_ISLAND;
                }
                return Biome::JUNGLE;
            }
            // Syrim NEW: Roofed Forest M - muy raro, solo en cálido + súper húmedo
            if($temperature > 0.60) {
                return Biome::ROOFED_FOREST_M;
            }
            return Biome::JUNGLE;
        }
    }

    public function recalculate() {
        $this->map = array_fill(0, 64 * 64, 0);
        for($i = 0; $i < 64; ++$i) {
            for($j = 0; $j < 64; ++$j) {
                $this->map[$i + ($j << 6)] = $this->lookup($i / 63.0, $j / 63.0);
            }
        }
    }

    public function addBiome(Biome $biome) {
        $this->biomes[$biome->getId()] = $biome;
    }

    public function getTemperature(float $x, float $z) : float {
        return ($this->temperature->noise2D($x, $z, true) + 1) / 2;
    }

    public function getRainfall(float $x, float $z) : float {
        return ($this->rainfall->noise2D($x, $z, true) + 1) / 2;
    }

    public function pickBiome(float $x, float $z) : Biome {
        $temperature = (int) ($this->getTemperature($x, $z) * 63);
        $rainfall = (int) ($this->getRainfall($x, $z) * 63);

        $biomeId = $this->map[$temperature + ($rainfall << 6)] ?? 0;
        return $this->biomes[$biomeId] ?? $this->fallback;
    }
}
