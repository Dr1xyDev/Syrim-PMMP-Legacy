<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Generator (base)
 *
 * Syrim: esta clase extiende del Generator de PMMP para que los generadores
 * de Nukkit puedan registrarse via PMMPGenerator::addGenerator().
 * Se eliminaron TODOS los metodos static que entraban en conflicto con
 * la firma del padre PMMP (addGenerator, getGenerator, getFastNoise*, etc.).
 * Los metodos getFastNoise* se renombraron a getNukkitNoise* para evitar
 * conflictos de firma con PMMP que usa \SplFixedArray.
 */

namespace pocketmine\level\generator\nukkit;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\nukkit\noise\Noise;
use pocketmine\level\generator\Generator as PMMPGenerator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class Generator extends PMMPGenerator {

    public const TYPE_OLD = 0;
    public const TYPE_INFINITE = 1;
    public const TYPE_FLAT = 2;
    public const TYPE_NETHER = 3;

    abstract public function getId() : int;

    public function getDimension() : int {
        return 0; // Level::DIMENSION_OVERWORLD
    }

    public function getChunkManager() : ChunkManager {
        return $this->level;
    }

    /**
     * Syrim: version Nukkit de getFastNoise1D. Renombrada para evitar
     * conflicto de firma con PMMPGenerator::getFastNoise1D que usa \SplFixedArray.
     */
    public static function getNukkitNoise1D(Noise $noise, int $xSize, int $samplingRate, int $x, int $y, int $z) : array {
        if($samplingRate === 0) {
            throw new \InvalidArgumentException("samplingRate cannot be 0");
        }
        if($xSize % $samplingRate !== 0) {
            throw new \InvalidArgumentException("xSize % samplingRate must return 0");
        }
        $noiseArray = array_fill(0, $xSize + 1, 0.0);

        for($xx = 0; $xx <= $xSize; $xx += $samplingRate) {
            $noiseArray[$xx] = $noise->noise3D($xx + $x, $y, $z);
        }

        for($xx = 0; $xx < $xSize; ++$xx) {
            if($xx % $samplingRate !== 0) {
                $nx = (int)($xx / $samplingRate) * $samplingRate;
                $noiseArray[$xx] = Noise::linearLerp($xx, $nx, $nx + $samplingRate, $noiseArray[$nx], $noiseArray[$nx + $samplingRate]);
            }
        }

        return $noiseArray;
    }

    /**
     * Syrim: version Nukkit de getFastNoise2D. Renombrada para evitar
     * conflicto de firma con PMMPGenerator::getFastNoise2D.
     */
    public static function getNukkitNoise2D(Noise $noise, int $xSize, int $zSize, int $samplingRate, int $x, int $y, int $z, int $xZoom = 0, int $zZoom = 0) : array {
        if($samplingRate === 0) {
            throw new \InvalidArgumentException("samplingRate cannot be 0");
        }
        if($xSize % $samplingRate !== 0) {
            throw new \InvalidArgumentException("xSize % samplingRate must return 0");
        }
        if($zSize % $samplingRate !== 0) {
            throw new \InvalidArgumentException("zSize % samplingRate must return 0");
        }

        $noiseArray = [];
        for($xx = 0; $xx <= $xSize; $xx += $samplingRate) {
            $noiseArray[$xx] = array_fill(0, $zSize + 1, 0.0);
            for($zz = 0; $zz <= $zSize; $zz += $samplingRate) {
                $noiseArray[$xx][$zz] = $noise->noise3D(($x + $xx) >> $xZoom, $y, ($z + $zz) >> $zZoom);
            }
        }

        for($xx = 0; $xx < $xSize; ++$xx) {
            if($xx % $samplingRate !== 0) {
                $noiseArray[$xx] = array_fill(0, $zSize + 1, 0.0);
            }
            for($zz = 0; $zz < $zSize; ++$zz) {
                if($xx % $samplingRate !== 0 || $zz % $samplingRate !== 0) {
                    $nx = (int)($xx / $samplingRate) * $samplingRate;
                    $nz = (int)($zz / $samplingRate) * $samplingRate;
                    $noiseArray[$xx][$zz] = Noise::bilinearLerp(
                        $xx, $zz, $noiseArray[$nx][$nz], $noiseArray[$nx][$nz + $samplingRate],
                        $noiseArray[$nx + $samplingRate][$nz], $noiseArray[$nx + $samplingRate][$nz + $samplingRate],
                        $nx, $nx + $samplingRate, $nz, $nz + $samplingRate
                    );
                }
            }
        }
        return $noiseArray;
    }

    /**
     * Syrim: version Nukkit de getFastNoise3D. Renombrada para evitar
     * conflicto de firma con PMMPGenerator::getFastNoise3D.
     */
    public static function getNukkitNoise3D(Noise $noise, int $xSize, int $ySize, int $zSize, int $xSamplingRate, int $ySamplingRate, int $zSamplingRate, int $x, int $y, int $z) : array {
        if($xSamplingRate === 0 || $ySamplingRate === 0 || $zSamplingRate === 0) {
            throw new \InvalidArgumentException("samplingRate cannot be 0");
        }
        if($xSize % $xSamplingRate !== 0 || $zSize % $zSamplingRate !== 0 || $ySize % $ySamplingRate !== 0) {
            throw new \InvalidArgumentException("size % samplingRate must return 0");
        }

        $noiseArray = [];
        for($xx = 0; $xx <= $xSize; $xx += $xSamplingRate) {
            for($zz = 0; $zz <= $zSize; $zz += $zSamplingRate) {
                for($yy = 0; $yy <= $ySize; $yy += $ySamplingRate) {
                    $noiseArray[$xx][$zz][$yy] = $noise->noise3D($x + $xx, $y + $yy, $z + $zz, true);
                }
            }
        }

        for($xx = 0; $xx < $xSize; ++$xx) {
            for($zz = 0; $zz < $zSize; ++$zz) {
                for($yy = 0; $yy < $ySize; ++$yy) {
                    if($xx % $xSamplingRate !== 0 || $zz % $zSamplingRate !== 0 || $yy % $ySamplingRate !== 0) {
                        $nx = (int)($xx / $xSamplingRate) * $xSamplingRate;
                        $ny = (int)($yy / $ySamplingRate) * $ySamplingRate;
                        $nz = (int)($zz / $zSamplingRate) * $zSamplingRate;

                        $nnx = $nx + $xSamplingRate;
                        $nny = $ny + $ySamplingRate;
                        $nnz = $nz + $zSamplingRate;

                        $dx1 = (float)($nnx - $xx) / (float)($nnx - $nx);
                        $dx2 = (float)($xx - $nx) / (float)($nnx - $nx);
                        $dy1 = (float)($nny - $yy) / (float)($nny - $ny);
                        $dy2 = (float)($yy - $ny) / (float)($nny - $ny);

                        $noiseArray[$xx][$zz][$yy] = ((float)($nnz - $zz) / (float)($nnz - $nz)) * (
                                $dy1 * ($dx1 * $noiseArray[$nx][$nz][$ny] + $dx2 * $noiseArray[$nnx][$nz][$ny])
                                + $dy2 * ($dx1 * $noiseArray[$nx][$nz][$nny] + $dx2 * $noiseArray[$nnx][$nz][$nny])
                            ) + ((float)($zz - $nz) / (float)($nnz - $nz)) * (
                                $dy1 * ($dx1 * $noiseArray[$nx][$nnz][$ny] + $dx2 * $noiseArray[$nnx][$nnz][$ny])
                                + $dy2 * ($dx1 * $noiseArray[$nx][$nnz][$nny] + $dx2 * $noiseArray[$nnx][$nnz][$nny])
                            );
                    }
                }
            }
        }

        return $noiseArray;
    }
}
