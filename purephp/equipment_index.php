<?php
require_once("weapons.php");
require_once("accessories.php");
require_once("armors.php");
// ID => ClassName
function get_equipment_index()
{
    return array(
            // Weapons
            BrassKnuckles::ID => "BrassKnuckles",
            WoodenSword::ID => "WoodenSword",
            FrozenKatana::ID => "FrozenKatana",
            // Armors
            FrogSkin::ID => "FrogSkin",
            CabbageLeaf::ID => "CabbageLeaf",
            DeadlySlimeArmor::ID => "DeadlySlimeArmor",
            UselessDirt::ID => "UselessDirt",
            // Accessories
            LuckyPebbles::ID => "LuckyPebbles",
            RockAmulet::ID => "RockAmulet",
            CoronatiteCore::ID => "CoronatiteCore",
            SoDamageMuchWowSuchOP::ID => "SoDamageMuchWowSuchOP",
            TrueSoDamageMuchWowSuchOP::ID => "TrueSoDamageMuchWowSuchOP"
    );
}