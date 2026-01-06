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
$have_pickedqty = 0;

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
if (isset($_POST['qtypicked']))
{
	$picked_qty = $_POST['qtypicked'];
	$have_pickedqty = 1;
}
if (isset($_GET['qtypicked']))
{
	$picked_qty = $_GET['qtypicked'];
	$have_pickedqty = 1;
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

include "logme.php";

$Query2 = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query2)))
{
	//header("Location pick_Menu.php?message=Unable+to+Read+Control");
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

logme($Link, $tran_user, $tran_device, "start check ssn location");
if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where s1.ssn_id = '" . $ssn . "'";
	$Query .= " and s1.ssn_id = '" . $location . "'";
	//$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
}
else
{
	$wk_wh = "";
	$wk_company = "";
	// get wh and company from the order
	$Query = "select p2.wh_id, p2.company_id "; 
	$Query .= "from pick_order p2 ";
	$Query .= "where p2.pick_order = '" . $order . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Order!<BR>\n");
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_wh = $Row[0];
			$wk_company = $Row[1];
		}
	}
	//release memory
	ibase_free_result($Result);

	// get user flags
	$wk_sysuser = "";
	$wk_usercomp = "";
	$wk_inventoryop = "";
	$Query = "SELECT sys_admin, company_id, inventory_operator from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
		$wk_usercomp = $Row[1];
		$wk_inventoryop = $Row[2];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_company == "")
	{
		if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
		{
			/* if im sysadmin then can access  all companys */
			$Query1 = "select company_id from company ";
		}
		else
		{
			/* if im not sysadmin then can only access companys in access company */
			$Query1 = "select company_id from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') ";
		}
		//echo($Query);
	}
	else
	{
		$Query1 = "'" . $wk_company. "'";
	}

	if ($wk_wh == "")
	{
		if ($wk_sysuser == "T") 
		{
			/* if im sysadmin then can access  all warehouses */
			$Query2 = "select wh_id from warehouse where wh_id < 'X' or wh_id >'X~'  ";
		}
		else
		{
			/* if im not sysadmin then can only access companys in access user */
			$Query2 = "select wh_id from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') ";
		}
	}
	else
	{
		$Query2 = "'" . $wk_wh. "'";
	}

	//$Query = "select s3.locn_id, sum(s3.current_qty), s3.wh_id "; 
	$Query = "select s3.locn_id, s3.current_qty, s3.wh_id "; 
	$Query .= "from issn s3  ";
	$Query .= " where s3.prod_id = '".$prod_no."'";
	$Query .= " and s3.current_qty > 0 ";
	// wh must match wh of order
	$Query .= " and s3.wh_id in (" . $Query2 . ") ";
	// company must match company of order
	$Query .= " and s3.company_id in (" . $Query1 . ") ";
	// wh and locn must also be the scanned location
	//$Query .= " and s3.wh_id = '" . substr($location,0,2) . "' and s3.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and s3.ssn_id = '" . $location . "'";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	//$Query .= " group by s3.wh_id, s3.locn_id";
}
//print($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
	exit();
}

$location_found = 0;
$scanned_ssn = $location;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($ssn <> "")
	{
		//$scanned_ssn = $location;
		$location = $Row[2] . $Row[0];
	}
	else
	{
		$location = $Row[2] . $Row[0];
	}
	if (trim($Row[2].$Row[0]) == $location)
	{
		// location is valid
		$location_found = 1;
		$available_qty = $Row[1];
	}
}

//release memory
ibase_free_result($Result);

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
		// scanned ssn
	$cookiedata .= '|';
		// picked qty
	$cookiedata .= '|';
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
}

logme($Link, $tran_user, $tran_device, "end check ssn location");
/*
	dont do this query - takes seconds
{
	$Query = "select first 1 1 "; 
	$Query .= "from pick_item p2 ";
	$Query .= "join pick_order p1 on p1.pick_order = p1.pick_order ";
	$Query .= "join company c1 on c1.company_id = p1.company_id ";
	$Query .= "join options o1 on o1.group_code = 'CMPPKPROD' and o1.code = c1.company_id and o1.description = 'O' ";
	$Query .= "where p2.device_id = '" . $tran_device . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		//echo("Unable to Read Company!<BR>\n");
		header("Location: pick_Menu.php?message=Unable+to+Read+Company");
		exit();
	}
	$picked_by_order = 0;
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$picked_by_order = 1;
	}
	//release memory
	ibase_free_result($Result);
}
*/
$picked_by_order = 1;

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
		if ($ssn <> "")
		{
			if (($have_pickedqty == 0) or
		 	   ($picked_qty == 0))
			{
				$picked_qty = min($available_qty,$required_qty);
			}
			//must add the scanned ssn to the cookie
			$cookiedata = substr($cookiedata,0,strlen($cookiedata) - 2);
			$cookiedata .= $scanned_ssn;
			$cookiedata .= '|';
			//$cookiedata = substr($cookiedata,0,strlen($cookiedata) - 1);
			$cookiedata .= $picked_qty;
			$cookiedata .= '|';
			setcookie("BDCSData","$cookiedata", time()+1186400, "/");
			if ($picked_qty < $available_qty)
			{
				// qty is not all of issn - so a split
				// go and get the printer to use
				//header("Location: getprinter.php");
				header("Location: transactionOL.php?scannedprinter=" . $tran_device);
			}
			else
			{
				header("Location: transactionOL.php");
			}
			// want to go to addrprodlabel at end of product

		}
		else
		{
			//header("Location: getfromssn.php");
			if (($have_pickedqty == 0) or
		 	   ($picked_qty == 0))
			{
				$picked_qty = min($available_qty,$required_qty);
			}
			//must add the scanned ssn to the cookie
			$cookiedata = substr($cookiedata,0,strlen($cookiedata) - 2);
			$cookiedata .= $scanned_ssn;
			$cookiedata .= '|';
			//$cookiedata = substr($cookiedata,0,strlen($cookiedata) - 1);
			$cookiedata .= $picked_qty;
			$cookiedata .= '|';
			setcookie("BDCSData","$cookiedata", time()+1186400, "/");
                    	setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata);
			if ($picked_qty < $available_qty)
			{
				// qty is not all of issn - so a split
				// go and get the printer to use
				//header("Location: getprinter.php");
				header("Location: transactionOL.php?scannedprinter=" . $tran_device);
			}
			else
			{
				header("Location: transactionOL.php");
				//header("Location: getfromqty.php");
			}
		}
	}
}
?>
