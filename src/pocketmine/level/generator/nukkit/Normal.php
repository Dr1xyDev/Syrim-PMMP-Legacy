<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Normal generator
 * Generador de mundo normal (overworld) vanilla de Nukkit.
 * Porteado desde el source exacto de CreeperFace y Nycuro.
 */

namespace pocketmine\level\generator\nukkit;

use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\Stone;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\biome\Biome;
use pocketmine\level\generator\nukkit\biome\BiomeSelector;
use pocketmine\level\generator\nukkit\noise\Simplex;
use pocketmine\level\generator\nukkit\object\ore\OreType;
use pocketmine\level\generator\nukkit\populator\Populator;
use pocketmine\level\generator\nukkit\populator\PopulatorCaves;
use pocketmine\level\generator\nukkit\populator\PopulatorGroundCover;
use pocketmine\level\generator\nukkit\populator\PopulatorOre;
use pocketmine\level\generator\nukkit\populator\PopulatorRavines;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Normal extends Generator {

    public function getId() : int {
        return self::TYPE_INFINITE;
    }

    /** @var ChunkManager */
    private $level;
    /** @var Random */
    private $nukkitRandom;
    /** @var int */
    private $localSeed1;
    /** @var int */
    private $localSeed2;
    /** @var Populator[] */
    private $populators = [];
    /** @var Populator[] */
    private $generationPopulators = [];

    /** @var Simplex */
    private $noiseSeaFloor;
    /** @var Simplex */
    private $noiseLand;
    /** @var Simplex */
    private $noiseMountains;
    /** @var Simplex */
    private $noiseBaseGround;
    /** @var Simplex */
    private $noiseRiver;

    /** @var BiomeSelector */
    private $selector;
    /** @var int */
    private $heightOffset;

    private $seaHeight = 64;
    private $seaFloorHeight = 48;
    private $beathStartHeight = 60;
    private $beathStopHeight = 64;
    private $bedrockDepth = 5;
    private $seaFloorGenerateRange = 5;
    private $landHeightRange = 18;
    private $mountainHeight = 13;
    private $basegroundHeight = 3;

    public function __construct(array $settings = []) {
        // Nothing here. Just used for future update.
    }

    public function getChunkManager() : ChunkManager {
        return $this->level;
    }

    public function getName() {
        return "normal";
    }

    public function getSettings() {
        return [];
    }

    public function pickBiome(int $x, int $z) : Biome {
        $hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();
        $hash = $hash * ($hash + 223);

        $xNoise = ($hash >> 20) & 3;
        $zNoise = ($hash >> 22) & 3;

        if($xNoise === 3) $xNoise = 1;
        if($zNoise === 3) $zNoise = 1;

        return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
    }

    public function init(ChunkManager $level, Random $random) {
        $this->level = $level;
        $this->nukkitRandom = $random;
        $this->nukkitRandom->setSeed($this->level->getSeed());
        $this->localSeed1 = mt_rand(0, PHP_INT_MAX);
        $this->localSeed2 = mt_rand(0, PHP_INT_MAX);

        $this->noiseSeaFloor = new Simplex($this->nukkitRandom, 1.0, 1.0 / 8.0, 1.0 / 64.0);
        $this->noiseLand = new Simplex($this->nukkitRandom, 2.0, 1.0 / 8.0, 1.0 / 512.0);
        $this->noiseMountains = new Simplex($this->nukkitRandom, 4.0, 1.0, 1.0 / 500.0);
        $this->noiseBaseGround = new Simplex($this->nukkitRandom, 4.0, 1.0 / 4.0, 1.0 / 64.0);
        $this->noiseRiver = new Simplex($this->nukkitRandom, 2.0, 1.0, 1.0 / 512.0);
        $this->nukkitRandom->setSeed($this->level->getSeed());
        $this->selector = new BiomeSelector($this->nukkitRandom, Biome::getBiome(Biome::FOREST));
        $this->heightOffset = $random->nextRange(-5, 3);

        // Syrim: registrar TODOS los biomas de Nukkit
        Biome::init();

        $this->selector->addBiome(Biome::getBiome(Biome::OCEAN));
        $this->selector->addBiome(Biome::getBiome(Biome::PLAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::DESERT));
        $this->selector->addBiome(Biome::getBiome(Biome::FOREST));
        $this->selector->addBiome(Biome::getBiome(Biome::TAIGA));
        $this->selector->addBiome(Biome::getBiome(Biome::RIVER));
        $this->selector->addBiome(Biome::getBiome(Biome::ICE_PLAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::BIRCH_FOREST));
        $this->selector->addBiome(Biome::getBiome(Biome::JUNGLE));
        $this->selector->addBiome(Biome::getBiome(Biome::SAVANNA));
        $this->selector->addBiome(Biome::getBiome(Biome::ROOFED_FOREST));
        $this->selector->addBiome(Biome::getBiome(Biome::ROOFED_FOREST_M));
        $this->selector->addBiome(Biome::getBiome(Biome::MUSHROOM_ISLAND));
        $this->selector->addBiome(Biome::getBiome(Biome::SWAMP));
        // Syrim NEW: bioma Mesa (meseta)
        $this->selector->addBiome(Biome::getBiome(Biome::MESA));

        $this->selector->recalculate();

        $caves = new PopulatorCaves();
        $this->populators[] = $caves;

        $ravines = new PopulatorRavines();
        $this->populators[] = $ravines;

        $cover = new PopulatorGroundCover();
        $this->generationPopulators[] = $cover;

        $ores = new PopulatorOre();
        $ores->setOreTypes([
            new OreType(new CoalOre(), 20, 17, 0, 128),
            new OreType(new IronOre(), 20, 9, 0, 64),
            new OreType(new RedstoneOre(), 8, 8, 0, 16),
            new OreType(new LapisOre(), 1, 7, 0, 16),
            new OreType(new GoldOre(), 2, 9, 0, 32),
            new OreType(new DiamondOre(), 1, 8, 0, 16),
            new OreType(new Dirt(), 10, 33, 0, 128),
            new OreType(new Gravel(), 8, 33, 0, 128),
            new OreType(new Stone(Stone::GRANITE), 10, 33, 0, 80),
            new OreType(new Stone(Stone::DIORITE), 10, 33, 0, 80),
            new OreType(new Stone(Stone::ANDESITE), 10, 33, 0, 80),
        ]);
        $this->populators[] = $ores;
    }

    public function generateChunk($chunkX, $chunkZ) {
        $this->nukkitRandom->setSeed($chunkX * $this->localSeed1 ^ $chunkZ * $this->localSeed2 ^ $this->level->getSeed());

        $seaFloorNoise = self::getNukkitNoise2D($this->noiseSeaFloor, 16, 16, 4, $chunkX * 16, 0, $chunkZ * 16);
        $landNoise = self::getNukkitNoise2D($this->noiseLand, 16, 16, 4, $chunkX * 16, 0, $chunkZ * 16);
        $mountainNoise = self::getNukkitNoise2D($this->noiseMountains, 16, 16, 4, $chunkX * 16, 0, $chunkZ * 16);
        $baseNoise = self::getNukkitNoise2D($this->noiseBaseGround, 16, 16, 4, $chunkX * 16, 0, $chunkZ * 16);
        $riverNoise = self::getNukkitNoise2D($this->noiseRiver, 16, 16, 4, $chunkX * 16, 0, $chunkZ * 16);

        /** @var Chunk $chunk */
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($genx = 0; $genx < 16; ++$genx) {
            for($genz = 0; $genz < 16; ++$genz) {

                $biome = null;
                $canBaseGround = false;
                $canRiver = true;

                // using a quadratic function which smooth the world
                // y = (2.956x)^2 - 0.6, (0 <= x <= 2)
                $landHeightNoise = $landNoise[$genx][$genz] + 1.0;
                $landHeightNoise *= 2.956;
                $landHeightNoise = $landHeightNoise * $landHeightNoise;
                $landHeightNoise = $landHeightNoise - 0.6;
                $landHeightNoise = $landHeightNoise > 0 ? $landHeightNoise : 0;

                // generate mountains
                $mountainHeightGenerate = $mountainNoise[$genx][$genz] - 0.2;
                $mountainHeightGenerate = $mountainHeightGenerate > 0 ? $mountainHeightGenerate : 0;
                $mountainGenerate = (int) ($this->mountainHeight * $mountainHeightGenerate);

                $landHeightGenerate = (int) ($this->landHeightRange * $landHeightNoise);
                if($landHeightGenerate > $this->landHeightRange) {
                    if($landHeightGenerate > $this->landHeightRange) {
                        $canBaseGround = true;
                    }
                    $landHeightGenerate = $this->landHeightRange;
                }

                $genyHeight = $this->seaFloorHeight + $landHeightGenerate;
                $genyHeight += $mountainGenerate;

                // prepare for generate ocean, desert, and land
                if($genyHeight < $this->beathStartHeight) {
                    if($genyHeight < $this->beathStartHeight - 5) {
                        $genyHeight += (int) ($this->seaFloorGenerateRange * $seaFloorNoise[$genx][$genz]);
                    }
                    $biome = Biome::getBiome(Biome::OCEAN);
                    if($genyHeight < $this->seaFloorHeight - $this->seaFloorGenerateRange) {
                        $genyHeight = $this->seaFloorHeight;
                    }
                    $canRiver = false;
                } elseif($genyHeight <= $this->beathStopHeight && $genyHeight >= $this->beathStartHeight) {
                    $biome = Biome::getBiome(Biome::BEACH);
                } else {
                    $biome = $this->pickBiome($chunkX * 16 + $genx, $chunkZ * 16 + $genz);
                    if($canBaseGround) {
                        $baseGroundHeight = (int) ($this->landHeightRange * $landHeightNoise) - $this->landHeightRange;
                        $baseGroundHeight2 = (int) ($this->basegroundHeight * ($baseNoise[$genx][$genz] + 1.0));
                        if($baseGroundHeight2 > $baseGroundHeight) $baseGroundHeight2 = $baseGroundHeight;
                        if($baseGroundHeight2 > $mountainGenerate) {
                            $baseGroundHeight2 = $baseGroundHeight2 - $mountainGenerate;
                        } else {
                            $baseGroundHeight2 = 0;
                        }
                        $genyHeight += $baseGroundHeight2;
                    }
                }
                if($canRiver && $genyHeight <= $this->seaHeight - 5) {
                    $canRiver = false;
                }

                // generate river
                if($canRiver) {
                    $riverGenerate = $riverNoise[$genx][$genz];
                    if($riverGenerate > -0.25 && $riverGenerate < 0.25) {
                        $riverGenerate = $riverGenerate > 0 ? $riverGenerate : -$riverGenerate;
                        $riverGenerate = 0.25 - $riverGenerate;
                        // y=x^2 * 4 - 0.0000001
                        $riverGenerate = $riverGenerate * $riverGenerate * 4.0;
                        // smooth again
                        $riverGenerate = $riverGenerate - 0.0000001;
                        $riverGenerate = $riverGenerate > 0 ? $riverGenerate : 0;
                        $genyHeight -= $riverGenerate * 64;
                        if($genyHeight < $this->seaHeight) {
                            $biome = Biome::getBiome(Biome::RIVER);
                            // to generate river floor
                            if($genyHeight <= $this->seaHeight - 8) {
                                $genyHeight1 = $this->seaHeight - 9 + (int) ($this->basegroundHeight * ($baseNoise[$genx][$genz] + 1.0));
                                $genyHeight2 = $genyHeight < $this->seaHeight - 7 ? $this->seaHeight - 7 : $genyHeight;
                                $genyHeight = $genyHeight1 > $genyHeight2 ? $genyHeight1 : $genyHeight2;
                            }
                        }
                    }
                }

                $chunk->setBiomeId($genx, $genz, $biome->getId());
                // Syrim: PMMP no tiene setBiomeColor, el color se calcula del biomeId.

                // generating
                $generateHeight = $genyHeight > $this->seaHeight ? $genyHeight : $this->seaHeight;
                for($geny = 0; $geny <= $generateHeight; ++$geny) {
                    if($geny <= $this->bedrockDepth && ($geny === 0 || $this->nukkitRandom->nextRange(1, 5) === 1)) {
                        $chunk->setBlockId($genx, $geny, $genz, Block::BEDROCK);
                    } elseif($geny > $genyHeight) {
                        if(($biome->getId() === Biome::ICE_PLAINS || $biome->getId() === Biome::TAIGA) && $geny === $this->seaHeight) {
                            $chunk->setBlockId($genx, $geny, $genz, Block::ICE);
                        } else {
                            $chunk->setBlockId($genx, $geny, $genz, Block::STILL_WATER);
                        }
                    } else {
                        $chunk->setBlockId($genx, $geny, $genz, Block::STONE);
                    }
                }
            }
        }

        // generation populators (ground cover)
        foreach($this->generationPopulators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->nukkitRandom);
        }
    }

    public function populateChunk($chunkX, $chunkZ) {
        $this->nukkitRandom->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        foreach($this->populators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->nukkitRandom);
        }

        /** @var Chunk $chunk */
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        if($chunk !== null) {
            $biome = Biome::getBiome($chunk->getBiomeId(7, 7));
            $biome->populateChunk($this->level, $chunkX, $chunkZ, $this->nukkitRandom);
        }
    }

    public function getSpawn() {
        return new Vector3(127.5, 128, 127.5);
    }
}
