<?php
session_start(); // Alltid överst på sidan
 
include "conn.php"; // Databasanslutningen
include "functions.php"; // Funktioner
 
// Inloggning
if (isset($_POST['submit'])){
 
  $_POST = db_escape($_POST);
 
  $passwd = safepass($_POST['passwd']);
  $sql = "SELECT id FROM members
         WHERE user='{$_POST['user']}'
         AND pass='$passwd'";
  $result = mysql_query($sql);
 
  // Hittades inte användarnamn och lösenord
  // skicka till formulär med felmeddelande
  if (mysql_num_rows($result) == 0){
    header("Location: index.php?badlogin=");
    exit;
  }
 
  // Sätt sessionen med unikt index
  $_SESSION['sess_id'] = mysql_result($result, 0, 'id');
  $_SESSION['sess_user'] = $_POST['user'];
  header("Location: video/index.php");
  exit;
}
 
// Utloggning
if (isset($_GET['logout'])){
  session_unset();
  session_destroy();
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Index</title>
</head>
<body>
<?php
 
// Om inte inloggad visa formulär, annars logga ut-länk
if (!isset($_SESSION['sess_user'])){
 
 
  // Visa felmeddelande vid felaktig inloggning
  if (isset($_GET['badlogin'])){
    echo "Fel användarnamn eller lösenord!<br>\n";
    echo "Försök igen!\n";
  }
 
?>
<fieldset style="margin: auto; width: 0%">
<legend>Inloggning krävs</legend>
<form action="index.php" method="post">
<label for="user">Användarnamn:</label><br>
<input type="text" name="user"><br>
<lablel for="password">Lösenord:</label><br>
<input type="password" name="passwd"><br>
<input type="submit" name="submit" value="Logga in">
</form>
<br>Inte medlem ?<br>
<a href="register.php">Registera dig</a>
</fieldset>
<?php
 
} else {
 
  echo "<a href=\"index.php?logout=\">Logga ut</a>\n";
	header("Location:video/index.php");

 
}
 
?>
</body>
</html>
