<?php
header('Content-type: application/json');
    require_once("purephp/game_logic.php");
    require_once("purephp/player.php");
    require_once("purephp/monsters.php");
    require_once("purephp/monsters_index.php");
    require_once("purephp/equipment.php");
    require_once("purephp/database.php");
    require_once("purephp/status.php");
    session_start();

    $GLOBALS["json_response"] = array();

    if(!isset($_SESSION["user"])){
        $_SESSION["login_message"] = "Please login again";
        $GLOBALS["json_response"]["login_status"] = "invalid";
        echo json_encode($GLOBALS["json_response"]);
        exit();
    }

    $KonosubaDB = new KonosubaDB();

    if(session_id() != $KonosubaDB->get_login($_SESSION["user"])["session_id"]){
        unset($_SESSION);
        $_SESSION["login_message"] = "You have been logged out because you have logged in from another device.";
        $GLOBALS["json_response"]["login_status"] = "invalid_another_device";
        echo json_encode($GLOBALS["json_response"]);
        exit();
    }
    $GameLogic = new GameLogic();
    $MonsterFactory = new MonsterFactory();
    $EquipmentFactory = new EquipmentFactory();
    // Buffer used for aggregating all event logs to output into a textarea at the end of the user to see
    $GLOBALS["console_output_buffer"] = "";

