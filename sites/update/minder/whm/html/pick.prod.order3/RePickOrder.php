<?php
include "login.inc";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
?>
<HTML>
<HEAD>
<TITLE>RePick Order</TITLE>
<link rel=stylesheet type="text/css" href="GetDespatch.css">
</HEAD>
<BODY>
<SCRIPT>
function processEdit() {
  if ( document.repickorder.order.value=="")
  {
  	document.repickorder.message.value="Must Enter the Order";
	document.repickorder.order.focus()
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
if ($wk_order_id <>"") 
{
	$my_ref =  $wk_order_id . '|' ;
	$transaction_type = "PKCR";
	$tran_tranclass = "O";
	$my_object = $wk_order_id;
	$location = "          ";
	$my_sublocn = "";
	$myref = "Set for RePick";
	$tran_qty = 0;
	$my_source = "SSSSSSSSS";

	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = " ";
	}
	if (($my_responsemessage == " ") or
	    ($my_responsemessage == ""))
	{
		$my_responsemessage = "Processed successfully ";
	}
	$wk_message = $my_responsemessage;


	//release memory
	//ibase_free_result($Result);
}

//release memory
//ibase_free_result($Result);

echo (" <FORM action=\"RePickOrder.php\" method=\"post\" name=repickorder>");
echo (" <P>");
echo ("<INPUT type=\"text\" name=\"message\" class=\"message\" readonly size=\"40\" ><BR> ");
echo ("Order:<INPUT type=\"text\" name=\"order\" value = \"".$wk_order_id."\" size=\"15\" ><BR> ");
//release memory
echo ("<BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
if ($wk_order_id == "")
{
	whm2buttons('Accept', 'cancel.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<SCRIPT>");
	echo("document.repickorder.order.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.repickorder.message.value=\"" . $wk_message . " Enter Order to RePick\";\n");
	}
	else
	{
		echo("document.repickorder.message.value=\"Enter Order to RePick\";\n");
	}
	echo("</SCRIPT>");
}
else
{
	whm2buttons('RePick', 'cancel.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<SCRIPT>");
	echo("document.repickorder.order.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.repickorder.message.value=\"" . $wk_message . " Enter Order to RePick\";\n");
	}
	else
	{
		echo("document.repickorder.message.value=\"Enter Order to RePick\";\n");
	}
	echo("</SCRIPT>");
}
//commit
//ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</BODY>
</HTML>

