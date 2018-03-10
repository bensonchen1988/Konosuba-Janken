<?php

/**
* Singleton
**/
final class Connection
{
	public static function get_connection()
	{
		static $connection = null;
		if($connection === null){
			$config = require_once("database_config.php");
			$connection = new PDO("mysql:dbname=".$config["dbname"].";host=".$config["dbhost"], $config["username"], $config["password"]);
		}
		return $connection;
	}

	private function __construct()
	{
		// Can't touch this duuuuuuun dun dun dun doo doo
	}

	private function __clone()
	{
		// My awesomeness knows no equals
	}
}


final class KonosubaDB
{	
	private $connection;

	public function __construct()
	{
		$this->connection = Connection::get_connection();
		$this->safe_create_required_tables();
	}

	private function safe_create_required_tables()
	{
		// safe create tables for new enviroment setup
		// Game State
		$game_state = "CREATE TABLE IF NOT EXISTS `game_state` (
 `username` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
 `player_level` int(11) NOT NULL DEFAULT '1',
 `player_exp` int(11) NOT NULL DEFAULT '0',
 `player_weapon` int(11) NOT NULL,
 `player_armor` int(11) NOT NULL,
 `player_accessory` int(11) NOT NULL,
 `player_current_hp` int(11) NOT NULL,
 `monster_current_hp` int(11) NOT NULL,
 `monster_id` int(11) NOT NULL,
 `player_lose_streak` int(11) NOT NULL DEFAULT '0',
 `player_stored_nukes` int(11) NOT NULL DEFAULT '0',
 `player_wins` int(11) NOT NULL DEFAULT '0',
 `monster_lose_streak` int(11) NOT NULL DEFAULT '0',
 `monster_stored_nukes` int(11) NOT NULL DEFAULT '0',
 `monster_wins` int(11) NOT NULL DEFAULT '0',
 `farm_mode` int(11) NOT NULL DEFAULT '0',
 `player_avatar` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$PS_game_state = $this->connection->prepare($game_state);
		$PS_game_state->execute();

		// User Login
		$user_login = "CREATE TABLE IF NOT EXISTS`user_login` (
 `username` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
 `password_encrypted` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `last_login_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$PS_user_login = $this->connection->prepare($user_login);
		$PS_user_login->execute();

		// Player Inventory
		$player_inventory = "CREATE TABLE IF NOT EXISTS `player_inventory` (
 `username` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
 `equipment_id` int(11) NOT NULL,
 `count` int(11) NOT NULL DEFAULT '1',
 PRIMARY KEY (`username`,`equipment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $PS_player_inventory = $this->connection->prepare($player_inventory);
        $PS_player_inventory->execute();


	}

	public function record_game_state($username, $player_level, $player_exp, $player_weapon, $player_armor, $player_accessory, $player_current_hp, $monster_current_hp, $monster_id, $player_lose_streak, $player_stored_nukes, $player_wins, $cpu_lose_streak, $cpu_stored_nukes, $cpu_wins, $farm_mode, $player_avatar)
	{
		$this->validate_username($username);
		// Input mapping
		$input = array(":username" => $username, ":player_level" => $player_level, ":player_exp" => $player_exp, ":player_weapon" => $player_weapon, ":player_armor" => $player_armor, ":player_accessory" => $player_accessory, ":player_current_hp" => $player_current_hp, ":monster_current_hp" => $monster_current_hp, ":monster_id" => $monster_id, ":player_lose_streak" => $player_lose_streak, ":player_stored_nukes" => $player_stored_nukes, ":player_wins" => $player_wins, ":monster_lose_streak" => $cpu_lose_streak, ":monster_stored_nukes" => $cpu_stored_nukes, ":monster_wins" => $cpu_wins, ":farm_mode" => $farm_mode, ":player_avatar" => $player_avatar);
		// Check if username record exist
		$result_set = $this->get_game_state($username);
		if($result_set === false){
			// Previous record doesn't exist, create new record
			$PS2 = $this->connection->prepare("insert into game_state (username, player_level, player_exp, player_weapon, player_armor, player_accessory, player_current_hp, monster_current_hp, monster_id, player_lose_streak, player_stored_nukes, player_wins, monster_lose_streak, monster_stored_nukes, monster_wins, farm_mode, player_avatar) values (:username, :player_level, :player_exp, :player_weapon, :player_armor, :player_accessory, :player_current_hp, :monster_current_hp, :monster_id, :player_lose_streak, :player_stored_nukes, :player_wins, :monster_lose_streak, :monster_stored_nukes, :monster_wins, :farm_mode, :player_avatar)");
			$PS2->execute($input);
		}
		else{
			// Exists, do update
			$PS3 = $this->connection->prepare("update game_state set player_level = :player_level, player_exp = :player_exp, player_weapon = :player_weapon, player_armor = :player_armor, player_accessory = :player_accessory, player_current_hp = :player_current_hp, monster_current_hp = :monster_current_hp, monster_id = :monster_id, player_lose_streak = :player_lose_streak, player_stored_nukes = :player_stored_nukes, player_wins = :player_wins, monster_lose_streak = :monster_lose_streak, monster_stored_nukes = :monster_stored_nukes, monster_wins = :monster_wins, farm_mode = :farm_mode, player_avatar = :player_avatar where username = :username");
			$PS3->execute($input);
		}
		// But mommy I want a Playstation 4!
	}

