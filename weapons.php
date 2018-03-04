<?php 
require_once("equipment.php");


abstract class Weapon implements Equipment{

	function get_equipment_type(){
		return Equipment::WEAPON;
	}
	abstract function get_atk();

	function get_stats_string(){
		return "ATK+" . $this->get_atk();
	}
}


class BrassKnuckles extends Weapon{

	const ID = 100001;

	public function get_atk(){
		return 10;
	}

	public function get_name(){
		return "Brass Knuckles";
	}

	public function get_id(){
		return BrassKnuckles::ID;
	}
}


class WoodenSword extends Weapon{

	const ID = 100002;

	public function get_atk(){
		return 15;
	}

	public function get_name(){
		return "Wooden Sword";
	}

	public function get_id(){
		return WoodenSword::ID;
	}
}

?>