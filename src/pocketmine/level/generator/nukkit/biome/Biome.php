<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Biome (base)
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\populator\Populator;
use pocketmine\utils\Random;

abstract class Biome {

    public const OCEAN = 0;
    public const PLAINS = 1;
    public const DESERT = 2;
    public const MOUNTAINS = 3;
    public const FOREST = 4;
    public const TAIGA = 5;
    public const SWAMP = 6;
    public const RIVER = 7;
    public const JUNGLE = 21;
    public const SAVANNA = 35;
    public const ROOFED_FOREST = 29;
    public const ROOFED_FOREST_M = 157;
    public const MUSHROOM_ISLAND = 14;

    public const HELL = 8;

    public const ICE_PLAINS = 12;

    public const BEACH = 16;

    public const SMALL_MOUNTAINS = 20;

    public const BIRCH_FOREST = 27;

    // Syrim NEW: bioma Mesa (meseta)
    public const MESA = 39;

    public const MAX_BIOMES = 256;

    /** @var Biome[] */
    private static $biomes = [];

    /** @var int */
    private $id = 0;
    /** @var bool */
    private $registered = false;

    /** @var Populator[] */
    private $populators = [];

    /** @var int */
    private $minElevation = 0;
    /** @var int */
    private $maxElevation = 0;

    /** @var Block[] */
    private $groundCover = null;

    /** @var float */
    protected $rainfall = 0.5;
    /** @var float */
    protected $temperature = 0.5;
    /** @var int */
    protected $grassColor = 0;

    protected static function register(int $id, Biome $biome) {
        $biome->setId($id);
        $biome->grassColor = self::generateBiomeColor($biome->getTemperature(), $biome->getRainfall());
        self::$biomes[$id] = $biome;
    }

    public static function init() {
        // Syrim: evitar doble inicialización (cada hilo worker tiene su
        // propia copia de statics, pero dentro del mismo hilo init() puede
        // llamarse varias veces).
        if(!empty(self::$biomes)) {
            return;
        }
        self::register(self::OCEAN, new OceanBiome());
        self::register(self::PLAINS, new PlainBiome());
        self::register(self::DESERT, new DesertBiome());
        self::register(self::MOUNTAINS, new MountainsBiome());
        self::register(self::FOREST, new ForestBiome());
        self::register(self::TAIGA, new TaigaBiome());
        self::register(self::SWAMP, new SwampBiome());
        self::register(self::RIVER, new RiverBiome());
        self::register(self::ICE_PLAINS, new IcePlainsBiome());
        self::register(self::SMALL_MOUNTAINS, new SmallMountainsBiome());
        self::register(self::BIRCH_FOREST, new ForestBiome(ForestBiome::TYPE_BIRCH));

        self::register(self::JUNGLE, new JungleBiome());
        self::register(self::ROOFED_FOREST, new RoofedForestBiome());
        self::register(self::ROOFED_FOREST_M, new RoofedForestMBiome());
        self::register(self::MUSHROOM_ISLAND, new MushroomIsland());
        self::register(self::SAVANNA, new SavannaBiome());

        self::register(self::BEACH, new BeachBiome());

        // Syrim NEW: bioma Mesa (meseta)
        self::register(self::MESA, new MesaBiome());

        self::register(self::HELL, new HellBiome());
    }

    public static function getBiome($id) : Biome {
        // Syrim: en hilos async (GeneratorRegisterTask), self::$biomes puede
        // estar vacía porque pthreads copia las statics por hilo. Si no hay
        // biomas registrados, forzar init() antes de buscar.
        if(empty(self::$biomes)) {
            self::init();
        }
        return self::$biomes[$id] ?? self::$biomes[self::OCEAN];
    }

    public static function getBiomeByName(string $name) : ?Biome {
        $name = str_replace("_", " ", $name);
        foreach(self::$biomes as $biome) {
            if($biome !== null && strtolower($biome->getName()) === strtolower($name)) {
                return $biome;
            }
        }
        return null;
    }

    public function clearPopulators() {
        $this->populators = [];
    }

    public function addPopulator(Populator $populator) {
        $this->populators[] = $populator;
    }

    public function populateChunk(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        foreach($this->populators as $populator) {
            $populator->populate($level, $chunkX, $chunkZ, $random);
        }
    }

    /**
     * @return Populator[]
     */
    public function getPopulators() : array {
        return $this->populators;
    }

    public function setId(int $id) {
        $this->id = $id;
    }

    public function getId() : int {
        return $this->id;
    }

    abstract public function getName() : string;

    public function getMinElevation() : int {
        return $this->minElevation;
    }

    public function getMaxElevation() : int {
        return $this->maxElevation;
    }

    public function setElevation(int $min, int $max) {
        $this->minElevation = $min;
        $this->maxElevation = $max;
    }

    /**
     * @return Block[]|null
     */
    public function getGroundCover() : ?array {
        return $this->groundCover;
    }

    /**
     * @param Block[] $covers
     */
    public function setGroundCover(array $covers) {
        $this->groundCover = $covers;
    }

    public function getTemperature() : float {
        return $this->temperature;
    }

    public function getRainfall() : float {
        return $this->rainfall;
    }

    private static function generateBiomeColor(float $temperature, float $rainfall) : int {
        $x = (1 - $temperature) * 255;
        $z = (1 - $rainfall * $temperature) * 255;
        $c = self::interpolateColor(256, $x, $z, [0x47, 0xd0, 0x33], [0x6c, 0xb4, 0x93], [0xbf, 0xb6, 0x55], [0x80, 0xb4, 0x97]);
        return ((int) $c[0] << 16) | ((int) $c[1] << 8) | (int) $c[2];
    }

    private static function interpolateColor(float $size, float $x, float $z, array $c1, array $c2, array $c3, array $c4) : array {
        $l1 = self::lerpColor($c1, $c2, $x / $size);
        $l2 = self::lerpColor($c3, $c4, $x / $size);
        return self::lerpColor($l1, $l2, $z / $size);
    }

    private static function lerpColor(array $a, array $b, float $s) : array {
        $invs = 1 - $s;
        return [
            $a[0] * $invs + $b[0] * $s,
            $a[1] * $invs + $b[1] * $s,
            $a[2] * $invs + $b[2] * $s
        ];
    }

    abstract public function getColor() : int;
}
