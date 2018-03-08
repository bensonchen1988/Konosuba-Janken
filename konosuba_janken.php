<?php
    require_once("purephp/game_logic.php");
    require_once("purephp/player.php");
    require_once("purephp/monsters.php");
    require_once("purephp/monsters_index.php");
    require_once("purephp/equipment.php");
    require_once("purephp/database.php");

    session_start();
    if(!isset($_SESSION["user"])){
    $_SESSION["login_message"] = "youuuuuuuuuuuu get off my lawn";
    header("Location: index.php");
    }

    $GameLogic = new GameLogic();
    $MonsterFactory = new MonsterFactory();
    $EquipmentFactory = new EquipmentFactory();
    $KonosubaDB = new KonosubaDB();
    // Buffer used for aggregating all event logs to output into a textarea at the end of the user to see
    $GLOBALS["console_output_buffer"] = "";
    ob_start();
?>


<!DOCTYPE>  
<html>  
</head>
<body>  
<h1> Konosuba Rock Paper Scissors! </h1>
Logged in as : <?php echo $_SESSION["user"]; ?>

<!-- Javascript help for some sweet audio! -->
<!-- Plays an audio file selected at random from the list below when the user hovers their mouse over the Explosion image -->
<script type="text/javascript">
    // Explosion audio
    var currentMusic;
    var sounds_list = ["explosion","explosion2","lalala","losion","n","n2","plosion","sion","sion2","thinking","truepower"]
    function explosion_sound()
    {
        currentMusic = document.getElementById(sounds_list[Math.floor(Math.random()*sounds_list.length)]);
        currentMusic.play();
    }

    function explosion_sound_stop()
    {
        currentMusic.pause();
        currentMusic.currentTime = 0;
    }

    // Auto Battle
    function autoBattleCheckBox(checkbox){
        if(checkbox.checked){
            localStorage.setItem("autoBattle", "checked");
        }
        else{
            localStorage.setItem("autoBattle", "unchecked");
        }
        doAutoBattle();
    }

    function doAutoBattle(){
        setTimeout(function () {
        if(localStorage.getItem("autoBattle") == "checked"){
            var random = Math.floor(Math.random() * 3);
            if(document.getElementById("explosion_input") !== null){
                random = Math.floor(Math.random() * 4); // 0 ~ 3
            }
            switch(random){
                case 0: 
                document.getElementById("rock_input").submit();
                break;
                case 1: 
                document.getElementById("paper_input").submit();
                break;
                case 2: 
                document.getElementById("scissors_input").submit();
                break;
                case 3: 
                document.getElementById("explosion_input").submit();
                break;
            }
        }
    }, 500);
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
        <?php
        $monster_index = get_monster_index();
        foreach($monster_index as $ID => $CLASSNAME){
            echo "<option value = " . $ID . ">" . $CLASSNAME::NAME . "</option>";
        }
        ?>
    <input type="submit" value="Change Monster">
    </select>
</form>

<?php
    // Load user-specific game state
    $game_state = $KonosubaDB->get_game_state($_SESSION["user"]);

    // Default initialization
    $player_lose_streak = 0;
    $player_stored_nukes = 0;
    $player_wins = 0;
    $cpu_lose_streak = 0;
    $cpu_stored_nukes = 0;
    $cpu_wins = 0;
    $farm_mode = 0;
    $PlayerCharacter = new Player();
    $Monster = $MonsterFactory->create_monster_by_id(GiantFrog::ID);

    // If game state for user exists in DB, load the values into the PlayerCharacter and Monster objects
    if($game_state !== false){
        $PlayerCharacter->set_level($game_state["player_level"]);
        $PlayerCharacter->set_exp($game_state["player_exp"]);
        $PlayerCharacter->set_current_hp($game_state["player_current_hp"]);
        $PlayerCharacter->set_weapon($EquipmentFactory->get_equipment($game_state["player_weapon"]));
        $PlayerCharacter->set_armor($EquipmentFactory->get_equipment($game_state["player_armor"]));
        $PlayerCharacter->set_accessory($EquipmentFactory->get_equipment($game_state["player_accessory"]));
        $Monster = $MonsterFactory->create_monster_by_id($game_state["monster_id"]);
        $Monster->set_current_hp($game_state["monster_current_hp"]);
        
        $player_lose_streak =$game_state["player_lose_streak"];
        $player_stored_nukes = $game_state["player_stored_nukes"];
        $player_wins = $game_state["player_wins"];
        $cpu_lose_streak = $game_state["monster_lose_streak"];
        $cpu_stored_nukes = $game_state["monster_stored_nukes"];
        $cpu_wins = $game_state["monster_wins"];
        $farm_mode = $game_state["farm_mode"];
    }


    if(isset($_POST["farm"])){
        $farm_mode = ($farm_mode - 1) * -1;
    }

    // Update weapon equipment status
    if(isset($_POST["weapon_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["weapon_select"]);
        $old_equipment = $PlayerCharacter->get_weapon();
        $PlayerCharacter->set_weapon($equipment);
        equipment_status_output($old_equipment, $equipment);
    }
    // Update armor equipment status
    if(isset($_POST["armor_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["armor_select"]);
        $old_equipment = $PlayerCharacter->get_armor();
        $PlayerCharacter->set_armor($equipment);
        equipment_status_output($old_equipment, $equipment);
    }
    // Update accessory equipment status
    if(isset($_POST["accessory_select"])){
        $equipment = $EquipmentFactory->get_equipment($_POST["accessory_select"]);
        $old_equipment = $PlayerCharacter->get_accessory();
        $PlayerCharacter->set_accessory($equipment);
        equipment_status_output($old_equipment, $equipment);
    }

    /**
    *   Helper function to output equipment changing status. 
    **/
    function equipment_status_output($old_equipment, $equipment)
    {
        if($old_equipment->get_id() !== $equipment->get_id()){
            if($equipment->get_id() === Unequipped::ID){
                $GLOBALS["console_output_buffer"] .=  "You've removed " . $old_equipment->get_name() . "!\n";
            }
            else{
                $GLOBALS["console_output_buffer"] .=   "You've equipped " . $equipment->get_name() . "!\n";
            }
        }
    }


    // Load inventory
    $inventory_db = $KonosubaDB->get_inventory($_SESSION["user"]);
    while($row = $inventory_db->fetch()){
            $PlayerCharacter->add_inventory($row["equipment_id"]);
    }
    

    // Process monster change request
    if(isset($_POST["monster_select"])){
        $GLOBALS["console_output_buffer"] .= "Changed Monster!\n";
        $Monster = $MonsterFactory->create_monster_by_id($_POST["monster_select"]);
        $player_lose_streak = 0;
        $cpu_lose_streak = 0;
        $cpu_stored_nukes = 0;
    }

    // Process reset game request: Initializes game state back to default.
    if(isset($_POST["reset"])){
        $GLOBALS["console_output_buffer"] .= "Resetted game!\n"; 
        $PlayerCharacter = new Player();
        $Monster = $MonsterFactory->create_monster_by_id(GiantFrog::ID);
        $KonosubaDB->reset_player_inventory($_SESSION["user"]);

        $player_lose_streak = 0;
        $player_stored_nukes = 0;
        $player_wins = 0;
        $cpu_lose_streak = 0;
        $cpu_stored_nukes = 0;
        $cpu_wins = 0;
        $farm_mode = 0;
    }
?>


<form action="konosuba_janken.php" method="post">
<?php 
echo '<input type="submit" name="farm" value="Farm Mode Is ';
if($farm_mode == 1){
    echo 'On"';
}
else{
    echo 'Off"';
}
echo ">";

?> 
</form>


<?php  
    /**********************************************************************************
    * THIS SECTION IS THE HEART OF THE CARDS, ER, I MEAN GAME
    **********************************************************************************/

    // Default player input set to do nothing
    $player_input = -1;
    
    if(isset($_POST["player_input"])){
        $player_input = $_POST["player_input"];
        $PlayerCharacter->set_input($player_input);
    }

    // Proceed with the game and combat if a valid player input is received
    if($player_input >= 0 && $player_input <= 3){
        $choices = array("Rock", "Paper", "Scissors", "EXPLOSION");
        // Generate Monster choice. Different monsters may have unique choice patterns.
        $computer_choice = $Monster->get_choice($cpu_stored_nukes>0);
        // Output both sides' choices to the event log buffer
        $GLOBALS["console_output_buffer"] .= "Your choice: $choices[$player_input]\n";
        $GLOBALS["console_output_buffer"] .= $Monster->get_name() ."'s choice: $choices[$computer_choice]\n";
    
        // Get the winner of the fight. 
        // TODO: Rewrite method signature into get_winner(Player, Monster, MetaData), emphasis on the MetaData because meta data is being manipulated inside
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
                        $KonosubaDB->add_inventory($_SESSION["user"], $id);
                        $equipment = $EquipmentFactory->get_equipment($id);
                        $GLOBALS["console_output_buffer"] .= "\nYou got a " . $equipment->get_name() . "!";
                    }
                }
                $GLOBALS["console_output_buffer"] .= "\n" . $Monster->get_name() . " died!\n";
                // Change monster
                if($farm_mode == 1){
                    $Monster = $MonsterFactory->create_monster_by_id($Monster->get_id());
                }
                else{
                    $Monster = $MonsterFactory->create_monster_by_player_level($PlayerCharacter->get_level());
                }   

                // Reset streaks
                $player_lose_streak = 0;
                $cpu_lose_streak = 0;
                // Monster is changed so it makes sense for this poor new fodder to have no Explosions :(
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
    }

    

?>  

<?php
    // Store game state into DB
    $KonosubaDB->record_game_state($_SESSION["user"], $PlayerCharacter->get_level(), $PlayerCharacter->get_exp(), $PlayerCharacter->get_weapon()->get_id(), $PlayerCharacter->get_armor()->get_id(), $PlayerCharacter->get_accessory()->get_id(), $PlayerCharacter->get_current_hp(), $Monster->get_current_hp(), $Monster->get_id(), $player_lose_streak, $player_stored_nukes, $player_wins, $cpu_lose_streak, $cpu_stored_nukes, $cpu_wins, $farm_mode);

?>

<img src = <?php echo "\"images/" . $Monster->get_name() ."\"";?> height = "300" width = "400" title = <?php echo "\"". $Monster->get_description() ."\""; ?>>
<br>
<?php echo $Monster->get_name() . ": Level " . $Monster->get_level(); ?>
<br>
HP: <?php echo $Monster->get_current_hp(); ?> / <?php echo $Monster->get_hp() ?>,  ATK: <?php echo $Monster->get_atk(); ?>, DEF: <?php echo $Monster->get_def(); ?>, CRIT: <?php echo $Monster->get_crit(); ?>
<br>

<table>
    <form action="konosuba_janken.php" method="POST" id = "rock_input">
        <input type="hidden" name="player_input" value="0">
        <input type="image" src="images/rock.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST" id = "paper_input">
        <input type="hidden" name="player_input" value="1">
        <input type="image" src="images/paper.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST" id = "scissors_input">
        <input type="hidden" name="player_input" value="2">
        <input type="image" src="images/scissors.jpg" height="200" width="200">
    </form>


    <?php
         $temp_nukes = $player_stored_nukes;
         if($player_input === 3){
            --$temp_nukes;
         }
         if($temp_nukes > 0){
            echo '<form action="konosuba_janken.php" method="POST" id = "explosion_input">
                <input type="hidden" name="player_input" value="3">
                <input type="image" src="images/explosion.gif" height="200" width="200" onmouseover="explosion_sound()" onmouseout="explosion_sound_stop()">
                </form>';
         }
    ?>

    <label for="auto_battle_checkbox">Auto Battle</label>
    <input id="auto_battle_checkbox" type="checkbox" onclick="autoBattleCheckBox(this)">

    <script type="text/javascript">
        document.getElementById("auto_battle_checkbox").checked = localStorage.getItem("autoBattle") == "checked";
        doAutoBattle();
    </script>


    <?php    
        // 1. Iterate through inventory and call EquipmentFactory->get_equipment($id) on them all 
        // 2. While iterating and creating, maintain 3 arrays for Weapons, Armors, and Accessories 
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
        // 3. Generate output list for each category 
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
        echo ", ";
        echo "DEF: " . $PlayerCharacter->get_def();
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