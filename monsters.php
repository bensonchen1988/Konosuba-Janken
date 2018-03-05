<?php
require_once("equipment.php");
require_once("weapons.php");
require_once("armors.php");
require_once("accessories.php");

	abstract class Monster{

		protected $current_hp;
		// Key: equipment_id, Value: Drop rate (out of 10000)
		protected $loot_table;

		abstract function get_level();
		abstract function get_id();
		abstract function get_name();
		abstract function get_atk();
		abstract function get_def();
		abstract function get_hp();
		abstract function get_exp();
		abstract function get_crit();
		function set_current_hp($hp){
			$this->current_hp = $hp;
		}
		function get_current_hp(){
			return $this->current_hp;
		}
		//returns array
		function get_loots(){
			$result = array();
			if(sizeof($this->loot_table) != 0){
				foreach($this->loot_table as $id => $droprate){
					// $value/10000 chance of dropping
					if(rand(1, 10000) <= $droprate){
						array_push($result, $id);
					}
				}
			}
			return $result;
		}

		function get_choice($has_nuke){
			if($has_nuke){
				return rand(0, 3);
			}
			return rand(0, 2);
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
				default: return new Hanz(); //Catch all for overleveled players
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
				case TrainingDummy::ID: return new TrainingDummy();
				default: throw new Exception("Invalid monster ID");
			}
		}
	}

	class GiantFrog extends Monster{

		const ID = 101;

		function __construct(){
			// Initialize HP to max
			$this->current_hp = $this->get_hp();
			// Initialize loot table
			$this->loot_table = array(BrassKnuckles::ID=>5000, WoodenSword::ID=>5000, FrogSkin::ID=>5000);
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
		function get_choice($has_nuke){
			// 80% Paper, 10% Rock, 10% Scissors
			// 70% Paper, 10% Rock, 10% Scissors, 10% Explosion
			$rock = 10;
			$paper = 90;
			$scissors = 100;
			$explosion = 0;
			if($has_nuke){
				$explosion = 100;
				$scissors = 90;
				$paper = 80;
			}

			$roll = rand(1,100);
			if($roll >= 1 and $roll <= $rock){
				return 0;
			}
			if($roll > $rock and $roll <= $paper){
				return 1;
			}
			if($roll > $paper and $roll <= $scissors){
				return 2;
			}
			if($roll > $scissors and $roll <= $explosion){
				return 3;
			}
		}
	}
	class FlyingCabbage extends Monster{

		const ID = 102;
		function __construct(){
			$this->current_hp = $this->get_hp();
			$this->loot_table = array(RockAmulet::ID=>5000, CabbageLeaf::ID=>2500);
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
	}
	class DullahansUndeads extends Monster{

		const ID = 103;
		function __construct(){
			$this->current_hp = $this->get_hp();
			$this->loot_table = array(LuckyPebbles::ID=>10000);
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
	}
	class Hanz extends Monster{

		const ID = 106;
		function __construct(){
			$this->current_hp = $this->get_hp();
			$this->loot_table = array(SoDamageMuchWowSuchOP::ID=>10000, TrueSoDamageMuchWowSuchOP::ID=>1000);
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
			return 55;
		}
		function get_def(){
			return 20;
		}
		function get_hp(){
			return 260;
		}
		function get_exp(){
			return 20;
		}
		function get_crit(){
			return 10;
		}
	}


	class TrainingDummy extends Monster{

		const ID = 999;
		function __construct(){
			$this->current_hp = $this->get_hp();
		}
		function get_id(){
			return TrainingDummy::ID;
		}
		function get_level(){
			return 999;
		}
		function get_name(){
			return "Training Dummy";
		}
		function get_atk(){
			return 0;
		}
		function get_def(){
			return 0;
		}
		function get_hp(){
			return 100000000;
		}
		function get_exp(){
			return 0;
		}
		function get_crit(){
			return 0;
		}
	}



?>