	public function get_game_state($username)
	{
		$this->validate_username($username);
		$PS = $this->connection->prepare("select * from game_state where username = :username");
		$PS->execute(array(":username" => $username));
		return $PS->fetch();
	}

	public function get_login($username)
	{
		$this->validate_username($username);
		$PS = $this->connection->prepare("select * from user_login where username = :username");
		$PS->execute( array(":username" => $username));
		return $PS->fetch();
	}

	public function sign_up($username, $password)
	{
		$this->validate_username($username);
		$this->validate_username($password);
		$PS = $this->connection->prepare("insert into user_login (username, password_encrypted) values (:username, :password)");
		$PS->execute( array(":username" => $username, ":password" => $password));
	}

	public function get_inventory($username)
	{
		$this->validate_username($username);
		$PS = $this->connection->prepare("select * from player_inventory where username = :username");
		$PS->execute(array(":username" => $username));
		return $PS;
	}

	public function add_inventory($username, $equipment_id){
		$this->validate_username($username);
		// Check existence
		$PS = $this->connection->prepare("select  * from player_inventory where username = :username and equipment_id = :equipment_id");
		$PS->execute(array(":username" => $username, ":equipment_id" => $equipment_id));
		$result_set = $PS->fetch();

		if($result_set === false){
			// Doesn't exist, add new record
			$PS1 = $this->connection->prepare("insert into player_inventory (username, equipment_id, count) values (:username, :equipment_id, 1)");
			$PS1->execute(array(":username" => $username, ":equipment_id" => $equipment_id));
		}
		else{
			// Exists, add 1 to count
			$count = $result_set["count"] + 1;
			$PS2 = $this->connection->prepare("update player_inventory set count = :count where username = :username and equipment_id = :equipment_id");
			$PS2->execute(array(":username" => $username, ":equipment_id" => $equipment_id, ":count" => $count));
		}
	}

	public function reset_player_inventory($username){
		$PS = $this->connection->prepare("delete from player_inventory where username = :username");
		$PS->execute(array(":username" => $username));
	}

	/**
	* Throws the hacker back to login page, no hints.
	**/
	private function validate_username($username){
		// Check null
		if($username === null){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check if it's a string
		if(!is_string($username)){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check length <= 15
		if(strlen($username) > 15){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check alphanumeric
		if(!ctype_alnum($username)){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
	}

	/**
	* Throws the hacker back to login page, no hints.
	**/
	private function validate_password($password){
		// Check null
		if($password === null){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check if it's a string
		if(!is_string($password)){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check length <= 15
		if(strlen($password) > 30){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
		// Check alphanumeric
		if(!ctype_alnum($password)){
    		header("Location: index.php");
			//throw new Exception("Invalid username");
		}
	}

}

?>