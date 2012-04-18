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
 ?>

<div id="right">
</div>

<div id="left">
<div id="folderlist">
<h2>TV</h2>
<h3><?php
echo "Shows";
?></h3>
<table><tr>
<?php
$col=0;
$show_array = Array();
$query="select DISTINCT (showname) from serier order by `showname`";
$result = mysql_query($query) or die(mysql_error());
while($row = mysql_fetch_array($result))
{
	$image="images/poster-" .$row['showname'] . ".jpg";
	if($col == 3)
	{
		echo '</tr><tr><td style="padding-left: 10px;">
			<ul id="item">
			<li class="trigger">
			<span><a href="season.php?showname=' . $row['showname'] . '"><img style="width:185px; height: 278px;" src="' . $image . '"><center>'.$row['showname'].'</center></a></span>
			</li>
			</ul>
			</td>';
			$col=1;
	}
	else
	{
			echo '<td style="padding-left: 10px;">
			<ul id="item">
			<li class="trigger">
			<span><a href="season.php?showname=' . $row['showname'] . '"><img style="width:185px; height: 278px;" src="' . $image . '"><center>'.$row['showname'].'</center></a></span>
			</li>
			</ul>
			</td>';
			$col++;
	}
}
?>
</tr></table>
<br style="clear: both;">
<br style="clear: both;">
</div>
</div>
