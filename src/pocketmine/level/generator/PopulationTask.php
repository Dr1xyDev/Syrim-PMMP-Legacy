<?php

/*
 *  ░█▀▀░█░█░█▀▄░▀█▀░█▄█
 *  ░▀▀█░░█░░█▀▄░░█░░█░█
 *  ░▀▀▀░░▀░░▀░▀░▀▀▀░▀░▀
 *
 *  Syrim - PocketMine-MP based core
 *  Version : 1.0.6
 *  Author  : Dr1xy dev
 *  API     : 3.0.1
 *
 */

namespace pocketmine\level\generator;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class PopulationTask extends AsyncTask{

        const BORDER_SIZE = 1;

        public $state;
        public $levelId;
        public $chunk;

        public $chunk0;
        public $chunk1;
        public $chunk2;
        public $chunk3;
        public $chunk5;
        public $chunk6;
        public $chunk7;
        public $chunk8;

        public $oldChunk0;
        public $oldChunk1;
        public $oldChunk2;
        public $oldChunk3;
        public $oldChunk5;
        public $oldChunk6;
        public $oldChunk7;
        public $oldChunk8;

        public function __construct(Level $level, Chunk $chunk){
                $this->state = true;
                $this->levelId = $level->getId();
                $this->chunk = $chunk->fastSerialize();

                $chunkX = $chunk->getX();
                $chunkZ = $chunk->getZ();

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }
                        $xx = -1 + ($i % 3);
                        $zz = -1 + (int) ($i / 3);
                        $ncx = $chunkX + $xx;
                        $ncz = $chunkZ + $zz;
                        $neighbor = $level->getChunk($ncx, $ncz, false);
                        $prop = "chunk" . $i;
                        $oldProp = "oldChunk" . $i;
                        $this->{$prop} = $neighbor !== null ? $neighbor->fastSerialize() : null;
                        $this->{$oldProp} = $neighbor !== null ? $neighbor->fastSerialize() : null;
                }
        }

        public function onRun(){
                $manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
                $generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
                if(!($manager instanceof SimpleChunkManager) or !($generator instanceof Generator)){
                        $this->state = false;
                        return;
                }

                $chunk = Chunk::fastDeserialize($this->chunk);
                if($chunk === null){
                        $this->state = false;
                        return;
                }

                $manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
                if(!$chunk->isGenerated()){
                        $generator->generateChunk($chunk->getX(), $chunk->getZ());
                        $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
                        $chunk->setGenerated();
                }

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }
                        $prop = "chunk" . $i;
                        $ser = $this->{$prop};
                        if($ser === null){
                                $xx = -1 + ($i % 3);
                                $zz = -1 + (int) ($i / 3);
                                $neighbor = new Chunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
                                $manager->setChunk($neighbor->getX(), $neighbor->getZ(), $neighbor);
                                $generator->generateChunk($neighbor->getX(), $neighbor->getZ());
                                $neighbor = $manager->getChunk($neighbor->getX(), $neighbor->getZ());
                                $neighbor->setGenerated();
                                $this->{$prop} = $neighbor->fastSerialize();
                        }else{
                                $neighbor = Chunk::fastDeserialize($ser);
                                if($neighbor !== null){
                                        $manager->setChunk($neighbor->getX(), $neighbor->getZ(), $neighbor);
                                }
                        }
                }

                $generator->populateChunk($chunk->getX(), $chunk->getZ());
                $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
                $chunk->setPopulated();
                $chunk->recalculateHeightMap();
                $chunk->populateSkyLight();
                $chunk->setLightPopulated();
                $this->chunk = $chunk->fastSerialize();

                for($i = 0; $i < 9; ++$i){
                        if($i === 4){
                                continue;
                        }
                        $prop = "chunk" . $i;
                        $oldProp = "oldChunk" . $i;
                        $xx = -1 + ($i % 3);
                        $zz = -1 + (int) ($i / 3);
                        $neighbor = $manager->getChunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
                        if($neighbor !== null){
                                $this->{$prop} = $neighbor->fastSerialize();
                        }
                        $this->{$oldProp} = null;
                }

                $manager->cleanChunks();
        }

        public function onCompletion(Server $server){
                $level = $server->getLevel($this->levelId);
                if($level !== null){
                        if(!$this->state){
                                $level->registerGenerator();
                        }

                        $chunk = Chunk::fastDeserialize($this->chunk);
                        for($i = 0; $i < 9; ++$i){
                                if($i === 4){
                                        continue;
                                }
                                $prop = "chunk" . $i;
                                $ser = $this->{$prop};
                                if($ser === null){
                                        continue;
                                }
                                $nchunk = Chunk::fastDeserialize($ser);
                                if($nchunk !== null){
                                        $level->generateChunkCallback($nchunk->getX(), $nchunk->getZ(), $nchunk);
                                }
                        }
                        $level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $this->state ? $chunk : null);
                }
        }
}
