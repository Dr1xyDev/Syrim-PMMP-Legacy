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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

abstract class Creature extends Living {
	public $attackingTick = 0;

	/**
	 * @param int $distance
	 *
	 * @return bool
	 */
	public function willMove($distance = 40){
		foreach($this->getViewers() as $viewer){
			if($this->distance($viewer) <= $distance) return true;
		}
		return false;
	}

	/**
	 * @param float             $damage
	 * @param EntityDamageEvent $source
	 *
	 * @return bool|void
	 */
	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
	}
	/**
	 * @param $mx
	 * @param $mz
	 *
	 * @return float|int
	 * 获取yaw角度
	 */
	public function getMyYaw($mx, $mz){  //根据motion计算转向角度
		//转向计算
		if($mz == 0){  //斜率不存在
			if($mx < 0){
				$yaw = -90;
			}else{
				$yaw = 90;
			}
		}else{  //存在斜率
			if($mx >= 0 and $mz > 0){  //第一象限
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			}elseif($mx >= 0 and $mz < 0){  //第二象限
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			}elseif($mx < 0 and $mz < 0){  //第三象限
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			}elseif($mx < 0 and $mz > 0){  //第四象限
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			}else{
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}

	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 *
	 * @return float|int
	 * 获取pitch角度
	 */
	public function getMyPitch(Vector3 $from, Vector3 $to){
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if($height > 0){
			return -rad2deg(asin($height / $distance));
		}elseif($height < 0){
			return rad2deg(asin(-$height / $distance));
		}else{
			return 0;
		}
	}
}
