<html>
<head>
<?php
include "viewport.php";
?>
<title>Export a CSV of Locations</title>
</head>
 <body BGCOLOR="#AAFFCC">
<script type="text/javascript">
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processQty() {
  if ( document.showstock.alfrom.value=="")
  {
  	document.showstock.message.value="Must Enter Aisle From";
	document.showstock.alfrom.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.alfrom.value)==false)
  {
  	document.showstock.message.value="Aisle From Not Numeric";
	document.showstock.alfrom.focus();
  	return false;
  }
  if ( document.showstock.alto.value=="")
  {
  	document.showstock.message.value="Must Enter Aisle To";
	document.showstock.alto.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.alto.value)==false)
  {
  	document.showstock.message.value="Aisle To Not Numeric";
	document.showstock.alto.focus();
  	return false;
  }
  if ( document.showstock.byfrom.value=="")
  {
  	document.showstock.message.vbyue="Must Enter Bay From";
	document.showstock.byfrom.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.byfrom.value)==false)
  {
  	document.showstock.message.value="Bay From Not Numeric";
	document.showstock.byfrom.focus();
  	return false;
  }
  if ( document.showstock.byto.value=="")
  {
  	document.showstock.message.value="Must Enter Bay To";
	document.showstock.byto.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.byto.value)==false)
  {
  	document.showstock.message.value="Bay To Not Numeric";
	document.showstock.byto.focus();
  	return false;
  }
  if ( document.showstock.shfrom.value=="")
  {
  	document.showstock.message.vshue="Must Enter Shelf From";
	document.showstock.shfrom.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.shfrom.value)==false)
  {
  	document.showstock.message.value="Shelf From Not Numeric";
	document.showstock.shfrom.focus();
  	return false;
  }
  if ( document.showstock.shto.value=="")
  {
  	document.showstock.message.value="Must Enter Shelf To";
	document.showstock.shto.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.shto.value)==false)
  {
  	document.showstock.message.value="Shelf To Not Numeric";
	document.showstock.shto.focus();
  	return false;
  }
  if ( document.showstock.cmfrom.value=="")
  {
  	document.showstock.message.vshue="Must Enter Compartment From";
	document.showstock.cmfrom.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.cmfrom.value)==false)
  {
  	document.showstock.message.value="Compartment From Not Numeric";
	document.showstock.cmfrom.focus();
  	return false;
  }
  if ( document.showstock.cmto.value=="")
  {
  	document.showstock.message.value="Must Enter Compartment To";
	document.showstock.cmto.focus();
  	return false;
  }
  if ( chkNumeric(document.showstock.cmto.value)==false)
  {
  	document.showstock.message.value="Compartment To Not Numeric";
	document.showstock.cmto.focus();
  	return false;
  }
  document.showstock.message.value="Working";
  return true;
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$alfrom="00";
$byfrom="00";
$shfrom="00";
$cmfrom="00";
$alto="99";
$byto="99";
$shto="99";
$cmto="99";
$filename="export.csv";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['alfrom'])) 
{
	$alfrom = $_POST["alfrom"];
}
if (isset($_POST['alto'])) 
{
	$alto = $_POST["alto"];
}
if (isset($_POST['byfrom'])) 
{
	$byfrom = $_POST["byfrom"];
}
if (isset($_POST['byto'])) 
{
	$byto = $_POST["byto"];
}
if (isset($_POST['shfrom'])) 
{
	$shfrom = $_POST["shfrom"];
}
if (isset($_POST['shto'])) 
{
	$shto = $_POST["shto"];
}
if (isset($_POST['cmfrom'])) 
{
	$cmfrom = $_POST["cmfrom"];
}
if (isset($_POST['cmto'])) 
{
	$cmto = $_POST["cmto"];
}
if (isset($_POST['filename'])) 
{
	$filename = $_POST["filename"];
}

$outfilename = "/tmp/" . $filename;
if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if ($image_x > 0 and $image_y > 0 and $wk_message == "Working")
{
	//echo('got image_x');
	//echo($image_x);
	//echo('got image_y');
	//echo($image_y);
	if (!($myfile = fopen($outfilename,"w")))
	{
		echo("cannot open file");
	}
	else
	{
		$line2 = sprintf("\n");
		for ($a1 = $alfrom; $a1 <= $alto ; $a1++)
		{
			$a2 = sprintf("%02s", $a1);
			for ($b1 = $byfrom; $b1 <= $byto ; $b1++)
			{
				$b2 = sprintf("%02s", $b1);
				for ($c1 = $shfrom; $c1 <= $shto ; $c1++)
				{
					$c2 = sprintf("%02s", $c1);
					for ($d1 = $cmfrom; $d1 <= $cmto ; $d1++)
					{
						$d2 = sprintf("%02s", $d1);
						$line = sprintf('"%s","%s","%s","%s"', $a2, $b2, $c2, $d2) . $line2;
						if (fwrite($myfile, $line) === FALSE)
						{
							echo("cannot write to file ($outfilename)");
							exit;
						}
					}
				}
			}
		}
		fclose($myfile);
		$message = "File Created";
	}
}
//else
{
	echo("<H4>Enter Values to Create</H4>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo (" <FORM action=\"ExportLocation.php\" method=\"post\" name=showstock onsubmit=\"return processQty()\" >");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly >\n");
	echo("<tr>");
	echo("<th>");
	echo("</th>");
	echo("<th>");
	echo("From");
	echo("</th>");
	echo("<th>");
	echo("To");
	echo("</th>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Aisle");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"alfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$alfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"alto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$alto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Bay");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"byfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$byfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"byto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$byto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Shelf");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"shfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$shfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"shto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$shto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Compartment");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"cmfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$cmfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"cmto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$cmto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Output File");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"filename\"  ");
	echo(" size=\"30\" value=\"$filename\" >\n");
	echo("</td>");
	echo("</tr>");
	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.showstock.alfrom.focus();</script>");
}
?>
</body>
</html>

