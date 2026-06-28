<?php

/*
 *
 *  _____            _               _____
 * / ____|          (_)             |  __ \
 *| |  __  ___ _ __  _ ___ _   _ ___| |__) | __ ___
 *| | |_ |/ _ \ '_ \| / __| | | / __|  ___/ '__/ _ \
 *| |__| |  __/ | | | \__ \ |_| \__ \ |   | | | (_) |
 * \_____|\___|_| |_|_|___/\__, |___/_|   |_|  \___/
 *                         __/ |
 *                        |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Syrim v1.0.6 - TitleCommand 
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TitleCommand extends VanillaCommand {

        /** @var int Duración visible del title en ticks (20 ticks = 1 segundo → 3s = 60 ticks) */
        const TITLE_DURATION_TICKS = 60;

        /** @var int Fade in en ticks */
        const TITLE_FADEIN_TICKS = 10;

        /** @var int Fade out en ticks */
        const TITLE_FADEOUT_TICKS = 10;

        /**
         * TitleCommand constructor.
         *
         * @param $name
         */
        public function __construct($name){
                parent::__construct(
                        $name,
                        "%pocketmine.command.title.description",
                        "%pocketmine.command.title.usage"
                );
                $this->setPermission("pocketmine.command.title");
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

                if(count($args) < 2){
                        $sender->sendMessage(new TranslationContainer("commands.generic.usage", ["%pocketmine.command.title.usage"]));
                        return false;
                }

                // Primer argumento: target (nombre de jugador o @all)
                $targetName = strtolower(array_shift($args));

                // El resto es el texto del title (puede contener espacios).
                // La primera coma separa title y subtitle.
                $text = implode(" ", $args);

                // Separar title y subtitle por la PRIMERA coma.
                // Si no hay coma, todo es title (subtitle vacío).
                $commaPos = strpos($text, ",");
                if($commaPos === false){
                        $title = $text;
                        $subtitle = "";
                }else{
                        $title = substr($text, 0, $commaPos);
                        $subtitle = substr($text, $commaPos + 1);
                }

                // Limpiar espacios sobrantes a los lados
                $title = trim($title);
                $subtitle = trim($subtitle);

                // Si ambos están vacíos no tiene sentido enviar
                if($title === "" and $subtitle === ""){
                        $sender->sendMessage(TextFormat::RED . "Debes especificar un title o subtitle.");
                        return false;
                }

                // Resolver targets
                if($targetName === "@all"){
                        $targets = $sender->getServer()->getOnlinePlayers();
                        if(count($targets) <= 0){
                                $sender->sendMessage(TextFormat::RED . "No hay jugadores conectados.");
                                return false;
                        }
                        $sent = 0;
                        foreach($targets as $p){
                                if($p instanceof Player and $p->isConnected()){
                                        $p->sendTitle($title, $subtitle, self::TITLE_FADEIN_TICKS, self::TITLE_FADEOUT_TICKS, self::TITLE_DURATION_TICKS);
                                        ++$sent;
                                }
                        }
                        $sender->sendMessage(TextFormat::GREEN . "Title enviado a " . $sent . " jugador(es).");
                        return true;
                }

                // Target individual
                $target = $sender->getServer()->getPlayer($targetName);
                if($target === null){
                        $sender->sendMessage(TextFormat::RED . "Jugador no encontrado: " . $targetName);
                        return false;
                }

                $target->sendTitle($title, $subtitle, self::TITLE_FADEIN_TICKS, self::TITLE_FADEOUT_TICKS, self::TITLE_DURATION_TICKS);
                $sender->sendMessage(TextFormat::GREEN . "Title enviado a " . $target->getName() . ".");
                return true;
        }
}
