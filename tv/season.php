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
if(!isset($_GET['showname']))
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
$showname = $_GET['showname'];

?>

<div id="right">
</div>

<div id="left">
<div id="folderlist">
<?php
echo "<a href=\"index.php\"><h2>$showname</h2></a>";
echo '<img style="margin-bottom: 30px;" src="images/banner-' . $showname . '.jpg"><ul>';
$query="select DISTINCT `season` from serier where `showname`='$showname'";
$result = mysql_query($query) or die(mysql_error());
while($row = mysql_fetch_array($result))
{
	echo '<li><a href="episode.php?season=' . $row['season'] . '&showname=' . $showname . '">Season ' . $row['season'] . '</a></li>'; 
}
?>
</ul>
<br style="clear: both;">
<br style="clear: both;">
</div>
</div>
