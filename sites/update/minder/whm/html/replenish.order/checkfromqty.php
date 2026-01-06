<?php
include "../login.inc";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
require_once "logme.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$scannedssn = '';
$location = '';

//if (isset($_COOKIE['BDCSData']))
{
	//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $wk_cookie);
	
if (isset($_POST['qtypicked']))
{
	$picked_qty = $_POST['qtypicked'];
	$old_picked_qty = $_POST['qtypicked'];
}
if (isset($_GET['qtypicked']))
{
	$picked_qty = $_GET['qtypicked'];
	$old_picked_qty = $_GET['qtypicked'];
}

//print($Query);
$rcount = 0;


{

	// save original fields
	$cookiedata = "";
	$cookiedata .= $label_no;
	$cookiedata .= '|';
	$cookiedata .= $order;
	$cookiedata .= '|';
	$cookiedata .= $ssn;
	$cookiedata .= '|';
	$cookiedata .= $prod_no;
	$cookiedata .= '|';
	$cookiedata .= $uom;
	$cookiedata .= '|';
	$cookiedata .= $description;
	$cookiedata .= '|';
	$cookiedata .= $required_qty;
	$cookiedata .= '|';
	$cookiedata .= $location;
	$cookiedata .= '|';
	$cookiedata .= $scannedssn;
	$cookiedata .= '|';
	$cookiedata .= $picked_qty;
	$cookiedata .= '|';
	//setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata );
	//commit
	ibase_commit($dbTran);
	//if ($picked_qty == $required_qty)
	{
		//header("Location: transactionOL.php");
		include "transactionOL.php";
	}
}


?>
