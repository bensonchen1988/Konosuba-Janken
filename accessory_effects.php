<?php


class AccessoryEffects{

	const TYPE_STATS_BOOST = 1;
	const TYPE_ROCK_ATTACK_BOOST = 2;
	const TYPE_PAPER_ATTACK_BOOST = 3;
	const TYPE_SCISSORS_ATTACK_BOOST = 4;
	const TYPE_EXPLOSION_ATTACK_BOOST = 5;
}

interface IEffects{
	// RETURNS AN ARRAY! This hooks to the game logic for it to determine what types of effects to try and apply
	function get_type();
}

interface IEffectsStats extends IEffects{
	function get_atk_boost();
	function get_def_boost();
	function get_crit_boost();
}

interface IEffectsAttackType extends IEffects{
	function get_multiplier();
}

class StatsBoost implements IEffectsStats{

	private $atk_boost;
	private $def_boost;
	private $crit_boost;

	function __construct($atk_boost, $def_boost, $crit_boost){
		$this->atk_boost = $atk_boost;
		$this->def_boost = $def_boost;
		$this->crit_boost = $crit_boost;
	}

	function get_type(){
		return AccessoryEffects::TYPE_STATS_BOOST;
	}

	function get_atk_boost(){
		return $this->atk_boost;
	}

	function get_def_boost(){
		return $this->def_boost;
	}

	function get_crit_boost(){
		return $this->crit_boost;
	}
}


class RockBoost implements IEffectsAttackType{
	private $multiplier;

	function __construct($m){
		$this->multiplier = $m;
	}
	function get_type(){
		return AccessoryEffects::TYPE_ROCK_ATTACK_BOOST;
	}

	function get_multiplier(){
		return $this->multiplier;
	}

}


class PaperBoost implements IEffectsAttackType{
	private $multiplier;

	function __construct($m){
		$this->multiplier = $m;
	}
	function get_type(){
		return AccessoryEffects::TYPE_PAPER_ATTACK_BOOST;
	}

	function get_multiplier(){
		return $this->multiplier;
	}

}


class ScissorsBoost implements IEffectsAttackType{
	private $multiplier;

	function __construct($m){
		$this->multiplier = $m;
	}
	function get_type(){
		return AccessoryEffects::TYPE_SCISSORS_ATTACK_BOOST;
	}

	function get_multiplier(){
		return $this->multiplier;
	}

}


class ExplosionBoost implements IEffectsAttackType{
	private $multiplier;

	function __construct($m){
		$this->multiplier = $m;
	}
	function get_type(){
		return AccessoryEffects::TYPE_EXPLOSION_ATTACK_BOOST;
	}

	function get_multiplier(){
		return $this->multiplier;
	}

}

?>