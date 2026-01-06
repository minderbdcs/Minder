<?php
header("Content-type: image/gif");
if (!isset($_GET['text']))
{
	$string = "Test String";
}
else
{
	$string = $_GET['text'];
}
$line1 = "";
$line2 = "";
$line3 = "";
//$string .= " 1 1 ";
//list($line1, $line2, $line3 ) = explode(' ', $string); 
//if ($line3 == "1")
//	$line3 = "";
//if ($line2 == "1")
//	$line2 = "";

if (!isset($_GET['fromimage']))
{
	$imgfile = "";
	$im = imagecreate(200,20);
}
else
{
	$imgfile = $_GET['fromimage'];
	//$im = imagecreatefromjpeg($imgfile);
	$im = imagecreatefromgif($imgfile);
	//$source = imagecreatefromgif($imgfile);
	//list($cwidth, $cheight) = getimagesize($imgfile);
}
$blue = imagecolorallocate($im, 0, 220, 255);
//$red = imagecolorallocate($im, 255, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 10, 10, 10);
//if ($imgfile <> "")
//{
	//imagecopyresized($im, $source, 68,13,0,0, $cwidth, $cheight, $cwidth, $cheight);
//}
//imagestring($im, 5, 5, 31,  $line3, $black );
//imagestring($im, 5, 5, 18,  $line2, $black );
imagestring($im, 5, 5, 5,  $string, $black );
//imagesetthickness($im, 4); 
//imageline($im, 89, 0, 89, 50, $black); 
//imageline($im, 0, 50, 88, 50, $black); 
imagegif($im);
imagedestroy($im);
?>

