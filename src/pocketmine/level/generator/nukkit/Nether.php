<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Nether generator
 */

namespace pocketmine\level\generator\nukkit;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\biome\Biome;
use pocketmine\level\generator\nukkit\noise\Simplex;
use pocketmine\level\generator\nukkit\object\ore\OreType;
use pocketmine\level\generator\nukkit\populator\Populator;
use pocketmine\level\generator\nukkit\populator\PopulatorGlowStone;
use pocketmine\level\generator\nukkit\populator\PopulatorGroundFire;
use pocketmine\level\generator\nukkit\populator\PopulatorLava;
use pocketmine\level\generator\nukkit\populator\PopulatorOre;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Nether extends Generator {

    /** @var ChunkManager */
    private $level;
    /** @var Random */
    private $nukkitRandom;
    /** @var float */
    private $waterHeight = 32.0;
    /** @var float */
    private $emptyHeight = 64.0;
    /** @var float */
    private $emptyAmplitude = 1.0;
    /** @var float */
    private $density = 0.5;
    /** @var float */
    private $bedrockDepth = 5.0;
    /** @var Populator[] */
    private $populators = [];
    /** @var Populator[] */
    private $generationPopulators = [];
    /** @var int */
    private $localSeed1;
    /** @var int */
    private $localSeed2;
    /** @var Simplex */
    private $noiseBase;

    public function __construct(array $settings = []) {
    }

    public function getId() : int {
        return self::TYPE_NETHER;
    }

    public function getDimension() : int {
        return Level::DIMENSION_NETHER;
    }

    public function getName() {
        return "nether";
    }

    public function getSettings() {
        return [];
    }

    public function getChunkManager() : ChunkManager {
        return $this->level;
    }

    public function init(ChunkManager $level, Random $random) {
        $this->level = $level;
        $this->nukkitRandom = $random;
        $this->nukkitRandom->setSeed($this->level->getSeed());
        $this->noiseBase = new Simplex($this->nukkitRandom, 4.0, 1.0 / 4.0, 1.0 / 64.0);
        $this->nukkitRandom->setSeed($this->level->getSeed());
        $this->localSeed1 = mt_rand(0, PHP_INT_MAX);
        $this->localSeed2 = mt_rand(0, PHP_INT_MAX);

        Biome::init();

        $ores = new PopulatorOre(Block::NETHERRACK);
        $ores->setOreTypes([
            new OreType(Block::get(Block::NETHER_QUARTZ_ORE), 20, 16, 0, 128),
            new OreType(Block::get(Block::SOUL_SAND), 5, 64, 0, 128),
            new OreType(Block::get(Block::GRAVEL), 5, 64, 0, 128),
            new OreType(Block::get(Block::LAVA), 1, 16, 0, (int) $this->waterHeight),
        ]);
        $this->populators[] = $ores;
        $this->populators[] = new PopulatorGlowStone();
        $groundFire = new PopulatorGroundFire();
        $groundFire->setBaseAmount(1);
        $groundFire->setRandomAmount(1);
        $this->populators[] = $groundFire;
        $lava = new PopulatorLava();
        $lava->setBaseAmount(0);
        $lava->setRandomAmount(2);
        $this->populators[] = $lava;
    }

    public function generateChunk($chunkX, $chunkZ) {
        $this->nukkitRandom->setSeed($chunkX * $this->localSeed1 ^ $chunkZ * $this->localSeed2 ^ $this->level->getSeed());

        $noise = self::getNukkitNoise3D($this->noiseBase, 16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);
        /** @var Chunk $chunk */
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($x = 0; $x < 16; ++$x) {
            for($z = 0; $z < 16; ++$z) {
                $biome = Biome::getBiome(Biome::HELL);
                $chunk->setBiomeId($x, $z, Biome::HELL);
                // Syrim: PMMP chunks no tienen setBiomeColor - se omite.

                $chunk->setBlockId($x, 0, $z, Block::BEDROCK);
                $chunk->setBlockId($x, 127, $z, Block::BEDROCK);

                for($y = 1; $y <= $this->bedrockDepth; ++$y) {
                    if($this->nukkitRandom->nextRange(1, 5) === 1) {
                        $chunk->setBlockId($x, $y, $z, Block::BEDROCK);
                        $chunk->setBlockId($x, 127 - $y, $z, Block::BEDROCK);
                    }
                }

                for($y = 1; $y < 127; ++$y) {
                    $noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
                    $noiseValue -= 1 - $this->density;
                    if($noiseValue > 0) {
                        $chunk->setBlockId($x, $y, $z, Block::NETHERRACK);
                    } elseif($y <= $this->waterHeight) {
                        $chunk->setBlockId($x, $y, $z, Block::STILL_LAVA);
                        $chunk->setBlockLight($x, $y + 1, $z, 15);
                    }
                }
            }
        }

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
        $biome = Biome::getBiome($chunk->getBiomeId(7, 7));
        $biome->populateChunk($this->level, $chunkX, $chunkZ, $this->nukkitRandom);
    }

    public function getSpawn() {
        return new Vector3(0, 64, 0);
    }
}
