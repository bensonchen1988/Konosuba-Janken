<?php 
require_once("equipment.php");
require_once("status.php");

abstract class Weapon implements Equipment
{
    function get_equipment_type(){
        return Equipment::WEAPON;
    }
    abstract function get_atk();
    abstract function get_crit();

    function get_stats_string(){
        $return_string = "ATK+" . $this->get_atk();
        if($this->get_crit() > 0){
            $return_string .= " CRIT+".$this->get_crit();
        }
        return $return_string . $this->get_procs_string();
    }

    function get_procs_string(){
        return "";
    }

    // On attack special effect proc; returns an array of Status's.
    // Returns an empty array by default;
    function get_procs_array(){
        return array();
    }
}


class BrassKnuckles extends Weapon
{
    const ID = 100001;

    public function get_atk(){
        return 10;
    }

    public function get_crit(){
        return 0;
    }

    public function get_name(){
        return "Brass Knuckles";
    }

    public function get_id(){
        return BrassKnuckles::ID;
    }
}


class WoodenSword extends Weapon
{
    const ID = 100002;

    public function get_atk(){
        return 15;
    }

    public function get_crit(){
        return 0;
    }

    public function get_name(){
        return "Wooden Sword";
    }

    public function get_id(){
        return WoodenSword::ID;
    }
}

class FrozenKatana extends Weapon
{
    const ID = 100003;

    public function get_atk(){
        return 45;
    }

    public function get_crit(){
        return 10;
    }

    public function get_name(){
        return "Frozen Katana";
    }

    public function get_id(){
        return FrozenKatana::ID;
    }

    public function get_procs_array(){
        // 25% chance to inflict frozen on enemy
        return array(new Status(Status::FROZEN, 2500, 1));
    }

    public function get_procs_string(){
        return ", 25% chance to FREEZE enemy on win for 1 turn";
    }
}

class SlimeBomb extends Weapon
{
    const ID = 100004;

    public function get_atk(){
        return 20;
    }

    public function get_crit(){
        return 0;
    }

    public function get_name(){
        return "Slime Bomb";
    }

    public function get_id(){
        return SlimeBomb::ID;
    }

    public function get_procs_array(){
        // 25% chance to inflict frozen on enemy
        return array(new Status(Status::POISONED, 5000, 10));
    }

    public function get_procs_string(){
        return ", 50% chance to POISON enemy on win for 10 turns";
    }
}