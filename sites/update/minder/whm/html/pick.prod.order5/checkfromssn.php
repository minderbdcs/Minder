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
$available_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$substitute = '';
$allow_sub = '';

if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $dummy, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
	
if (isset($_POST['scannedssn']))
{
	$scannedssn = $_POST['scannedssn'];
}
if (isset($_GET['scannedssn']))
{
	$scannedssn = $_GET['scannedssn'];
}
if (isset($_POST['substitute']))
{
	$substitute = $_POST['substitute'];
}
if (isset($_GET['substitute']))
{
	$substitute = $_GET['substitute'];
}
if (isset($_POST['allow_sub']))
{
	$allow_sub = $_POST['allow_sub'];
}
if (isset($_GET['allow_sub']))
{
	$allow_sub = $_GET['allow_sub'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
//echo ("substitute[$substitute] allow[$allow_sub]\n");

$wk_doupdate = -1;
// if substitute is 'Y' and allow_sub is 'Y' then update the pick_item
if ($allow_sub == "Y" and $substitute == "Y")
{
	// get original ssn for pick_item
	$Query = "select pi.ssn_id,issn.pick_order ";
	$Query .= "from pick_item pi join issn on issn.ssn_id = '".$scannedssn."'";
	$Query .= " where pick_label_no = '".$label_no."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		//print("Unable to Read Picks!<BR>\n");
		header("Location: getfromssn.php?locnfound=1&scannedssn=" . urlencode($scannedssn));
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		// ssn is null 
		$original_ssn = $Row[0];
		$this_label = $Row[1];
	}
	//print("original [$original_ssn] prevlabel[$this_label]\n");
	//release memory
	ibase_free_result($Result);
	if ($this_label == "")
	{
		// ok to update since not on an order
		$wk_doupdate = 1;
	}
	else
	{
		if ($this_label == $label_no)
		{
			// already updated
			$wk_doupdate = 1;
		}
		else
		{
			// not ok to update since already on an order
			$wk_doupdate = 0;
		}
	}
	//print(" doupdate [$wk_doupdate]\n");
	if ($wk_doupdate == 1)
	{
		//echo ("update pick item");
		//ok update the pick_item here
		$Query = "select 1 from pick_item "; 
		$Query .= " where pick_label_no = '".$label_no."'";
		$Query .= " and original_ssn_id is null";
		if (!($Result = ibase_query($Link, $Query)))
		{
			//print("Unable to Read Picks!<BR>\n");
			$Query = "";
		}
		else
		{
			$Query = "";
			while ( ($Row = ibase_fetch_row($Result)) ) {
				if ($Row[0] == 1)
				{
					// no original yet
					$Query = "update pick_item set original_ssn_id = ssn_id, ssn_id = '" . $scannedssn . "'"; 
				}
			}
		}
		if ($Query == "")
		{
			$Query = "update pick_item set ssn_id = '" . $scannedssn . "'"; 
		}
		$Query .= " where pick_label_no = '".$label_no."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			//print("Unable to Read Picks!<BR>\n");
			header("Location: getfromssn.php?locnfound=1&scannedssn=" . urlencode($scannedssn));
			exit();
		}
		$Query = "update issn set pick_order = NULL, issn_status = 'ST'";
		$Query .= " where ssn_id = '" . $original_ssn . "'"; 
		if (!($Result = ibase_query($Link, $Query)))
		{
			//print("Unable to Read ISSN!<BR>\n");
			header("Location: getfromssn.php?locnfound=1&scannedssn=" . urlencode($scannedssn));
			exit();
		}
		$Query = "update issn set pick_order = '".$label_no."', issn_status = 'RS'";
		$Query .= " where ssn_id = '" . $scannedssn . "'"; 
		if (!($Result = ibase_query($Link, $Query)))
		{
			//print("Unable to Read ISSN!<BR>\n");
			header("Location: getfromssn.php?locnfound=1&scannedssn=" . urlencode($scannedssn));
			exit();
		}
	}
}

// want ssn label desc
if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id, s1.ssn_id "; 
	$Query .= "from pick_item p1 ";
	$Query .= " join issn s1 on s1.ssn_id = p1.ssn_id ";
	$Query .= " where p1.pick_label_no = '".$label_no."'";
	$Query .= " and s1.current_qty > 0 ";
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

	// wh must match wh of order
	// company must match company of order
	//$Query .= " and (s3.locn_id starting 'PM') ";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query .= " group by s3.wh_id, s3.locn_id";

	$Query = "select s3.locn_id, s3.current_qty , s3.wh_id, s3.ssn_id "; 
	$Query .= "from pick_item p1 ";
	$Query .= " join issn s3 on s3.prod_id = p1.prod_id ";
	$Query .= " where p1.pick_label_no = '".$label_no."'";
	$Query .= " and s3.ssn_id  = '".$scannedssn."'";
	$Query .= " and s3.current_qty > 0 ";
	$Query .= " and s3.wh_id in (" . $Query2 . ") ";
	$Query .= " and s3.company_id in (" . $Query1 . ") ";
}
//print($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
	exit();
}

$location_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[3]) == $scannedssn)
	{
		// ssn is valid
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
	if ($wk_doupdate == 0)
	{
		//print ("about to head back locnfound 2\n");
		header("Location: getfromssn.php?locnfound=2&scannedssn=" . urlencode($scannedssn));
	}
	else
	{
		if ($location_found == 0)
		{
			header("Location: getfromssn.php?locnfound=0&scannedssn=" . urlencode($scannedssn));
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
			$cookiedata .= $scannedssn;
			$cookiedata .= '|';
			//$cookiedata .= "1";
			$cookiedata .= min($available_qty, $required_qty);
			$cookiedata .= '|';
			setcookie("BDCSData","$cookiedata", time()+1186400, "/");
			if ($ssn <> '')
			{
				header("Location: transactionOL.php");
			}
			else
			{
				//header("Location: getfromqty.php");
				header("Location: transactionOL.php");
			}
		}
	}
}
?>
