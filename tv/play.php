<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style-new.css" rel="stylesheet" type="text/css">
<title>Målnet.se</title>
<?php

session_start();
if(!isset($_SESSION['sess_user']))
{
	header("Location: http://målnet.se/");
	exit;
}
if(!isset($_GET['id']))
{
	header("Location: index.php");
	exit;
}
?>	
<body>

<div id="page">

<div id="header" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>'">
	<h1>Målnet.se</h1>
</div>

<?php 
include '../meny.htm';
include 'db-conn.php';
mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
$id = $_GET['id'];
?>

<div id="right">
</div>

<div id="left">
<div id="folderlist">
<?php
$show_array = mysql_fetch_array(mysql_query("select * from serier where id=$id"));
$show = $show_array['showname'];
$season = $show_array['season'];
$episode = $show_array['episode'];
$title = $show_array['title'];
$image = $show_array['image'];
$path = $show_array['path'];
$banner = $show_array['banner'];
?>
<a href="season.php?showname=<?php echo $show;?>"><h2><?php echo $show;?></a>
<img src="images/rightarrow.gif">
<a href="episode.php?showname=<?php echo $show;?>&season=<?php echo $season;?>">Season <?php echo $season;?></h2></a>
<img  src="<?php echo $banner;?>">
<?php
echo "<h2 style=\"text-align: center;\" >$title</h2>";
echo '<video src="' . $show_array['path'] . '" pöster="' . $show_array['image'] . '" controls="controls"  preload="auto" ondblclick="' . "document.getElementsByTagName('video')[0].webkitEnterFullscreen();" .'">';
echo '</video>';
?>

<br style="clear: both;">
<br style="clear: both;">
</div>
</div>
