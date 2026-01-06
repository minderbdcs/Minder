<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;

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
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
}
// want ssn label desc
$rcount = 0;

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query2 = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query2)))
{
	echo("Unable to Read Control<BR>\n");
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

if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where s1.ssn_id = '" . $ssn . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
}
else
{
	$Query = "select s3.locn_id, s3.current_qty , s3.wh_id "; 
	$Query .= "from issn s3 ";
	$Query .= " where s3.prod_id = '" . $prod_no . "'";
	$Query .= " and s3.wh_id = '" . substr($location,0,2) . "' and s3.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
}
//print($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
	exit();
}

$location_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[2].$Row[0]) == $location)
	{
		// location is valid
		$location_found = 1;
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
		header("Location: getfromssn.php");
	}
}
?>
