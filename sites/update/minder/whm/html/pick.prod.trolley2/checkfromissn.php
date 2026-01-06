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
$substitute = '';
$scannedssn = '';
$scanned_ssn = '';
$scanned_qty = 1;

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//echo("Unable to Connect!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
//include "logme.php";
require_once "logme.php";
//include "checkdata.php";
require_once "checkdata.php";
{
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $dummy, $dummy2) = explode("|", $wk_cookie . "|||||||||||");
}
	
if (isset($_POST['scannedssn']))
{
	$scannedssn = $_POST['scannedssn'];
}
if (isset($_GET['scannedssn']))
{
	$scannedssn = $_GET['scannedssn'];
}

//phpinfo();

$wk_doupdate = -1;
// allow for barcode or altbarcode datatype
// do the symbology prefix removal
$wk_ok_reason = "";

$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
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
$allowed_status .= "AL,";

//var_dump($scannedssn);

{
	if ($scannedssn <> "")
	{
		// pick by issn
		$field_type = checkForTypein($scannedssn, 'BARCODE' ); 
		//echo ('BARCODE:' . $field_type);
		if ($field_type != "none")
		{
			// an ssn 
			if ($startposn > 0)
			{
				$wk_realdata = substr($scannedssn,$startposn);
				$scanned_ssn = $wk_realdata;
			} else {
				$scanned_ssn = $scannedssn;
			}
		} else {
			$field_type = checkForTypein($scannedssn, 'ALTBARCODE' ); 
			//echo ('ALTBARCODE:' . $field_type);
			if ($field_type != "none")
			{
				// an alternate ssn form
				if ($startposn > 0)
				{
					$wk_realdata = substr($scannedssn,$startposn);
					$scanned_ssn = $wk_realdata;
				} else {
					$scanned_ssn = $scannedssn;
				}
			} else {
				$scannedssn = "";
				$wk_ok_reason .= "Not an ISSN";
			}
		}
	}
	else
	{
		$wk_ok_reason .= "Passed ISSN is Empty";
	}
}
//var_dump($scanned_ssn);
// need to enforce that
// current qty > 0
// wh_id is not an X? one
// issn_status is one that can be picked

// want ssn label desc
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id, s1.ssn_id, s1.issn_status "; 
	$Query .= " from issn s1  ";
	$Query .= " where s1.ssn_id  = '".$scanned_ssn ."'";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	//echo("Unable to Read ISSN!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
	exit();
}

$location_found = 0;
$issn_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	// check the qty
	if ($Row[1] == "") 
	{
		$wk_ok_reason .= " Qty is Null ";
		
	}
	else
	{
		if (intval($Row[1]) < 1)
		{
			$wk_ok_reason .= " Qty is 0  ";
		}
		else
		{
			$scanned_qty = $Row[1];
		}
	}
	// check wh
	if ($Row[2] == "") 
	{
		$wk_ok_reason .= " WH is Empty ";
		
	}
	else
	{
		if ((substr($Row[2],0,1) == "X") or ($Row[2] == "SY"))
		{
			$wk_ok_reason .= " WH  " . $Row[2] . " is a system one ";
		}
	}
	// check the issn status
	if ($Row[4] == "") 
	{
		$wk_ok_reason .= " ISSN has No Status ";
	}
	else
	{
		$wkPosn = strpos($allowed_status, $Row[4]);
		if ($wkPosn === false )
		{
			$wk_ok_reason .= " ISSN Status " . $Row[4] . " is Not Allowed for Picking ";
		}

	}
	if ((trim($Row[3]) == $scanned_ssn) and ($wk_ok_reason == ""))
	{
		// ssn is valid
		$location_found = 1;
	}
	//var_dump($Row);
	$issn_found = 1;
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//var_dump($wk_ok_reason);
//var_dump($location_found);
//die;
 if ($issn_found == 0) {
	$wk_ok_reason .= " ISSN was Not Found ";
}

{
	{
		if ($location_found == 0)
		{
			header("Location: getfromissn.php?scannedssn=" . urlencode($scannedssn) . "&message=" . urlencode($wk_ok_reason));
		}
		else
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
			$cookiedata .= $scanned_ssn;
			$cookiedata .= '|';
			$cookiedata .= $scanned_qty;
			$cookiedata .= '|';
			//setcookie("BDCSData","$cookiedata", time()+1186400, "/");
			setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata);
			{
				//header("Location: getorderissn.php");
				//header("Location: getordersissn2.php");
				//header("Location: getordersissn.php");
				// if already have an order allocated to this device then dopkal=F
				header("Location: getordersissn.php?dopkal=T");
			}
		}
	}
}
?>
