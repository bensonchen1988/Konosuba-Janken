
<?php
require_once("player.php");
require_once("monsters.php");
require_once("accessory_effects.php");

    class GameLogic
    {
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


        function get_exp_penalty_rate()
        {
            return 0.2;
        }


        function calculate_damage_on_monster(Player $PlayerCharacter, Monster $Monster)
        {
            // Crit proc
            $crit_roll = rand(1,100);
            $attacker = new CombatData();
            $defender = new CombatData();
            $attacker->base_atk = $PlayerCharacter->get_atk();
            $attacker->input = $PlayerCharacter->get_input();
            if($PlayerCharacter->has_weapon()){
                $attacker->bonus_atk += $PlayerCharacter->get_weapon()->get_atk();
            }
            $attacker->crit = $PlayerCharacter->get_crit();

            $defender->def = $Monster->get_def();

            if($PlayerCharacter->has_accessory()){
                $this->apply_effects($PlayerCharacter->get_accessory()->get_effects(), $attacker);
            }

            if($crit_roll <= $attacker->crit){
                $attacker->base_atk = $attacker->base_atk * 2;
                $attacker->bonus_atk = $attacker->bonus_atk * 2;
                $defender->def = 0;
                $GLOBALS["console_output_buffer"] .= "\nCRITICAL STRIKE!";
            }
            $damage = max(1, $attacker->get_total_atk() - $defender->def);
            $GLOBALS["console_output_buffer"] .= " You did " . $damage ." damage!";
            return $damage;

        }

        function calculate_damage_on_player(Monster $Monster, Player $PlayerCharacter)
        {
            if($Monster->get_atk() == 0){
                return 0;
            }
            // Crit proc
            $crit_roll = rand(1,100);
            $attacker = new CombatData();
            $defender = new CombatData();
            $attacker->base_atk = $Monster->get_atk();
            $attacker->crit = $Monster->get_crit();
            $defender->def = $PlayerCharacter->get_def();
            if($PlayerCharacter->has_armor()){
                $defender->def += $PlayerCharacter->get_armor()->get_def();
            }

            if($PlayerCharacter->has_accessory()){
                $this->apply_effects($PlayerCharacter->get_accessory()->get_effects(), $defender);
            }

            if($crit_roll <= $attacker->crit){
                $attacker->base_atk = $attacker->base_atk * 2;
                $attacker->bonus_atk = $attacker->bonus_atk * 2;
                $defender->def = 0;
                $GLOBALS["console_output_buffer"] .= "\nCRITICAL STRIKE!";
            }
            $damage = max(1, $attacker->get_total_atk() - $defender->def);
            $GLOBALS["console_output_buffer"] .= "\nYou took " . $damage ." damage!";
            return $damage;

        }

        function get_winner(int $computer_choice, int $player_input, array $choices, int &$player_stored_nukes, 
        	int &$player_lose_streak, int &$player_wins, int &$cpu_stored_nukes, 
        	int &$cpu_lose_streak, int &$cpu_wins, string $monster_name){
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
                $GLOBALS["console_output_buffer"] .= "DRAW GAME!";
                return "d";
            }
            if($player_input == 3 || $computer_choice == 3){
                if($player_input == 3){
                    $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $monster_name);
                    return "p";
                }else{
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

        private function process_win(int &$winner_wins, int &$winner_lose_streak, int &$loser_lose_streak, 
        	int &$loser_stored_nukes, string $winner_name, string $loser_name){
            $GLOBALS["console_output_buffer"] .= "$winner_name Win!";
            $loser_lose_streak++;
            if($loser_lose_streak >= $this->lose_streak_to_get_explosion){
                $loser_lose_streak = 0;
                $loser_stored_nukes++;
                $GLOBALS["console_output_buffer"] .= "\n$loser_name gained an Explosion for losing ". $this->lose_streak_to_get_explosion . " times in a row!";
            }
            $winner_lose_streak = 0;
            ++$winner_wins;
        }

        function get_kill_hp_regen(Monster $Monster)
        {
            return floor(0.2 * $Monster->get_hp());
        }

        private function apply_effects(array $AccessoryEffects_array, CombatData $CombatData)
        {
            foreach($AccessoryEffects_array as $AccessoryEffects){
                switch($AccessoryEffects->get_type()){
                    case AccessoryEffects::TYPE_STATS_BOOST: $this->apply_stat_effects($AccessoryEffects, $CombatData); break;
                    case AccessoryEffects::TYPE_ROCK_ATTACK_BOOST:
                    case AccessoryEffects::TYPE_PAPER_ATTACK_BOOST: 
                    case AccessoryEffects::TYPE_SCISSORS_ATTACK_BOOST:
                    case AccessoryEffects::TYPE_EXPLOSION_ATTACK_BOOST: $this->apply_type_effects($AccessoryEffects, $CombatData); break;
                    default: break;
                }
            }
        }

        private function apply_stat_effects(IEffectsStats $AccessoryEffects, CombatData $CombatData)
        {
            $CombatData->bonus_atk += $AccessoryEffects->get_atk_boost();
            $CombatData->def += $AccessoryEffects->get_def_boost();
            $CombatData->crit += $AccessoryEffects->get_crit_boost();
        }

        private function apply_type_effects(IEffectsAttackType $AccessoryEffects, CombatData $CombatData)
        {
            switch($AccessoryEffects->get_type()){
                case AccessoryEffects::TYPE_ROCK_ATTACK_BOOST:if($CombatData->input == 0){$CombatData->base_atk = $CombatData->base_atk * $AccessoryEffects->get_multiplier();} break;
                case AccessoryEffects::TYPE_PAPER_ATTACK_BOOST:if($CombatData->input == 1){$CombatData->base_atk = $CombatData->base_atk * $AccessoryEffects->get_multiplier();} break;
                case AccessoryEffects::TYPE_SCISSORS_ATTACK_BOOST:if($CombatData->input == 2){$CombatData->base_atk = $CombatData->base_atk * $AccessoryEffects->get_multiplier();} break;
                case AccessoryEffects::TYPE_EXPLOSION_ATTACK_BOOST:if($CombatData->input == 3){$CombatData->base_atk = $CombatData->base_atk * $AccessoryEffects->get_multiplier();} break;
                default: break;
            }
        }

    }


    class CombatData
    {
        public $base_atk = 0;
        public $bonus_atk = 0;
        public $def = 0;
        public $crit = 0;
        // Rock:0 Paper:1 Scissors:2
        public $input = -1;

        function get_total_atk()
        {
            return floor($this->base_atk + $this->bonus_atk);
        }

    }
