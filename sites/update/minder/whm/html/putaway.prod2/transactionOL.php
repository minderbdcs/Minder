<?php
session_start();
{
	$tran_type = 'SSN';
	$transaction_type = 'TROL';
	$from_location = '';
	$ssn_from = '';
	if (isset($_POST['from_location']))
	{
		$from_location = $_POST['from_location'];
	}
	if (isset($_GET['from_location']))
	{
		$from_location = $_GET['from_location'];
	}
	if (isset($_POST['ssn']))
	{
		$ssn_from = $_POST['ssn'];
	}
	if (isset($_GET['ssn']))
	{
		$ssn_from = $_GET['ssn'];
	}

// ================================================================================================================
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	//include "logme.php";
	require_once "logme.php";
	
	//include "checkdata.php";
	require_once "checkdata.php";
/**
 * getProductCompany
 *
 * @param $Link
 * @param string $prodId
 * @param string $ssnId
 * @return string or null
 */
function getProductCompany($Link, $prodId, $ssnId) {
    $result = '';
    $wkProdFound = '';
    $sql = 'SELECT P1.PROD_ID, P1.COMPANY_ID 
            FROM ISSN I1 
            JOIN PROD_PROFILE P1 ON I1.PROD_ID = P1.PROD_ID AND I1.COMPANY_ID = P1.COMPANY_ID
            WHERE I1.SSN_ID  = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $ssnId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $wkProdFound = $d[0];
                $result = $d[1];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    if ($wkProdFound == '') {
        // try company ALL
        $sql = 'SELECT P1.PROD_ID, P1.COMPANY_ID 
            FROM PROD_PROFILE P1 
            WHERE P1.PROD  = ? AND P1.COMPANY_ID = ?';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            $r = ibase_execute($q, $prodId, 'ALL');
            if ($r) {
                $d = ibase_fetch_row($r);
                if ($d) {
                    $wkProdFound = $d[0];
                    $result = $d[1];
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
    }
    return $result;
}

/**
 * getProductSeen
 *
 * @param $Link
 * @param string $prodId
 * @param string $companyId
 * @return boolean
 */
function getProductSeen($Link, $prodId, $companyId) {
    $result = False;
    $wkProdFound = '';
    $wkManditoryFields = array();
    $sql = 'SELECT I1.CODE  
            FROM OPTIONS I1 
            WHERE I1.GROUP_CODE  = ? 
            AND   I1.CODE STARTING ?  
            AND   I1.DESCRIPTION = ?'; 
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, 'PUTAWAY', 'PROD_PROFILE', 'M');
        if ($r) {
            while ($d = ibase_fetch_row($r)) {
                if ($d) {
                    $wkManditoryFields [] = $d[0];
                }
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    //var_dump($wkManditoryFields);
    if (count($wkManditoryFields) > 0)
    {
        $result = True;
        $sql = 'SELECT '; 
        foreach ($wkManditoryFields as $ManId => $ManValue)
        {
            $sql .= $ManValue . ',';
        }
        $sql .= 'PROD_ID ';
        $sql .= '    FROM PROD_PROFILE  
            WHERE PROD_PROFILE.PROD_ID = ? AND PROD_PROFILE.COMPANY_ID = ?  ';
        $q = ibase_prepare($Link, $sql);
        if ($q) {
            //$r = ibase_execute($q, $ssnId);
            $r = ibase_execute($q, $prodId, $companyId);
            if ($r) {
                $d = ibase_fetch_row($r);
                if ($d) {
                    foreach ($d as $dKey => $dValue)
                    {
                        if (is_null($dValue))
                        {
                            $result = False;
			}
                    }
                }
                ibase_free_result($r);
            }
            ibase_free_query($q);
        }
    }
    return $result;
}

// ================================================================================================================
	if (isset($ssn_from))
	{
		$field_type = checkForTypein($ssn_from, 'BARCODE','SSN' ); 
		if ($field_type == "none")
		{
			// not an ssn - perhaps a location
			$field_type = checkForTypein($ssn_from, 'LOCATION' ); 
			if ($field_type == "none")
			{
				header("Location: getfromssn.php?location=" . urlencode($from_location)."&message=" . urlencode("Not an SSN or Location"));
			}
			else
			{
				$ssn_data = substr($ssn_from, $startposn);
				$ssn_from = $ssn_data;
				header("Location: getfromssn.php?location=".$ssn_from);
			}
			exit();
		}
		else
		{
			$ssn_data = substr($ssn_from, $startposn);
			$ssn_from = $ssn_data;
		}
	}
	if (strlen($from_location) > 10)
	{
		header("Location: getfromssn.php?location=".$from_location."&message=Invalid+Location!");
		exit();
	}
	if (isset($_POST['seenprod']))
	{
		$seen_product = $_POST['seenprod'];
	}
	if (isset($_GET['seenprod']))
	{
		$seen_product = $_GET['seenprod'];
	}


	// Set the variables for the database access:
	//require_once('DB.php');
	//require('db_access.php');
	//include "logme.php";
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getfromssn.php?location=".$from_location."&message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$Query = "select ssn_id, prod_id from issn where ssn_id='" . $ssn_from . "'";
	//echo($Query); 
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: getfromssn.php?location=".$from_location."&message=Can+t+read+ISSN!");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_ssn =  $Row[0];
		$wk_prod =  $Row[1];
		ibase_free_result($Result); 
		unset($Result);
	}
	else
	{
		// no issn
		header("Location: getfromssn.php?location=".$from_location."&message=Can+t+find+SSN!");
		exit();
	}
	
	if ($wk_prod > "")
	{
		if (!isset($seen_product))
		{
			// check the manditory fields in the prod profile as to whether we treat the
			// product as seen
			// save the grn , product from and company
			setBDCScookie($Link, $tran_device, "PRODUCT", $wk_prod );
			setBDCScookie($Link, $tran_device, "GRN", $ssn_from . "|" . $from_location );
			setBDCScookie($Link, $tran_device, "FROM", "../putaway.prod2/getfromssn.php" );
			$wk_prod_company = '';
			$wk_prod_company = getProductCompany($Link, $wk_prod, $ssn_from) ;
			$wk_product_seen = False;
			$wk_product_seen = getProductSeen($Link, $wk_prod, $wk_prod_company) ;
			if ($wk_prod_company == 'ALL')
				$wk_prod_company = '';
			setBDCScookie($Link, $tran_device, "OWNER", $wk_prod_company );
			//commit
			ibase_commit($dbTran);
			if (!$wk_product_seen)
			{
				// a product not seen yet so go to query of product
				header("Location: ../query/product.php?product=". urlencode($wk_prod) . "&grn=".urlencode($ssn_from . "|". $from_location) . "&from=" . urlencode("../putaway.prod2/getfromssn.php") . "&owner=". urlencode($wk_prod_company)); // what about the company
				exit();
			}
		}
	}

	if ($tran_type == "PRODUCT" )
	{
		$my_object = $product_from ;
		$my_source = 'SSBBSKSSS';
		$tran_tranclass = "P";
		$tran_qty = $qty_from;
	}
	elseif ($tran_type == "SSN")
	{
		$my_object = $ssn_from;
		$my_source = 'SSBSSKSSS';
		//$tran_tranclass = "A";
		$tran_tranclass = "W";
		$tran_qty = 1;
	}
	else
	{
		$my_object = '';
		$my_source = 'SSSBSSSSS';
		//$tran_tranclass = "A";
		$tran_tranclass = "W";
		$tran_qty = 0;
	}

	// check putaway id
	$wk_putaway = "";
	$wk_putaway_exists = 0;
	$wk_putaway = getBDCScookie($Link, $tran_device, "PUTAWAY_ID" );

	if ($wk_putaway == "")
	{
		// no putaway
		$Query = "select putaway_id from get_next_putaway";
		//echo($Query); 
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: getfromssn.php?location=".$from_location."&message=Can+t+read+Putaway+ID!");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_putaway = $Row[0];
		}
		ibase_free_result($Result); 
		setBDCScookie($Link, $tran_device, "PUTAWAY_ID", $wk_putaway );
	}

	$location = $from_location;
	$my_sublocn = "";
	$my_ref = "putaway transfer to device" ;

	include("transaction.php");
	$my_message = "";
	$my_message = dotransaction_response($transaction_type , $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		header("Location: getfromssn.php?location=".$from_location."&".$my_message);
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	// ok done transfer to hh
	if ($tran_type == "PRODUCT" )
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
	elseif ($tran_type == "SSN")
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
	else
	{
		// go to get the to location
		header("Location: getfromssn.php?location=".$from_location);
	}
}
?>
