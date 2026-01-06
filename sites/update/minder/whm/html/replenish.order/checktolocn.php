<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
//include "logme.php";
require_once "logme.php";
//include "checkdata.php";
require_once "checkdata.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$current_despatchZone = getBDCScookie($Link, $tran_device, "CURRENT_DESPATCH_ZONE"  );
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$company_id = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$location = '';

$wk_ok_reason = "";

if (isset($_POST['label']))
{
	$label_no = $_POST['label'];
}
if (isset($_GET['label']))
{
	$label_no = $_GET['label'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['product']))
{
	$prod_no = $_POST['product'];
}
if (isset($_GET['product']))
{
	$prod_no = $_GET['product'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['desc']))
{
	$description = $_POST['desc'];
}
if (isset($_GET['desc']))
{
	$description = $_GET['desc'];
}
if (isset($_POST['description']))
{
	$description = $_POST['description'];
}
if (isset($_GET['description']))
{
	$description = $_GET['description'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['nolocations']))
{
	$nolocations = $_POST['nolocations'];
}
if (isset($_GET['nolocations']))
{
	$nolocations = $_GET['nolocations'];
}

if (isset($_POST['company']))
{
	$company_id = $_POST['company'];
}
if (isset($_GET['company']))
{
	$company_id = $_GET['company'];
}
$rcount = 0;

if ($location <> "")
{
	{
		// a location 
		$field_type = checkForTypein($location, 'LOCATION' ); 
		if ($field_type != "none")
		{
			// a location
			if ($startposn > 0)
			{
				$wk_realdata = substr($location,$startposn);
				$location = $wk_realdata;
			}
		} else {
			$location = "";
			//$wk_ok_reason .= "Invalid Location";
			$wk_ok_reason .= "Not a Location Barcode";
		}

	}
}


if ($ssn == '')
{
/*
	$Query = "select  to_wh_id, to_locn_id "; 
	$Query .= "from transfer_request  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and trn_status in ('PL','PG')";
	$Query .= " and prod_id = '" . $prod_no . "'";
	$Query .= " and company_id = '" . $company_id . "'";
	$Query .= " and to_wh_id = '" . substr($location,0,2) . "' and to_locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
*/
	// check location is in zone
	$Query = "select  wh_id, locn_id "; 
	$Query .= "from location  ";
	$Query .= " where wh_id = '" . substr($location,0,2) . "' and locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and zone_c = '" . $current_despatchZone  . "'";

	// check location is in replenish params
	$Query3 = "select  first 1 1  "; 
	$Query3 .= "from transfer_request  ";
	$Query3 .= " where device_id = '".$tran_device."'";
	$Query3 .= " and trn_status in ('PL','PG')";
	$Query3 .= " and prod_id = '" . $prod_no . "'";
	$Query3 .= " and company_id = '" . $company_id . "'";
	$Query3 .= " and to_wh_id = '" . substr($location,0,2) . "' ";
	$Query3 .= " and zone_c = '" . $current_despatchZone  . "'";
}
//echo($Query);
$rcount = 0;

//print($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Read+Locations");
	exit();
}

$location_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[0].$Row[1]) == $location)
	{
		// location is valid
		$location_found = 1;
	}
}

//release memory
ibase_free_result($Result);

//print($Query3);
if (!($Result = ibase_query($Link, $Query3)))
{
	header("Location: replenish_Menu.php?message=Unable+to+Read+Requests");
	exit();
}

$location_found3 = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[0]) == 1)
	{
		// location is valid
		$location_found3 = 1;
	}
}

//release memory
ibase_free_result($Result);

if (($location_found == 1) and ($location_found3 == 1))
{
	// is ok
	// location exists in zone and matches at least i transfer request
} else {
	$location_found = 0;
}
//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

{
	if ($location_found == 0)
	{
		$wk_next_page = "gettolocn.php";
	}
	else
	{
		$wk_next_page = "gettoqty.php";
	}
	echo("<FORM action=\"" . $wk_next_page . "\" method=\"post\" name=gettolocn>\n");
	echo("<INPUT type=\"hidden\" name=\"product\" value=\"" . $prod_no . "\" >");
	echo("<INPUT type=\"hidden\" name=\"company\" value=\"" . $company_id . "\" >");
	echo("<INPUT type=\"hidden\" name=\"description\" value=\"" . $description . "\" >");
	echo("<INPUT type=\"hidden\" name=\"nolocations\" value=\"" . $nolocations . "\" >");

	if ($location_found == 0)
	{
		echo("<INPUT type=\"hidden\" name=\"locnfound\" value=\"0\">");
	}
	else
	{
		echo("<INPUT type=\"hidden\" name=\"location\" value=\"" . $location . "\">");
	}
	echo("<INPUT type=\"submit\" name=\"sendme\">");
	echo("</form>\n");
	echo("<script type=\"text/javascript\">\n");
	echo("document.gettolocn.submit();\n");
	echo("</script>\n");

}
?>
