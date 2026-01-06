<?php
include "login.inc";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
?>
<HTML>
<HEAD>
<TITLE>Reprint Despatch</TITLE>
<link rel=stylesheet type="text/css" href="PrintOrder.css">
</HEAD>
<BODY>
<SCRIPT>
function processEdit() {
  if ( document.printdespatch.order.value=="")
  {
  	document.printdespatch.message.value="Must Enter the Order";
	document.printdespatch.order.focus()
  	return false
  }
  return true;
}
</SCRIPT>
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

$wk_order_id = "";
$wk_printer = "";
$wk_image_x = "";
$wk_image_y = "";
if (isset($_POST['order'])) 
{
	$wk_order_id = $_POST["order"];
}
if (isset($_GET['order'])) 
{
	$wk_order_id = $_GET["order"];
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

$wk_order_exists = "";
//$wk_desc = "";
if ($wk_order_id <> "")
{
	$wk_order_id = strtoupper($wk_order_id);
	// check that the order exists and the labeltype list
	$Query4 = "SELECT 1 FROM pick_order where pick_order = '" . $wk_order_id . "' ";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Order!<BR>\n");
		exit();
	}
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_order_exists = "Y";
	}
	//release memory
	ibase_free_result($Result4);
}
if (isset($wk_order_id))
{
	if ($wk_order_exists == "")
	{
		$wk_order_id = "";
  		$wk_message = "Order Dosn't Exist ";
	}
}
if (($wk_order_id <>"") and ($wk_printer <> "") )
{

	$Query = "SELECT WK_RESULT FROM PC_LABEL_SALE_ORDER('" . $wk_order_id . "|" . $wk_printer . "|1|')";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Print Order!<BR>\n");
		exit();
	}
	if ( ($Row = ibase_fetch_row($Result)) ) 
	{
		//echo($Row[0]);
		$wk_dummy = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}

//release memory
//ibase_free_result($Result);

if ($wk_printer == "")
{
	$wk_printer = "PF";
}
echo (" <FORM action=\"PrintOrder.php\" method=\"post\" name=printdespatch>");
echo (" <P>");
echo ("<INPUT type=\"text\" name=\"message\" class=\"message\" readonly size=\"40\" ><BR> ");
echo ("Order:<INPUT type=\"text\" name=\"order\" value = \"".$wk_order_id."\" size=\"15\" ><BR> ");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
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
echo ("<BR><BR><BR><BR><BR><BR><BR>");
echo ("<BR><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
if ($wk_order_id == "")
{
	whm2buttons('Accept', 'pick_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<SCRIPT>");
	echo("document.printdespatch.order.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.printdespatch.message.value=\"" . $wk_message . " Enter Order to Print\";\n");
	}
	else
	{
		echo("document.printdespatch.message.value=\"Enter Order to Print\";\n");
	}
	echo("</SCRIPT>");
}
else
{
	{
		if ($wk_message <> "")
		{
			echo("<SCRIPT>");
			echo("document.printdespatch.message.value=\"" . $wk_message . "\";\n");
			echo("</SCRIPT>");
		}
		whm2buttons('Print', 'pick_Menu.php',"Y","Back_50x100.gif","Back","Print_50x100.gif");
	}
}
//commit
//ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</BODY>
</HTML>

