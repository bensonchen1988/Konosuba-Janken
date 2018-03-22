<?php

require_once("equipment_index.php");

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
        $equipment_index = get_equipment_index();

        if(!array_key_exists($equipment_id, $equipment_index)){
            return new Unequipped();
        }
        return new $equipment_index[$equipment_id]();
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
    // On attack special effect proc; returns an array of Status's.
    // Returns an empty array by default;
    public function get_procs_array();

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
    // On attack special effect proc; returns an array of Status's.
    // Returns an empty array by default;
    public function get_procs_array(){
        return array();
    }
}