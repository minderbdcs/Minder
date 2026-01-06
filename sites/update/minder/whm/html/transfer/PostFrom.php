<?php
include "../login.inc";

	require_once 'DB.php';
	require_once 'db_access.php';
require_once "logme.php";  
require_once "checkdata.php";

/**
 * check for Options
 *
 * @param ibase_link $Link Connection to database
 * @param string $code
 * @return string
 */
function getTransferOption($Link, $code)
{
	{
		$Query = "select description from options where group_code='TRANSFER'  and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/tmp/transactionTO.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return $wk_data ;
} // end of function


/**
 * check ISSN exists else try differing 10m ranges for the SSN_ID
 *
 * @param ibase_link $Link Connection to database
 * @param string $ssnId
 * @return string
 */
function checkISSN10m($Link, $ssnId)
{
	$wk_issn_found = 0;
	$wk_current_ssnId = $ssnId;
	$wk_issnId_found = NULL;
	$wk_cnt = 0;
	{
		$Query = "select 1, ssn_id from issn where ssn_id  = '" . $ssnId . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read ISSNs!<BR>\n");
			$log = fopen('/tmp/transactionTO.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_issn_found = $Row[0];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	while (($wk_issn_found == 0) and ($wk_cnt < 9))
	{
		// issn not found try adding 10m to the ssn_id
		$wk_cnt++;
		$wk_current_ssnId += 10000000;
		{
			$Query = "select 1, ssn_id from issn where ssn_id  = '" . $wk_current_ssnId . "' "; 
			//echo($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read ISSNs!<BR>\n");
				$log = fopen('/tmp/transactionTO.log' , 'a');
				fwrite($log, $Query);
				fclose($log);
				//exit();
			}
			while ( ($Row = ibase_fetch_row($Result)) ) {
				if ($Row[0] > "")
				{
					$wk_issn_found = $Row[0];
				}
				if ($Row[1] > "")
				{
					$wk_issnId_found = $Row[1];
				}
			}
			//release memory
			ibase_free_result($Result);
		} 
	}
	// if no issns found return the original issn id
	//return $wk_current_ssnId ;
	return  ($wk_issn_found == 0) ? $ssnId: $wk_current_ssnId;
} // end of function

/*
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header ("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

	$wk_use_plus_10m = getTransferOption($Link, "SSN+10000000");
	if ($wk_use_plus_10m == "")
	{
		$wk_use_plus_10m = "F";
	}
	$cookiedata = "";
	$added_data = "";
	if (isset($_POST['tran_type'])) 
	{
		$cookiedata .= $_POST['tran_type'];
	}
	if (isset($_GET['tran_type'])) 
	{
		$cookiedata .= $_GET['tran_type'];
	}
	$cookiedata .= '|';
	if (isset($_POST['transaction_type'])) 
	{
		$cookiedata .= $_POST['transaction_type'];
	}
	if (isset($_GET['transaction_type'])) 
	{
		$cookiedata .= $_GET['transaction_type'];
	}
	$cookiedata .= '|';
	if (isset($_POST['locationfrom'])) 
	{
		$cookiedata .= $_POST['locationfrom'];
	}
	if (isset($_GET['locationfrom'])) 
	{
		$cookiedata .= $_GET['locationfrom'];
	}
	if (isset($_POST['ssnfrom'])) 
	{
		$ssnfrom = $_POST['ssnfrom'];
	}
	if (isset($_GET['ssnfrom'])) 
	{
		$ssnfrom = $_GET['ssnfrom'];
	}
	if (isset($ssnfrom))
	{
		if (substr($ssnfrom, 0, 1) == "_")
		{
			$ssn_from_nolabel = "T";
			$ssnfrom = substr($ssnfrom, 1);
		}
	}
	// check param entrys ===============================================

	if (isset($ssnfrom))
	{
	        // trim it
		$ssnfrom = trim($ssnfrom);
		if ($ssnfrom <> "")
		{
			//echo ("ssnfrom:" . $ssnfrom;
			// perhaps a ssn 
			$field_type = checkForTypein($ssnfrom, 'BARCODE' ); 
			if ($field_type == "none")
			{
				// perhaps an alt barcode
				$field_type = checkForTypein($ssnfrom, 'ALTBARCODE' ); 
				if ($field_type == "none")
				{
					// a dont know
					unset($ssnfrom);
					//echo ("dont know");
					header("Location: GetSSNFrom.php?message=Not+an+ISSN");
					exit();
				}
				else
				{
					if ($startposn > 0)
					{
						$wk_realdata = substr($ssnfrom, $startposn);
						$ssnfrom = $wk_realdata;
					}
				}
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($ssnfrom, $startposn);
					$ssnfrom = $wk_realdata;
				}
			}
			//echo ("ssnfrom:" . $ssnfrom);
		}
	}
	// end param entry for ssn

	if (isset($ssnfrom))
	{
		/* if allowed to change the ssn
                   check that the issn exists
		   if not
		   try adding 10000000 to the ssn
		*/
		if ($wk_use_plus_10m == "T")
		{
			$my_ssnfrom = checkISSN10m($Link, $ssnfrom);
		} else {
			$my_ssnfrom = $ssnfrom;
		}
		// must get location from current issn
		//$Query = "SELECT wh_id, locn_id, current_qty  from issn where ssn_id = '".$ssnfrom."'";
		$Query = "SELECT wh_id, locn_id, current_qty  from issn where ssn_id = '".$my_ssnfrom."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			header ("Location: GetSSNFrom.php?message=query");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)))
		{
			$locationfrom = $Row[0].$Row[1];
			$qtyfrom = $Row[2] + 1;
			$cookiedata .= $locationfrom;
		}
		else
		{
			if (isset($_POST['ssnsplit']) or 
			    isset($_GET['ssnsplit'])) 
			{
				header ("Location: GetSSNSplitFrom.php?message=nossn");
			}
			else
			{
				header ("Location: GetSSNFrom.php?message=nossn");
			}
			exit();
		}
		//release memory
		//$Result->free();
		ibase_free_result($Result);

		if (!isset($locationfrom))
		{
			exit();
		}
	}
	$cookiedata .= '|';
/*
	if (isset($_POST['ssnfrom'])) 
	{
		$cookiedata .= $_POST['ssnfrom'];
	}
	if (isset($_GET['ssnfrom'])) 
	{
		$cookiedata .= $_GET['ssnfrom'];
	}
*/
	if (isset($ssnfrom)) 
	{
		$cookiedata .= $ssnfrom;
	}
	$cookiedata .= '|';
	if (isset($_POST['productfrom'])) 
	{
		$cookiedata .= $_POST['productfrom'];
	}
	if (isset($_GET['productfrom'])) 
	{
		$cookiedata .= $_GET['productfrom'];
	}
	$cookiedata .= '|';
	if (isset($qtyfrom))
	{
		$cookiedata .= $qtyfrom;
	}
	else
	{
		if (isset($_POST['qtyfrom'])) 
		{
			$cookiedata .= $_POST['qtyfrom'];
		}
		if (isset($_GET['qtyfrom'])) 
		{
			$cookiedata .= $_GET['qtyfrom'];
		}
	}
	if (isset($_POST['getallqtys'])) 
	{
		$added_data .= "getallqtys=" . urlencode($_POST['getallqtys']);
	}
	if (isset($_GET['getallqtys'])) 
	{
		$added_data .= "getallqtys=" . urlencode($_GET['getallqtys']);
	}
	if (isset($_POST['reasonfrom'])) 
	{
		if (strlen($added_data) > 0)
		{
			$added_data .= "&";
		}
		$added_data .= "reasonfrom=" . urlencode($_POST['reasonfrom']);
	}
	if (isset($_GET['reasonfrom'])) 
	{
		if (strlen($added_data) > 0)
		{
			$added_data .= "&";
		}
		$added_data .= "reasonfrom=" . urlencode($_GET['reasonfrom']);
	}
	setcookie("BDCSData","$cookiedata", time()+11186400, "/");
	//include "logme.php";
	setBDCScookie($Link, $tran_device, "transfer", $cookiedata);
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
	
	//close
	//$Link->disconnect();
	//ibase_close($Link);

	if (isset($ssn_from_nolabel)) 
	{
		header ("Location: GetSSNNoLabelLocnFrom.php");
		exit();
	}
	if ((isset($_POST['ssnsplit']))  or
	    (isset($_GET['ssnsplit']))) 
	{
		if (isset($qtyfrom))
		{
			if ( $qtyfrom > 1)
			{
				header ("Location: GetSSNSplitTo.php");
			}
			else
			{
				header ("Location: GetSSNSplitFrom.php?message=zeroqty");
			}
		}
		else
		{
			header ("Location: GetSSNSplitFrom.php?message=noqty");
		}
		exit();
	}
	if ($added_data > "")
	{
		header ("Location: transactionTO.php?" . $added_data);
	}
	else
	{
		header ("Location: transactionTO.php");
	}

?>
