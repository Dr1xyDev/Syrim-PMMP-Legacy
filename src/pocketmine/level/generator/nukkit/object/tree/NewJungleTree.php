<?php
/*
 * Syrim - Stub que referencia el NewJungleTree ya porteado
 * (en pocketmine\level\generator\object\NewJungleTree)
 */

namespace pocketmine\level\generator\nukkit\object\tree;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\NewJungleTree as BaseNewJungleTree;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class NewJungleTree extends BaseNewJungleTree {

    // Syrim: esta clase existe solo para que los populators de Nukkit
    // puedan referenciarla con el namespace nukkit\object\tree.
    // Toda la lógica está en la clase base (ya porteada).
}
