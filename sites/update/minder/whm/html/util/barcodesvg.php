<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php
include "viewport.php";
?>
<title>Barcodes via SVG</title>
<style type="text/css">
body {
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: 0.8em;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';

// ===============================================================================================
function makeDir($path)
{
     return is_dir($path) || mkdir($path);
}
// ===============================================================================================
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
if (isset($_POST['cdata']))
{
	$cdata = $_POST['cdata'];
}
if (isset($_GET['cdata']))
{
	$cdata = $_GET['cdata'];
}
if (isset($_POST['btype']))
{
	$btype = $_POST['btype'];
}
if (isset($_GET['btype']))
{
	$btype = $_GET['btype'];
}
if (isset($_POST['otype']))
{
	$otype = $_POST['otype'];
}
if (isset($_GET['otype']))
{
	$otype = $_GET['otype'];
}
echo("<H4>SVG Barcode Creation</H4>\n");
echo("<FONT size=\"2\">\n");
{
	echo("<FORM action=\"barcodesvg.php\" method=\"post\" name=getlabel\n>");
}

echo("Enter Barcode Text<br>\n");
echo("Client :<input type=\"text\" name=\"cdata\" value=\"");
if (isset($cdata))
{
	echo $cdata ;
}
echo ("\"><br>\n");
echo("Barcode Type:<SELECT name=\"btype\" size=\"4\" >\n");
	echo( "<OPTION value=\"code39\" >39\n");
	echo( "<OPTION value=\"ean13\" >EAN13\n");
	echo( "<OPTION value=\"ean8\" >EAN8\n");
	echo( "<OPTION value=\"ean\" >EAN\n");
	echo( "<OPTION value=\"gs1\" >GS1\n");
	echo( "<OPTION value=\"gtin\" >GTIN\n");
	echo("</SELECT><br>\n");
echo("Output Type:<SELECT name=\"otype\" size=\"3\" >\n");
	echo( "<OPTION value=\"svg\" >svg\n");
	echo( "<OPTION value=\"eps\" >eps\n");
	echo( "<OPTION value=\"pdf\" >pdf\n");
	echo("</SELECT><br>\n");
echo("Text :<input type=\"text\" name=\"bdata\" value=\"");
if (isset($bdata))
{
	echo $bdata ;
}
echo ("\"><br>\n");

if (isset($bdata) and isset($btype) and isset($otype))
{
/*
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/barcode.php?btype=' . $btype . '&bdata=' . $bdata . '" alt="Img">');
*/
	if ($btype == "")
	{
		$btype = "ean13";
	}
	if ($bdata == "")
	{
		$bdata = "1234567890123";
	}
	if ($otype == "")
	{
		$otype = "svg";
	}
	if (!isset($cdata))
	{
		$cdata = "";
	}
	if ($cdata == "")
	{
		$cdata = "tmp";
	}
/* set folder and file names for the client used */
	// strip / & space ' " from client code
	//$cdata = preg_replace('/\s+/', '', $cdata);
	$cdata = preg_replace("/[^a-zA-Z0-9]/", "", $cdata);
	$cdata = strtolower($cdata);
	$cyear = date("Y");
	$cmonth = date("m");
	$fdata = preg_replace("/[^a-zA-Z0-9]/", "", $bdata);
	$fdata = strtolower($fdata);
	$wkSvg = "/data/" . $cdata . "/" . $cyear . "/" . $cmonth . "/" . $fdata ;
	// now create the folders
	$wkSvgDir =  "/data";
	makeDir($wkSvgDir);
	$wkSvgDir .=  "/". $cdata ;
	makeDir($wkSvgDir);
	$wkSvgDir .=  "/". $cyear ;
	makeDir($wkSvgDir);
	$wkSvgDir .=  "/". $cmonth ;
	makeDir($wkSvgDir);
	
/* run pybarcode to generate the svg file */
	//python pybarcode.py create -b ean13  1234567890123 f.test
	$wk_bcmd = "python /usr/local/bin/pybarcode.py create -b " . $btype . " " . $bdata . " " . $wkSvg;

/* convert the svg to the required format */
	$wk_ocmd = "" ;
	if ($otype == "eps")
	{
		$wk_ocmd = "/usr/local/bin/svg2eps.sh " . $wkSvg . ".svg " . $wkSvg . ".eps";
	} elseif ($otype == "pdf")
	{
		$wk_ocmd = "/usr/local/bin/svg2pdf.sh " . $wkSvg . ".svg " . $wkSvg . ".pdf";
	}
	//echo $wk_bcmd;
        $wkCMD = $wk_bcmd   . " 2>&1";
        //$content = shell_exec($wkCMD);
        //$content = htmlspecialchars($content);
	$content = "";
	$contento = "";
	$wkResults = array();
	$retval = 0;
	$retval2 = 0;
	exec($wkCMD,$wkResults, $retval2);
	//echo ("Retval2:" . $retval2);
        //$content = $wkResults;
        $content = "";
        foreach($wkResults as $wkResultsIdx=>$wkResultsValue) {
		$content .= "\n" . $wkResultsValue;
	}
	//var_dump($wkResults);
	if ($retval2)
	{
		// error occurred
	}
        //$contentarea = "  <tr><td colspan=5>
        $contentarea = "  
          <textarea name=\"content\" cols=\"60\" rows=\"7\">". print_r($content,True)."</textarea><br>
          <center>Command contents</center><br>
          ";
          //</td></tr>";
	if ($wk_ocmd <> "")
	{
		//echo $wk_ocmd;
        	$wkCMD = $wk_bcmd   . " 2>&1";
		exec($wkCMD,$wkResults, $retval2);
        	foreach($wkResults as $wkResultsIdx=>$wkResultsValue) {
			$contento .= "\n" . $wkResultsValue;
		}
		if ($retval2)
		{
			// error occurred
		}
	}
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
		//echo ("</TR>");
		//echo ("<TR>");
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

