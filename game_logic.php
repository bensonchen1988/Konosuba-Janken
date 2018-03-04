
<?php
require_once("player.php");
require_once("monsters.php");
	class GameLogic{
		private $lose_streak_to_get_explosion = 3;

		private $monster_exp_table = array(1, 2, 3, 4, 5, 6);
		private $monster_names_table = array("Giant Frog", "Flying Cabbage", "Dullahan's Undeads", "Dullahan", "Destroyer", "Hanz");
		private $monster_stats_table = array();

		private $player_level_to_monster_mapping_table = array(1, 2, 3, 4, 5, 6);
		// monster name = "monster"+lvl

		private $const_atk = "ATK";
		private $const_def = "DEF";
		private $const_hp = "HP";
		private $const_crit = "CRIT";


		function get_exp_penalty_rate(){
			return 0.2;
		}

		function calculate_damage_on_monster(Player $PlayerCharacter, Monster $Monster){
			// Crit proc
			$crit_roll = rand(1,100);
			$attacker_atk = $PlayerCharacter->get_atk();
			if($PlayerCharacter->has_weapon()){
				$attacker_atk += $PlayerCharacter->get_weapon()->get_atk();
			}
			$attacker_crit = $PlayerCharacter->get_crit();
			$defender_def = $Monster->get_def();

			if($crit_roll <= $attacker_crit){
				$attacker_atk = $attacker_atk * 2;
				$defender_def = 0;
				echo "<br> CRITICAL STRIKE!";
			}
			$damage = max(1, $attacker_atk - $defender_def);
			echo " You did " . $damage ." damage!";
			return $damage;

		}

		function calculate_damage_on_player(Monster $Monster, Player $PlayerCharacter){
			// Crit proc
			$crit_roll = rand(1,100);
			$attacker_atk = $Monster->get_atk();
			$attacker_crit = $Monster->get_crit();
			$defender_def = $PlayerCharacter->get_def();
			if($PlayerCharacter->has_armor()){
				$defender_def += $PlayerCharacter->get_armor()->get_def();
			}

			if($crit_roll <= $attacker_crit){
				$attacker_atk = $attacker_atk * 2;
				$defender_def = 0;
				echo "<br> CRITICAL STRIKE!";
			}
			$damage = max(1, $attacker_atk - $defender_def);
			echo " You took " . $damage ." damage!";
			return $damage;

		}

		function get_winner($computer_choice, $player_input, $choices, &$player_stored_nukes, &$player_lose_streak, &$player_wins, &$cpu_stored_nukes, &$cpu_lose_streak, &$cpu_wins, $monster_name){
		    if($player_input == 3){
		        --$player_stored_nukes;
		    }
		    if($computer_choice == 3){
		        --$cpu_stored_nukes;
		    }
			if($player_input == -1){
				return "e";
			}
			if($computer_choice == $player_input){
				echo "DRAW GAME!";
				return "d";
			}
		    if($player_input == 3 || $computer_choice == 3){
		        if($player_input == 3){
		            $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $monster_name);
		            return "p";
		        }
		        else{
		            $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, $monster_name, "You");
		        }
		        return "c";
		    }
			if(($player_input+1)%3 == $computer_choice){
		        $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, $monster_name, "You");
				return "c";
			}
		    $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $monster_name);
		    return "p";
		}

		public function process_win(&$winner_wins, &$winner_lose_streak, &$loser_lose_streak, &$loser_stored_nukes, $winner_name, $loser_name){
		    echo "$winner_name Win!";
		    $loser_lose_streak++;
		    if($loser_lose_streak >= $this->lose_streak_to_get_explosion){
		        $loser_lose_streak = 0;
		        $loser_stored_nukes++;
		        echo "<br> $loser_name gained an Explosion for losing ". $this->lose_streak_to_get_explosion . " times in a row!";
		    }
		    $winner_lose_streak = 0;
		    ++$winner_wins;
		}

		public function get_kill_hp_regen($Monster){
			return floor(0.2 * $Monster->get_hp());
		}
	}

?>
