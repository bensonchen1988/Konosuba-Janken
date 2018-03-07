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
	$dbutil->sign_up($_POST["username"], $_POST["password"]);
	$_SESSION["user"] = $_POST["username"];
	header("Location: konosuba_janken.php");
}
else{
	if(strcmp($result_set["password_encrypted"], $_POST["password"]) == 0){
		$_SESSION["user"] = $_POST["username"];
		header("Location: konosuba_janken.php");
	}
	else{
		$_SESSION["login_message"] = "Wrong password";
		header("Location: index.php");
	}
}

?>