?>

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
        $Status = new Status($game_state["monster_status"], 0, $game_state["monster_status_turns"]);
        $Monster->set_status($Status);
        $PlayerCharacter->set_mode($game_state["player_avatar"]);
        
        $player_lose_streak =$game_state["player_lose_streak"];
        $player_stored_nukes = $game_state["player_stored_nukes"];
        $player_wins = $game_state["player_wins"];
        $cpu_lose_streak = $game_state["monster_lose_streak"];
        $cpu_stored_nukes = $game_state["monster_stored_nukes"];
        $cpu_wins = $game_state["monster_wins"];
        $farm_mode = $game_state["farm_mode"];
    }

    // Load inventory
    $inventory_db = $KonosubaDB->get_inventory($_SESSION["user"]);
    while($row = $inventory_db->fetch()){
            $PlayerCharacter->add_inventory($row["equipment_id"]);
    }

    if(isset($_POST["avatar_select"])){
    	// Special DarknessVanir interaction with MODE_DARKNESS
    	if($_POST["avatar_select"] == Player::MODE_DARKNESS && $Monster->get_id() === DarknessVanir::ID){
			$GLOBALS["console_output_buffer"] .= "Darkness is currently possessed by Vanir!";
		}
		else{
	    	$old_avatar = $PlayerCharacter->get_mode();
	    	$PlayerCharacter->set_mode($_POST["avatar_select"]);
	    	$new_avatar = $PlayerCharacter->get_mode();
	        if($old_avatar !== $new_avatar){
	            switch($new_avatar){
	                case Player::MODE_KAZUMA: $GLOBALS["console_output_buffer"] .= "Kazuma cautiously crawls to the front line."; break;
	                case Player::MODE_AQUA: $GLOBALS["console_output_buffer"] .= "Aqua reluctantly gets dragged to the front line."; break;
	                case Player::MODE_MEGUMIN: $GLOBALS["console_output_buffer"] .= "Megumin walks confidently to the front line, eager to show off her Explosion magic."; break;
	                case Player::MODE_DARKNESS: $GLOBALS["console_output_buffer"] .= "Darkness practically flies to the front line to receive abuse."; break;
	                default: $GLOBALS["console_output_buffer"] .=  "huh? who the hell's this?";
	            }
	        }
   	    }
    }

    if(isset($_POST["farm"])){
        $farm_mode = ($farm_mode - 1) * -1;
    }

    // Update weapon equipment status
    if(isset($_POST["weapon_select"])){
        $inventory = $PlayerCharacter->get_inventory();
        if($_POST["weapon_select"] != -1 && !in_array($_POST["weapon_select"], $inventory)){
            $GLOBALS["console_output_buffer"] .= "Someone's being a bad boy...\n";
        }
        else{
            $equipment = $EquipmentFactory->get_equipment($_POST["weapon_select"]);
            $old_equipment = $PlayerCharacter->get_weapon();
            $PlayerCharacter->set_weapon($equipment);
            equipment_status_output($old_equipment, $equipment);
        }
    }
    // Update armor equipment status
    if(isset($_POST["armor_select"])){
        if($_POST["armor_select"] != -1 && !in_array($_POST["armor_select"], $inventory)){
            $GLOBALS["console_output_buffer"] .= "Someone's being a bad boy...\n";
        }
        else{
            $equipment = $EquipmentFactory->get_equipment($_POST["armor_select"]);
            $old_equipment = $PlayerCharacter->get_armor();
            $PlayerCharacter->set_armor($equipment);
            equipment_status_output($old_equipment, $equipment);
        }
    }
    // Update accessory equipment status
    if(isset($_POST["accessory_select"])){
        if($_POST["accessory_select"] != -1 && !in_array($_POST["accessory_select"], $inventory)){
            $GLOBALS["console_output_buffer"] .= "Someone's being a bad boy...\n";
        }
        else{
            $equipment = $EquipmentFactory->get_equipment($_POST["accessory_select"]);
            $old_equipment = $PlayerCharacter->get_accessory();
            $PlayerCharacter->set_accessory($equipment);
            equipment_status_output($old_equipment, $equipment);
        }
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

    // Player input validation
    if($player_input == 3 && $player_stored_nukes < 1){
        $player_input = -1;
        $GLOBALS["console_output_buffer"] .= "Someone's being a bad boy...\n";
    }

    // Proceed with the game and combat if a valid player input is received
    if($player_input >= 0 && $player_input <= 3){
    	$GameLogic->process_turn_based_effects($PlayerCharacter, $Monster);
        $choices = array("Rock", "Paper", "Scissors", "EXPLOSION");
        // Generate Monster choice. Different monsters may have unique choice patterns.
        $computer_choice = $Monster->get_choice($cpu_stored_nukes>0);
        // Output both sides' choices to the event log buffer
        $GLOBALS["console_output_buffer"] .= "Your choice: $choices[$player_input]\n";
    
        // Get the winner of the fight. 
        // TODO: Rewrite method signature into get_winner(Player, Monster, MetaData), emphasis on the MetaData because meta data is being manipulated inside
        $result = $GameLogic->get_winner($computer_choice, $player_input, $player_stored_nukes, $player_lose_streak, $player_wins, $cpu_stored_nukes, $cpu_lose_streak, $cpu_wins, $Monster, $choices);

        $Monster->get_status()->tick();
        
        if($result === "p"){
            // If player wins, do damage to monster
            $damage = $GameLogic->calculate_damage_on_monster($PlayerCharacter, $Monster);
            $monster_current_hp = $Monster->get_current_hp() - $damage;
            $Monster->set_current_hp($monster_current_hp);
            // If dead, reward and next monster
            if($monster_current_hp <= 0){
                // If monster dies from the attack, reward exp to player, check for level up (to level cap), and replace monster with new one
                // Award exp and level up if possible
                $GLOBALS["console_output_buffer"] .= "\n" . $Monster->get_name() . " died!";
                $exp_awarded = $Monster->get_exp();
                $GLOBALS["console_output_buffer"] .= "\nYou gained " . $exp_awarded . " EXP!";
                $old_level = $PlayerCharacter->get_level();
                $PlayerCharacter->gain_exp($exp_awarded);
                $new_level = $PlayerCharacter->get_level();
                if($new_level > $old_level){
                	$GLOBALS["console_output_buffer"] .= "\nYou leveled up from " . $old_level . " to " . $new_level . "!";
                }
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
            // Not dead, check for status procs
            else{
                $GameLogic->process_status_procs_on_attack($PlayerCharacter, $Monster);
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
                $lost_exp = floor($PlayerCharacter->get_exp() * $GameLogic->get_exp_penalty_rate());
                $PlayerCharacter->set_exp($PlayerCharacter->get_exp() - $lost_exp);

                $GLOBALS["console_output_buffer"] .= "You lost " . $lost_exp . " EXP!\n";
                // Change monster
                $Monster = $MonsterFactory->create_monster_by_player_level($PlayerCharacter->get_level());
            }
        }
    }

    // Special DarknessVanir with MODE_DARKNESS interaction
    if($PlayerCharacter->get_mode() === Player::MODE_DARKNESS && $Monster->get_id() === DarknessVanir::ID){
    	$GLOBALS["console_output_buffer"] .= "Oh no! Darkness got possessed by Vanir!";
    	$PlayerCharacter->set_mode(Player::MODE_KAZUMA);
    }

?>  
<?php    
    // 1. Iterate through inventory and call EquipmentFactory->get_equipment($id) on them all 
    // 2. While iterating and creating, maintain 3 arrays for Weapons, Armors, and Accessories 
    $weapons_array = array();
    $armors_array = array();
    $accessories_array = array();
    $equipment_names_array = array();
    foreach($PlayerCharacter->get_inventory() as $id){
        $item = $EquipmentFactory->get_equipment($id);
        $equipment_names_array[$id] = $item->get_name(). " - " . $item->get_stats_string();
        if($item->get_equipment_type() === Equipment::WEAPON){
            array_push($weapons_array, $id);
        }
        else if($item->get_equipment_type() === Equipment::ARMOR){
            array_push($armors_array, $id);
        }
        else{
            array_push($accessories_array, $id);
        }
    }
?>
<?php
    /*******************************************************
	Store game state into DB
    ********************************************************/ 
    $KonosubaDB->record_game_state($_SESSION["user"], $PlayerCharacter->get_level(), $PlayerCharacter->get_exp(), $PlayerCharacter->get_weapon()->get_id(), $PlayerCharacter->get_armor()->get_id(), $PlayerCharacter->get_accessory()->get_id(), $PlayerCharacter->get_current_hp(), $Monster->get_current_hp(), $Monster->get_id(), $player_lose_streak, $player_stored_nukes, $player_wins, $cpu_lose_streak, $cpu_stored_nukes, $cpu_wins, $farm_mode, $PlayerCharacter->get_mode(), $Monster->get_status()->get_status_type(), $Monster->get_status()->get_remaining_turns());


    /*******************************************************
    JSON Return
    ********************************************************/
    $monster_name_index = array();
    foreach(get_monster_index() as $ID => $CLASSNAME){
        $monster_name_index[$ID] = $CLASSNAME::NAME;
    }
    $GLOBALS["json_response"]["console"] = $GLOBALS["console_output_buffer"];
    $GLOBALS["json_response"]["monster"] = array("name" => $Monster->get_name(), "level" => $Monster->get_level(), "current_hp" => $Monster->get_current_hp(), "hp" => $Monster->get_hp(), "atk" => $Monster->get_atk(), "def" => $Monster->get_def(), "crit" => $Monster->get_crit(), "description" => $Monster->get_description(), "status" => $Monster->get_status()->get_status_type(), "status_remaining_turns" => $Monster->get_status()->get_remaining_turns());
    $GLOBALS["json_response"]["player"] = array("level" => $PlayerCharacter->get_level(), "front_line_id" => $PlayerCharacter->get_mode(), "mode_name" => $PlayerCharacter->get_mode_name(), "current_hp" => $PlayerCharacter->get_current_hp(), "hp" => $PlayerCharacter->get_hp(), "atk" => $PlayerCharacter->get_atk(), "def" => $PlayerCharacter->get_def(), "crit" => $PlayerCharacter->get_crit(), "current_exp" => $PlayerCharacter->get_exp(), "required_exp" => $PlayerCharacter->get_required_exp(), "equipped_weapon" => $PlayerCharacter->get_weapon()->get_id(), "equipped_armor" => $PlayerCharacter->get_armor()->get_id(), "equipped_accessory" => $PlayerCharacter->get_accessory()->get_id(), "mode_description" => $PlayerCharacter->get_avatar_description());
    $GLOBALS["json_response"]["meta_data"] = array("player_lose_streak" => $player_lose_streak, "player_explosions" => $player_stored_nukes, "player_wins" => $player_wins, "monster_lose_streak" => $cpu_lose_streak, "monster_explosions" => $cpu_stored_nukes, "monster_wins" => $cpu_wins);
    $GLOBALS["json_response"]["farm_mode"] = $farm_mode;
    $GLOBALS["json_response"]["weapons"] = $weapons_array;
    $GLOBALS["json_response"]["armors"] = $armors_array;
    $GLOBALS["json_response"]["accessories"] = $accessories_array;
    $GLOBALS["json_response"]["equipment_names"] = $equipment_names_array;
    $GLOBALS["json_response"]["monster_index"] = $monster_name_index;
    echo json_encode($GLOBALS["json_response"]);
?>
