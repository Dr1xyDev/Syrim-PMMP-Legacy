<?php

/**
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link   https://itxtech.org
 *
 */

namespace pocketmine\inventory;

use pocketmine\Player;

abstract class TemporaryInventory extends ContainerInventory {
        // Syrim: los inventarios "temporary" (CraftingTable, Anvil, Enchanting)
        // no persisten su contenido al cerrarse; el método onClose() devuelve
        // los items al jugador. No hay lógica adicional pendiente de implementar
        // en esta clase base - los métodos específicos los define cada subclase.

        /**
         * @return mixed
         */
        abstract public function getResultSlotIndex();


        /**
         * @param Player $who
         */
        public function onClose(Player $who){
                foreach($this->getContents() as $slot => $item){
                        if($slot === $this->getResultSlotIndex()){
                                //Do not drop the item in the result slot - it is a virtual item and does not actually exist.
                                continue;
                        }
                        $who->dropItem($item);
                }
                $this->clearAll();
        }
}