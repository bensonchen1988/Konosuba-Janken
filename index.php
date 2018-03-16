<?php

session_start();
?>

<!DOCTYPE>  
<html>  
</head>
<body>  
<form action="login_action.php" method = "post">
Username: 1~15 alphanumerics<br>
<input type="text" name="username" maxlength = "15" required = "required" pattern = "^[a-zA-Z0-9_]*$" autocomplete = "off">
<br>
Password: 1~30 alphanumerics<br>
<input type="password" name="password" maxlength = "30" required = "required" pattern = "^[a-zA-Z0-9_]*$">
<br><br>
<input type="submit" value = "Login/Signup">
</form>
Proof of concept hosted on Heroku free tier.
<br>
Might be laggy, will most likely migrate to AWS EC2 in the future.
<br>
Still getting updated regularily, you might get logged out when updates occur!
<br>
Game data might be wiped without warning! (Game is still very short anyways)
<br>
Latest Update: Implemented status effects! (March 16, 2018)
<br>
<br>
<?php
if(isset($_SESSION["login_message"])){
    echo $_SESSION["login_message"];
}
unset($_SESSION["login_message"]);
?>
</body>
</html>