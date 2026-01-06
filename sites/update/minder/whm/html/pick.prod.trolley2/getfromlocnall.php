<?php
include "../login.inc";
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Pick All Location Page</title>
<link rel=stylesheet type="text/css" href="fromlocn.css">
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
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
<?php
require_once 'DB.php';
require 'db_access.php';
require_once "logme.php";
include "2buttons.php";
require_once "checkdata.php";
require_once('transaction.php');

/*
Enter a Person ID that has been used in orders
Enter Location to take all the ISSN's

use transaction PKTL
*/
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbtran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}



echo "<body>";
//require_once "logme.php";



$personId = '';

if (isset($_POST['person']))
{
	$personId = $_POST['person'];
}
if (isset($_GET['person']))
{
	$personId = $_GET['person'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['order_no']))
{
	$order_no = $_POST['order_no'];
}
if (isset($_GET['order_no']))
{
	$order_no = $_GET['order_no'];
}
if (isset($_POST['wh_id']))
{
	$whId  = $_POST['wh_id'];
}
if (isset($_GET['wh_id']))
{
	$whId = $_GET['wh_id'];
}
if (isset($_POST['locn_id']))
{
	$locnId  = $_POST['locn_id'];
}
if (isset($_GET['locn_id']))
{
	$locnId = $_GET['locn_id'];
}
$wk_ok_reason = "";
if (isset($location))
{
	// check location exists

	$field_type = checkForTypein($location, 'LOCATION' ); 
	if ($field_type != "none")
	{
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($location,$startposn);
			$location = $wk_realdata;
		}
		{
			// check the wh id
			$Query = "SELECT WH_ID  FROM WAREHOUSE WHERE WH_ID = '" . substr($location,0,2)."'";
			// echo($Query); 
			$wk_wh_id_used = '';
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_wh_id_used =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
			if ($wk_wh_id_used == '')
			{
				$wk_ok_reason .= "Not a Valid Warehouse";
			} else {
				setBDCScookie($Link, $tran_device, "locationfrom", $location);
				//$wk_ok_reason .= "about to header";
			}
		}
	} else {
		// not a location
		unset($location) ;
		//$wk_ok_reason .= "Invalid Location";
		$wk_ok_reason .= "Not a Location Barcode";
	}
}
if (isset($location))
{
	// run the transaction PKTL
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	//$my_location = $location;
	$wk_sublocn = "";
	$wk_object = (isset($personId)) ?  $personId : "";
	$my_source = 'SSBSSKSSS';
	$my_message = "";
	//$my_message = dotransaction("PKTL", "P", $wk_object, $my_location, $wk_sublocn, "transfer to Despatch", 0, $my_source, $tran_user, $tran_device, "N");
	$my_message = dotransaction_response("PKTL", "P", $wk_object, $location, $wk_sublocn, "pick all of location", 1, $my_source, $tran_user, $tran_device);
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
			$my_responsemessage = "Processed successfully ";
	}
	//echo($my_responsemessage);
	$my_result = explode('|', $my_responsemessage);
	$wk_ok_reason .=  $my_result[0];
	if (isset($my_result[1]))
	{
		$order_no = $my_result[1];
		setBDCScookie($Link, $tran_device, "order", $order_no);
		//$wk_ok_reason .= " Order:" .  $my_result[1];
	}

}
echo ("<div id=\"col3\">\n");


echo("<form action=\"getfromlocnall.php\" method=\"post\" name=getlocn>\n");
echo("Pick All Location<br>");
{
	echo("Person<br><input type=\"text\" name=\"person\" size=\"20\" >");
	echo("<br>From Location<br><input type=\"text\" name=\"location\" size=\"12\"  >");
	if (isset($order_no))
	{	
		if ($order_no <> "")
		{
			echo("<br>Order<br><input type=\"text\" name=\"order\" readonly size=\"20\" value=\"" . $order_no . "\"  >");
		}
	}
}

echo ("<table>\n");
echo ("<tr>\n");
{
	echo("<td colspan=\"2\" >Enter Person Id then Scan Location to Take From</td>\n");
}
echo ("</tr>\n");
//echo ("</table>\n");
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbtran);

//close
//ibase_close($Link);

//echo("</form>\n");
{
	// html 4.0 browser
 	//echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	{
		whm2buttons('Accept', 'pick_Menu.php','N', "Back_50x100.gif","Back","accept.gif", 'N');
	}
	echo ("</tr>");
	echo ("</table>");
}
{
	echo("<script type=\"text/javascript\">\n");
	//echo("document.getlocn.location.focus();\n");
	echo("document.getlocn.person.focus();\n");
	echo("</script>\n");
} 
echo ("</div>\n");
echo ("<div id=\"message\">\n");
echo("<form action=\"\" method=\"post\" name=\"tomessage\">\n");
echo("<input type=\"text\" name=\"message\" readonly value=\"" . $wk_ok_reason . "\">");
echo("</form>");
echo ("</div>\n");
?>
</body>
</html>
