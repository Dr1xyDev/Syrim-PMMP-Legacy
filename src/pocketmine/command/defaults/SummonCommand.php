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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

class SummonCommand extends VanillaCommand {

        /**
         * SummonCommand constructor.
         *
         * @param $name
         */
        public function __construct($name){
                parent::__construct(
                        $name,
                        "%pocketmine.command.summon.description",
                        "%commands.summon.usage"
                );
                $this->setPermission("pocketmine.command.summon");
        }

        /**
         * @param CommandSender $sender
         * @param string        $currentAlias
         * @param array         $args
         *
         * @return bool
         */
        public function execute(CommandSender $sender, $currentAlias, array $args){
                if(!$this->testPermission($sender)){
                        return true;
                }

                if(count($args) != 1 and count($args) != 4 and count($args) != 5){
                        $sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
                        return true;
                }

                $x = 0;
                $y = 0;
                $z = 0;
                if(count($args) == 4 or count($args) == 5){            //position is set
                        // Syrim:解析每个坐标分量（x/y/z）-> unificado en parseCoord().
                        // Antes había 3 bloques casi idénticos; ahora se llama al helper.
                        $coords = [
                                [$args[1], $sender, "x"],
                                [$args[2], $sender, "y"],
                                [$args[3], $sender, "z"],
                        ];
                        foreach($coords as $idx => $info){
                                [$arg, $snd, $axis] = $info;
                                $resolved = self::parseCoord($arg, $snd, $axis);
                                if($resolved === null){
                                        // Mensaje ya enviado dentro del helper.
                                        return false;
                                }
                                ${$axis} = $resolved;
                        }
                        $y = min(128, max(0, $y));
                }    //finish setting the location

                if(count($args) == 1){
                        if($sender instanceof Player){
                                $x = $sender->x;
                                $y = $sender->y;
                                $z = $sender->z;
                        }else{
                                $sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
                                return false;
                        }
                } //finish setting the location

                $entity = null;
                $type = $args[0];
                $level = ($sender instanceof Player) ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();
                $nbt = new CompoundTag("", [
                        "Pos" => new ListTag("Pos", [
                                new DoubleTag("", $x),
                                new DoubleTag("", $y),
                                new DoubleTag("", $z)
                        ]),
                        "Motion" => new ListTag("Motion", [
                                new DoubleTag("", 0),
                                new DoubleTag("", 0),
                                new DoubleTag("", 0)
                        ]),
                        "Rotation" => new ListTag("Rotation", [
                                new FloatTag("", lcg_value() * 360),
                                new FloatTag("", 0)
                        ]),
                ]);
                if(count($args) == 5 and $args[4][0] == "{"){//Tags are found
                        $nbtExtra = JsonNBTParser::parseJSON($args[4]);
                        $nbt = NBT::combineCompoundTags($nbt, $nbtExtra, true);
                }

                $entity = Entity::createEntity($type, $level, $nbt);
                if($entity instanceof Entity){
                        $entity->allowMove = true;
                        $entity->spawnToAll();
                        $sender->sendMessage("Successfully spawned entity $type at ($x, $y, $z)");
                        return true;
                }else{
                        $sender->sendMessage(TextFormat::RED . "An error occurred when spawning the entity $type");
                        return false;
                }
        }

        /**
         * Syrim: helper para parsear una coordenada del comando /summon.
         * Acepta un número directo ("12") o un offset relativo ("~", "~5").
         *
         * @param string        $arg
         * @param CommandSender $sender
         * @param string        $axis  "x" | "y" | "z"
         *
         * @return float|null Devuelve la coordenada resuelta, o null si el
         *                    argumento es inválido (mensaje ya enviado al sender).
         */
        private static function parseCoord(string $arg, CommandSender $sender, string $axis){
                if(is_numeric($arg)){
                        return (float) $arg;
                }
                if(strpos($arg, "~") === 0){
                        $offset = trim($arg, "~");
                        if(!$sender instanceof Player){
                                $sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
                                return null;
                        }
                        if($offset === "" || !is_numeric($offset)){
                                return (float) $sender->{$axis};
                        }
                        return (float) $sender->{$axis} + (float) $offset;
                }
                $sender->sendMessage(TextFormat::RED . "Argument error");
                return null;
        }
}
