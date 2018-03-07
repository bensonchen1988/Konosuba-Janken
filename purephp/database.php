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
	}

	public function record_meta_data($stats_array)
	{
		
	}

	public function record_game_state($username, $player_level, $player_exp, $player_weapon, $player_armor, $player_accessory, $player_current_hp, $monster_current_hp, $monster_id)
	{
		// Input mapping
		$input = array(":username" => $username, ":player_level" => $player_level, ":player_exp" => $player_exp, ":player_weapon" => $player_weapon, ":player_armor" => $player_armor, ":player_accessory" => $player_accessory, ":player_current_hp" => $player_current_hp, ":monster_current_hp" => $monster_current_hp, ":monster_id" => $monster_id);
		// Check if username record exist
		$result_set = $this->get_game_state($username);
		if($result_set === false){
			// Previous record doesn't exist, create new record
			$PS2 = $this->connection->prepare("insert into game_state (username, player_level, player_exp, player_weapon, player_armor, player_accessory, player_current_hp, monster_current_hp, monster_id) values (:username, :player_level, :player_exp, :player_weapon, :player_armor, :player_accessory, :player_current_hp, :monster_current_hp, :monster_id)");
			$PS2->execute($input);
		}
		else{
			// Exists, do update
			$PS3 = $this->connection->prepare("update game_state set player_level = :player_level, player_exp = :player_exp, player_weapon = :player_weapon, player_armor = :player_armor, player_accessory = :player_accessory, player_current_hp = :player_current_hp, monster_current_hp = :monster_current_hp, monster_id = :monster_id where username = :username");
			$PS3->execute($input);
		}
		// But mommy I want a Playstation 4!
	}

	public function get_game_state($username)
	{
		$PS = $this->connection->prepare("select * from game_state where username = :username");
		$PS->execute(array(":username" => $username));
		return $PS->fetch();
	}

	public function get_login($username)
	{
		$PS = $this->connection->prepare("select * from user_login where username = :username");
		$PS->execute( array(":username" => $username));
		return $PS->fetch();
	}

	public function sign_up($username, $password)
	{
		$PS = $this->connection->prepare("insert into user_login (username, password_encrypted) values (:username, :password)");
		$PS->execute( array(":username" => $username, ":password" => $password));
	}

}

?>