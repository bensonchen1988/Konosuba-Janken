<?php
require_once("monsters.php");
// ID => ClassName
function get_monster_index()
{
    return array(
    GiantFrog::ID => "GiantFrog",
    FlyingCabbage::ID => "FlyingCabbage",
    DullahansUndeads::ID => "DullahansUndeads",
    Dullahan::ID => "Dullahan",
    Destroyer::ID => "Destroyer",
    Hanz::ID => "Hanz",
    WinterGeneral::ID => "WinterGeneral",
    TrainingDummy::ID => "TrainingDummy"
    );
}