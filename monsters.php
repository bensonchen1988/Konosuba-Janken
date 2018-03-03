<?php

	abstract class Monster{

		protected $current_hp;

		abstract function get_level();
		abstract function get_id();
		abstract function get_name();
		abstract function get_atk();
		abstract function get_def();
		abstract function get_hp();
		abstract function get_exp();
		abstract function get_crit();
		abstract function get_loots();
		function set_current_hp($hp){
			$this->current_hp = $hp;
		}
		function get_current_hp(){
			return $this->current_hp;
		}

	}

	class MonsterFactory{

		// can be further expanded to accomodate level buckets
		function create_monster_by_player_level($player_level){
			switch($player_level){
				case 1: return new GiantFrog();
				case 2: return new FlyingCabbage();
				case 3: return new DullahansUndeads();
				case 4: return new Dullahan();
				case 5: return new Destroyer();
				case 6: return new Hanz();
				default: return new Hanz();
			}
		}

		function create_monster_by_id($id){
			switch($id){
				case GiantFrog::ID: return new GiantFrog();
				case FlyingCabbage::ID: return new FlyingCabbage();
				case DullahansUndeads::ID: return new DullahansUndeads();
				case Dullahan::ID: return new Dullahan();
				case Destroyer::ID: return new Destroyer();
				case Hanz::ID: return new Hanz();
				default: return new GiantFrog();
			}
		}
	}

	class GiantFrog extends Monster{

		const ID = 101;

		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return GiantFrog::ID;
		}
		function get_level(){
			return 1;
		}
		function get_name(){
			return "Giant Frog";
		}
		function get_atk(){
			return 2;
		}
		function get_def(){
			return 1;
		}
		function get_hp(){
			return 20;
		}
		function get_exp(){
			return 1;
		}
		function get_crit(){
			return 2;
		}
		//returns array
		function get_loots(){

		}
	}
	class FlyingCabbage extends Monster{

		const ID = 102;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return FlyingCabbage::ID;
		}
		function get_level(){
			return 2;
		}
		function get_name(){
			return "Flying Cabbage";
		}
		function get_atk(){
			return 3;
		}
		function get_def(){
			return 2;
		}
		function get_hp(){
			return 35;
		}
		function get_exp(){
			return 2;
		}
		function get_crit(){
			return 3;
		}
		//returns array
		function get_loots(){

		}
	}
	class DullahansUndeads extends Monster{

		const ID = 103;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return DullahansUndeads::ID;
		}
		function get_level(){
			return 3;
		}
		function get_name(){
			return "Dullahan's Undeads";
		}
		function get_atk(){
			return 5;
		}
		function get_def(){
			return 3;
		}
		function get_hp(){
			return 55;
		}
		function get_exp(){
			return 3;
		}
		function get_crit(){
			return 4;
		}
		//returns array
		function get_loots(){

		}
	}
	class Dullahan extends Monster{

		const ID = 104;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return Dullahan::ID;
		}
		function get_level(){
			return 4;
		}
		function get_name(){
			return "Dullahan";
		}
		function get_atk(){
			return 7;
		}
		function get_def(){
			return 5;
		}
		function get_hp(){
			return 76;
		}
		function get_exp(){
			return 4;
		}
		function get_crit(){
			return 5;
		}
		//returns array
		function get_loots(){

		}
	}
	class Destroyer extends Monster{

		const ID = 105;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return Destroyer::ID;
		}
		function get_level(){
			return 5;
		}
		function get_name(){
			return "Destroyer";
		}
		function get_atk(){
			return 10;
		}
		function get_def(){
			return 7;
		}
		function get_hp(){
			return 102;
		}
		function get_exp(){
			return 7;
		}
		function get_crit(){
			return 6;
		}
		//returns array
		function get_loots(){

		}
	}
	class Hanz extends Monster{

		const ID = 106;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return Hanz::ID;
		}
		function get_level(){
			return 6;
		}
		function get_name(){
			return "Hanz";
		}
		function get_atk(){
			return 15;
		}
		function get_def(){
			return 10;
		}
		function get_hp(){
			return 160;
		}
		function get_exp(){
			return 10;
		}
		function get_crit(){
			return 7;
		}
		//returns array
		function get_loots(){

		}
	}



?>