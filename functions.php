<?php
if(isset($_POST['passwd']))
{
// Sätt variabeln $password till $_POST['passwd']
$password = mysql_real_escape_string($_POST['passwd']);
}
 
function safepass($password)
{
    $username = strtolower($_POST['user']);
    // Sätt variabeln $username till $_POST['user']
    $username = mysql_real_escape_string($username);
 
 
    // Statiskt salt som innehåller
    // krångliga tecken
    $salt = "bajspenis!! sadas as#";
 
    // Kryptera användarnamn+salt till
    // ett dynamiskt lösenord som är
    // olika för varje användare även
    // om flera har samma lösenord
    $dynSalt = sha1($username.$password.$salt);
 
    // Kryptera den dynamiska salten
    // en extra gång med sha1
    return sha1($dynSalt);
 
 
}
?>
