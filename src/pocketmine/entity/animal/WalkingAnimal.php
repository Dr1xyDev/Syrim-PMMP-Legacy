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

namespace pocketmine\entity\animal;

use pocketmine\entity\WalkingEntity;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Timings;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class WalkingAnimal extends WalkingEntity implements Animal{

	protected $waterTick = 0;

	public function getSpeed(){
		return 0.7;
	}

	public function initEntity(){
		parent::initEntity();
		if($this->getDataProperty(self::DATA_AGEABLE_FLAGS) === null){
			$this->setDataProperty(self::DATA_AGEABLE_FLAGS, self::DATA_TYPE_BYTE, 0);
		}
	}

	public function isBaby(){
		return $this->getDataFlag(self::DATA_AGEABLE_FLAGS, self::DATA_FLAG_BABY);
	}

	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->hasEffect(Effect::WATER_BREATHING) && $this->isInsideOfWater()){
			$hasUpdate = true;
			$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
			if($airTicks <= -20){
				$airTicks = 0;
				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
				$this->attack($ev->getFinalDamage(), $ev);
			}
			$this->setDataProperty(Entity::DATA_AIR, Entity::DATA_TYPE_SHORT, $airTicks);
		}else{
			$this->setDataProperty(Entity::DATA_AIR, Entity::DATA_TYPE_SHORT, 300);
		}

		return $hasUpdate;
	}

	public function isFriendly(){
		return true;
	}

	public function onUpdate($currentTick){
		if(!$this->isAlive()){
			if(++$this->deadTicks >= 23){
				$this->close();
				return false;
			}
			return true;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		$this->lastUpdate = $currentTick;
		$this->entityBaseTick($tickDiff);

		$target = $this->updateMove();
		if($target instanceof Player){
			if($this->distance($target) <= 2){
				$this->pitch = 22;
				$this->x = $this->lastX;
				$this->y = $this->lastY;
				$this->z = $this->lastZ;
			}
		}elseif(
			$target instanceof Vector3
			&& $this->distance($target) <= 1
		){
			$this->moveTime = 0;
		}
		return true;
	}

}
