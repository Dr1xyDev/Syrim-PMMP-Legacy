<?php
/*
 * Syrim - Port de Nukkit 1.1.5 PopulatorCaves
 *
 * Syrim MEJORAS:
 * - Frecuencia reducida (menos cuevas)
 * - No genera cuevas cerca de rios (radio de seguridad)
 * - Forma más natural: tunel serpenteante con radio variable
 *   y curvatura orgánica en vez de esfera cúbica
 */

namespace pocketmine\level\generator\nukkit\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\nukkit\biome\Biome;
use pocketmine\level\generator\nukkit\biome\CaveBiome;
use pocketmine\utils\Random;

class PopulatorCaves extends Populator {

    /** @var int Radio de busqueda alrededor del chunk */
    protected $checkAreaSize = 4; // Syrim: reducido de 8 a 4 (menos densidad)

    /** @var int Rareza de cuevas (mayor = menos cuevas) */
    public static $caveRarity = 15;  // Syrim: era 7, subido para menos cuevas

    /** @var int Altitud minima de cuevas */
    public static $caveMinAltitude = 8;

    /** @var int Altitud maxima de cuevas */
    public static $caveMaxAltitude = 100; // Syrim: reducido de 128 a 100

    /** @var int Radio de seguridad alrededor de rios (no generar cuevas) */
    public static $riverSafeRadius = 12; // Syrim: bloques de distancia minima a un rio

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

                // Syrim: probabilidad reducida — 1 de cada caveRarity chunks genera cueva
                if(mt_rand(0, self::$caveRarity) !== 0) continue;

                // Syrim: 1-2 cuevas por chunk que pasa el filtro (antes eran 0-40)
                $count = mt_rand(1, 2);
                for($i = 0; $i < $count; ++$i) {
                    $caveX = ($x << 4) + mt_rand(0, 15);
                    $caveY = mt_rand(self::$caveMinAltitude, self::$caveMaxAltitude);
                    $caveZ = ($z << 4) + mt_rand(0, 15);

                    // Syrim: NO generar cerca de rios
                    if($this->isNearRiver($level, $generatingChunk, $caveX, $caveZ)) {
                        continue;
                    }

                    $this->generateCave($level, $generatingChunk, $caveX, $caveY, $caveZ);
                }
            }
        }
    }

    /**
     * Syrim: verifica si la posicion esta cerca de un rio.
     * Revisa el bioma en un radio alrededor de la posicion.
     * Si encuentra bioma RIVER dentro del radio, retorna true.
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
     * Syrim: genera una cueva natural (tunel serpenteante).
     *
     * El tunel tiene:
     * - Direccion inicial aleatoria que cambia gradualmente (serpenteo)
     * - Radio variable a lo largo del tunel (estrechamientos y ampliaciones)
     * - Inclinacion vertical suave (no solo horizontal)
     * - Forma organica (elipsoide alargado, no esfera cubica)
     */
    private function generateCave(ChunkManager $level, Chunk $chunk, int $x, int $y, int $z) {
        // Longitud del tunel (corto-medio para no perforar demasiado)
        $length = mt_rand(15, 40);

        // Direccion inicial (radianes)
        $angle = mt_rand(0, 360) * M_PI / 180;
        // Velocidad de cambio de direccion (serpenteo)
        $angleChange = 0.0;
        // Inclinacion vertical (-0.3 a 0.3 por paso)
        $pitch = (mt_rand(0, 100) / 100 - 0.5) * 0.3;
        $pitchChange = 0.0;

        // Radio base del tunel
        $baseRadius = 1.2 + (mt_rand(0, 100) / 100) * 0.8; // 1.2 a 2.0
        // Variacion de radio a lo largo del tunel
        $radiusVariation = 0.0;

        $cx = (float) $x;
        $cy = (float) $y;
        $cz = (float) $z;

        for($step = 0; $step < $length; ++$step) {
            // Actualizar direccion gradualmente (serpenteo natural)
            $angleChange += (mt_rand(0, 100) / 100 - 0.5) * 0.15;
            $angleChange = max(-0.4, min(0.4, $angleChange)); // clamp
            $angle += $angleChange;

            // Actualizar inclinacion gradualmente
            $pitchChange += (mt_rand(0, 100) / 100 - 0.5) * 0.08;
            $pitchChange = max(-0.15, min(0.15, $pitchChange)); // clamp
            $pitch += $pitchChange;
            $pitch = max(-0.5, min(0.5, $pitch)); // limitar inclinacion

            // Avanzar
            $cx += cos($angle) * 1.0;
            $cz += sin($angle) * 1.0;
            $cy += $pitch;

            // Radio variable (respiraciones y estrechamientos)
            $radiusVariation += (mt_rand(0, 100) / 100 - 0.5) * 0.3;
            $radiusVariation = max(-0.5, min(0.5, $radiusVariation)); // clamp
            $radius = $baseRadius + $radiusVariation;

            // Solo excavar dentro del chunk generador
            $localX = (int)($cx - ($chunk->getX() << 4));
            $localZ = (int)($cz - ($chunk->getZ() << 4));

            if($localX < 0 || $localX > 15 || $localZ < 0 || $localZ > 15) continue;
            if($cy < 1 || $cy > 126) continue;

            $biome = Biome::getBiome($chunk->getBiomeId($localX, $localZ));
            $stoneBlock = ($biome instanceof CaveBiome) ? $biome->getStoneBlock() : Block::STONE;

            // Excavar con forma elipsoidal (mas alto que ancho para tunel natural)
            $rIntX = (int) ceil($radius);
            $rIntY = (int) ceil($radius * 0.7);  // mas estrecho verticalmente
            $rIntZ = (int) ceil($radius);

            for($dx = -$rIntX; $dx <= $rIntX; ++$dx) {
                for($dy = -$rIntY; $dy <= $rIntY; ++$dy) {
                    for($dz = -$rIntZ; $dz <= $rIntZ; ++$dz) {
                        // Forma elipsoidal suave
                        $nx = $dx / $radius;
                        $ny = $dy / ($radius * 0.7);
                        $nz = $dz / $radius;
                        $distSq = $nx * $nx + $ny * $ny + $nz * $nz;

                        // Suavizar bordes con un poco de ruido
                        if($distSq <= 1.0) {
                            // No excavar todos los bloques del borde (deja pilares naturales)
                            if($distSq > 0.85 && mt_rand(0, 3) === 0) continue;

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
