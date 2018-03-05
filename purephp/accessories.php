<?php
require_once("accessory_effects.php");


abstract class Accessory implements Equipment
{
    protected $stats_effect = array();

    function get_equipment_type()
    {
        return Equipment::ACCESSORY;
    }

    function get_effects()
    {
        return $this->stats_effect;
    }
}


class LuckyPebbles extends Accessory
{
    const ID = 300001;

    function __construct()
    {
        array_push($this->stats_effect, new StatsBoost(1, 1, 1));
    }

    function get_id()
    {
        return LuckyPebbles::ID;
    }

    function get_stats_string()
    {
        return "ATK+" . $this->stats_effect[0]->get_atk_boost() . " " . "DEF+" . $this->stats_effect[0]->get_def_boost() . " " . "CRIT+" . $this->stats_effect[0]->get_crit_boost();
    }

    function get_name()
    {
        return "Lucky Pebbles";
    }
}

class RockAmulet extends Accessory
{
    const ID = 300002;

    function __construct()
    {
        array_push($this->stats_effect, new RockBoost(1.5));
    }

    function get_id()
    {
        return RockAmulet::ID;
    }

    function get_stats_string()
    {
        return "1.5X Base Atk when winning with Rock";
    }

    function get_name()
    {
        return "Rock Amulet";
    }
}
class CoronatiteCore extends Accessory
{
    const ID = 300003;

    function __construct()
    {
        array_push($this->stats_effect, new StatsBoost(0, 50, 50));
    }

    function get_id()
    {
        return CoronatiteCore::ID;
    }

    function get_stats_string()
    {
        return "DEF+50, CRIT+50";
    }

    function get_name()
    {
        return "Coronatite Core";
    }
}

class SoDamageMuchWowSuchOP extends Accessory
{
    const ID = 999999;

    function __construct()
    {
        array_push($this->stats_effect, new RockBoost(2), new PaperBoost(2), new ScissorsBoost(2), new StatsBoost(50, 25, 50), new ExplosionBoost(2));
    }

    function get_id()
    {
        return SoDamageMuchWowSuchOP::ID;
    }

    function get_stats_string()
    {
        return "2X Base Atk, ATK+50, DEF+25, CRIT+50";
    }

    function get_name()
    {
        return "So Damage Much Wow Such OP";
    }
}

class TrueSoDamageMuchWowSuchOP extends Accessory
{
    const ID = 666666;

    function __construct()
    {
        array_push($this->stats_effect, new RockBoost(5), new PaperBoost(5), new ScissorsBoost(5), new StatsBoost(250, 50, 100), new ExplosionBoost(5));
    }

    function get_id()
    {
        return TrueSoDamageMuchWowSuchOP::ID;
    }

    function get_stats_string()
    {
        return "5X Base Atk, ATK+250, DEF+50, CRIT+100";
    }

    function get_name()
    {
        return "True So Damage Much Wow Such OP";
    }
}