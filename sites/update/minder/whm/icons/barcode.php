<?php
//require_once 'Image/Barcode.php';
require_once 'Image/Barcode2.php';
$bdata = "123";
$btype = "Code39";
if (isset($_GET['bdata']))
{
	$bdata = $_GET['bdata'];
}
if (isset($_GET['btype']))
{
	$btype = $_GET['btype'];
}
$itype = "gif";
	// create label file here
	$bimg = new Image_Barcode2;
	$bimg->draw($bdata,$btype,$itype);
?>
