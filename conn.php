<?php
 
// Byt ut mot dina inloggningsuppgifter och databas
$mysql_server = "localhost";
$mysql_user = "root";
$mysql_password = ":-)";
$mysql_database = "projekt";
 
$conn = mysql_connect($mysql_server, $mysql_user, $mysql_password);
mysql_select_db($mysql_database, $conn);
 
 
// En funktion att användas när magic_quotes_gpc inte är satt. För att förhindra SQL-injections, eller i lidrigare fall MySQl-fel.
function db_escape ($post)
{
   if (is_string($post)) {
     if (get_magic_quotes_gpc()) {
        $post = stripslashes($post);
     }
     return mysql_real_escape_string($post);
   }
   
   foreach ($post as $key => $val) {
      $post[$key] = db_escape($val);
   }
   
   return $post;
}
 
 
/* 
   Se till att det inte finns några dolda tecken, typ radbyte 
   eller mellanslag, efter den avslutande PHP-taggen !!!
*/ 
?>
