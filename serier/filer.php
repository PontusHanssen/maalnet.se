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
//Låter sidan vänta i 0.3 sekunder så att eventuella ändringar i filsystemet får tid att hända
sleep(0.3);
//Startar sessioen och kontrollerar att du är inloggad
session_start();
if(!isset($_SESSION['sess_user']))
{
	header("Location: http://målnet.se/");
	exit;
}

//Funktion för att på fram filändelsen för att bestämma filtyp för rätt ikon och meny
function file_type($fileName)
{
$parts=explode(".",$fileName);
return $parts[count($parts)-1];
}
//Ser till att ladda vald mapp, om ingen mapp är vald blir det huvudmappen uploads/
if(isset($_GET['folder']))
{
        $folder =  $_GET['folder'];
}
else
{
        $folder = "movies/";
}
//Tar bort onödiga / som uppstår när man bläddrar mellan mappar
while(substr($folder, -1) == "/")
{
        $folder = rtrim($folder, "/");
}
//Tar fram vad mappen över aktiv mapp heter
$dotdot=explode("/", $folder);
$dotdot=str_replace($dotdot[count(explode("/", $folder))-1], "", $folder);
//Arrayer för filer och mappar
$file_array=array();
$folder_array=array();
//Skapar handle för filströmmen till vald mapp
$handle=opendir($folder);
//Loop som körs för allt innehåll i filströmmen
while (false !== ($file = readdir($handle)))
{
//Skippar . och .. 
        if($file != "." && $file != "..")
        {
//Slår ihop aktiva mappen och det hitade filnamnet
                $check_dir=$folder . "/" . $file;
//Kollar om det är en mapp eller fil och lägger till i respektive array
                if(is_dir($check_dir))
                {
                        $folder_array[] = $file;
                }
                else
                {
                        $file_array[] = $file;
                }
        }
}
//Stänger strömmen till mappen
closedir($handle);

//Sorterar arrayerna i bokstavsordning
natcasesort($file_array);
reset($file_array);
natcasesort($folder_array);
reset($folder_array);
//Här börjar utskriften av sidan
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
<h3>Actions</h3>
	<ul>
		<li><a href="#" onclick="toggle('upload')"><img src="images/new.png">Upload File</a>
		<div id="upload" style="display: none;">

		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="dir" value="<?php echo $folder;?>">
		<input type="file" name="file"><input type="submit" name="uploadfile" value="Upload"></form></div></li>

		<li><a href="#" onclick="toggle('copy')"><img src="images/copy.png">Copy/Move</a>
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


	</ul>
</div>
</div>

<div id="left">
<div id="folderlist">
<h2>Moviebrowser</h2>
<h3><?php
$foldertree=explode("/",$folder);
for($i=0;$i<count($foldertree);$i++)
{
	echo '<a href="'.$_SERVER['PHP_SELF'].'?folder=';
	for($k=0;$k<$i;$k++)
	{

		echo $foldertree[$k].'/';
	}
	echo $foldertree[$i].'">'.$foldertree[$i].'/</a>';
}?></h3>
<div id="convertdoc">
<a href="#" onclick="toggle('convertdoc')" style="float: right; top: 5px;">Close</a>
<h3>Convert to..</h3><br>
<form id="convertform" name="convertform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" id="file2convert" name="file2convert" value="">
<input type="submit" name="convert2doc" value=".doc">
<input type="submit" name="convert2abw" value=".abw">
<input type="submit" name="covert2html" value=".html">
<input type="submit" name="convert2txt" value=".txt">
<input type="submit" name="convert2pdf" value=".pdf">
</form></div>
<?php
foreach($folder_array as $item)
{
	$itempath=$folder . "/" . $item;
	$relfolder=substr($folder, 8, -1);
	echo '
	<ul id="item">
	<li class="trigger">
	<ul class="menu">
	<li><a href="'.$_SERVER['PHP_SELF'].'?folder='.$itempath.'">Open</a></li>
	<li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
	</ul>
	<span><img src="images/folder.png">'.$item.'</span>
	</li>
	</ul>
	';
}

foreach($file_array as $item)
{
	$itempath = $folder . "/" . $item;

	switch(file_type($folder . "/" . $item))
	{
		case "avi":
		case "mkv":
		echo '
		<ul id="item">
		<li class="trigger">
		<ul class="menu">
		<li><a href="#">Add to library</a></li>
		</ul>
		<span><img src="images/movie.png">'.$item.'</span>
		</li>
		</ul>
		';
		break;
		case "doc":
		case "docx":
		case "odt":
		case "abw":
		case "pages":
		case "html":
		echo '
		<ul id="item">
		<li class="trigger">
		<ul class="menu">
		<li><a href="'.$itempath.'">Open</a></li>
		<li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
		</ul>
		<span><img src="images/doc.png">'.$item.'</span>
		</li>
		</ul>
		';
		break;
		case "jpg":
		case "jpeg":
		case "png":
		case "gif":
		case "bmp":
		echo '
		<ul id="item">
		<li class="trigger">
		<ul class="menu">
		<li><a href="'.$itempath.'"><img src="'.$itempath.'"></a></li>
		<li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
		</ul>
		<span><img src="images/pic.png">'.$item.'</span>
		</li>
		</ul>
		';
		break;
		case "cpp":
		case "c":
		case "py":
		echo '
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$itempath.'">Open</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
                </ul>
                <span><img src="images/code.png">'.$item.'</span>
                </li>
                </ul>
                ';
		break;
		case "pdf":
                echo '
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$itempath.'">Open</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?publish='.$itempath.'">Publish</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
                </ul>
                <span><img src="images/pdf.png">'.$item.'</span>
                </li>
                </ul>
                ';
		break;
		case "txt":
                echo '
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$itempath.'">Open</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?publish='.$itempath.'">Publish</a></li>
                <li><a href="#" onclick="convert(\''.$itempath.'\')">Convert</a>
                <li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
                </ul>
                <span><img src="images/txt.png">'.$item.'</span>
                </li>
                </ul>
                ';
                break;
		default:
                echo '
                <ul id="item">
                <li class="trigger">
                <ul class="menu">
                <li><a href="'.$itempath.'">Open</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?publish='.$itempath.'">Publish</a></li>
                <li><a href="'.$_SERVER['PHP_SELF'].'?delete='.$itempath.'">Delete</a></li>
                </ul>
                <span><img src="images/unknown.png">'.$item.'</span>
                </li>
                </ul>
                ';
                break;

	}
}
?>
<br style="clear: both;">
<br style="clear: both;">

</div>
</div>
