
<?php
	class GameLogic{
		private $exp_table = array(3, 7, 10, 14, 18, 20);
		private $monster_exp_table = array(1, 2, 3, 4, 5, 6);
		private $monster_names_table = array("Giant Frog", "Flying Cabbage", "Dullahan's Undeads", "Dullahan", "Destroyer", "Hanz");
		private $player_stats_table = array();
		private $monster_stats_table = array();

		private $player_level_to_monster_mapping_table = array(1, 2, 3, 4, 5, 6);
		// monster name = "monster"+lvl

		private $const_atk = "ATK";
		private $const_def = "DEF";
		private $const_hp = "HP";
		private $const_crit = "CRIT";


		public function __construct () {
			$this->player_stats_table[0] = array($this->const_atk => 5, $this->const_def=> 1, $this->const_hp=> 20, $this->const_crit=> 5);
			$this->player_stats_table[1] = array($this->const_atk => 8, $this->const_def=> 2, $this->const_hp=> 35, $this->const_crit=> 6);
			$this->player_stats_table[2] = array($this->const_atk => 13, $this->const_def=> 3, $this->const_hp=> 55, $this->const_crit=> 7);
			$this->player_stats_table[3] = array($this->const_atk => 19, $this->const_def=> 5, $this->const_hp=> 76, $this->const_crit=> 8);
			$this->player_stats_table[4] = array($this->const_atk => 27, $this->const_def=> 7, $this->const_hp=> 102, $this->const_crit=> 9);
			$this->player_stats_table[5] = array($this->const_atk => 39, $this->const_def=> 10, $this->const_hp=> 160, $this->const_crit=> 10);

			$this->monster_stats_table[0] = array($this->const_atk => 2, $this->const_def=> 1, $this->const_hp=> 20, $this->const_crit=> 2);
			$this->monster_stats_table[1] = array($this->const_atk => 3, $this->const_def=> 2, $this->const_hp=> 35, $this->const_crit=> 3);
			$this->monster_stats_table[2] = array($this->const_atk => 5, $this->const_def=> 3, $this->const_hp=> 55, $this->const_crit=> 4);
			$this->monster_stats_table[3] = array($this->const_atk => 7, $this->const_def=> 5, $this->const_hp=> 76, $this->const_crit=> 5);
			$this->monster_stats_table[4] = array($this->const_atk => 10, $this->const_def=> 7, $this->const_hp=> 102, $this->const_crit=> 6);
			$this->monster_stats_table[5] = array($this->const_atk => 15, $this->const_def=> 10, $this->const_hp=> 160, $this->const_crit=> 7);
		}

		function get_exp_penalty_rate(){
			return 0.2;
		}

		function get_monster_name($monster_level){
			return $this->monster_names_table[$monster_level-1];
		}

		function get_monster_level($player_level){
			return $this->player_level_to_monster_mapping_table[$player_level-1];
		}

		function get_monster_exp($monster_level){
			return $this->monster_exp_table[$monster_level-1];
		}

		function get_required_exp($current_level){
			return $this->exp_table[$current_level-1];
		}

		function get_atk($current_level){
			return $this->player_stats_table[$current_level-1][$this->const_atk];
		}

		function get_hp($current_level){
			return $this->player_stats_table[$current_level-1][$this->const_hp];
		}

		function get_def($current_level){
			return $this->player_stats_table[$current_level-1][$this->const_def];
		}

		function get_crit($current_level){
			return $this->player_stats_table[$current_level-1][$this->const_crit];
		}



		function get_monster_atk($current_level){
			return $this->monster_stats_table[$current_level-1][$this->const_atk];
		}

		function get_monster_hp($current_level){
			return $this->monster_stats_table[$current_level-1][$this->const_hp];
		}

		function get_monster_def($current_level){
			return $this->monster_stats_table[$current_level-1][$this->const_def];
		}

		function get_monster_crit($current_level){
			return $this->monster_stats_table[$current_level-1][$this->const_crit];
		}


		function get_max_level(){
			return sizeof($this->exp_table);
		}

		function calculate_damage_on_monster($player_level, $monster_level){
			// Crit proc
			$crit_roll = rand(1,100);
			$attacker_atk = $this->get_atk($player_level);
			$attacker_crit = $this->get_crit($player_level);
			$defender_def = $this->get_monster_def($monster_level);

			if($crit_roll <= $attacker_crit){
				$attacker_atk = $attacker_atk * 2;
				$defender_def = 0;
				echo "<br> CRITICAL STRIKE!";
			}
			$damage = max(1, $attacker_atk - $defender_def);
			echo " You did " . $damage ." damage!";
			return $damage;

		}

		function calculate_damage_on_player($monster_level, $player_level){
			// Crit proc
			$crit_roll = rand(1,100);
			$attacker_atk = $this->get_monster_atk($monster_level);
			$attacker_crit = $this->get_monster_crit($monster_level);
			$defender_def = $this->get_def($player_level);

			if($crit_roll <= $attacker_crit){
				$attacker_atk = $attacker_atk * 2;
				$defender_def = 0;
				echo "<br> CRITICAL STRIKE!";
			}
			$damage = max(1, $attacker_atk - $defender_def);
			echo " You took " . $damage ." damage!";
			return $damage;

		}

		function get_winner($computer_choice, $player_input, $choices, &$player_stored_nukes, &$player_lose_streak, &$player_wins, &$cpu_stored_nukes, &$cpu_lose_streak, &$cpu_wins){
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
		            $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "YOU");
		            return "p";
		        }
		        else{
		            $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, "COMPUTER");
		        }
		        return "c";
		    }
			if(($player_input+1)%3 == $computer_choice){
		        $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, "COMPUTER");
				return "c";
			}
		    $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "YOU");
		    return "p";
		}

		public function process_win(&$winner_wins, &$winner_lose_streak, &$loser_lose_streak, &$loser_stored_nukes, $winner_name){
		    echo "$winner_name WIN!";
		    $loser_lose_streak++;
		    if($loser_lose_streak >= 3){
		        $loser_lose_streak = 0;
		        $loser_stored_nukes++;
		    }
		    $winner_lose_streak = 0;
		    ++$winner_wins;
		}
	}
?>
