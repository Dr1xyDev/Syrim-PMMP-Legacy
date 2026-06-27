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

namespace pocketmine\item;

use pocketmine\block\Block;

class Bed extends Item {

        const WHITE_BED = 0;
        const ORANGE_BED = 1;
        const MAGENTA_BED = 2;
        const LIGTH_BLUE_BED = 3;
        const YELLOW_BED = 4;
        const LIME_BED = 5;
        const PINK_BED = 6;
        const GRAY_BED = 7;
        const LIGHT_GRAY_BED = 8;
        const CYAN_BED = 9;
        const PURPLE_BED = 10;
        const BLUE_BED = 11;
        const BROWN_BED = 12;
        const GREEN_BED = 13;
        const RED_BED = 14;
        const BLACK_BED = 15;

        /** @var string[] Syrim: nombres vanilla de las 16 variantes de cama (MCPE v113). */
        private static $BED_NAMES = [
            self::WHITE_BED      => "White Bed",
            self::ORANGE_BED     => "Orange Bed",
            self::MAGENTA_BED    => "Magenta Bed",
            self::LIGTH_BLUE_BED => "Light Blue Bed",
            self::YELLOW_BED     => "Yellow Bed",
            self::LIME_BED       => "Lime Bed",
            self::PINK_BED       => "Pink Bed",
            self::GRAY_BED       => "Gray Bed",
            self::LIGHT_GRAY_BED => "Light Gray Bed",
            self::CYAN_BED       => "Cyan Bed",
            self::PURPLE_BED     => "Purple Bed",
            self::BLUE_BED       => "Blue Bed",
            self::BROWN_BED      => "Brown Bed",
            self::GREEN_BED      => "Green Bed",
            self::RED_BED        => "Red Bed",
            self::BLACK_BED      => "Black Bed",
        ];

        /**
         * Bed constructor.
         *
         * @param int $meta
         * @param int $count
         */
        public function __construct($meta = self::WHITE_BED, $count = 1){
                $this->block = Block::get(Item::BED_BLOCK, $meta);
                // Syrim: nombre localizado según el color de la cama.
                parent::__construct(self::BED, $meta, $count, self::$BED_NAMES[$meta] ?? "Bed");
        }

        /**
         * @return int
         */
        public function getMaxStackSize() : int{
                return 1;
        }
}
