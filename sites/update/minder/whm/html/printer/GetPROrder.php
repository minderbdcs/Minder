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
<title>Reprint Order Letter</title>
</head>
<body>
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

if (isset($_POST['order'])) 
{
	$wk_order_no = $_POST["order"];
}
if (isset($_GET['order'])) 
{
	$wk_order_no = $_GET["order"];
}
if (isset($_POST['supplier'])) 
{
	$wk_supplier_list = $_POST["supplier"];
}
if (isset($_GET['supplier'])) 
{
	$wk_supplier_list = $_GET["supplier"];
}

include "checkdata.php";

if (isset($wk_order_no))
{
	$field_type = checkForTypein($wk_order_no, 'SALESORDER' ); 
	if ($field_type == "none")
	{
		// not a sales order
		$order_data = $wk_order_no;
	}
	else
	{
		$order_data = substr($wk_order_no, $startposn);
	}
	$wk_order_no = $order_data;
}
$wk_order_exists = "";
$wk_default_supplier = "";
if (isset($wk_order_no))
{
	// check that the order exists and the supplier list
	$Query4 = "SELECT pick_order, supplier_list FROM pick_order where pick_order = '" . $wk_order_no . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Order!<BR>\n");
		exit();
	}
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_order_exists = $Row5[0];
		$wk_default_supplier = $Row5[1];
	}
	//release memory
	ibase_free_result($Result4);
}
if (isset($wk_order_no))
{
	if ($wk_order_exists == "")
	{
		unset($wk_order_no);
  		echo("<H3 ALIGN=\"LEFT\">Order Dosn't Exist</H3>");
	}
}
if (isset($wk_order_no) and isset($wk_supplier_list))
{
	// need wk_printer_name
	// need the default printer code
	$Query4 = "SELECT sys_equip.computer_name, control.default_pick_printer, control.default_pick_priority FROM control LEFT OUTER JOIN sys_equip ON control.default_pick_printer = sys_equip.device_id ";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Printer!<BR>\n");
		exit();
	}
	$wk_printer_name = "";
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_printer_name = $Row5[0];
		$wk_printer_id = $Row5[1];
		$wk_default_pick_priority = $Row5[2];
		if ($wk_printer_name == "")
		{
			$wk_printer_name = $wk_printer_id;
		}
	}
	//release memory
	ibase_free_result($Result4);
	// do PRCL
	// 1st get message id
	$Query4 = "SELECT message_id FROM GET_NEXT_MESSAGE ";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Get Next Message!<BR>\n");
		exit();
	}
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_message_id = $Row5[0];
	}
	//release memory
	ibase_free_result($Result4);
	// need the supplier list flag
	if ($wk_supplier_list == "T")
	{
		$wk_supplier_list = "true";
	}
	else
	{
		$wk_supplier_list = "false";
	}
	// add transactionv4
	$tran_type = "PRCL";
	$tran_tranclass = "S";
	$tran_delim = "|";
	$tran_message = $wk_message_id;
	$tran_data = $tran_user . $tran_delim . $tran_device . $tran_delim . $tran_message . $tran_delim;
	$tran_data .= "<ContactInstanceID>" . $wk_order_no . $tran_delim;
	$tran_data .= "<CoverLetterFlag>true" . $tran_delim;
	$tran_data .= "<SupplierListFlag>" . $wk_supplier_list . $tran_delim;
	$tran_data .= "<Printername>" . $wk_printer_name . $tran_delim;
	$my_source = "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";
	//$my_source .= "KSSSSSS";

	$Query4 = "SELECT REC_ID FROM ADD_TRAN_V4('V4','";
	$Query4 .= $tran_type."','";
	$Query4 .= $tran_tranclass."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query4 .= $tran_trandate."','";
	$Query4 .= $tran_delim."','";
	$Query4 .= $tran_user."','";
	$Query4 .= $tran_device."','";
	$Query4 .= $tran_message."','";
	$Query4 .= $tran_data."','";
	$Query4 .= "F','','MASTER',0,'";
	$Query4 .= $my_source."')";

	//echo($Query4);
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Add Transaction4!<BR>\n");
		exit();
	}
	else
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_record_id = $Row5[0];
		//echo("rec id " . $wk_record_id);
	}
	//release memory
	ibase_free_result($Result4);

	$tran_type = "PRCL";
	$tran_tranclass = "S";
	$tran_delim = "|";
	$tran_message = $wk_message_id;
	$tran_priority = $wk_default_pick_priority;

	$Query4 = "EXECUTE PROCEDURE ADD_MESSAGE_V4('";
	$Query4 .= $tran_message."','";
	$Query4 .= "V4','";
	$Query4 .= $tran_type."','";
	$Query4 .= $tran_tranclass."','";
	$Query4 .= $tran_trandate."','";
	$Query4 .= $tran_user."','";
	$Query4 .= $tran_device."','";
	$Query4 .= $tran_priority."','";
	$Query4 .= "WS','',NULL)";

	//echo($Query4);
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Add Transaction4!<BR>\n");
		exit();
	}
	//release memory
	//ibase_free_result($Result4);
	// add web request
}

//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo (" <FORM action=\"GetPROrder.php\" method=\"post\" name=printorder>");
echo (" <P>");
if (isset($wk_order_no))
{
  	echo("<h4 ALIGN=\"LEFT\">Select Supplier List</h4>");
	echo ("<INPUT type=\"text\" name=\"order\" value = \"".$wk_order_no."\"> ");
	echo ("<SELECT name=\"supplier\" > ");
	echo ("<OPTION value=\"T\"");
	if ($wk_default_supplier == "T")
	{
		echo(" selected >True</OPTION> ");
	}
	else
	{
		echo(">True</OPTION> ");
	}
	echo ("<OPTION value=\"F\"");
	if ($wk_default_supplier == "F")
	{
		echo(" selected >False</OPTION> ");
	}
	else
	{
		echo(">False</OPTION> ");
	}
	echo ("</SELECT> ");
	whm2buttons('Print', 'print_Menu.php',"Y","Back_50x100.gif","Back","Print_50x100.gif");
}
else
{
  	echo("<h4 ALIGN=\"LEFT\">Enter Order to Print</h4>");
	echo ("<INPUT type=\"text\" name=\"order\" > ");
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
}
echo("<script type=\"text/javascript\">\n");
if (isset($wk_order_no))
{
	echo("document.printorder.supplier.focus();\n");
}
else
{
	echo("document.printorder.order.focus();\n");
}
echo("</script>");
?>
</body>
</html>
