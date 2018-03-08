<?php 
require_once("equipment.php");

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
        return $return_string;
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
        return "Frozen Sword";
    }

    public function get_id(){
        return FrozenKatana::ID;
    }
}