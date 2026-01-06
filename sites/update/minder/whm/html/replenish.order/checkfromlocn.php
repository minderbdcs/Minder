<?php
include "../login.inc";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
include "logme.php";
include "checkdata.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

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
$wk_to_wh_id = "";
$location = "";

$wk_ok_reason = "";

if (isset($_POST['label']))
{
	$label_no = $_POST['label'];
}
if (isset($_GET['label']))
{
	$label_no = $_GET['label'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['prod']))
{
	$prod_no = $_POST['prod'];
}
if (isset($_GET['prod']))
{
	$prod_no = $_GET['prod'];
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
if (isset($_POST['required_qty']))
{
	$required_qty = $_POST['required_qty'];
}
if (isset($_GET['required_qty']))
{
	$required_qty = $_GET['required_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['company']))
{
	$company_id = $_POST['company'];
}
if (isset($_GET['company']))
{
	$company_id = $_GET['company'];
}
if (isset($_POST['to_wh_id']))
{
	$wk_to_wh_id = $_POST['to_wh_id'];
}
if (isset($_GET['to_wh_id']))
{
	$wk_to_wh_id = $_GET['to_wh_id'];
}

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

{
	// save original fields
	$cookiedata = "";
	{
		$cookiedata .= $label_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $order;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $ssn;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $prod_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $uom;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $description;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $required_qty;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $location;
	}
	$cookiedata .= '|';
	$cookiedata .= '|';
	$cookiedata .= '|';
	//setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata );
}
// want ssn label desc
$rcount = 0;



$Query2 = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query2)))
{
	//header("Location replenish_Menu.php?message=Unable+to+Read+Control");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}

//release memory
ibase_free_result($Result);

if ($ssn == '')
{
	$Query = "select s3.locn_id, sum(s3.current_qty) , s3.wh_id  "; 
	$Query .= "from issn s3 ";
	$Query .= "join location l1 on l1.wh_id = s3.wh_id and l1.locn_id = s3.locn_id ";
	$Query .= " where s3.prod_id = '" . $prod_no . "'";
	$Query .= "  and company_id  = '" . $company_id . "'";
	$Query .= " and s3.current_qty > 0 ";
	$Query .= " and s3.wh_id = '" . substr($location,0,2) . "' and s3.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and s3.wh_id = '" . $wk_to_wh_id  . "' ";
	//$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
	//$Query .= " and (s3.wh_id <> 'SY') ";
	//$Query .= " and (l1.store_type <> 'PS') "; //not a pick location
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query .= " group by s3.wh_id, s3.locn_id";
}
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Read+ISSNs+Check");
	exit();
}

$location_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[2].$Row[0]) == $location)
	{
		// location is valid
		$location_found = 1;
		$available_qty = $Row[1];
	}
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);
{
	if ($location_found == 0)
	{
		header("Location: getfromlocn.php?locnfound=0");
	}
	else
	{
		{
			// must now get the qty to take
			/*	
			$picked_qty = min($available_qty,$required_qty);
			$cookiedata = substr($cookiedata,0,strlen($cookiedata) - 1);
			$cookiedata .= $picked_qty;
			$cookiedata .= '|';
			setcookie("BDCSData","$cookiedata", time()+1186400, "/");
			header("Location: transactionOL.php");
			*/
			//header("Location: getfromprod.php");
			header("Location: getfromqty.php");
		}
	}
}
?>
