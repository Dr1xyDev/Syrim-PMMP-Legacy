<?php
/*
 * Syrim - Port de Nukkit 1.1.5 ObjectTallGrass
 */

namespace pocketmine\level\generator\nukkit\object;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class ObjectTallGrass {

    public static function growGrass(ChunkManager $level, Vector3 $pos, Random $random, int $count, int $radius) {
        $arr = [
            [Block::DANDELION, 0],
            [Block::RED_FLOWER, 0],
            [Block::TALL_GRASS, 1],
            [Block::TALL_GRASS, 1],
            [Block::TALL_GRASS, 1],
            [Block::TALL_GRASS, 1]
        ];
        $arrC = count($arr) - 1;
        for($c = 0; $c < $count; ++$c) {
            $x = $random->nextRange((int) ($pos->x - $radius), (int) ($pos->x + $radius));
            $z = $random->nextRange((int) ($pos->z - $radius), (int) ($pos->z + $radius));

            if($level->getBlockIdAt($x, (int) ($pos->y + 1), $z) === Block::AIR
                && $level->getBlockIdAt($x, (int) $pos->y, $z) === Block::GRASS) {
                $t = $arr[$random->nextRange(0, $arrC)];
                $level->setBlockIdAt($x, (int) ($pos->y + 1), $z, $t[0]);
                $level->setBlockDataAt($x, (int) ($pos->y + 1), $z, $t[1]);
            }
        }
    }
}
