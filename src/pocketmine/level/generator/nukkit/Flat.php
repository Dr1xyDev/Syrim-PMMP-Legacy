<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Flat generator
 */

namespace pocketmine\level\generator\nukkit;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\object\ore\OreType;
use pocketmine\level\generator\nukkit\populator\PopulatorOre;
use pocketmine\level\generator\nukkit\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\CoalOre;
use pocketmine\block\IronOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\LapisOre;
use pocketmine\block\GoldOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\Gravel;

class Flat extends Generator {

    /** @var ChunkManager */
    private $level;
    /** @var Random */
    private $random;
    /** @var Populator[] */
    private $populators = [];
    /** @var int[][] */
    private $structure;
    /** @var array */
    private $options;
    /** @var int */
    private $floorLevel = 0;
    /** @var string */
    private $preset = "2;7,2x3,2;1;";
    /** @var int */
    private $biome = 1;

    public function __construct(array $settings = []) {
        $this->options = $settings;
        if(isset($this->options["decoration"])) {
            $ores = new PopulatorOre();
            $ores->setOreTypes([
                new OreType(new CoalOre(), 20, 16, 0, 128),
                new OreType(new IronOre(), 20, 8, 0, 64),
                new OreType(new RedstoneOre(), 8, 7, 0, 16),
                new OreType(new LapisOre(), 1, 6, 0, 32),
                new OreType(new GoldOre(), 2, 8, 0, 32),
                new OreType(new DiamondOre(), 1, 7, 0, 16),
                new OreType(new Dirt(), 20, 32, 0, 128),
                new OreType(new Gravel(), 20, 16, 0, 128),
            ]);
            $this->populators[] = $ores;
        }
    }

    public function getId() : int {
        return self::TYPE_FLAT;
    }

    public function getChunkManager() : ChunkManager {
        return $this->level;
    }

    public function getSettings() {
        return $this->options;
    }

    public function getName() {
        return "flat";
    }

    private function parsePreset(string $preset) {
        try {
            $this->preset = $preset;
            $presetArray = explode(";", $preset);
            $blocks = $presetArray[1] ?? "";
            $this->biome = $presetArray[2] ?? 1;
            $this->structure = array_fill(0, 256, [0, 0]);
            $y = 0;
            foreach(explode(",", $blocks) as $block) {
                $id = 0; $meta = 0; $cnt = 1;
                if(preg_match("/^[0-9]{1,3}x[0-9]+$/", $block)) {
                    $s = explode("x", $block);
                    $cnt = (int) $s[0];
                    $id = (int) $s[1];
                } elseif(preg_match("/^[0-9]{1,3}:[0-9]{0,2}$/", $block)) {
                    $s = explode(":", $block);
                    $id = (int) $s[0];
                    $meta = (int) $s[1];
                } elseif(preg_match("/^[0-9]{1,3}$/", $block)) {
                    $id = (int) $block;
                } else {
                    continue;
                }
                $cY = $y;
                $y += $cnt;
                if($y > 255) $y = 255;
                for(; $cY < $y; ++$cY) {
                    $this->structure[$cY] = [$id, $meta];
                }
            }
            $this->floorLevel = $y;
        } catch(\Throwable $e) {
            \pocketmine\Server::getInstance()->getLogger()->error("error while parsing the preset: " . $e->getMessage());
        }
    }

    public function init(ChunkManager $level, Random $random) {
        $this->level = $level;
        $this->random = $random;
        $this->parsePreset($this->preset);
    }

    public function generateChunk($chunkX, $chunkZ) {
        $this->random->setSeed(($chunkX * 0xdeadbeef) ^ ($chunkZ * 0xdeadbeef) ^ $this->level->getSeed());
        /** @var Chunk $chunk */
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        for($Z = 0; $Z < 16; ++$Z) {
            for($X = 0; $X < 16; ++$X) {
                $chunk->setBiomeId($X, $Z, $this->biome);
                for($y = 0; $y < 256; ++$y) {
                    $chunk->setBlock($X, $y, $Z, $this->structure[$y][0], $this->structure[$y][1]);
                }
            }
        }
    }

    public function populateChunk($chunkX, $chunkZ) {
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        foreach($this->populators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }
    }

    public function getSpawn() {
        return new Vector3(128, $this->floorLevel, 128);
    }
}
