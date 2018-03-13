<?php
require_once("purephp/database.php");

session_start();

$dbutil = new KonosubaDB();

if(!isset($_POST["username"]) || !isset($_POST["password"])){
	$_SESSION["login_message"] = "Gimme something to work with pls";
	header("Location: index.php");
}

$password_encrypted = password_hash($_POST["password"], PASSWORD_DEFAULT);

$result_set = $dbutil->get_login($_POST["username"]);
if($result_set === false){
	$dbutil->sign_up($_POST["username"], $password_encrypted);
	$_SESSION["user"] = $_POST["username"];
	$dbutil->set_session($_SESSION["user"], session_id());
	header("Location: game.php");
}
else{
	if(password_verify($_POST["password"], $result_set["password_encrypted"])){
		$_SESSION["user"] = $_POST["username"];
		$dbutil->set_session($_SESSION["user"], session_id());
		header("Location: game.php");
	}
	else{
		$_SESSION["login_message"] = "Wrong password";
		header("Location: index.php");
	}
}

?>