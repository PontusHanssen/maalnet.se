<?php

function uploadfile($dir, $file)
{
	$dest = $dir . "/" . $file['name'];
	move_uploaded_file($file['tmp_name'], $dest);
}

function movefile($dir, $src, $dest)
{
	$cmd = 'mv "' . $dir . '/' . $src . '" "' . $dir . '/' . $dest . '"';
	shell_exec($cmd);
}

function copyfile($dir, $src, $dest)
{
	$cmd = 'cp -r "'.$dir.'/'.$src.'" "'.$dir.'/'.$dest.'"';
	shell_exec($cmd);
}

function newfolder($dir, $newfolder)
{
	$path = '"' . getcwd() . "/" . $dir . "/" . $newfolder . '"';
	shell_exec("mkdir $path");
}

function deletefile($file)
{
	$cmd = 'rm -rf "'. getcwd()	. '/' . $file . '"';
	shell_exec($cmd);
}

function file_type($fileName)
{
$parts=explode(".",$fileName);
return $parts[count($parts)-1];
}

function convert($format, $dir, $file)
{
	$src = '"'.$dir.'"';
	$dest = '"' . dirname($dir) . '/' . basename($dir, file_type($dir)) . $format . '"';
	$cmd = 'abiword -t ' . $format . ' ' . $src . ' -o ' . $dest;
	shell_exec($cmd);
}


?>