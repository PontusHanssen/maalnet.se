<?php
session_start(); // Alltid överst på sidan
 
include "conn.php"; // Databasanslutningen
include "functions.php"; // Funktioner
 
if (isset($_POST['submit'])){
 
  $_POST = db_escape($_POST);
 
  // Tag bort eventuella blanksteg i början eller slutet
  foreach($_POST as $key => $val){
    $_POST[$key] = trim($val);
  }
 
  //Kolla efter tomma fält
  if (empty($_POST['user']) || empty($_POST['passwd']) ||
      empty($_POST['name']) || empty($_POST['email'])) {
    $reg_error[] = 0;
  }
 
  // Kolla om användarnamnet är upptaget
  $sql = "SELECT COUNT(*) FROM members WHERE user='{$_POST['user']}'";
  $result = mysql_query($sql);
  if (mysql_result($result, 0) > 0) {
    $reg_error[] = 1;
  }
 
  // Kolla om e-post kan tänkas vara ok
  if (!preg_match('/^[-A-Za-z0-9_.]+[@][A-Za-z0-9_-]+([.][A-Za-z0-9_-]+)*[.][A-Za-z]{2,6}$/', $_POST['email'])) {
    $reg_error[] = 2;
  }
 
  // Kolla så att lösenorden stämmer överrens
  if ($_POST['passwd'] != $_POST['passwd2']) {
    $reg_error[] = 3;
  }
 
  // Inga fel? Spara och logga in samt skicka till välkomstsida
  if (!isset($reg_error)) {
 
      // Salta lösenordet
    $passwd = safepass($_POST['passwd']);
    $sql = "INSERT INTO members(user, pass, name, email)
            VALUES('{$_POST['user']}', '$passwd', '{$_POST['name']}', '{$_POST['email']}')";
    mysql_query($sql);
 
    $_SESSION['sess_id'] = mysql_insert_id();
    $_SESSION['sess_user'] = $_POST['user'];
    header("Location: welcome.php");
    exit;
 
  }
 
} else {
 
  // Sätt variabler för tomt formulär
  for ($i=0; $i<4; $i++) {
    $back[$i] = "";
  }
 
}
 
$error_list[0] = "Alla fält är inte infyllda";
$error_list[1] = "Användarnamnet är upptaget";
$error_list[2] = "Felaktig e-postadress";
$error_list[3] = "Lösenorden stämmer inte överrens";
 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type"
  content="text/html; charset=iso-8859-1">
<title>Registrera dig</title>
</head>
<body>
<h3>Registrera dig</h3>
<?php
if (isset($reg_error)){
 
  echo "Något blev fel:<br>\n";
  echo "<ul>\n";
  for ($i=0; $i<sizeof($reg_error); $i++) {
    echo "<li>{$error_list[$reg_error[$i]]}</li>\n";
  }
  echo "</ul>\n";
 
  $back[0] = stripslashes($_POST['user']);
  $back[2] = stripslashes($_POST['name']);
  $back[3] = stripslashes($_POST['email']);
 
}
?>
<form action="register.php" method="post">
<table cellspacing="3">
 
<tr>
<td>Användarnamn:</td>
<td><input type="text" name="user" value="<?php echo $back[0]; ?>"></td>
</tr>
 
<tr>
<td>Lösenord:</td>
<td><input type="password" name="passwd" value=""></td>
</tr>
 
<tr>
<td>Repetera lösenord:</td>
<td><input type="password" name="passwd2" value=""></td>
</tr>
 
<tr>
<td>Ditt namn:</td>
<td><input type="text" name="name" value="<?php echo $back[2]; ?>"></td>
</tr>
 
<tr>
<td>E-postadress</td>
<td><input type="text" name="email" value="<?php echo $back[3]; ?>"></td>
</tr>
 
<tr>
<td colspan="2" align="center">
  <input type="submit" name="submit" value="Spara dina uppgifter">
</td>
</tr>
 
</table>
</form>
 
</body>
</html>
