<?php

require_once("weapons.php");
require_once("accessories.php");
require_once("armors.php");

/**
Factory class for creating equipments
*Weapon IDs: 100001~199999
*Armor IDs: 200001~299999
*Accessory IDs: 300001~399999
**/
class EquipmentFactory
{
    /**
    * Returns an equipment object based on the provided ID.
    * All equipments implement the interface Equipment.
    **/
    function get_equipment($equipment_id){
        switch($equipment_id){
            // Weapons
            case BrassKnuckles::ID: return new BrassKnuckles();
            case WoodenSword::ID: return new WoodenSword();
            // Armors
            case FrogSkin::ID: return new FrogSkin();
            case CabbageLeaf::ID: return new CabbageLeaf();
            // Accessories
            case LuckyPebbles::ID: return new LuckyPebbles();
            case RockAmulet::ID: return new RockAmulet();
            case CoronatiteCore::ID: return new CoronatiteCore();
            case SoDamageMuchWowSuchOP::ID: return new SoDamageMuchWowSuchOP();
            case TrueSoDamageMuchWowSuchOP::ID: return new TrueSoDamageMuchWowSuchOP();
            default: return new Unequipped();
        }
    }
}

interface Equipment
{
    const WEAPON = 0;
    const ARMOR = 1;
    const ACCESSORY = 2;

    /**
    * Returns one of the above Equipment constants
    **/
    public function get_equipment_type();
    /**
    * Returns a string of the equipment name to be displayed to the user
    **/
    public function get_name();
    /**
    * Returns an ID number.
    *Weapon IDs: 100001~199999
    *Armor IDs: 200001~299999
    *Accessory IDs: 300001~399999
    **/
    public function get_id();
    /*
    * Returns a string describing the equipment's effects. Stats to be shown in format "STAT1+STAT1VALUE, STAT2+STAT2VALUE..", eg.:"ATK+5, CRIT+5"
    * May also add description like "ATK X2 when winning with Rock"
    */
    public function get_stats_string();

}

class Unequipped implements Equipment
{
    const ID = -1;
    public function get_equipment_type()
    {
        return -1;
    }
    public function get_name()
    {
        return "Unequipped";
    }
    public function get_id()
    {
        return Unequipped::ID;
    }
    public function get_stats_string()
    {
        return "Nothing equipped";
    }
}