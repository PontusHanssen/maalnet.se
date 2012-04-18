<?php

if($_GET['id'])
	$id=$_GET['id'];
else
	header("loation: google.se");

include "db-conn.php";

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
$db_table = "film";

$query="select * from $db_table where `id`=$id";
$result = mysql_query($query);
$film = mysql_fetch_array($result);

echo '<h1>'.$film['titel'].'</h1><img src="'.$film['bild'].'"><h2>Rating: '.$film['rating'].'</h2><br>'.$film['beskrivning'].'<br><a href="'.$film['url'].'">Se filmen</a>';
?>
