<?php
    require_once("game_logic.php");
    require_once("characters.php");
    require_once("monsters.php");
    $GameLogic = new GameLogic();
    $MonsterFactory = new MonsterFactory();

    ob_start();
?>


<!DOCTYPE>  
<html>  
<!-- CSS help for some terrible styling! -->
<style>
.center {
    margin: auto;
    width: 20%;
    padding: 5px;
}
.center_player {
    margin: auto;
    width: 72%;
    padding: 5px;
}
.center_monster {
    margin: auto;
    width: 38%;
    padding: 5px;
}
.right {
    position: absolute;
    right: 0px;
    width: 300px;
    padding: 5px;
}
</style>
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
    <source src="/sounds/explosion.ogg" type="audio/ogg">
    <source src="/sounds/explosion.mp3" type="audio/mpeg">
</audio>
<audio id="explosion2">
    <source src="/sounds/explosion2.ogg" type="audio/ogg">
    <source src="/sounds/explosion2.mp3" type="audio/mpeg">
</audio>
<audio id="lalala">
    <source src="/sounds/lalala.ogg" type="audio/ogg">
    <source src="/sounds/lalala.mp3" type="audio/mpeg">
</audio>
<audio id="losion">
    <source src="/sounds/losion.ogg" type="audio/ogg">
    <source src="/sounds/losion.mp3" type="audio/mpeg">
</audio>
<audio id="n">
    <source src="/sounds/n.ogg" type="audio/ogg">
    <source src="/sounds/n.mp3" type="audio/mpeg">
</audio>
<audio id="n2">
    <source src="/sounds/n2.ogg" type="audio/ogg">
    <source src="/sounds/n2.mp3" type="audio/mpeg">
</audio>
<audio id="plosion">
    <source src="/sounds/plosion.ogg" type="audio/ogg">
    <source src="/sounds/plosion.mp3" type="audio/mpeg">
</audio>
<audio id="sion">
    <source src="/sounds/sion.ogg" type="audio/ogg">
    <source src="/sounds/sion.mp3" type="audio/mpeg">
</audio>
<audio id="sion2">
    <source src="/sounds/sion2.ogg" type="audio/ogg">
    <source src="/sounds/sion2.mp3" type="audio/mpeg">
</audio>
<audio id="thinking">
    <source src="/sounds/thinking.ogg" type="audio/ogg">
    <source src="/sounds/thinking.mp3" type="audio/mpeg">
</audio>
<audio id="truepower">
    <source src="/sounds/truepower.ogg" type="audio/ogg">
    <source src="/sounds/truepower.mp3" type="audio/mpeg">
</audio>

<form action="konosuba_janken.php" method="post">
    <input type="submit" name="reset" value="reset">
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
    <input type="submit" value="Change Monster">
    </select>
</form>
<?php
    // meta data about game progress
    $cookie_name = "konosuba_janken";

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
    $player_input = -1;
    
    if(isset($_POST["player_input"])){
        $player_input = $_POST['player_input'];
    }
?>


