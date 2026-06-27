<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorRavines
 *
 * Syrim MEJORAS:
 * - Frecuencia reducida (menos barrancos)
 * - No genera cerca de rios (radio de seguridad)
 * - Forma más natural: barranco serpenteante con paredes asimetricas
 *   y profundidad variable en vez de tunel elipsoidal cubico
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\biome\Biome;
use pocketmine\level\generator\nukkit\biome\CaveBiome;
use pocketmine\utils\Random;

class PopulatorRavines extends Populator {

    /** @var int Radio de busqueda */
    protected $checkAreaSize = 4; // Syrim: reducido de 8 a 4

    /** @var int Rareza (mayor = menos barrancos) */
    private $ravineRarity = 600; // Syrim: era 300, subido para menos barrancos

    /** @var int */
    private $ravineMinAltitude = 20;
    /** @var int */
    private $ravineMaxAltitude = 67;
    /** @var int */
    private $ravineMinLength = 50;  // Syrim: reducido de 84 a 50
    /** @var int */
    private $ravineMaxLength = 80;  // Syrim: reducido de 111 a 80
    /** @var float */
    private $ravineDepth = 3.0;
    /** @var int */
    private $worldHeightCap = 256;

    /** @var int Radio de seguridad alrededor de rios */
    public static $riverSafeRadius = 12;

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) {
        $localSeed1 = ($level->getSeed() & 0xFFFFFFFF);
        $localSeed2 = ($level->getSeed() >> 32) & 0xFFFFFFFF;
        if($localSeed1 === 0) $localSeed1 = 0x12345678;
        if($localSeed2 === 0) $localSeed2 = 0x9ABCDEF0;

        $size = $this->checkAreaSize;
        /** @var Chunk $generatingChunk */
        $generatingChunk = $level->getChunk($chunkX, $chunkZ);
        if($generatingChunk === null) return;

        for($x = $chunkX - $size; $x <= $chunkX + $size; ++$x) {
            for($z = $chunkZ - $size; $z <= $chunkZ + $size; ++$z) {
                $seed = ($x * $localSeed1) ^ ($z * $localSeed2) ^ $level->getSeed();
                mt_srand(abs($seed));
                if(mt_rand(0, $this->ravineRarity) < 1) {
                    $ravineX = ($x << 4) + mt_rand(0, 15);
                    $ravineY = mt_rand($this->ravineMinAltitude, $this->ravineMaxAltitude);
                    $ravineZ = ($z << 4) + mt_rand(0, 15);
                    $length = mt_rand($this->ravineMinLength, $this->ravineMaxLength);

                    // Syrim: NO generar cerca de rios
                    if($this->isNearRiver($level, $generatingChunk, $ravineX, $ravineZ)) {
                        continue;
                    }

                    $this->generateRavine($level, $generatingChunk, $ravineX, $ravineY, $ravineZ, $length);
                }
            }
        }
    }

    /**
     * Syrim: verifica si la posicion esta cerca de un rio.
     */
    private function isNearRiver(ChunkManager $level, Chunk $chunk, int $worldX, int $worldZ) : bool {
        $radius = self::$riverSafeRadius;
        for($dx = -$radius; $dx <= $radius; $dx += 4) {
            for($dz = -$radius; $dz <= $radius; $dz += 4) {
                $cx = ($worldX + $dx) >> 4;
                $cz = ($worldZ + $dz) >> 4;
                $lx = ($worldX + $dx) & 0x0f;
                $lz = ($worldZ + $dz) & 0x0f;

                $checkChunk = $level->getChunk($cx, $cz);
                if($checkChunk !== null) {
                    $biomeId = $checkChunk->getBiomeId($lx, $lz);
                    if($biomeId === Biome::RIVER) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Syrim: genera un barranco natural (serpenteante, asimetrico).
     *
     * Caracteristicas vanilla-like:
     * - Sigue una curva serpenteante con cambios de direccion suaves
     * - Se ensancha y se estrecha a lo largo del recorrido
     * - La profundidad varia (mas profundo en el centro)
     * - Las paredes son asimetricas (no perfectamente circulares)
     * - Deja bloques aleatorios en los bordes para dar textura natural
     */
    private function generateRavine(ChunkManager $level, Chunk $chunk, int $x, int $y, int $z, int $length) {
        // Direccion inicial
        $angle = mt_rand(0, 360) * M_PI / 180;
        $angleChange = 0.0;

        // Inclinacion (los barrancos son mayormente horizontales)
        $pitch = (mt_rand(0, 100) / 100 - 0.5) * 0.15;
        $pitchChange = 0.0;

        // Radio base
        $baseRadiusXZ = 2.0 + (mt_rand(0, 100) / 100) * 1.5; // 2.0 a 3.5
        $baseRadiusY = $baseRadiusXZ * 0.6; // mas plano que ancho

        // Variacion de radio a lo largo del barranco
        $radiusVariation = 0.0;

        $cx = (float) $x;
        $cy = (float) $y;
        $cz = (float) $z;

        for($step = 0; $step < $length; ++$step) {
            // Serpenteo: cambios de direccion suaves y graduales
            $angleChange += (mt_rand(0, 100) / 100 - 0.5) * 0.2;
            $angleChange = max(-0.5, min(0.5, $angleChange));
            $angle += $angleChange;

            // Inclinacion variable
            $pitchChange += (mt_rand(0, 100) / 100 - 0.5) * 0.05;
            $pitchChange = max(-0.1, min(0.1, $pitchChange));
            $pitch += $pitchChange;
            $pitch = max(-0.3, min(0.3, $pitch));

            // Avanzar
            $cx += cos($angle);
            $cz += sin($angle);
            $cy += $pitch;

            // Radio variable (respiraciones del barranco)
            $radiusVariation += (mt_rand(0, 100) / 100 - 0.5) * 0.4;
            $radiusVariation = max(-1.0, min(1.0, $radiusVariation));

            $radiusXZ = $baseRadiusXZ + $radiusVariation;
            $radiusY = $radiusXZ * 0.6;

            // Factor de profundidad: mas profundo en el centro del barranco
            $depthFactor = sin(M_PI * $step / $length); // 0 al inicio, 1 en el centro, 0 al final
            $radiusY *= (0.5 + $depthFactor * 0.8); // mas alto en el centro

            // Solo excavar dentro del chunk
            $localX = (int)($cx - ($chunk->getX() << 4));
            $localZ = (int)($cz - ($chunk->getZ() << 4));

            if($localX < 0 || $localX > 15 || $localZ < 0 || $localZ > 15) continue;
            if($cy < 1 || $cy > 126) continue;

            $biome = Biome::getBiome($chunk->getBiomeId($localX, $localZ));
            $stoneBlock = ($biome instanceof CaveBiome) ? $biome->getStoneBlock() : Block::STONE;

            $rIntX = (int) ceil($radiusXZ);
            $rIntY = (int) ceil($radiusY);
            $rIntZ = (int) ceil($radiusXZ);

            for($dx = -$rIntX; $dx <= $rIntX; ++$dx) {
                for($dy = -$rIntY; $dy <= $rIntY; ++$dy) {
                    for($dz = -$rIntZ; $dz <= $rIntZ; ++$dz) {
                        // Forma elipsoidal aplanada (barranco natural)
                        $nx = $dx / $radiusXZ;
                        $ny = $dy / $radiusY;
                        $nz = $dz / $radiusXZ;
                        $distSq = $nx * $nx + $ny * $ny + $nz * $nz;

                        if($distSq <= 1.0) {
                            // Dejar bloques aleatorios en los bordes para textura natural
                            if($distSq > 0.75 && mt_rand(0, 2) === 0) continue;

                            $tx = $localX + $dx;
                            $ty = (int)($cy + $dy);
                            $tz = $localZ + $dz;
                            if($tx < 0 || $tx > 15 || $tz < 0 || $tz > 15) continue;
                            if($ty < 1 || $ty > 126) continue;

                            $bid = $chunk->getBlockId($tx, $ty, $tz);
                            if($bid === $stoneBlock || $bid === Block::DIRT || $bid === Block::GRASS) {
                                $chunk->setBlockId($tx, $ty, $tz, Block::AIR);
                            }
                        }
                    }
                }
            }
        }
    }
}
