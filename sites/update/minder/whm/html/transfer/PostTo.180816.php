<?php
include "../login.inc";
require_once('DB.php');
require('db_access.php');
//include "checkdata.php";
//include "logme.php";
require_once "logme.php";
require_once "checkdata.php";

	/*
	if (isset($_COOKIE['BDCSData']))
	{
		print("cookie found ");
		print("cookie :".$_COOKIE["BDCSData"].":");
	}
	else
	{
		print("cookie Not found ");
	}
	*/
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$owner = getBDCScookie($Link, $tran_device, "company" );
	//$cookiedata = $_COOKIE["BDCSData"];
	$cookiedata = getBDCScookie($Link, $tran_device, "transfer");
	list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from ) = explode("|", $cookiedata);
	if (isset($_POST['productto'])) 
	{
		$product_to = $_POST['productto'];
	}
	if (isset($_GET['productto'])) 
	{
		$product_to = $_GET['productto'];
	}
	if (isset($product_to))
	{
		if ($product_to <> "")
		{
			//echo ("productto:" . $product_to);
			// perhaps a product internal
			$field_type = checkForTypein($product_to, 'PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
				// perhaps a product 13
				$field_type = checkForTypein($product_to, 'PROD_13' ); 
				if ($field_type == "none")
				{
					// check prod exists for this
					$Query = "SELECT 1 from prod_profile where prod_id = '";
					$Query .= $product_to ."' and company_id= '";
					$Query .= $owner . "'";
					//$Result = $Link->query($Query);
					$wk_found = 0;
					if (($Result = ibase_query($Link, $Query)))
					{
						if (($Row = ibase_fetch_row($Result)))
						{
							$wk_found = $Row[0];
						}
					}
					ibase_free_result($Result);
					ibase_commit($dbTran);
					$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
					if ($wk_found == 0)
					{
						// a dont know
						unset($product_to);
						//echo ("dont know");
						header("Location: GetProductTo.php?locationto=" . urlencode($location_to). "&message=Not+a+Product");
						exit();
					}
				}
				else
				{
					if ($startposn > 0)
					{
						$wk_realdata = substr($product_to,$startposn);
						$product_to = $wk_realdata;
					}
				}
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($product_to,$startposn);
					$product_to = $wk_realdata;
				}
			}
			//echo ("productto:" . $product_to);
			// check prod exists for this
			$Query = "SELECT 1 from prod_profile where prod_id = '";
			$Query .= $product_to ."' and company_id= '";
			$Query .= $owner . "'";
			//$Result = $Link->query($Query);
			$wk_found = 0;
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_found = $Row[0];
				}
			}
			ibase_free_result($Result);
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			if ($wk_found == 0)
			{
				// a dont know
				unset($product_to);
				//echo ("dont know");
				header("Location: GetProductTo.php?locationto=" . urlencode($location_to). "&message=Not+a+Product");
				exit();
			}
		}
	}
	if (isset($_POST['locationfrom'])) 
	{
		$location_from = $_POST['locationfrom'];
	}
	if (isset($_GET['locationfrom'])) 
	{
		$location_from = $_GET['locationfrom'];
	}
	if (isset($_POST['locationto'])) 
	{
		$location_to = $_POST['locationto'];
	}
	if (isset($_GET['locationto'])) 
	{
		$location_to = $_GET['locationto'];
	}
	if (isset($location_to))
	{
		if ($location_to <> "")
		{
			//echo ("locationto:" . $location_to);
			// perhaps a location internal
			$field_type = checkForTypein($location_to, 'LOCATION' ); 
			if ($field_type == "none")
			{
				// not a location
				// check that its an existing location
				$Query = "SELECT 1 from location where wh_id = '";
				$Query .= substr($location_to,0,2)."' and locn_id= '";
				$Query .= substr($location_to,2,strlen($location_to) - 2) . "'";
				//$Result = $Link->query($Query);
				$wk_found = 0;
				if (($Result = ibase_query($Link, $Query)))
				{
					if (($Row = ibase_fetch_row($Result)))
					{
						$wk_found = $Row[0];
					}
				}
				ibase_free_result($Result);
				ibase_commit($dbTran);
				$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
				if ($wk_found == 0)
				{
					// a dont know
					//echo ("dont know");
					header("Location: GetLocnTo.php?locationto=" . urlencode($location_to). "&message=Not+a+location");
					unset($location_to);
					exit();
				}
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($location_to,$startposn);
					$location_to = $wk_realdata;
				}
			}
			//echo ("locationto:" . $location_to);
			// check that its an existing location
			$Query = "SELECT 1 from location where wh_id = '";
			$Query .= substr($location_to,0,2)."' and locn_id= '";
			$Query .= substr($location_to,2,strlen($location_to) - 2) . "'";
			//$Result = $Link->query($Query);
			$wk_found = 0;
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_found = $Row[0];
				}
			}
			ibase_free_result($Result);
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			if ($wk_found == 0)
			{
				// a dont know
				//echo ("dont know");
				header("Location: GetLocnTo.php?locationto=" . urlencode($location_to). "&message=Not+a+location");
				unset($location_to);
				exit();
			}
		}
	}
	$cookiedata = $tran_type . "|" . $transaction_type;
	$cookiedata .= "|" . $location_from . "|". $ssn_from;
	$cookiedata .= "|" . $product_from . "|" . $qty_from;
	$cookiedata .= '|';

	if (isset($_POST['transaction2_type'])) 
	{
		$cookiedata .= $_POST['transaction2_type'];
	}
	if (isset($_GET['transaction2_type'])) 
	{
		$cookiedata .= $_GET['transaction2_type'];
	}
	$cookiedata .= '|';
	if (isset($_POST['ssnto'])) 
	{
		$ssnto = $_POST['ssnto'];
	}
	if (isset($_GET['ssnto'])) 
	{
		$ssnto = $_GET['ssnto'];
	}
	if (isset($ssnto))
	{
		//if ( (strlen($ssnto) <> 8) )
/*
		if ( (strlen($ssnto) <> 8) and (strlen($ssnto) <> 0) )
		{
			// a location
			$locationto = $ssnto;
			header ("Location: GetSSNTo.php?locationto=".urlencode($ssnto));
			exit;
		}
*/
		if ( strlen($ssnto) <> 0 )
		{
			//echo ("ssnto:" . $ssnto);
			// perhaps a location
			$field_type = checkForTypein($ssnto, 'LOCATION' ); 
			if ($field_type == "none")
			{
				// perhaps an ssn
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($ssnto,$startposn);
					$ssnto = $wk_realdata;
				}
				// a location
				$locationto = $ssnto;
				header ("Location: GetSSNTo.php?locationto=".urlencode($ssnto));
				exit;
			}
			//echo ("ssnto:" . $ssnto);
			// check ssn to is valid
			//echo ("ssnto:" . $ssnto);
			// perhaps a barcode 
			$field_type = checkForTypein($ssnto, 'BARCODE' ); 
			if ($field_type == "none")
			{
				// perhaps an  alt barcode
				$field_type = checkForTypein($ssnto, 'ALTBARCODE' ); 
				if ($field_type == "none")
				{
					// check that its an existing issn 
					$Query = "SELECT 1 from issn where ssn_id = '";
					$Query .= $ssnto."' ";
					//$Result = $Link->query($Query);
					$wk_found = 0;
					if (($Result = ibase_query($Link, $Query)))
					{
						if (($Row = ibase_fetch_row($Result)))
						{
							$wk_found = $Row[0];
						}
					}
					ibase_free_result($Result);
					ibase_commit($dbTran);
					$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
					if ($wk_found == 0)
					{
						// a dont know
						//echo ("dont know");
						header("Location: GetLocnTo.php?locationto=" . urlencode($location_to). "&message=Not+an+ISSN");
						unset($ssnto);
						exit();
					}
				}
				else
				{
					if ($startposn > 0)
					{
						$wk_realdata = substr($ssnto,$startposn);
						$ssnto = $wk_realdata;
					}
				}
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($ssnto,$startposn);
					$ssnto = $wk_realdata;
				}
			}
			// check that its an existing issn 
			$Query = "SELECT 1 from issn where ssn_id = '";
			$Query .= $ssnto."' ";
			//$Result = $Link->query($Query);
			$wk_found = 0;
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_found = $Row[0];
				}
			}
			ibase_free_result($Result);
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			if ($wk_found == 0)
			{
				// a dont know
				//echo ("dont know");
				header("Location: GetLocnTo.php?locationto=" . urlencode($location_to). "&message=Not+an+ISSN");
				unset($ssnto);
				exit();
			}
			//echo ("ssnto:" . $ssnto);
		}
	}
	if (isset($location_to)) 
	{
		$cookiedata .= $location_to;
	}
	$cookiedata .= '|';
	if ($tran_type == "PRODUCT")
	{
		if ($product_to <> "")
		{
/*
			if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
			{
				header ("Location: GetSSNFrom.php?message=connect");
				exit();
			}
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/
			//$Query = "SELECT sum(current_qty) from issn where prod_id = '" . $product_to . "' and locn_id= '" . $tran_device . "'";
			$Query = "SELECT sum(current_qty) from issn where prod_id = '" . $product_to . "' and locn_id= '" . $tran_device . "' and company_id = '" . $owner . "'";
			//$Result = $Link->query($Query);
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$qtyto = $Row[0];
				}
				ibase_free_result($Result);
				ibase_commit($dbTran);
				if ($qtyto == "")
				{
					$qtyto = 0;
				}
				if ($qtyto == 0)
				{
					header("Location: GetProductTo.php?locationto=" . urlencode($location_to). "&message=Product+Not+on+Device");
					exit();
				}
				$cookiedata .= $qtyto;
				$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			}
		}
	}
	else
	{
		if (isset($_POST['qtyto'])) 
		{
			$cookiedata .= $_POST['qtyto'];
			$qtyto = $_POST['qtyto'];
		}
		if (isset($_GET['qtyto'])) 
		{
			$cookiedata .= $_GET['qtyto'];
			$qtyto = $_GET['qtyto'];
		}
	}
	$cookiedata .= '|';
	if (isset($_POST['ssnto'])) 
	{
		$cookiedata .= $_POST['ssnto'];
	}
	if (isset($_GET['ssnto'])) 
	{
		$cookiedata .= $_GET['ssnto'];
	}
	$cookiedata .= '|';
	if (isset($product_to)) 
	{
		$cookiedata .= $product_to;
	}
	$cookiedata .= '|';
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	//print("$cookiedata ");
	setBDCScookie($Link, $tran_device, "transfer", $cookiedata);
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
	
	//close
	//$Link->disconnect();
	//ibase_close($Link);

	if ((isset($_POST['ssnsplit'])) or  
	    (isset($_GET['ssnsplit'])))
	{
		//header ("Location: transactionSS.php");
		if ($qty_from >= $qtyto)
		{
			header ("Location: transactionSS.php");
		}
		else
		{
			//print("qtyfrom:$qty_from qtyto:$qtyto\n");
			header ("Location: GetSSNSplitFrom.php?message=lowqty");
		}
		exit();
	}
	header ("Location: transactionTI.php");

?>
