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

if($_GET['id'])
        $id=$_GET['id'];
else
        header("loation: http://målnet.se/");

$query="select * from $db_table where `id`=$id";
$result = mysql_query($query);
$film = mysql_fetch_array($result);

?>

<body>

<div id="page">

<div id="header" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>'">
	<h1>Målnet.se</h1>
</div>

<?php
include '../meny.htm'
?>

<div id="right">
<div id="actions">
<h3>Places</h3>
	<ul>
		<li><a href="index.php"><img src="images/library.png">All Movies</a></li>
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

<?php
echo '<h2>'.$film['titel'].'</h1><img src="'.$film['bild'].'"><h3>Rating: '.$film['rating'].'</h3><p>'.$film['beskrivning'].'</p>';
echo '<video src="' . $film['url'] . '" controls="yes" preload="auto" ondblclick="' . "document.getElementsByTagName('video')[0].webkitEnterFullscreen();" .'"></video>';
               
?>
<br style="clear: both;">
<br style="clear: both;">

</div>
</div>
