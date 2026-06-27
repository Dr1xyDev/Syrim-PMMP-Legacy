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
 *  Nota : Versiones anteriores existieron pero no fueron publicadas
 *         debido a motivos privados del autor.
 */

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Witch extends WalkingMonster implements Ageable{
        const NETWORK_ID = 45;

        public $dropExp = [5, 5];

        /**
         * @return string
         */
        public function getName() : string{
                return "Witch";
        }

        public function initEntity(){
                $this->setMaxHealth(26);
                parent::initEntity();
        }

        /**
         * @param Player $player
         */

        /**
         * @return array
         */
        public function getDrops(){
                // Syrim: drops vanilla de la Witch - random de 0-2 items.
                $drops = [];
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::GLOWSTONE_DUST, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::SUGAR, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::GUNPOWDER, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::SPIDER_EYE, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::GLASS_BOTTLE, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::STICK, 0, mt_rand(0, 2));
                if(mt_rand(0, 1) === 0) $drops[] = Item::get(Item::REDSTONE_DUST, 0, mt_rand(0, 2));
                return $drops;
        }
}
