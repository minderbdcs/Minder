<?php
header("Content-type: image/jpeg");
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
list($line1, $line2, $line3 ) = explode(' ', $string); 
if (!isset($_GET['image']))
{
	$imgfile = "";
}
else
{
	$imgfile = $_GET['image'];
	$source = imagecreatefromjpeg($imgfile);
	//$source = imagecreatefromgif($imgfile);
	list($cwidth, $cheight) = getimagesize($imgfile);
}
//$im = imagecreatefrompng("http:/127.0.0.1/icons/back.png");
$im = imagecreate(90,50);
$blue = imagecolorallocate($im, 0, 220, 255);
$red = imagecolorallocate($im, 255, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 10, 10, 10);
if ($imgfile <> "")
	imagecopyresized($im, $source, 68,13,0,0, $cwidth, $cheight, $cwidth, $cheight);
imagestring($im, 5, 5, 31,  $line3, $red );
imagestring($im, 5, 5, 18,  $line2, $red );
imagestring($im, 5, 5, 5,  $line1, $red );
imagesetthickness($im, 4); 
imageline($im, 89, 0, 89, 50, $black); 
imageline($im, 0, 50, 88, 50, $black); 
imagejpeg($im);
//imagepng($im);
imagedestroy($im);
?>

