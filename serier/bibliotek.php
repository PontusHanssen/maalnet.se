
<?php
include "db-conn.php";
$db_table="film";

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);

$bokstav = "F";
$litenbokstav = strtolower($bokstav);

$titlar=array();
$bilder=array();
$id = array();

$query="select * from $db_table";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
{
	$titlar[] = $row['titel'];
	$bilder[] = $row['bild'];
	$id[] = $row['id'];
}

for($i=0;$i<count($titlar);$i++)
{
	if($titlar[$i][0] == $bokstav || $titlar[$i][0] == $litenbokstav)
	{
		echo '<a href="film.php?id='.$id[$i].'">Titel: '.$titlar[$i].'<br><img src="'.$bilder[$i].'"></a><br>';
	}
}

?>
