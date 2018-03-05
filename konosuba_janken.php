<?php
    require_once("game_logic.php");
    require_once("player.php");
    require_once("monsters.php");
    require_once("equipment.php");
    $GameLogic = new GameLogic();
    $MonsterFactory = new MonsterFactory();
    $EquipmentFactory = new EquipmentFactory();
    $cookie_expiration_in_seconds = 86400*30;
    $GLOBALS["console_output_buffer"] = "";
    ob_start();
?>


<!DOCTYPE>  
<html>  
</head>
<body>  
<h1> Konosuba Rock Paper Scissors! </h1>

<!-- javascript help for some sweet audio! -->
<script type="text/javascript">
    var currentMusic;
    var sounds_list = ["explosion","explosion2","lalala","losion","n","n2","plosion","sion","sion2","thinking","truepower"]
    function explosion_sound(){
        currentMusic = document.getElementById(sounds_list[Math.floor(Math.random()*sounds_list.length)]);
        currentMusic.play();
    }

    function explosion_sound_stop(){
        currentMusic.pause();
        currentMusic.currentTime = 0;
    }
</script>

<audio id="explosion">
    <source src="sounds/explosion.ogg" type="audio/ogg">
    <source src="sounds/explosion.mp3" type="audio/mpeg">
</audio>
<audio id="explosion2">
    <source src="sounds/explosion2.ogg" type="audio/ogg">
    <source src="sounds/explosion2.mp3" type="audio/mpeg">
</audio>
<audio id="lalala">
    <source src="sounds/lalala.ogg" type="audio/ogg">
    <source src="sounds/lalala.mp3" type="audio/mpeg">
</audio>
<audio id="losion">
    <source src="sounds/losion.ogg" type="audio/ogg">
    <source src="sounds/losion.mp3" type="audio/mpeg">
</audio>
<audio id="n">
    <source src="sounds/n.ogg" type="audio/ogg">
    <source src="sounds/n.mp3" type="audio/mpeg">
</audio>
<audio id="n2">
    <source src="sounds/n2.ogg" type="audio/ogg">
    <source src="sounds/n2.mp3" type="audio/mpeg">
</audio>
<audio id="plosion">
    <source src="sounds/plosion.ogg" type="audio/ogg">
    <source src="sounds/plosion.mp3" type="audio/mpeg">
</audio>
<audio id="sion">
    <source src="sounds/sion.ogg" type="audio/ogg">
    <source src="sounds/sion.mp3" type="audio/mpeg">
</audio>
<audio id="sion2">
    <source src="sounds/sion2.ogg" type="audio/ogg">
    <source src="sounds/sion2.mp3" type="audio/mpeg">
</audio>
<audio id="thinking">
    <source src="sounds/thinking.ogg" type="audio/ogg">
    <source src="sounds/thinking.mp3" type="audio/mpeg">
</audio>
<audio id="truepower">
    <source src="sounds/truepower.ogg" type="audio/ogg">
    <source src="sounds/truepower.mp3" type="audio/mpeg">
</audio>

<form action="konosuba_janken.php" method="post">
    <input type="submit" name="reset" value="Reset Game">
</form>

<form action="konosuba_janken.php" method="post">
    <select id="monster_select" name="monster_select"> 
        <!-- TODO: PROGRAMMATICALLY GENERATE THE LIST -->
        <option value = 101>Giant Frog</option>
        <option value = 102>Flying Cabbage</option>
        <option value = 103>Dullahan's Undead</option>
        <option value = 104>Dullahan</option>
        <option value = 105>Destroyer</option>
        <option value = 106>Hanz</option>
        <option value = 999>Training Dummy</option>
    <input type="submit" value="Change Monster">
    </select>
