<?php
require_once("purephp/database.php");

session_start();

$dbutil = new KonosubaDB();

if(!isset($_POST["username"]) || !isset($_POST["password"])){
	$_SESSION["login_message"] = "Gimme something to work with pls";
	header("Location: index.php");
}

$result_set = $dbutil->get_login($_POST["username"]);
if($result_set === false){
	$_SESSION["login_message"] = "username not found";
	header("Location: index.php");
}
else{
	if(strcmp($result_set["password_encrypted"], $_POST["password"]) == 0){
		$_SESSION["user"] = $_POST["username"];
		header("Location: konosuba_janken.php");
	}
	else{
		$_SESSION["login_message"] = "|" . $result_set["password_encrypted"] . "| : |" . $_POST["password"] . "|" . strcmp($result_set["password_encrypted"], $_POST["password"]);
	header("Location: index.php");
	}
}

?>