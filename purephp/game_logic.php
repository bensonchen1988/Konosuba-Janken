
<?php
require_once("player.php");
require_once("monsters.php");
require_once("accessory_effects.php");

    class GameLogic
    {
        // Available choices
        const ROCK = 0;
        const PAPER = 1;
        const SCISSORS = 2;
        const EXPLOSION = 3;

        // Lose streak threshold to receive an Explosion charge
        private $lose_streak_to_get_explosion = 3;

        // Death exp penalty rate
        private $exp_penalty_rate = 0.2;

        /**
        * Returns the percentage in decimal form of EXP loss upon dying
        **/
        function get_exp_penalty_rate()
        {
            return $this->exp_penalty_rate;
        }

        /**
        * Returns an array containing the rules of what beats what
        **/
        private function get_rules(){
            // Key: the choice, Value: Array of choices that key beats
            return array(
                self::ROCK => array(self::SCISSORS),
                self::PAPER => array(self::ROCK),
                self::SCISSORS => array(self::PAPER),
                self::EXPLOSION => array(self::SCISSORS, self::ROCK, self::PAPER)
            );
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
                $GLOBALS["console_output_buffer"] .= "\nKazuma bonus: You did " . $PlayerCharacter->get_atk() ." extra damage!";
                $damage += $PlayerCharacter->get_atk();
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
                $GLOBALS["console_output_buffer"] .= "\nDarkness buff: -90% damage received!";
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
        function get_winner(int $computer_choice, int $player_input, int &$player_stored_nukes, 
        	int &$player_lose_streak, int &$player_wins, int &$cpu_stored_nukes, 
        	int &$cpu_lose_streak, int &$cpu_wins, Monster $Monster, $choices){
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

            if($Monster->get_status()->get_status_type() != Status::NORMAL){
                switch($Monster->get_status()->get_status_type()){
                    case Status::FROZEN: $GLOBALS["console_output_buffer"] .= $Monster->get_name() . " is FROZEN and can't move!\n"; $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $Monster->get_name()); return "p";
                    case Status::POISONED: $poison_damage = floor($Monster->get_current_hp() * 0.1); $Monster->set_current_hp($Monster->get_current_hp() - $poison_damage); $GLOBALS["console_output_buffer"] .= $Monster->get_name() . " is POISONED! " . $Monster->get_name() . " took ".$poison_damage . " damage!\n"; break;
                    default: break;
                }
            }
            $GLOBALS["console_output_buffer"] .= $Monster->get_name() ."'s choice: $choices[$computer_choice]\n";

            $rules = $this->get_rules();
            $player = in_array($computer_choice, $rules[$player_input]) ? 1 : 0;
            $computer = in_array($player_input, $rules[$computer_choice]) ? 1 : 0;
            $result = $player - $computer;

            // Tie Game
            if($result === 0){
                $GLOBALS["console_output_buffer"] .= "DRAW GAME!";
                return "d";
            }
            // Normal win check
            if($result < 0){
                // Computer Win
                $this->process_win($cpu_wins, $cpu_lose_streak, $player_lose_streak, $player_stored_nukes, $Monster->get_name(), "You");
                return "c";
            }
            else{
                // Player Win
                $this->process_win($player_wins, $player_lose_streak, $cpu_lose_streak, $cpu_stored_nukes, "You", $Monster->get_name());
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
                $PlayerCharacter->set_current_hp($PlayerCharacter->get_current_hp() + $PlayerCharacter->get_atk());
                $GLOBALS["console_output_buffer"] .= "You recovered " . $PlayerCharacter->get_atk() . " HP with Aqua's blessing!\n";
            }
        }

        public function process_status_procs_on_attack(Player $PlayerCharacter, Monster $Monster){
            // Array of Status
            $status_array = $PlayerCharacter->get_procs_array();
            foreach($status_array as $status){
                $chance = rand(1,10000);
                if($chance <= $status->get_rate()){
                    //proc'd
                    $Monster->set_status($status);
                    $GLOBALS["console_output_buffer"] .= "\n". $Monster->get_name() . " got " . $status->get_status_type() . "!";
                }
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
