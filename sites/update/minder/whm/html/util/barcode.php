<html>
<head>
<?php
include "viewport.php";
?>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
//require_once 'Image/Barcode.php';
require_once 'Image/Barcode2.php';

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['bdata']))
{
	$bdata = $_POST['bdata'];
}
if (isset($_GET['bdata']))
{
	$bdata = $_GET['bdata'];
}
if (isset($_POST['btype']))
{
	$btype = $_POST['btype'];
}
if (isset($_GET['btype']))
{
	$btype = $_GET['btype'];
}
echo("<FONT size=\"2\">\n");
{
	echo("<FORM action=\"barcode.php\" method=\"post\" name=getlabel\n>");
}

echo("Enter Barcode Text<br>\n");
echo("Type:<SELECT name=\"btype\" size=\"6\" >\n");
	echo( "<OPTION value=\"Code39\" >39\n");
	echo( "<OPTION value=\"ean13\" >EAN13\n");
	echo( "<OPTION value=\"ean8\" >EAN8\n");
	echo( "<OPTION value=\"int25\" >INT25\n");
	echo( "<OPTION value=\"code128\" >128\n");
	echo( "<OPTION value=\"postnet\" >PostNet\n");
	echo( "<OPTION value=\"upca\" >UPC-A\n");
	echo( "<OPTION value=\"upcc\" >UPC-C\n");
	echo("</SELECT><br>\n");
echo("Text :<input type=\"text\" name=\"bdata\" value=\"" . $bdata . "\">\n");
if (isset($bdata) and isset($btype))
{
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/barcode.php?btype=' . $btype . '&bdata=' . $bdata . '" alt="Img">');
}
// Create a table.
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<TR>");
echo ("<TD>");
echo("<INPUT type=\"IMAGE\" ");  
{
	{
		echo('SRC="/icons/whm/accept.gif" alt="Print">');
		echo ("</TD>");
		echo ("</TR>");
		echo ("<TR>");
	}
}
echo("</FORM>\n");

{
	// html 4.0 browser
	$backto = "util_Menu.php";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
}

?>
</body>
</html>

