<?php
//header("Content-type: image/gif");
header("Content-type: image/png");
putenv('GDFONTPATH=' . realpath('.') );
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
$string .= " 1 1 1 ";
list($line1, $line2, $line3 ) = explode(' ', $string); 
if ($line3 == "1")
	$line3 = "";
if ($line2 == "1")
	$line2 = "";

if (!isset($_GET['fromimage']))
{
	$imgfile = "";
	$im = imagecreate(90,50);
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
$red = imagecolorallocate($im, 255, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
//$black = imagecolorallocate($im, 10, 10, 10);
$black = imagecolorallocate($im, 0, 0, 0);
//$grey = imagecolorallocate($im, 128, 128, 128);
//if ($imgfile <> "")
//{
	//imagecopyresized($im, $source, 68,13,0,0, $cwidth, $cheight, $cwidth, $cheight);
//}
//imagestring($im, 5, 5, 31,  $line3, $black );
//imagestring($im, 5, 5, 18,  $line2, $black );
//imagestring($im, 5, 5, 5,  $line1, $black );
$wk_spare1 = ( strlen($line1) < 9) ? 9 - strlen($line1) : 0;
$wk_spare2 = ( strlen($line2) < 9) ? 9 - strlen($line2) : 0;
$wk_spare3 = ( strlen($line3) < 9) ? 9 - strlen($line3) : 0;
// max word len is 9  chars
// then sparex is 9 - len(linex) 
// so add to x (spare /2 ) * average char length
$wk_x1 = 5 + ($wk_spare1 * 4);
$wk_x2 = 5 + ($wk_spare2 * 4);
$wk_x3 = 5 + ($wk_spare3 * 4);
//imagettftext($im, 12, 0, 5, 45, $black, 'LiberationMono-Bold.ttf', $line3);
//imagettftext($im, 12, 0, 5, 30, $black, 'LiberationMono-Bold.ttf', $line2 );
//imagettftext($im, 12, 0, 5, 17, $black, 'LiberationMono-Bold.ttf', $line1 );
//imagettftext($im, 12, 0, $wk_x3, 45, $black, 'LiberationMono-Bold.ttf', $line3);
//imagettftext($im, 12, 0, $wk_x2, 30, $black, 'LiberationMono-Bold.ttf', $line2 );
//imagettftext($im, 12, 0, $wk_x1, 17, $black, 'LiberationMono-Bold.ttf', $line1 );
imagettftext($im, 12, 0, $wk_x3, 45, $black, './LiberationMono-Bold.ttf', $line3);
imagettftext($im, 12, 0, $wk_x2, 30, $black, './LiberationMono-Bold.ttf', $line2 );
imagettftext($im, 12, 0, $wk_x1, 17, $black, './LiberationMono-Bold.ttf', $line1 );
//imagesetthickness($im, 4); 
//imageline($im, 89, 0, 89, 50, $black); 
//imageline($im, 0, 50, 88, 50, $black); 
//imagegif($im);
imagepng($im);
imagedestroy($im);
?>

