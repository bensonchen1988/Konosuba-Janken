<?php

require_once("equipment.php");

    class Player{

        private $current_level;
        private $current_exp;
        private $current_hp;

        // Equipment instance
        private $equipped_weapon;
        // Equipment instance
        private $equipped_armor;
        // Equipment instance
        private $equipped_accessory;

        private $player_input = -1;

        // Array of equipment IDs
        private $inventory = array();

        public function __construct()
        {
            $this->current_level = 1;
            $this->current_exp = 0;
            $this->current_hp = $this->get_hp();
            $unequipped = new Unequipped();
            $this->equipped_weapon = $unequipped;
            $this->equipped_armor = $unequipped;
            $this->equipped_accessory = $unequipped;
            
        }

        function has_weapon()
        {
            if($this->equipped_weapon->get_id() > 0){
                return true;
            }
            return false;
        }

        function set_input(int $input)
        {
            $this->player_input = $input;
        }

        function get_input()
        {
            return $this->player_input;
        }

        function has_armor()
        {
            if($this->equipped_armor->get_id() > 0){
                return true;
            }
            return false;
        }

        function has_accessory()
        {
            if($this->equipped_accessory->get_id() > 0){
                return true;
            }
            return false;
        }

        // Thinking of a way for stricter type hinting for the following 3 functions
        function set_weapon(Equipment $equipment)
        {
            $this->equipped_weapon= $equipment;
        }
        function set_armor(Equipment $equipment)
        {
            $this->equipped_armor = $equipment;
        }
        function set_accessory(Equipment $equipment)
        {
            $this->equipped_accessory = $equipment;
        }

        function get_weapon()
        {
            return $this->equipped_weapon;
        }
        function get_armor()
        {
            return $this->equipped_armor;
        }
        function get_accessory()
        {
            return $this->equipped_accessory;
        }

        /**
        * Adds a single equipment ID to the inventory
        * Duplicates are purged (for now, might implement a combine feature in the future)
        **/
        function add_inventory(int $equipment_id)
        {
            array_push($this->inventory, $equipment_id);
            $this->inventory = array_unique($this->inventory);
        }

        function set_inventory(array $inventory_array)
        {
            $this->inventory = $inventory_array;
        }

        function get_inventory()
        {
            return $this->inventory;
        }

        function set_current_hp(int $hp)
        {
            $this->current_hp = $hp;
        }

        function get_current_hp()
        {
            // prevent hp overcap
            return min($this->current_hp, $this->get_hp());
        }

        function set_level(int $current_level)
        {
            $this->current_level = $current_level;
        }

        function get_level()
        {
            return $this->current_level;
        }

        function set_exp(int $current_exp)
        {
            $this->current_exp = $current_exp;
        }

        function get_exp()
        {
            return $this->current_exp;
        }

        function get_required_exp()
        {
            return floor($this->current_level * 3 * 1.1 ** $this->current_level);
        }

        function get_atk()
        {
            return floor($this->current_level* (3 * 1.05 ** $this->current_level));
        }

        function get_hp()
        {
            return 15 + floor($this->current_level* (20 * 1.05 ** $this->current_level));
        }

        function get_def()
        {
            return 1 + floor($this->current_level* (1 * 1.05 ** $this->current_level));
        }

        function get_crit()
        {
            return min(5 + floor(1.13 ** $this->current_level), 100);
        }

        /**
        * Gains exp and levels the player up. Multiple level ups at once is possible.
        * HP is refilled completely upon leveling up.
        **/
        function gain_exp(int $exp_awarded)
        {
            $this->set_exp($this->get_exp() + $exp_awarded);
            while($this->get_exp() >= $this->get_required_exp()){
                $this->set_exp($this->get_exp() - $this->get_required_exp());
                $this->set_level($this->get_level() + 1);
                if($this->get_level() > $this->get_max_level()){
                    $this->set_level($this->get_max_level());
                }
                $this->set_current_hp($this->get_hp());
            }
        }

        /**
        * Current level cap
        **/
        function get_max_level()
        {
            return 99;
        }
    }