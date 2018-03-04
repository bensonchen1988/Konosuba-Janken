<?php

// Ideally, would use a DB to store and keep track of a list of all equipment IDs
interface Equipment{
	const WEAPON = 0;
	const ARMOR = 1;
	const ACCESSORY = 2;

	public function get_equipment_type();
	public function get_name();
	public function get_id();
	/*
	* Returns a string describing the equipment's effects. Stats to be shown in format "STAT1+STAT1VALUE, STAT2+STAT2VALUE..", eg.:"ATK+5, CRIT+5"
	* May also add description like "ATK X2 when winning with Rock"
	*/
	public function get_stats_string();

}
abstract class Accessory implements Equipment{

	function get_equipment_type(){
		return Equipment::ACCESSORY;
	}

	abstract function get_effects();
}

class Unequipped implements Equipment{

	const ID = -1;
	public function get_equipment_type(){
		return -1;
	}
	public function get_name(){
		return "Unequipped";
	}
	public function get_id(){
		return Unequipped::ID;
	}
	public function get_stats_string(){
		return "Nothing equipped";
	}
}

/*
*	Weapon IDs: 100001~199999
*	Armor IDs: 200001~299999
*	Accessory IDs: 300001~399999
*/
class EquipmentFactory{

	function get_equipment($equipment_id){
		switch($equipment_id){
			// Weapons
			case BrassKnuckles::ID: return new BrassKnuckles();
			case WoodenSword::ID: return new WoodenSword();
			// Armors
			case FrogSkin::ID: return new FrogSkin();
			default: return new Unequipped();
		}
	}
}




?>