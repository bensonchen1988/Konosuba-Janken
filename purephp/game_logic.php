
<?php
require_once("player.php");
require_once("monsters.php");
require_once("accessory_effects.php");

    class GameLogic
    {
        // Lose streak threshold to receive an Explosion charge
        private $lose_streak_to_get_explosion = 3;

        /**
        * Returns the percentage in decimal form of EXP loss upon dying
        **/
        function get_exp_penalty_rate()
        {
            return 0.2;
        }

        /**
        * Function to be called when the Player wins the RPS. Calculates the damage done to the Monster.
        **/
        function calculate_damage_on_monster(Player $PlayerCharacter, Monster $Monster)
        {
            // Initialize helper class for easy value manipulation
            $attacker = new CombatData();
            $defender = new CombatData();
            $attacker->base_atk = $PlayerCharacter->get_atk();
            $attacker->crit = $PlayerCharacter->get_crit();
            $attacker->input = $PlayerCharacter->get_input();
            // Apply weapon bonus (if any)
            if($PlayerCharacter->has_weapon()){
                $attacker->bonus_atk += $PlayerCharacter->get_weapon()->get_atk();
                $attacker->crit += $PlayerCharacter->get_weapon()->get_crit();
            }

            $defender->def = $Monster->get_def();

            // Apply accessory bonus (if any)
            if($PlayerCharacter->has_accessory()){
                $this->apply_effects($PlayerCharacter->get_accessory()->get_effects(), $attacker);
            }


            // Megumin Explosion bonus
            if($PlayerCharacter->get_mode() === Player::MODE_MEGUMIN && $PlayerCharacter->get_input() === 3){
                $GLOBALS["console_output_buffer"] .= "\nMegumin Explosion bonus: 5X Damage and defense pierce!";
                $attacker->base_atk = $attacker->base_atk*5;
                $attacker->bonus_atk = $attacker->bonus_atk*5;
                $defender->def = 0;
            }

            // Calculate crit. Crits do a default of 2X total damage at the moment, considering expanding to allow Accessories to modify this multiplier
            $crit_roll = rand(1,100);
            if($crit_roll <= $attacker->crit){
                $attacker->base_atk = $attacker->base_atk * 2;
                $attacker->bonus_atk = $attacker->bonus_atk * 2;
                $defender->def = 0;
                $GLOBALS["console_output_buffer"] .= "\nCRITICAL STRIKE!";
            }
            // Deals a minimum of 1 damage
            $damage = max(1, $attacker->get_total_atk() - $defender->def);

            // Darkness nerf
            if($PlayerCharacter->get_mode() === Player::MODE_DARKNESS){
                $damage = 0;
                $GLOBALS["console_output_buffer"] .= "\nDarkness debuff: Your attack missed!";
            }
            $GLOBALS["console_output_buffer"] .= " You did " . $damage ." damage!";
            // Kazuma bonus, deals current level amount of extra unmitigated damage
            if($PlayerCharacter->get_mode() === Player::MODE_KAZUMA){
                $GLOBALS["console_output_buffer"] .= "\nKazuma bonus: You did " . $PlayerCharacter->get_level() ." extra damage!";
                $damage += $PlayerCharacter->get_level();
            }

            return $damage;

        }

        function calculate_damage_on_player(Monster $Monster, Player $PlayerCharacter)
        {
            // Special monsters like Training Dummy doesn't attack
            if($Monster->get_atk() == 0){
                return 0;
            }
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

            // Crit proc
            $crit_roll = rand(1,100);
            if($crit_roll <= $attacker->crit){
                $attacker->base_atk = $attacker->base_atk * 2;
                $attacker->bonus_atk = $attacker->bonus_atk * 2;
                $defender->def = 0;
                $GLOBALS["console_output_buffer"] .= "\nCRITICAL STRIKE!";
            }
            $damage = max(1, $attacker->get_total_atk() - $defender->def);
            // Darkness buff
            if($PlayerCharacter->get_mode() === Player::MODE_DARKNESS){
                $damage = max(1, floor($damage*0.1));
                $GLOBALS["console_output_buffer"] .= "\nDarkness buff: -80% damage received!";
            }

            $GLOBALS["console_output_buffer"] .= "\nYou took " . $damage ." damage!";
            return $damage;

        }

        /**
        * Gets the winner of the RPS game.
        * Rock beats Scissors, Paper beats Rock, and Scissors beats Paper.
        * Explosions beats everything except for another Explosion, which ends in a tie.
        * Considering changing function signature
        **/
        function get_winner(int $computer_choice, int $player_input, array $choices, int &$player_stored_nukes, 
        	int &$player_lose_streak, int &$player_wins, int &$cpu_stored_nukes, 
        	int &$cpu_lose_streak, int &$cpu_wins, string $monster_name){
            // Decrease player's stored nuke's count if player chose to use Explosion
            if($player_input === 3){
                --$player_stored_nukes;
            }
            // Decrease monster's stored nuke's count if monster chose to use Explosion
            if($computer_choice === 3){
                --$cpu_stored_nukes;
            }
            // Should never get to this 
            if($player_input == -1){
                return "e";
            }
            // Tie Game
            if($computer_choice === $player_input){
                $GLOBALS["console_output_buffer"] .= "DRAW GAME!";
                return "d";
            }
            // Explosion win check
            if($player_input == 3 || $computer_choice == 3){
                if($player_input == 3){
                    $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $monster_name);
                    return "p";
                }
                $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, $monster_name, "You");
                return "c";
            }
            // Normal win check
            if(($player_input+1)%3 == $computer_choice){
                $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, $monster_name, "You");
                return "c";
            }
            else{
                $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $monster_name);
                return "p";
            }
        }

        /**
        * Updates meta data post RPS game.
        **/
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

        /**
        * Returns the HP regen amount (20% of Monster's Max HP) to be awarded when the monster dies
        **/
        function get_kill_hp_regen(Monster $Monster)
        {
            return floor(0.2 * $Monster->get_hp());
        }

        /**
        * Applies accessory effects
        **/
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

        /**
        * Helper function for apply_effects; applies simple effects to ATK, DEF, and CRIT
        **/ 
        private function apply_stat_effects(IEffectsStats $AccessoryEffects, CombatData $CombatData)
        {
            $CombatData->bonus_atk += $AccessoryEffects->get_atk_boost();
            $CombatData->def += $AccessoryEffects->get_def_boost();
            $CombatData->crit += $AccessoryEffects->get_crit_boost();
        }

        /**
        * Helper function for apply_effects; applies special effects to combat
        **/ 
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

        public function process_turn_based_effects(Player $PlayerCharacter, Monster $Monster){
            // Aqua regen
            $this->aqua_regen($PlayerCharacter);
        }

        private function aqua_regen(Player $PlayerCharacter){
            if($PlayerCharacter->get_mode() === Player::MODE_AQUA){
                $PlayerCharacter->set_current_hp($PlayerCharacter->get_current_hp() + $PlayerCharacter->get_level());
                $GLOBALS["console_output_buffer"] .= "You recovered " . $PlayerCharacter->get_level() . " HP with Aqua's blessing!\n";
            }
        }

    }

    /**
    * Helper class to memo combat status across scope
    **/
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
