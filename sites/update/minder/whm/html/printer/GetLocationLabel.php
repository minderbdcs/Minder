<?php
include "login.inc";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Reprint Location Labels</title>
<link rel=stylesheet type="text/css" href="GetLocation.css">
<script type="text/javascript">
function processEdit() {
  if ( document.printlocation.locn.value=="")
  {
  	document.printlocation.message.value="Must Enter the Location";
	document.printlocation.locn.focus()
  	return false
  }
  if ( document.printlocation.labelqty.value=="")
  {
  	document.printlocation.message.value="Enter the Qty of Labels";
	document.printlocation.labelqty.focus()
  	return false
  }
  return true;
}
function changeLocn() {
  document.printlocation.message.value="Enter Qty of Labels";
  document.printlocation.labelqty.value="";
  document.printlocation.labelqty.focus();
}
</script>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "transaction.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wk_locn_id = "";
$wk_label_type = "";
$wk_label_qty = "";
$wk_printer = "";
$wk_image_x = "";
$wk_image_y = "";
if (isset($_POST['locn'])) 
{
	$wk_locn_id = $_POST["locn"];
}
if (isset($_GET['locn'])) 
{
	$wk_locn_id = $_GET["locn"];
}
if (isset($_POST['labelqty'])) 
{
	$wk_label_qty = $_POST["labelqty"];
}
if (isset($_GET['labelqty'])) 
{
	$wk_label_qty = $_GET["labelqty"];
}
if (isset($_POST['printer'])) 
{
	$wk_printer = $_POST["printer"];
}
if (isset($_GET['printer'])) 
{
	$wk_printer = $_GET["printer"];
}
if (isset($_POST['x'])) 
{
	$wk_image_x = $_POST["x"];
}
if (isset($_GET['x'])) 
{
	$wk_image_x = $_GET["x"];
}
if (isset($_POST['y'])) 
{
	$wk_image_y = $_POST["y"];
}
if (isset($_GET['y'])) 
{
	$wk_image_y = $_GET["y"];
}
$wk_message = "";

$wk_locn_exists = "";
//$wk_desc = "";
if (isset($wk_locn_id))
{
	// check that the locn exists and the labeltype list
	$Query4 = "SELECT locn_name FROM location where wh_id = '" . substr($wk_locn_id,0,2) . "' and locn_id = '";
	$Query4 .= substr($wk_locn_id,2) . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Location!<BR>\n");
		exit();
	}
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_locn_exists = "Y";
		$wk_desc = $Row5[0];
	}
	//release memory
	ibase_free_result($Result4);
}
if (isset($wk_locn_id))
{
	if ($wk_locn_exists == "")
	{
		$wk_locn_id = "";
  		$wk_message = "Location Dosn't Exist ";
	}
}
//if (($wk_locn_id <>"") and ($wk_copys <> "") and ($wk_label_qty <> "") and ($wk_per_label_qty <> "") and ($wk_printer <> "") and ($wk_image_x > 0) and ($wk_image_y > 0))
if (($wk_locn_id <>"") and ($wk_printer <> "") )
{
	$my_ref = $wk_printer . $wk_locn_id . "|" .  $wk_locn_id . '|' ;

	$Query = "select res from pc_label_location('" . $my_ref . "')";
	//$Query = "execute procedure  pc_label_location('" . $my_ref . "')";
	if (!($Result5 = ibase_query($Link, $Query)))
	{
		echo("Unable to Print Location!<BR>\n");
		exit();
	}
	if ( ($Row6 = ibase_fetch_row($Result5)) ) 
	{
		$wk_res = $Row6[0];
	}
	//release memory
	ibase_free_result($Result5);
}

//release memory
//ibase_free_result($Result);

echo (" <FORM action=\"GetLocationLabel.php\" method=\"post\" name=printlocation>");
echo (" <P>");
echo ("<INPUT type=\"text\" name=\"message\" class=\"message\" readonly size=\"40\" ><BR> ");
echo ("Location:<INPUT type=\"text\" name=\"locn\" value = \"".$wk_locn_id."\" size=\"30\" ONCHANGE=\"changelocn();\" ><BR> ");
if (isset($wk_desc))
{
	echo ("<INPUT type=\"text\" name=\"locndesc\" value = \"".$wk_desc."\" size=\"50\" readonly>");
}
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo ("<TR><TD>");
echo ("Qty of Labels:");
echo ("</TD><TD>");
echo ("<INPUT type=\"text\" name=\"labelqty\" value=\"$wk_label_qty\" size=\"4\" maxlength=\"4\">");
echo ("</TD></TR>");
echo ("<TR><TD>");
echo ("Printer:");
echo ("</TD><TD>");
echo ("<SELECT name=\"printer\" > ");
$Query = "SELECT device_id FROM sys_equip WHERE device_type = 'PR' ORDER BY device_id ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read sys_equip!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) 
{
	echo ("<OPTION value=\"" . $Row[0] . "\"");
	if ($wk_printer == "")
	{
		$wk_printer = $Row[0];
	}
	if ($wk_printer == $Row[0])
	{
		echo(" selected ");
	}
	echo(">" . $Row[0] . "</OPTION>");
}
//release memory
ibase_free_result($Result);
echo ("</SELECT>");
echo ("</TD></TR>");
echo ("</TABLE>");
echo ("<BR><BR><BR><BR><BR><BR>");
echo ("<BR><BR><BR><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
if ($wk_locn_id == "")
{
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.printlocation.locn.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.printlocation.message.value=\"" . $wk_message . " Enter Location to Print\";\n");
	}
	else
	{
		echo("document.printlocation.message.value=\"Enter Location to Print\";\n");
	}
	echo("</script>");
}
else
{
	if ($wk_label_qty == 0)
	{
		whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
		echo("<script type=\"text/javascript\">\n");
		echo("document.printlocation.labelqty.focus();\n");
		echo("document.printlocation.message.value=\"Enter Qty of Labels to Print\";\n");
		echo("</script>");
	}
	else
	{
		{
			if ($wk_message <> "")
			{
				echo("<script type=\"text/javascript\">\n");
				echo("document.printlocation.message.value=\"" . $wk_message . "\";\n");
				echo("</script>");
			}
			whm2buttons('Print', 'print_Menu.php',"Y","Back_50x100.gif","Back","Print_50x100.gif");
		}
	}
}
//commit
//ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</body>
</html>

