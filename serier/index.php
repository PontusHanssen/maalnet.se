<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style-new.css" rel="stylesheet" type="text/css">
<title>Målnet.se</title>
<script type="text/javascript">
//<![CDATA[
//Javascriptfunktionen för att visa/dölja inmatningsboxarna till höger
function toggle(o)
{
var e = document.getElementById(o);
e.style.display = (e.style.display == 'none') ? 'block' : 'none';
}
//Funktionen för att visa/dölja dokumentkonverteringsfönstret
function convert(d)
{
var f = document.getElementById('file2convert');
f.value = d;
toggle('convertdoc');
}
//]]>
</script>

</head>

<?php
include "db-conn.php";
$db_table="film";

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);


//Startar sessioen och kontrollerar att du är inloggad
session_start();
if(!isset($_SESSION['sess_user']))
{
	header("Location: http://målnet.se/");
	exit;
}

function file_type($fileName)
{
$parts=explode(".",$fileName);
return $parts[count($parts)-1];
}
if(isset($_GET['page']))
{
	$bokstav = $_GET['page'];
	$litenbokstav = strtolower($bokstav);
}
else
{
	$bokstav = "A";
	$litenbokstav = "a";
}
$titlar=array();
$bilder=array();
$id = array();
$url = array();
$bostaver=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","Å","Ä","Ö");

$query="select * from $db_table order by titel asc";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
{
        $titlar[] = $row['titel'];
        $bilder[] = $row['bild'];
        $id[] = $row['id'];
	$url[] = $row['url'];
}

?>

<body>

<div id="page">

<div id="header" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>'">
	<h1>Målnet.se</h1>
</div>

<?php 
include '../meny.htm';
 ?>
<div id="right">
<div id="actions">
<h3>Places</h3>
	<ul>
		<li><a href="index.php?page=ALL"><img src="images/library.png">All Movies</a></li>
                <li><a href="filer.php"><img src="images/files.png">Filemanager</a></li>

<?php /*		<li><a href="#" onclick="toggle('copy')"><img src="images/copy.png">Copy/Move</a>
		<div id="copy" style="display: none;">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                <input type="hidden" name="dir" value="<?php echo $folder;?>">
		<select name="from">
		<?php foreach($folder_array as $item)
		{
			echo '<option value="'.$item.'">'.$item.'/</option>';
		}
		foreach($file_array as $item)
		{
			echo '<option value="'.$item.'">'.$item.'</option>';
		}
		?></select>
		<select name="to">
		<option value="../">../</option>
		<?php foreach($folder_array as $item)
		{
			echo '<option value="'.$item.'">'.$item.'/</option>';
		}?>
		</select><br>
		<input type="submit" name="copyfile" value="Copy"><input type="submit" name="movefile" value="Move"></form></div></li>
		<li><a href="#" onclick="toggle('folder')"><img src="images/new-folder.png">New Folder</a>
		<div id="folder" style="display: none;">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                <input type="hidden" name="dir" value="<?php echo $folder;?>">
		<input type="text" name="foldername"><input type="submit" name="newfolder" value="Create"></form></div></li>
*/?>

	</ul>
</div>
</div>

<div id="left">
<div id="folderlist">
<h3><?php
foreach($bostaver as $b)
{
	if($bokstav == $b)
		echo '<a href="?page='.$b.'" >['.$b.']</a> ';
	else
		echo '<a href="?page='.$b.'">'.$b.'</a> ';
}
?>
</h2>
<h3>
Library
</h3>
<?php
$rad=0;
echo '<table border="0" style="margin: 0; padding: 0; position: relative; top: -100px;"><tr>';
for($i=0;$i<count($titlar);$i++)
{
        if($titlar[$i][0] == $bokstav || $titlar[$i][0] == $litenbokstav || $litenbokstav == "all")
        {
		if($rad<2)
		{
//                	echo '<td style="padding-left: 10px;"><a href="film.php?id='.$id[$i].'"><img src="'.$bilder[$i].'"></a><br><b><center>'.$titlar[$i].'</center></b></td>';
			$rad++;
	                echo '
		<td style="padding-left: 10px;">
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$url[$i].'">Play</a></li>
                <li><a href="film.php?id='.$id[$i].'">View description</a></li>
                </ul>
                <span><a href="film.php?id='.$id[$i].'"><img src="'.$bilder[$i].'"><br><center>'.$titlar[$i].'</center></a></span>
                </li>
                </ul>
		</td>
                ';

		}
		else
		{
			$rad=0;
			echo '</tr><tr> 		<td style="padding-left: 10px;">
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$url[$i].'">Play</a></li>
                <li><a href="film.php?id='.$id[$i].'">View description</a></li>
                </ul>
                <span><a href="film.php?id='.$id[$i].'"><img src="'.$bilder[$i].'"><br><center>'.$titlar[$i].'</center></a></span>
                </li>
                </ul>
		</td>
';
		$rad=1;
			echo '<br>';
		}
        }
}

?>
<br style="clear: both;">
<br style="clear: both;">

</div>
</div>
