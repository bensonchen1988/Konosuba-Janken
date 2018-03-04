<?php 
require_once("equipment.php");


abstract class Armor implements Equipment{

	function get_equipment_type(){
		return Equipment::ARMOR;
	}
	abstract function get_def();

	function get_stats_string(){
		return "DEF+" . $this->get_def();
	}
}


class FrogSkin extends Armor{

	const ID = 200001;

	public function get_def(){
		return 1;
	}

	public function get_name(){
		return "Frog Skin";
	}

	public function get_id(){
		return FrogSkin::ID;
	}
}

?>