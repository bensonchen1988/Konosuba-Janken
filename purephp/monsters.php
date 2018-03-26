<?php
require_once("equipment.php");
require_once("weapons.php");
require_once("armors.php");
require_once("accessories.php");
require_once("monsters_index.php");
require_once("status.php");

    abstract class Monster
    {
        protected $current_hp;
        protected $status;
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

        function get_status_display(){
            $return_string = "";
            foreach($this->get_status() as $status_element){
                $return_string .= $status_element->get_status_type() . " (" . $status_element->get_remaining_turns() . ") "; 
            }
            return $return_string;
        }

        function get_status(){
            if(empty($this->status)){
                return array(new Status(Status::NORMAL, 10000, 999));
            }
            return $this->status;
        }

        function set_status($status_array){
            foreach($status_array as $status){
                $contains = false;
                foreach($this->status as $status_element){
                    if($status_element->get_status_type() == $status->get_status_type()){
                        $contains = true;
                        $status_element->set_remaining_turns($status->get_remaining_turns());
                    }
                }
                if(!$contains){
                    array_push($this->status, $status);
                }
            }
        }

        function set_current_hp($hp)
        {
            $this->current_hp = $hp;
        }

        function get_current_hp()
        {
            return $this->current_hp;
        }

        //returns array
        function get_loots()
        {
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

        function get_choice($has_nuke = false)
        {
            if($has_nuke){
                return rand(0, 3);
            }
            return rand(0, 2);
        }

        function get_description()
        {
            return "Default description";
        }

        function tick_statuses(){
            foreach($this->status as $key => $status_element){
                $status_element->tick();
                if($status_element->get_status_type() == Status::NORMAL){
                    unset($this->status[$key]);
                }
            }
        }

    }

    class MonsterFactory
    {
        /**
        * Creates a Monster based on the provided player level. Basically matchmaking.
        **/ 
        function create_monster_by_player_level($player_level)
        {
            if($player_level === 1){
                return new GiantFrog();
            }
            if($player_level === 2){
                return new FlyingCabbage();
            }
            if($player_level === 3){
                return new DullahansUndeads();
            }
            if($player_level === 4){
                return new Dullahan();
            }
            if($player_level === 5){
                return new Destroyer();
            }
            if($player_level >= 6 && $player_level < 10){
                $arr = array(new Hanz(), new WinterGeneral()); 
                return $arr[array_rand($arr)];
            }
            if($player_level >= 10 && $player_level < 20){
                return new Vanir();
            }
            if($player_level >= 20){
                return new DarknessVanir();
            }
            return new TrainingDummy();
            /**
            switch($player_level){
                case 10: return new Vanir();
                case 9:
                case 8:
                case 7:
                case 6: $arr = array(new Hanz(), new WinterGeneral()); return $arr[array_rand($arr)];
                case 5: return new Destroyer();
                case 4: return new Dullahan();
                case 3: return new DullahansUndeads();
                case 2: return new FlyingCabbage();
                case 1: return new GiantFrog();
                default: return new Vanir(); //Catch all for overleveled players for now
            }
            **/
        }

        /**
        * Creates a Monster based on the provided monster ID.
        **/
        function create_monster_by_id($id)
        {
            $monster_index = get_monster_index();
            if(!array_key_exists($id, $monster_index)){
                throw new Exception("Invalid monster ID");
            }
            return new $monster_index[$id]();
            /*
            switch($id){
                case GiantFrog::ID: return new GiantFrog();
                case FlyingCabbage::ID: return new FlyingCabbage();
                case DullahansUndeads::ID: return new DullahansUndeads();
                case Dullahan::ID: return new Dullahan();
                case Destroyer::ID: return new Destroyer();
                case Hanz::ID: return new Hanz();
                case WinterGeneral::ID: return new WinterGeneral();
                default: throw new Exception("Invalid monster ID");
            }
            */
        }
    }

    /**************************************
    * Monsters
    **************************************/

    class GiantFrog extends Monster
    {
        const ID = 101;
        const NAME = "Giant Frog";

        function __construct()
        {
            // Initialize HP to max
            $this->current_hp = $this->get_hp();
            // Initialize loot table
            $this->loot_table = array(BrassKnuckles::ID=>5000, WoodenSword::ID=>5000, FrogSkin::ID=>5000);
            $this->status = array();
        }
        function get_id()
        {
            return GiantFrog::ID;
        }
        function get_level()
        {
            return 1;
        }
        function get_name()
        {
            return "Giant Frog";
        }
        function get_atk()
        {
            return 2;
        }
        function get_def()
        {
            return 1;
        }
        function get_hp()
        {
            return 20;
        }
        function get_exp()
        {
            return 1;
        }
        function get_crit()
        {
            return 2;
        }
        function get_choice($has_nuke = false)
        {
            // 80% Paper, 10% Rock, 10% Scissors
            // 70% Paper, 10% Rock, 10% Scissors, 10% Explosion
            $rock = 10;
            $paper = $rock + 80;
            $scissors = $paper+10;
            $explosion = 0;
            if($has_nuke === true){
                $paper = $rock + 70;
                $scissors = $paper+10;
                $explosion = $scissors + 10;
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

        function get_description()
        {
            return "A Giant Frog. Known to hibernate underground during the winter, and feeds on farm livestocks when active. Apparently its legs are very delicious when fried! Its skin is soft like Paper...";
        }
    }
    class FlyingCabbage extends Monster
    {

        const ID = 102;
        const NAME = "Flying Cabbage";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(RockAmulet::ID=>5000, CabbageLeaf::ID=>2500);
            $this->status = array();
        }
        function get_id()
        {
            return FlyingCabbage::ID;
        }
        function get_level()
        {
            return 2;
        }
        function get_name()
        {
            return "Flying Cabbage";
        }
        function get_atk()
        {
            return 3;
        }
        function get_def()
        {
            return 2;
        }
        function get_hp()
        {
            return 35;
        }
        function get_exp()
        {
            return 2;
        }
        function get_crit()
        {
            return 3;
        }
        function get_choice($has_nuke = false)
        {
            return 0;
        }
        function get_description()
        {
            return "A Flying... Cabbage!? They roam around to be harvested when ripe, hoping to avoid the horrible fate of rotting away worthlessly instead of being deliciously consumed by humans. It can't seem to do anything other than ram itself at you.";
        }
    }
    class DullahansUndeads extends Monster
    {
        const ID = 103;
        const NAME = "Dullahan's Undeads";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(LuckyPebbles::ID=>10000);
            $this->status = array();
        }
        function get_level()
        {
            return 3;
        }
        function get_id()
        {
            return self::ID;
        }
        function get_name()
        {
            return self::NAME;
        }
        function get_atk()
        {
            return 5;
        }
        function get_def()
        {
            return 3;
        }
        function get_hp()
        {
            return 55;
        }
        function get_exp()
        {
            return 3;
        }
        function get_crit()
        {
            return 4;
        }
        function get_description()
        {
            return "Summoned minions of Dullahan, these undeads only wish to be salvated.";
        }
    }
    class Dullahan extends Monster
    {
        const ID = 104;
        const NAME = "Dullahan";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->status = array();
        }
        function get_id()
        {
            return Dullahan::ID;
        }
        function get_level()
        {
            return 4;
        }
        function get_name()
        {
            return "Dullahan";
        }
        function get_atk()
        {
            return 7;
        }
        function get_def()
        {
            return 5;
        }
        function get_hp()
        {
            return 76;
        }
        function get_exp()
        {
            return 4;
        }
        function get_crit()
        {
            return 5;
        }
        function get_description()
        {
            return "A leader in the Demon Lord's Army, Dullahan possesses incredible swordsmanship. The way he swings his sword around reminds one of very sharp Scissors...";
        }
        function get_choice($has_nuke = false)
        {
            // Normal rates: Rock 20%, Paper 10%, Scissors 70%
            // Always uses Explosion when possible
            $rock = 20;
            $paper = $rock + 10;
            $scissors = $paper + 70;
            $explosion = 0;
            if($has_nuke === true){
                $explosion = 100;
                $scissors = 0;
                $paper = 0;
                $rock = 0;
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
    class Destroyer extends Monster
    {

        const ID = 105;
        const NAME = "Moving Fortress Destroyer";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(CoronatiteCore::ID=>10000);
            $this->status = array();
        }
        function get_id()
        {
            return Destroyer::ID;
        }
        function get_level()
        {
            return 5;
        }
        function get_name()
        {
            return "Moving Fortress Destroyer";
        }
        function get_atk()
        {
            return 10;
        }
        function get_def()
        {
            return 7;
        }
        function get_hp()
        {
            return 102;
        }
        function get_exp()
        {
            return 7;
        }
        function get_crit()
        {
            return 6;
        }
        function get_description()
        {
            return "A destructive weapon which destroyed the city that created it, the Moving Fortress Destroyer wrecks havoc upon anything in its path.";
        }
    }

    class Hanz extends Monster
    {
        const ID = 106;
        const NAME = "Hanz";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(SoDamageMuchWowSuchOP::ID=>8000, TrueSoDamageMuchWowSuchOP::ID=>1000, DeadlySlimeArmor::ID=>10000, SlimeBomb::ID=>10000);
            $this->status = array();
        }
        function get_id()
        {
            return Hanz::ID;
        }
        function get_level()
        {
            return 6;
        }
        function get_name()
        {
            return "Hanz";
        }
        function get_atk()
        {
            return 55;
        }
        function get_def()
        {
            return 50;
        }
        function get_hp()
        {
            return 260;
        }
        function get_exp()
        {
            return 140;
        }
        function get_crit()
        {
            return 10;
        }
        function get_description()
        {
            return "A leader of the Demon Lord's Army, Hanz is an extremely dangerous Deadly Poison Slime, capable of corroding and dissolving almost anything he touches.";
        }
    }

    class WinterGeneral extends Monster
    {
        const ID = 107;
        const NAME = "Winter General";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(TrueSoDamageMuchWowSuchOP::ID=>1000, FrozenKatana::ID=>7000);
            $this->status = array();
        }
        function get_id()
        {
            return WinterGeneral::ID;
        }
        function get_level()
        {
            return 6;
        }
        function get_name()
        {
            return "Winter General";
        }
        function get_atk()
        {
            return 75;
        }
        function get_def()
        {
            return 40;
        }
        function get_hp()
        {
            return 160;
        }
        function get_exp()
        {
            return 135;
        }
        function get_crit()
        {
            return 20;
        }
        function get_description()
        {
            return "The personification of the harsh winter cold.";
        }
    }

    class Vanir extends Monster
    {
        const ID = 108;
        const NAME = "Vanir";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(UselessDirt::ID => 10000);
            $this->status = array();
        }
        function get_id()
        {
            return self::ID;
        }
        function get_level()
        {
            return 10;
        }
        function get_name()
        {
            return self::NAME;
        }
        function get_atk()
        {
            return 666;
        }
        function get_def()
        {
            return 6666;
        }
        function get_hp()
        {
            return 6666;
        }
        function get_exp()
        {
            return 6666;
        }
        function get_crit()
        {
            return 66;
        }
        function get_description()
        {
            return "The master troll, this demon feeds upon the negative emotions of humans such as embarrassment and frustration.";
        }
    }

    class DarknessVanir extends Monster
    {
        const ID = 109;
        const NAME = "Darkness (Vanir Possessed)";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->loot_table = array(UselessDirt::ID => 10000);
            $this->status = array();
        }
        function get_id()
        {
            return self::ID;
        }
        function get_level()
        {
            return 20;
        }
        function get_name()
        {
            return self::NAME;
        }
        function get_atk()
        {
            return 6666;
        }
        function get_def()
        {
            return 66666;
        }
        function get_hp()
        {
            return 666666;
        }
        function get_exp()
        {
            return 66666;
        }
        function get_crit()
        {
            return 66;
        }
        function get_description()
        {
            return "Oh no! Vanir has possessed Darkness! She seems to be enjoying herself though...";
        }
    }


    class TrainingDummy extends Monster
    {
        const ID = 999;
        const NAME = "Training Dummy";
        function __construct()
        {
            $this->current_hp = $this->get_hp();
            $this->status = array();
        }
        function get_id()
        {
            return TrainingDummy::ID;
        }
        function get_level()
        {
            return 999;
        }
        function get_name()
        {
            return "Training Dummy";
        }
        function get_atk()
        {
            return 0;
        }
        function get_def()
        {
            return 0;
        }
        function get_hp()
        {
            return 100000000;
        }
        function get_exp()
        {
            return 0;
        }
        function get_crit()
        {
            return 0;
        }
    }