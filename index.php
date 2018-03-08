<?php

session_start()

?>

<!DOCTYPE>  
<html>  
</head>
<body>  
<form action="login_action.php" method = "post">
Username<br>
<input type="text" name="username" maxlength = "15" required = "required" pattern = "^[a-zA-Z0-9_]*$">
<br>
Password<br>
<input type="password" name="password" maxlength = "30" required = "required" pattern = "^[a-zA-Z0-9_]*$">
<br><br>
<input type="submit" value = "Login/Signup">
</form>
<br>
<?php
if(isset($_SESSION["login_message"])){
    echo $_SESSION["login_message"];
}
unset($_SESSION["login_message"]);
?>
</body>
</html>