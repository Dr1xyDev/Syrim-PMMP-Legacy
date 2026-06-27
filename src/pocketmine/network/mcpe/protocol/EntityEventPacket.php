<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class EntityEventPacket extends DataPacket {

        const NETWORK_ID = ProtocolInfo::ENTITY_EVENT_PACKET;

        const JUMP = 1;
        const HURT_ANIMATION = 2;
        const DEATH_ANIMATION = 3;

        const TAME_FAIL = 6;
        const TAME_SUCCESS = 7;
        const SHAKE_WET = 8;
        const USE_ITEM = 9;
        const EAT_GRASS_ANIMATION = 10;
        const FISH_HOOK_BUBBLE = 11;
        const FISH_HOOK_POSITION = 12;
        const FISH_HOOK_HOOK = 13;
        const FISH_HOOK_TEASE = 14;
        const SQUID_INK_CLOUD = 15;
        const AMBIENT_SOUND = 16;
        const RESPAWN = 17;

        const EATING_ITEM = 57;

        const CONSUME_TOTEM = 65;

        // Syrim: eventos adicionales soportados por el protocolo v113 (MCPE 1.1.5).
        // Estos IDs están documentados en el protocolo de Minecraft Bedrock v113.
        const ARROW_SHAKE = 55;        // Vibración de flecha clavada
        const BABY_ANIMAL_FEED = 60;   // Alimentar a un bebé animal
        const LOVE_PARTICLES = 18;     // Corazones de apareamiento
        const FISH_HOOK_LURE = 66;     // Lure de caña de pescar (post-v113)

        public $eid;
        public $event;
        public $data = 0;

        /**
         *
         */
        public function decode(){
                $this->eid = $this->getEntityId();
                $this->event = $this->getByte();
                $this->data = $this->getVarInt();
        }

        /**
         *
         */
        public function encode(){
                $this->reset();
                $this->putEntityId($this->eid);
                $this->putByte($this->event);
                $this->putVarInt($this->data);
        }

        /**
         * @return string Current packet name
         */
        public function getName(){
                return "EntityEventPacket";
        }

}