<?php
    $cookie_name_stats = "stats_cookie";

    $player_level_key = "plk";
    $player_current_hp_key = "pchk";
    $monster_current_hp_key = "mchk";
    $monster_id_key = "midk";
    $player_exp_key = "pek";


    $PlayerCharacter = new Player();
    $Monster = $MonsterFactory->create_monster_by_id(GiantFrog::ID);
    if(isset($_COOKIE[$cookie_name_stats])){
        $cook_stats = unserialize($_COOKIE[$cookie_name_stats]);
        $PlayerCharacter->set_level($cook_stats[$player_level_key]);
        $PlayerCharacter->set_exp($cook_stats[$player_exp_key]);
        $PlayerCharacter->set_current_hp($cook_stats[$player_current_hp_key]);
        $Monster = $MonsterFactory->create_monster_by_id($cook_stats[$monster_id_key]);
        $Monster->set_current_hp($cook_stats[$monster_current_hp_key]);
        // DO PLAYER LEVEL UP AND MONSTER CHANGE AT LAST STEP BEFORE COOKIE SAVE
    }

    if(isset($_POST["monster_select"])){
        echo "Changed Monster!<br>";
        $Monster = $MonsterFactory->create_monster_by_id($_POST["monster_select"]);
    }

    if(isset($_POST["reset"])){
        echo "Resetted game! <br>";    
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
    $choices = array("Rock", "Paper", "Scissors", "EXPLOSION");
    $computer_choice = rand(0, 2);
    if($cpu_stored_nukes > 0){
        $computer_choice = rand(0, 3);
    }
    $computer_choice_display = $choices[$computer_choice];

    if($player_input === -1){
        $player_input_display = "Please make a choice!";
    }
    else{
    	$player_input_display = $choices[$player_input];
    }
    echo "Your choice: $player_input_display<br>";
    echo "Computer's choice: $computer_choice_display";
    echo "<br>";

    $result = $GameLogic->get_winner($computer_choice, $player_input, $choices, $player_stored_nukes, $player_lose_streak, $player_wins, $cpu_stored_nukes, $cpu_lose_streak, $cpu_wins);

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
            // Change monster
            $Monster = $MonsterFactory->create_monster_by_player_level($PlayerCharacter->get_level());
        }

    }

    if($result === "c"){
        // If computer(monster) wins, do damage to player
        $damage = $GameLogic->calculate_damage_on_player($Monster, $PlayerCharacter);
        //$player_current_hp = $player_current_hp - $damage;
        $PlayerCharacter->set_current_hp($PlayerCharacter->get_current_hp() - $damage);
        // If player dies, reset player HP and do exp penalty, and reset monster to a new one
        if($PlayerCharacter->get_current_hp()<= 0){
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
    $thecookie = array();
    $thecookie[$player_lose_streak_key] = $player_lose_streak;
    $thecookie[$player_stored_nukes_key] = $player_stored_nukes;
    $thecookie[$player_wins_key] = $player_wins;
    $thecookie[$cpu_lose_streak_key] = $cpu_lose_streak;
    $thecookie[$cpu_stored_nukes_key] = $cpu_stored_nukes;
    $thecookie[$cpu_wins_key] = $cpu_wins;

    setcookie($cookie_name, serialize($thecookie), time()+86400, "/");
?>

<?php

    $thecookie_stats = array();
    $thecookie_stats[$player_level_key] = $PlayerCharacter->get_level();
    $thecookie_stats[$player_current_hp_key] = $PlayerCharacter->get_current_hp();
    $thecookie_stats[$monster_current_hp_key] = $Monster->get_current_hp();
    $thecookie_stats[$monster_id_key] = $Monster->get_id();
    $thecookie_stats[$player_exp_key] = $PlayerCharacter->get_exp();

    setcookie($cookie_name_stats, serialize($thecookie_stats), time()+86400, "/");

?>


<div class = "center_monster">
    <img src = <?php echo "\"images/" . $Monster->get_name() ."\"";?> height = "300" width = "400">
    <br>
    <?php echo $Monster->get_name() . ": Level " . $Monster->get_level(); ?>
    <br>
    HP: <?php echo $Monster->get_current_hp(); ?> / <?php echo $Monster->get_hp() ?>,  ATK: <?php echo $Monster->get_atk(); ?>, DEF: <?php echo $Monster->get_def(); ?>, CRIT: <?php echo $Monster->get_crit(); ?>
</div>


<div class = "center_player">
<table>
    <form action="konosuba_janken.php" method="POST">
        <input type="hidden" name="player_input" value="0">
        <input type="image" src="images/rock.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST">
        <input type="hidden" name="player_input" value="1">
        <input type="image" src="images/paper.jpg" height="200" width="200">
    </form>

    <form action="konosuba_janken.php" method="POST">
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
         ob_end_flush();
    ?>

</table>
    You: Level <?php echo $PlayerCharacter->get_level(); ?>
    <br>
    HP: <?php echo $PlayerCharacter->get_current_hp(); ?> / <?php echo $PlayerCharacter->get_hp() ?>,  ATK: <?php echo $PlayerCharacter->get_atk(); ?>, DEF: <?php echo $PlayerCharacter->get_def(); ?>, CRIT: <?php echo $PlayerCharacter->get_crit(); ?>
    <br>
    EXP: <?php echo $PlayerCharacter->get_exp(); ?> / <?php echo $PlayerCharacter->get_required_exp() ?>
</div>


<br> Your lose streak: <?php echo $player_lose_streak ?>
<br> Your Explosions available: <?php echo $player_stored_nukes ?>
<br> Your total wins: <?php echo $player_wins ?>
<br> Computer's lose streak: <?php echo $cpu_lose_streak ?>
<br> Computer's Explosions available: <?php echo $cpu_stored_nukes ?>
<br> Computer's total wins: <?php echo $cpu_wins ?>
</body>  
</html>  