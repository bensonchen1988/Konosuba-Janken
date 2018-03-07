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

	public function record_game_state($username, $player_level, $player_exp, $player_weapon, $player_armor, $player_accessory, $player_current_hp, $monster_current_hp, $monster_id, $player_lose_streak, $player_stored_nukes, $player_wins, $cpu_lose_streak, $cpu_stored_nukes, $cpu_wins, $farm_mode)
	{
		// Input mapping
		$input = array(":username" => $username, ":player_level" => $player_level, ":player_exp" => $player_exp, ":player_weapon" => $player_weapon, ":player_armor" => $player_armor, ":player_accessory" => $player_accessory, ":player_current_hp" => $player_current_hp, ":monster_current_hp" => $monster_current_hp, ":monster_id" => $monster_id, ":player_lose_streak" => $player_lose_streak, ":player_stored_nukes" => $player_stored_nukes, ":player_wins" => $player_wins, ":monster_lose_streak" => $cpu_lose_streak, ":monster_stored_nukes" => $cpu_stored_nukes, ":monster_wins" => $cpu_wins, ":farm_mode" => $farm_mode);
		// Check if username record exist
		$result_set = $this->get_game_state($username);
		if($result_set === false){
			// Previous record doesn't exist, create new record
			$PS2 = $this->connection->prepare("insert into game_state (username, player_level, player_exp, player_weapon, player_armor, player_accessory, player_current_hp, monster_current_hp, monster_id, player_lose_streak, player_stored_nukes, player_wins, monster_lose_streak, monster_stored_nukes, monster_wins, farm_mode) values (:username, :player_level, :player_exp, :player_weapon, :player_armor, :player_accessory, :player_current_hp, :monster_current_hp, :monster_id, :player_lose_streak, :player_stored_nukes, :player_wins, :monster_lose_streak, :monster_stored_nukes, :monster_wins, :farm_mode)");
			$PS2->execute($input);
		}
		else{
			// Exists, do update
			$PS3 = $this->connection->prepare("update game_state set player_level = :player_level, player_exp = :player_exp, player_weapon = :player_weapon, player_armor = :player_armor, player_accessory = :player_accessory, player_current_hp = :player_current_hp, monster_current_hp = :monster_current_hp, monster_id = :monster_id, player_lose_streak = :player_lose_streak, player_stored_nukes = :player_stored_nukes, player_wins = :player_wins, monster_lose_streak = :monster_lose_streak, monster_stored_nukes = :monster_stored_nukes, monster_wins = :monster_wins, farm_mode = :farm_mode where username = :username");
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

	public function get_inventory($username)
	{
		$PS = $this->connection->prepare("select * from player_inventory where username = :username");
		$PS->execute(array(":username" => $username));
		return $PS;
	}

	public function add_inventory($username, $equipment_id){
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

}

?>