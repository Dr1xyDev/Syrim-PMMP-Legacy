<?php
/*
 * Syrim - Port de Nukkit 1.1.5 Noise
 * Original: cn.nukkit.level.generator.noise.Noise
 * Adaptado a pocketmine\utils\Random
 */

namespace pocketmine\level\generator\nukkit\noise;

abstract class Noise {

    /** @var int[] */
    protected $perm = [];
    /** @var float */
    protected $offsetX = 0.0;
    /** @var float */
    protected $offsetY = 0.0;
    /** @var float */
    protected $offsetZ = 0.0;
    /** @var float */
    protected $octaves = 8.0;
    /** @var float */
    protected $persistence = 0.0;
    /** @var float */
    protected $expansion = 0.0;

    public static function floor(float $x) : int {
        return $x >= 0 ? (int) $x : (int) ($x - 1);
    }

    public static function fade(float $x) : float {
        return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
    }

    public static function lerp(float $x, float $y, float $z) : float {
        return $y + $x * ($z - $y);
    }

    public static function linearLerp(float $x, float $x1, float $x2, float $q0, float $q1) : float {
        return (($x2 - $x) / ($x2 - $x1)) * $q0 + (($x - $x1) / ($x2 - $x1)) * $q1;
    }

    public static function bilinearLerp(float $x, float $y, float $q00, float $q01, float $q10, float $q11, float $x1, float $x2, float $y1, float $y2) : float {
        $dx1 = (($x2 - $x) / ($x2 - $x1));
        $dx2 = (($x - $x1) / ($x2 - $x1));

        return (($y2 - $y) / ($y2 - $y1)) * (
                $dx1 * $q00 + $dx2 * $q10
            ) + (($y - $y1) / ($y2 - $y1)) * (
                $dx1 * $q01 + $dx2 * $q11
            );
    }

    public static function trilinearLerp(float $x, float $y, float $z, float $q000, float $q001, float $q010, float $q011, float $q100, float $q101, float $q110, float $q111, float $x1, float $x2, float $y1, float $y2, float $z1, float $z2) : float {
        $dx1 = (($x2 - $x) / ($x2 - $x1));
        $dx2 = (($x - $x1) / ($x2 - $x1));
        $dy1 = (($y2 - $y) / ($y2 - $y1));
        $dy2 = (($y - $y1) / ($y2 - $y1));

        return (($z2 - $z) / ($z2 - $z1)) * (
                $dy1 * (
                    $dx1 * $q000 + $dx2 * $q100
                ) + $dy2 * (
                    $dx1 * $q001 + $dx2 * $q101
                )
            ) + (($z - $z1) / ($z2 - $z1)) * (
                $dy1 * (
                    $dx1 * $q010 + $dx2 * $q110
                ) + $dy2 * (
                    $dx1 * $q011 + $dx2 * $q111
                )
            );
    }

    public static function grad(int $hash, float $x, float $y, float $z) : float {
        $hash &= 15;
        $u = $hash < 8 ? $x : $y;
        $v = $hash < 4 ? $y : (($hash === 12 || $hash === 14) ? $x : $z);

        return (($hash & 1) === 0 ? $u : -$u) + (($hash & 2) === 0 ? $v : -$v);
    }

    abstract public function getNoise2D(float $x, float $z) : float;

    abstract public function getNoise3D(float $x, float $y, float $z) : float;

    public function noise2D(float $x, float $z, bool $normalized = false) : float {
        $result = 0.0;
        $amp = 1.0;
        $freq = 1.0;
        $max = 0.0;

        $x *= $this->expansion;
        $z *= $this->expansion;

        for($i = 0; $i < $this->octaves; ++$i) {
            $result += $this->getNoise2D($x * $freq, $z * $freq) * $amp;
            $max += $amp;
            $freq *= 2;
            $amp *= $this->persistence;
        }

        if($normalized) {
            $result /= $max;
        }

        return $result;
    }

    public function noise3D(float $x, float $y, float $z, bool $normalized = false) : float {
        $result = 0.0;
        $amp = 1.0;
        $freq = 1.0;
        $max = 0.0;

        $x *= $this->expansion;
        $y *= $this->expansion;
        $z *= $this->expansion;

        for($i = 0; $i < $this->octaves; ++$i) {
            $result += $this->getNoise3D($x * $freq, $y * $freq, $z * $freq) * $amp;
            $max += $amp;
            $freq *= 2;
            $amp *= $this->persistence;
        }

        if($normalized) {
            $result /= $max;
        }

        return $result;
    }

    public function setOffset(float $x, float $y, float $z) {
        $this->offsetX = $x;
        $this->offsetY = $y;
        $this->offsetZ = $z;
    }
}
