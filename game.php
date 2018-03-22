<?php 
session_start();
if(!isset($_SESSION["user"])){
$_SESSION["login_message"] = "Please login again";
header("Location: index.php");
}

// Process logout
if(isset($_POST["logout"])){
unset($_SESSION);
session_destroy();
$_SESSION["login_message"] = "Logged out";
header("Location: index.php");
}

?>

<!DOCTYPE>  
<html>  
</head>
<body>  
<h1> Konosuba Rock Paper Scissors, AJAX Edition! </h1>

<h3>Logged in as: <?php echo $_SESSION["user"]; ?></h3>

<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>

<script>

    //initial load
    make_choice(-1);
    document.getElementById("explosion").style.visibility = "hidden";

    
    function explosion_sound()
    {
    	var currentMusic;
    	var sounds_list = ["explosion","explosion2","lalala","losion","n","n2","plosion","sion","sion2","thinking","truepower"]
        currentMusic = document.getElementById(sounds_list[Math.floor(Math.random()*sounds_list.length)]);
        currentMusic.play();
    }

    function make_choice_explosion(){
        explosion_sound();
        make_choice(3);
    }

    function make_choice(input) {

        /**
         * http://api.jquery.com/jQuery.ajax/
         */
        $.ajax({
            url: 'konosuba_janken.php',

            type: "POST",

            dataType: "json",

            data: {
                "player_input" : input
            },

            /**
             * A function to be called if the request fails. 
             */
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },

            /**
             * A function to be called if the request succeeds.
             */
            success: function(data) {
                refresh(data);
            }
        });
    }

    function update_monster_image(monster_name, monster_description){
        document.getElementById("monster_image").src="images/"+monster_name+".jpg";
        document.getElementById("monster_image").title=monster_description;
    }
    function update_front_line_image(mode_name, mode_description){
        document.getElementById("front_line_image").src="images/"+mode_name+".jpg";
        document.getElementById("front_line_image").title = mode_description;
    }

    function parse_monster_data(data){
        var status_text = "Normal";
        if(data["status"] != "Normal"){
            status_text = data["status"] + " for " + data["status_remaining_turns"] + " turn(s).";
            if(data["status"] == "Frozen"){
                status_text = status_text.fontcolor("blue");
            }
            if(data["status"] == "Poisoned"){
                status_text = status_text.fontcolor("green");
            }
        }
        return data["name"] + ": Level " + data["level"]+ "\n" + "HP: " + data["current_hp"] + " / " + data["hp"] + ", ATK: " + data["atk"] + ", DEF: " + data["def"] + ", CRIT: " + data["crit"] + ", STATUS: " + status_text ;
    }
    function parse_player_data(data){
        return "You: Level " + data["level"]+ ", Front Line: " + data["mode_name"] + "\n" + "HP: " + data["current_hp"] + " / " + data["hp"] + ", ATK: " + data["atk"] + ", DEF: " + data["def"] + ", CRIT: " + data["crit"] + "\n" + "EXP: " + data["current_exp"] +"/"+data["required_exp"];
    }

    function parse_meta_data(data){
        return "Your lose streak: " + data["player_lose_streak"] + "\n" + "Your Explosions available: " + data["player_explosions"] + "\n" + "Your total wins: " + data["player_wins"] + "\n"+ "Computer's lose streak: " + data["monster_lose_streak"] + "\n" + "Computer's Explosions available: " + data["monster_explosions"] + "\n" + "Computer's total wins: " + data["monster_wins"]
    }

    function change_front_line(id){
         $.ajax({
            url: 'konosuba_janken.php',
            type: "POST",
            dataType: "json",
            data: {
                "avatar_select" : id
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(data) {
                refresh(data);
            }
        });
    }

    function change_monster(id){
         $.ajax({
            url: 'konosuba_janken.php',
            type: "POST",
            dataType: "json",
            data: {
                "monster_select" : id
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(data) {
                refresh(data);
            }
        });
    }

    function reset_game(){
         $.ajax({
            url: 'konosuba_janken.php',
            type: "POST",
            dataType: "json",
            data: {
                "reset" : "reset me pls"
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(data) {
                refresh(data);
            }
        });
    }


    function farm_mode_toggle(){
         $.ajax({
            url: 'konosuba_janken.php',
            type: "POST",
            dataType: "json",
            data: {
                "farm" : "eiiii farm togglyyy"
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(data) {
                refresh(data);
            }
        });
    }



    // Auto Battle
    function autoBattleCheckBox(checkbox){
        if(checkbox.checked){
            localStorage.setItem("autoBattle", "checked");
            doAutoBattle();
        }
        else{
            localStorage.setItem("autoBattle", "unchecked");
            clearInterval(localStorage.getItem("intervalId"));
        }
    }

    function doAutoBattle(){
        var ids = setInterval(function () {
        if(localStorage.getItem("autoBattle") == "checked"){
            var random = Math.floor(Math.random() * 3);
            if(document.getElementById("explosion").style.display != "none"){
                random = Math.floor(Math.random() * 4); // 0 ~ 3
            }
            switch(random){
                case 0: 
                document.getElementById("rock").click();
                break;
                case 1: 
                document.getElementById("paper").click();
                break;
                case 2: 
                document.getElementById("scissors").click();
                break;
                case 3: 
                document.getElementById("explosion").click();
                break;
            }
        }
    }, 1500);
        localStorage.setItem("intervalId", ids);
    }

    //refresh user display
    function refresh(data){
    	if(data["login_status"] == "invalid"){
    		do_redirect();
    	}
    	else if(data["login_status"] == "invalid_another_device"){
    		do_redirect_device();
    	}
    	else{
	        populate_front_line_dropdown(data["player"]["front_line_id"]);
	        populate_monster_select_dropdown(data["monster_index"]);
	        document.getElementById("console").value = data["console"];
	        var monsterText = parse_monster_data(data["monster"]);
	        document.getElementById("monster").innerHTML = monsterText;
	        var playerText = parse_player_data(data["player"]);
	        document.getElementById("player").textContent = playerText;
	        var metaText = parse_meta_data(data["meta_data"]);
	        document.getElementById("meta_data").textContent = metaText;
	        update_monster_image(data["monster"]["name"], data["monster"]["description"]);
	        update_front_line_image(data["player"]["mode_name"], data["player"]["mode_description"]);
	        update_farm_mode(data["farm_mode"]);
	        update_equipments(data);
	        display_explosion(data);
    	}
    }

    function do_redirect(){
    	alert("Session expired, please login again!");
		window.location.replace(<?php require_once("purephp/game_config.php"); echo "\"" . get_main_page() . "\""; ?>);
    }

    function do_redirect_device(){
    	alert("Your account was logged in from another location; Please login again, and scream in despair if it wasn't you because password changing isn't implemented yet.");
		window.location.replace(<?php require_once("purephp/game_config.php"); echo "\"" . get_main_page() . "\""; ?>);
    }

    function display_explosion(data){
        var explosion = document.getElementById("explosion");
        explosion.style.display = "none";
        if(data["meta_data"]["player_explosions"] > 0){
            explosion.style.display = "inline";
        }
    }

    function populate_monster_select_dropdown(monster_index){
        var monster_list = document.getElementById("monster_select");
        monster_list.innerHTML = "";
        // ID => Monster Name
        for(var key in monster_index){
            var element = document.createElement("option");
            element.value = key;
            element.textContent = monster_index[key];
            monster_list.append(element);
        }
    }

    function populate_front_line_dropdown(front_line_id){
        var front_line_list = document.getElementById("front_line_select");
        front_line_list.innerHTML = "";
        var kazuma = document.createElement("option");
        var aqua = document.createElement("option");
        var megumin = document.createElement("option");
        var darkness = document.createElement("option");
        kazuma.textContent = "Kazuma";
        kazuma.value = 0;
        if(front_line_id == kazuma.value){
            kazuma.selected = true;
        }
        aqua.textContent = "Aqua";
        aqua.value = 1;
        if(front_line_id == aqua.value){
            aqua.selected = true;
        }
        megumin.textContent = "Megumin";
        megumin.value = 2;
        if(front_line_id == megumin.value){
            megumin.selected = true;
        }
        darkness.textContent = "Darkness";
        darkness.value = 3;
        if(front_line_id == darkness.value){
            darkness.selected = true;
        }
        front_line_list.append(kazuma);
        front_line_list.append(aqua);
        front_line_list.append(megumin);
        front_line_list.append(darkness);

    }

    function update_equipments(data){
        var weapons_list = document.getElementById("weapon_select");
        var armors_list = document.getElementById("armor_select");
        var accessory_list = document.getElementById("accessory_select");
        weapons_list.innerHTML = "";
        armors_list.innerHTML = "";
        accessory_list.innerHTML = "";
        var weapon_default = document.createElement("option");
        weapon_default.textContent = "-- Choose Weapon --";
        weapon_default.value = -1;
        weapons_list.append(weapon_default);

        var armor_default = document.createElement("option");
        armor_default.textContent = "-- Choose Armor --";
        armor_default.value = -1;
        armors_list.append(armor_default);

        var accessory_default = document.createElement("option");
        accessory_default.textContent = "-- Choose Accessory --";
        accessory_default.value = -1;
        accessory_list.append(accessory_default);

        for(var i = 0; i < data["weapons"].length; i++){
            var element = document.createElement("option");
            var id = data["weapons"][i];
            element.textContent = data["equipment_names"][id];
            element.value = id; 
            if(id == data["player"]["equipped_weapon"]){
                element.selected = true;
            }
            weapons_list.appendChild(element);
        }
        for(var i = 0; i < data["armors"].length; i++){
            var element = document.createElement("option");
            var id = data["armors"][i];
            element.textContent = data["equipment_names"][id];
            element.value = id;
            if(id == data["player"]["equipped_armor"]){
                element.selected = true;
            }
            armors_list.appendChild(element);
        }
        for(var i = 0; i < data["accessories"].length; i++){
            var element = document.createElement("option");
            var id = data["accessories"][i];
            element.textContent = data["equipment_names"][id];
            element.value = id;
            if(id == data["player"]["equipped_accessory"]){
                element.selected = true;
            }
            accessory_list.appendChild(element);
        }
    }

    //1 is on, 0 is off
    function update_farm_mode(farm_mode){
        var farm_text = document.getElementById("farm_mode_display");
        if(farm_mode == 1){
            farm_text.textContent = "Farm Mode is On";
        }
        else{
            farm_text.textContent = "Farm Mode is Off";
        }
    }

    function change_equipments(weapon_id, armor_id, accessory_id){
                 $.ajax({
            url: 'konosuba_janken.php',
            type: "POST",
            dataType: "json",
            data: {
                "weapon_select" : weapon_id,
                "armor_select" : armor_id,
                "accessory_select" : accessory_id
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(data) {
                refresh(data);
            }
        });
    }

</script>

<style>
textarea{
    resize: none;
}

#monster, #player, #meta_data
{
    white-space:pre-wrap;
}
</style>
<form action="game.php" method="post">
<input type="submit" name="logout" value="Logout">
</form>
<form>
    <select id="monster_select" name="monster_select"> 
    <input type="button" value="Change Monster" onclick="change_monster(monster_select.value)">
    </select>
</form>
<button id="farm_mode" onclick="farm_mode_toggle()"><span id = "farm_mode_display">Farm Mode is Off</span></button>
<br>
<img src = "" height = "200" width = "300" title = "default" id = "monster_image">
<div id= "monster"></div>

<img src="images/rock.jpg" height="150" width="150" id = "rock" onclick="make_choice(0)">
<img src="images/paper.jpg" height="150" width="150" id = "paper" onclick="make_choice(1)">
<img src="images/scissors.jpg" height="150" width="150" id = "scissors" onclick="make_choice(2)">
<img src="images/explosion.gif" height="150" width="150" id = "explosion" onclick="make_choice_explosion()">
<label for="auto_battle_checkbox">Auto Battle</label>
<input id="auto_battle_checkbox" type="checkbox" onclick="autoBattleCheckBox(this)">

<form>
<select id="weapon_select" name="weapon_select"> 
</select>
<select id="armor_select" name="armor_select"> 
    <option value = -1>--Choose Armor--</option>
</select>
<select id="accessory_select" name="accessory_select"> 
    <option value = -1>--Choose Accessory--</option>
</select>
    <input type="button" value="Change Equipments" onclick="change_equipments(weapon_select.value, armor_select.value, accessory_select.value)">
</form>
<img src = "" height = "200" width = "300" title = "default" id = "front_line_image">
<form>
<select id="front_line_select" name="front_line_select"> 
</select>
    <input type="button" value="Change Front Line" onclick="change_front_line(front_line_select.value)">
</form>

<div id= "player"></div>
 <textarea rows="5" cols="60" readonly style="background-color: lightcyan" id = "console">
</textarea> 

<div id= "meta_data"></div>
<button id="reset" onclick="reset_game()">Reset Game</button>

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
</body>
</html>