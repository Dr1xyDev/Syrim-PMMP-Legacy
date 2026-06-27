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

namespace pocketmine\entity\monster\walking;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class Evoker extends WalkingMonster {
	const NETWORK_ID = 104;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 2;

	public $dropExp = [5, 5];

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Evoker";
	}

	public function initEntity(){
		$this->setMaxHealth(24);
		//$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_EVOKER_SPELL, true);
		parent::initEntity();
	}

	public function checkDistance($target){
		if(sqrt($this->distanceSquared($target)) <= 10){
			return true;
		}else{
			return false;
		}
	}

	public function attackEntity(Entity $player){
		if($this->distanceSquared($player) <= 10){
			//$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
			//$player->attack($ev->getFinalDamage(), $ev);
		}
	}

	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
	}
	/**
	 * @return array
	 */
	public function getDrops(){
		$drops[] = ItemItem::get(ItemItem::EMERALD, 0, mt_rand(0, 1));
		$drops[] = ItemItem::get(ItemItem::TOTEM, 0, 1);

		return $drops;
	}
}
