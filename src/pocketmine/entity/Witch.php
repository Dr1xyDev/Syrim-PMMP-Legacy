<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Witch extends Monster {
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
        public function spawnTo(Player $player){
                $pk = new AddEntityPacket();
                $pk->eid = $this->getId();
                $pk->type = Witch::NETWORK_ID;
                $pk->x = $this->x;
                $pk->y = $this->y;
                $pk->z = $this->z;
                $pk->speedX = $this->motionX;
                $pk->speedY = $this->motionY;
                $pk->speedZ = $this->motionZ;
                $pk->yaw = $this->yaw;
                $pk->pitch = $this->pitch;
                $pk->metadata = $this->dataProperties;
                $player->dataPacket($pk);
                parent::spawnTo($player);
        }

        /**
         * @return array
         */
        public function getDrops(){
                // Syrim: la Witch suelta: 0-6 glowstone dust, 0-6 redstone, 0-6 sugar,
                // 0-6 gunpowder, 0-6 spider eye, 0-6 glass bottle, 0-6 stick.
                // Implementación: random drops con Looting.
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