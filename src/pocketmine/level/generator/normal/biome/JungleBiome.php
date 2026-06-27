<?php

/*
 *  ░█▀▀░█░█░█▀▄░▀█▀░█▄█
 *  ░▀▀█░░█░░█▀▄░░█░░█░█
 *  ░▀▀▀░░▀░░▀░▀░▀▀▀░▀░▀
 *
 *  Syrim - PocketMine-MP based core
 *  Version : 1.0.5
 *  Author  : Dr1xy dev
 *  API     : 3.0.1 (modified)  |  Protocol : v113  |  MultiPHP : 7.3 / 7.4 / 8.0
 *
 *  Nota : Bioma Jungla nativo de Syrim.
 *         Usa los nuevos árboles jungle porteados desde Nukkit:
 *           - NewJungleTree (jungle pequeño con vines + cacao)
 *           - ObjectJungleBigTree (jungle gigante 2x2 con ramas)
 *         La selección entre variantes se hace automáticamente dentro
 *         de Tree::growTree() (caso Sapling::JUNGLE).
 */

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\populator\Cave;

class JungleBiome extends GrassyBiome{

        public function __construct(){
                parent::__construct();

                // Syrim: árboles jungle densos (12 base + 3 random por chunk).
                // Tree::growTree() con Sapling::JUNGLE escoge aleatoriamente
                // entre NewJungleTree, ObjectJungleBigTree y JungleTree legacy.
                $trees = new Tree(Sapling::JUNGLE);
                $trees->setBaseAmount(12);
                $trees->setRandomAmount(3);
                $this->addPopulator($trees);

                // Hierba alta dispersa
                $tallGrass = new TallGrass();
                $tallGrass->setBaseAmount(7);
                $tallGrass->setRandomAmount(2);
                $this->addPopulator($tallGrass);

                // Cuevas
                $cave = new Cave();
                $this->addPopulator($cave);

                // Elevación suave, típica de jungla
                $this->setElevation(62, 70);

                // Clima cálido y húmedo
                $this->temperature = 0.95;
                $this->rainfall = 0.90;
        }

        public function getName(){
                return "Jungle";
        }
}
