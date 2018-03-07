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
		$PS = $this->connection->prepare("select * from wamp_practice");
	}

	public function get_login($username)
	{
		$PS = $this->connection->prepare("select * from user_login where username = :username");
		$PS->execute( array(":username" => $username));
		return $PS->fetch();
	}

}

?>