</form>
<?php
    // meta data about game progress
    $cookie_name = "meta_data_cookie";

    $player_lose_streak_key = "ulsk";
    $player_stored_nukes_key = "usnk";
    $player_wins_key = "uwk";
    $cpu_lose_streak_key = "clsk";
    $cpu_stored_nukes_key = "csnk";
    $cpu_wins_key = "cnk";

    $player_lose_streak = 0;
    $player_stored_nukes = 0;
    $player_wins = 0;
    $cpu_lose_streak = 0;
    $cpu_stored_nukes = 0;
    $cpu_wins = 0;

    if(isset($_COOKIE[$cookie_name])){
        $cook = unserialize($_COOKIE[$cookie_name]);
        $player_lose_streak = $cook[$player_lose_streak_key];
        $player_stored_nukes = $cook[$player_stored_nukes_key];
        $player_wins = $cook[$player_wins_key];
        $cpu_lose_streak = $cook[$cpu_lose_streak_key];
        $cpu_stored_nukes = $cook[$cpu_stored_nukes_key];
        $cpu_wins = $cook[$cpu_wins_key];
    }

?>

<?php
    $cookie_name_stats = "stats_cookie";
    $cookie_name_inventory = "inventory_cookie";

    $player_level_key = "plk";
    $player_current_hp_key = "pchk";
    $monster_current_hp_key = "mchk";
    $monster_id_key = "midk";
    $player_exp_key = "pek";
    $player_weapon_key = "pwk";
    $player_armor_key = "pak";
    $player_accessory_key = "pacck";

    $PlayerCharacter = new Player();
    $Monster = $MonsterFactory->create_monster_by_id(GiantFrog::ID);
    if(isset($_COOKIE[$cookie_name_stats])){
        $cook_stats = unserialize($_COOKIE[$cookie_name_stats]);
        $PlayerCharacter->set_level($cook_stats[$player_level_key]);
        $PlayerCharacter->set_exp($cook_stats[$player_exp_key]);
        $PlayerCharacter->set_current_hp($cook_stats[$player_current_hp_key]);
        $PlayerCharacter->set_weapon($EquipmentFactory->get_equipment($cook_stats[$player_weapon_key]));
        $PlayerCharacter->set_armor($EquipmentFactory->get_equipment($cook_stats[$player_armor_key]));
        $PlayerCharacter->set_accessory($EquipmentFactory->get_equipment($cook_stats[$player_accessory_key]));
        $Monster = $MonsterFactory->create_monster_by_id($cook_stats[$monster_id_key]);
        $Monster->set_current_hp($cook_stats[$monster_current_hp_key]);
        
    }

    if(isset($_POST["weapon_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["weapon_select"]);
        $old_equipment = $PlayerCharacter->get_weapon();
        $PlayerCharacter->set_weapon($equipment);
        equipment_status_output($old_equipment, $equipment);
    }
    if(isset($_POST["armor_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["armor_select"]);
        $old_equipment = $PlayerCharacter->get_armor();
        $PlayerCharacter->set_armor($equipment);
        equipment_status_output($old_equipment, $equipment);
    }
    if(isset($_POST["accessory_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["accessory_select"]);
        $old_equipment = $PlayerCharacter->get_accessory();
        $PlayerCharacter->set_accessory($equipment);
        equipment_status_output($old_equipment, $equipment);
    }

    function equipment_status_output($old_equipment, $equipment){
        if($old_equipment->get_id() !== $equipment->get_id()){
            if($equipment->get_id() === Unequipped::ID){
                $GLOBALS["console_output_buffer"] .=  "You've removed " . $old_equipment->get_name() . "!\n";
            }
            else{
                $GLOBALS["console_output_buffer"] .=   "You've equipped " . $equipment->get_name() . "!\n";
            }
        }
    }


    if(isset($_COOKIE[$cookie_name_inventory])){
        $inventory = unserialize($_COOKIE[$cookie_name_inventory]);
        $PlayerCharacter->set_inventory($inventory);
    }


    if(isset($_POST["monster_select"])){
        $GLOBALS["console_output_buffer"] .= "Changed Monster!\n";
        $Monster = $MonsterFactory->create_monster_by_id($_POST["monster_select"]);
        $player_lose_streak = 0;
        $cpu_lose_streak = 0;
        $cpu_stored_nukes = 0;
    }

    if(isset($_POST["reset"])){
        $GLOBALS["console_output_buffer"] .= "Resetted game!\n"; 
        $PlayerCharacter = new Player();
        $Monster = $MonsterFactory->create_monster_by_id(GiantFrog::ID);

        $player_lose_streak = 0;
        $player_stored_nukes = 0;
        $player_wins = 0;
        $cpu_lose_streak = 0;
        $cpu_stored_nukes = 0;
        $cpu_wins = 0;
    }


?>

<?php
    $player_input = -1;
    
    if(isset($_POST["player_input"])){
        $player_input = $_POST["player_input"];
        $PlayerCharacter->set_input($player_input);
    }
?>



<?php  
    $choices = array("Rock", "Paper", "Scissors", "EXPLOSION");
    
    $computer_choice = $Monster->get_choice($cpu_stored_nukes>0);

    if($player_input === -1){
        $player_input_display = "Please make a choice!";
    }
    else{
    	$player_input_display = $choices[$player_input];
        $GLOBALS["console_output_buffer"] .= "Your choice: $player_input_display\n";
        $computer_choice_display = $choices[$computer_choice];
        $GLOBALS["console_output_buffer"] .= $Monster->get_name() ."'s choice: $computer_choice_display\n";
    }

    $result = $GameLogic->get_winner($computer_choice, $player_input, $choices, $player_stored_nukes, $player_lose_streak, $player_wins, $cpu_stored_nukes, $cpu_lose_streak, $cpu_wins, $Monster->get_name());

    if($result === "p"){
        // If player wins, do damage to monster
        $damage = $GameLogic->calculate_damage_on_monster($PlayerCharacter, $Monster);
        $monster_current_hp = $Monster->get_current_hp() - $damage;
        $Monster->set_current_hp($monster_current_hp);
        if($monster_current_hp <= 0){
            // If monster dies from the attack, reward exp to player, check for level up (to level cap), and replace monster with new one
            // Award exp and level up if possible
            $exp_awarded = $Monster->get_exp();
            $PlayerCharacter->gain_exp($exp_awarded);
            // HP regen award for killing monster
            $PlayerCharacter->set_current_hp($PlayerCharacter->get_current_hp()+$GameLogic->get_kill_hp_regen($Monster));
            // Loot Check
            if(sizeof($Monster->get_loots()) > 0){
                foreach($Monster->get_loots() as $id){
                    $PlayerCharacter->add_inventory($id);
                    $equipment = $EquipmentFactory->get_equipment($id);
                    $GLOBALS["console_output_buffer"] .= "\nYou got a " . $equipment->get_name() . "!";
                }
            }
            $GLOBALS["console_output_buffer"] .= "\n" . $Monster->get_name() . " died!\n";
            // Change monster
            $Monster = $MonsterFactory->create_monster_by_player_level($PlayerCharacter->get_level());

            // Reset streaks
            $player_lose_streak = 0;
            $cpu_lose_streak = 0;
            $cpu_stored_nukes = 0;
        }

    }

    if($result === "c"){
        // If computer(monster) wins, do damage to player
        $damage = $GameLogic->calculate_damage_on_player($Monster, $PlayerCharacter);
        //$player_current_hp = $player_current_hp - $damage;
        $PlayerCharacter->set_current_hp($PlayerCharacter->get_current_hp() - $damage);
        // If player dies, reset player HP and do exp penalty, and reset monster to a new one
        if($PlayerCharacter->get_current_hp()<= 0){
            $GLOBALS["console_output_buffer"] .= "\n" . "You died!\n";
            // Reset HP and do exp penalty;
            //$player_current_hp = $GameLogic->get_hp($player_level);
            $PlayerCharacter->set_current_hp($PlayerCharacter->get_hp());
            //$player_exp = floor($player_exp * (1-$GameLogic->get_exp_penalty_rate()));
            $PlayerCharacter->set_exp(floor($PlayerCharacter->get_exp() * (1-$GameLogic->get_exp_penalty_rate())));

            // Change monster
            $Monster = $MonsterFactory->create_monster_by_player_level($PlayerCharacter->get_level());
        }
    }

?>  


<?php
    /**
    * META DATA COOKIE
    **/
    $thecookie = array();
    $thecookie[$player_lose_streak_key] = $player_lose_streak;
    $thecookie[$player_stored_nukes_key] = $player_stored_nukes;
    $thecookie[$player_wins_key] = $player_wins;
    $thecookie[$cpu_lose_streak_key] = $cpu_lose_streak;
    $thecookie[$cpu_stored_nukes_key] = $cpu_stored_nukes;
    $thecookie[$cpu_wins_key] = $cpu_wins;

    setcookie($cookie_name, serialize($thecookie), time()+$cookie_expiration_in_seconds, "/");
?>

<?php
    /**
    * COMBAT STATS COOKIE
    **/
    $thecookie_stats = array();
    $thecookie_stats[$player_level_key] = $PlayerCharacter->get_level();
    $thecookie_stats[$player_current_hp_key] = $PlayerCharacter->get_current_hp();
    $thecookie_stats[$monster_current_hp_key] = $Monster->get_current_hp();
    $thecookie_stats[$monster_id_key] = $Monster->get_id();
    $thecookie_stats[$player_exp_key] = $PlayerCharacter->get_exp();
    $thecookie_stats[$player_weapon_key] = $PlayerCharacter->get_weapon()->get_id();
    $thecookie_stats[$player_armor_key] = $PlayerCharacter->get_armor()->get_id();
    $thecookie_stats[$player_accessory_key] = $PlayerCharacter->get_accessory()->get_id();

    setcookie($cookie_name_stats, serialize($thecookie_stats), time()+$cookie_expiration_in_seconds, "/");

?>

<?php
    /**
    * PLAYER INVENTORY COOKIE
    **/
    $thecookie_inventory = $PlayerCharacter->get_inventory();
    setcookie($cookie_name_inventory, serialize($thecookie_inventory), time()+$cookie_expiration_in_seconds, "/");
?>

<img src = <?php echo "\"images/" . $Monster->get_name() ."\"";?> height = "300" width = "400" title = <?php echo "\"". $Monster->get_description() ."\""; ?>>
<br>
<?php echo $Monster->get_name() . ": Level " . $Monster->get_level(); ?>
<br>
HP: <?php echo $Monster->get_current_hp(); ?> / <?php echo $Monster->get_hp() ?>,  ATK: <?php echo $Monster->get_atk(); ?>, DEF: <?php echo $Monster->get_def(); ?>, CRIT: <?php echo $Monster->get_crit(); ?>
<br>

<table>
    <form action="konosuba_janken.php" method="POST" id = "rock">
        <input type="hidden" name="player_input" value="0">
        <input type="image" src="images/rock.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST">
        <input type="hidden" name="player_input" value="1" id = "paper">
        <input type="image" src="images/paper.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST" id = "scissors">
        <input type="hidden" name="player_input" value="2">
        <input type="image" src="images/scissors.jpg" height="200" width="200">
    </form>


    <?php

         $temp_nukes = $player_stored_nukes;
         if($player_input === 3){
            --$temp_nukes;
         }
         if($temp_nukes > 0){
            //echo "<div class=\"center\">";
            echo '<form action="konosuba_janken.php" method="POST">
                <input type="hidden" name="player_input" value="3">
                <input type="image" src="images/explosion.gif" height="200" width="200" onmouseover="explosion_sound()" onmouseout="explosion_sound_stop()">
                </form>';
           // echo "</div>";
         }
       //  echo "<br>";
    ?>

    <!-- 1. Iterate through inventory and call EquipmentFactory->get_equipment($id) on them all -->
    <!-- 2. While iterating and creating, maintain 3 arrays for Weapons, Armors, and Accessories -->
    <!-- 3. Generate output list for each category -->

    <?php
        $weapons_array = array();
        $armors_array = array();
        $accessories_array = array();
        foreach($PlayerCharacter->get_inventory() as $id){
            $item = $EquipmentFactory->get_equipment($id);
            if($item->get_equipment_type() === Equipment::WEAPON){
                array_push($weapons_array, $item);
            }
            else if($item->get_equipment_type() === Equipment::ARMOR){
                array_push($armors_array, $item);
            }
            else{
                array_push($accessories_array, $item);
            }
        }
    ?>

    <br>

    <?php
        function equipment_list_printer($equipment, $player_equipment){
            echo "<option ";
            if($equipment->get_id() == $player_equipment->get_id()){
                echo "selected ";
            }
            echo "value = " . $equipment->get_id() . ">" . $equipment->get_name() . " - " . $equipment->get_stats_string() . "</option>";
        }
    ?>

    <form action="konosuba_janken.php" method="post">
    <select id="weapon_select" name="weapon_select"> 
        <option value = -1>--Choose Weapon--</option>
        <?php
            foreach($weapons_array as $weapon){
                equipment_list_printer($weapon, $PlayerCharacter->get_weapon());
            }
        ?>
    </select>
    <select id="armor_select" name="armor_select"> 
        <option value = -1>--Choose Armor--</option>
        <?php
            foreach($armors_array as $armor){
                equipment_list_printer($armor, $PlayerCharacter->get_armor());
            }
        ?>
    </select>
    <select id="accessory_select" name="accessory_select"> 
        <option value = -1>--Choose Accessory--</option>
        <?php
            foreach($accessories_array as $accessory){
                equipment_list_printer($accessory, $PlayerCharacter->get_accessory());
            }
        ?>
    </select>
    <input type="submit" value="Change Equipments">
    </form>

</table>

    <?php
        echo "You: Level " . $PlayerCharacter->get_level();
        echo "<br>";
        echo "HP: " . $PlayerCharacter->get_current_hp() . "/" . $PlayerCharacter->get_hp();
        echo ", ";
        echo "ATK: " . $PlayerCharacter->get_atk();
        //if ($PlayerCharacter->has_weapon()){
        //    echo "+". $PlayerCharacter->get_weapon()->get_atk();
        //}
        echo ", ";
        echo "DEF: " . $PlayerCharacter->get_def();
        //if ($PlayerCharacter->has_armor()){
        //    echo "+". $PlayerCharacter->get_armor()->get_def();
        //}
        echo ", ";
        echo "CRIT: " . $PlayerCharacter->get_crit();
        echo "<br>";
        echo "EXP: " . $PlayerCharacter->get_exp() . "/" . $PlayerCharacter->get_required_exp();
        echo "<br>";
    ?>

<style>
textarea{
    resize: none;
}
</style>
 <textarea rows="5" cols="60" readonly style="background-color: lightcyan">
<?php echo $GLOBALS["console_output_buffer"]; ?>
</textarea> 

<br> Your lose streak: <?php echo $player_lose_streak ?>
<br> Your Explosions available: <?php echo $player_stored_nukes ?>
<br> Your total wins: <?php echo $player_wins ?>
<br> Computer's lose streak: <?php echo $cpu_lose_streak ?>
<br> Computer's Explosions available: <?php echo $cpu_stored_nukes ?>
<br> Computer's total wins: <?php echo $cpu_wins ?>

<?php 
    ob_end_flush();
?>
</body>  
</html>  