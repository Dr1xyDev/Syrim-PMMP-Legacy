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

namespace pocketmine\inventory;

/**
 * Manages crafting operations
 * This class includes future methods for shaped crafting
 *
 * Syrim: la "small matrix inventory" (grid 2x2 del inventario del jugador)
 * se maneja actualmente con el mismo CraftingInventory usando InventoryType::CRAFTING
 * (tamaño 4). No se ha separado en una clase propia porque eso obligaría a
 * reescribir toda la lógica de PlayerTransactionHandler, que se basa en el
 * tipo de inventario recibido en el ContainerOpenPacket.
 */
class CraftingInventory extends BaseInventory {

        /** @var Inventory */
        private $resultInventory;

        /**
         * @param InventoryHolder $holder
         * @param Inventory       $resultInventory
         * @param InventoryType   $inventoryType
         *
         * @throws \Throwable
         */
        public function __construct(InventoryHolder $holder, Inventory $resultInventory, InventoryType $inventoryType){
                if($inventoryType->getDefaultTitle() !== "Crafting"){
                        throw new \InvalidStateException("Invalid Inventory type, expected CRAFTING or WORKBENCH");
                }
                $this->resultInventory = $resultInventory;
                parent::__construct($holder, $inventoryType);
        }

        /**
         * @return Inventory
         */
        public function getResultInventory(){
                return $this->resultInventory;
        }

        /**
         * @return mixed
         */
        public function getSize(){
                return $this->getResultInventory()->getSize() + parent::getSize();
        }
}