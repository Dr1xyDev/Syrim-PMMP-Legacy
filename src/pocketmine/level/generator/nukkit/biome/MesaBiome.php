<?php
/*
 * Syrim - Bioma Mesa (Meseta)
 *
 * Mesetas amplias de terracota con capas de colores, red sand en el suelo,
 * paredes empinadas y arbustos secos. Solo aparece cerca de desiertos
 * y lejos de zonas humedas.
 *
 * Bloques usados:
 * - Block::SAND meta 1 = Red Sand (suelo)
 * - Block::HARDENED_CLAY = Terracota (paredes de meseta)
 * - Block::STAINED_CLAY meta 1-14 = Terracota coloreada (capas decorativas)
 * - Block::RED_SANDSTONE = Base de meseta
 */

namespace pocketmine\level\generator\nukkit\biome;

use pocketmine\block\Block;
use pocketmine\level\generator\nukkit\populator\PopulatorDeadBush;

class MesaBiome extends SandyBiome {

    public function __construct() {
        // Syrim: NO llamar parent::__construct() porque SandyBiome anade
        // cactus y deadbush por defecto. Aqui solo queremos deadbush.
        // Pero SandyBiome define ground cover de arena, que si queremos.

        // Ground cover: red sand en superficie, red sandstone debajo
        $this->setGroundCover([
            Block::get(Block::SAND, 1),       // Red Sand
            Block::get(Block::SAND, 1),       // Red Sand
            Block::get(Block::RED_SANDSTONE, 0),
            Block::get(Block::RED_SANDSTONE, 0),
            Block::get(Block::RED_SANDSTONE, 0),
        ]);

        // Populator: arbustos secos (dead bush)
        $deadbush = new PopulatorDeadBush();
        $deadbush->setBaseAmount(3);
        $deadbush->setRandomAmount(2);
        $this->addPopulator($deadbush);

        // Elevacion alta para crear mesetas
        $this->setElevation(65, 90);

        // Clima seco y calido (como desierto)
        $this->temperature = 2.0;
        $this->rainfall = 0.0;
    }

    public function getName() : string {
        return "Mesa";
    }

    public function getColor() : int {
        // Color rojizo-amarillento tipico de mesa
        return 0xC55A2C;
    }
}
