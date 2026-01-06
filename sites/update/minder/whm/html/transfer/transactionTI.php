<?php
session_start();

		
/**
 * trap errors
 *
 * @param ibase_link $Link Connection to database
 * @param string $code
 * @return string
 */

function errorHandler2( $errno, $errstr, $errfile, $errline, $errcontext)
{
	global $dbTran, $wk_db_error ;
	//$log = fopen('/tmp/issn.orders.log' , 'a');
	$log = fopen('/data/tmp/issn.orders.TRIL.log' , 'a');
	$datetime  = strftime("%D %T "); 
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s\n", $datetime, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  \n", $datetime );
	}

	fwrite($log, $userline);
	fwrite($log, $errno);
	fwrite($log,"  ");
	fwrite($log, $errstr);
	fwrite($log,"  \n");
	fwrite($log, $errfile);
	fwrite($log," line ");
	fwrite($log, $errline);
	fwrite($log,"  \n");
	fwrite($log, print_r($errcontext, true));
	fwrite($log,"\n");
	fclose($log);
/*
	if  (($errstr like ' deadlock ')
	{
		need to rollback and try again
		then resume at the start of the pkal loop
	}
*/
	$wk_pos1 = strpos($errstr, ' deadlock ');
	if ($wk_pos1 !== FALSE)
	{
		$wk_db_error = True;
		//rollback
		ibase_rollback($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}
}


/**
 * log a message to logfile
 *
 * @param ibase_link $Link Connection to database
 * @param string $message
 */

function logtime2( $Link,  $message)
{
	$Query = "";
	$log = fopen('/data/tmp/transferTI2.log' , 'a');
		$wk_current_time = "";
		$Query = "select cast(cast('NOW' as timestamp) as char(24)) from control ";
		$Query = "select cast('NOW' as timestamp) from control ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table control<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_current_time =  $Row[0];
		}
		else
		{
			$wk_current_time = "";
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
		$wk_current_micro = microtime();
		$wk_current_time .= " " . $wk_current_micro;
		$wk_message = str_replace("'", "`", $message);
		
		// 1st try ip address of handheld
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s", $wk_current_time, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  ", $wk_current_time );
	}

	fwrite($log,"  ");
	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}


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
			//$log = fopen('/tmp/transactionTO.log' , 'a');
			$log = fopen('/data/tmp/transactionTO.log' , 'a');
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
 * get Deadlock Limit 
 *
 * @param ibase_link $Link Connection to database
 * @return string
 */
function getDeadlockLimit ($Link  )
{
	$wkResult = "";
	$Query = "select p1.description    
                  from options p1
                  where p1.group_code = 'TRANSFER' AND p1.code='DEADLOCK' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult == "") 
	{
		$wkResult  = "5";
	}
	return $wkResult;
}


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
			//$log = fopen('/tmp/transactionTO.log' , 'a');
			$log = fopen('/data/tmp/transactionTO.log' , 'a');
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
				//$log = fopen('/tmp/transactionTO.log' , 'a');
				$log = fopen('/data/tmp/transactionTO.log' , 'a');
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


/**
 * check ISSN exists else try differing 10m ranges for the SSN_ID
 *
 * @param ibase_link $Link Connection to database
 * @param string $ssnId
 * @return string
 */
function getQtyOnDevice($Link )
{
	$gotmore = 0;
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	{
		$Query = "SELECT COUNT(*) FROM ISSN WHERE LOCN_ID = '$tran_device'" ;
		// echo($Query); 
		if (($Result = ibase_query($Link, $Query)))
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				$gotmore =  $Row[0];
				ibase_free_result($Result); 
				unset($Result); 
			}
		}
	}
	return  $gotmore  ;
} // end of function


//if (isset($_COOKIE['BDCSData']))
{
	//list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from) = explode("|", $_COOKIE["BDCSData"] . "|||||||");
	//$cookiedata = getBDCScookie($Link, $tran_device, "transfer");
	//list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from) = explode("|", $cookiedata . "|||||||");
	//echo($_COOKIE["BDCSData"]);
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include("transaction.php");
	include "logme.php";
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (isset($_POST['getallqtys'])) 
	{
		$getallqtys = $_POST['getallqtys'];
	}
	if (isset($_GET['getallqtys'])) 
	{
		$getallqtys = $_GET['getallqtys'];
	}
	if (isset($_POST['reasonfrom'])) 
	{
		$reason = $_POST['reasonfrom'];
	}
	if (isset($_GET['reasonfrom'])) 
	{
		$reason = $_GET['reasonfrom'];
	}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

	$wk_use_plus_10m = getTransferOption($Link, "SSN+10000000");
	if ($wk_use_plus_10m == "")
	{
		$wk_use_plus_10m = "F";
	}
	//$cookiedata = $_COOKIE["BDCSData"];
	$cookiedata = getBDCScookie($Link, $tran_device, "transfer");
	//list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from ) = explode("|", $cookiedata);

	list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to, $ssn_to) = explode("|", $cookiedata);
	//list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from) = explode("|", $cookiedata);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . $cookiedata);

	$tran_deadlock_limit = getDeadlockLimit ($Link ) + 0;

	//$location = $location_from;
	$location = $location_to;
	$transaction_type  = $transaction2_type;
	$wk_reset_locn = False;
	if ($tran_type == "PRODUCT" )
	{
		$company = getBDCScookie($Link, $tran_device, "company");
		$my_object = $product_from ;
		$my_product = $product_from ;
		$my_source = 'SSBBSKSSS';
		$tran_tranclass = "P";
		$tran_qty = $qty_from;
		//echo("qty is $tran_qty");
		if (isset($getallqtys))
		{
			//echo("getallqtys is $getallqtys");
			if ($getallqtys == "Y")
			{
				// must get all remaining qty of product
				$Query = "select sum(current_qty) from issn where prod_id = '" ;
				$Query .= $product_from . "' and  wh_id = '";
				$Query .= substr($location,0,2)."' and locn_id = '";
				$Query .= substr($location,2,strlen($location) - 2)."'";
				$Query .= " and company_id = '" . $company . "'";
				//echo($Query);
				if (!($Result = ibase_query($Link, $Query)))
				{
					// no result 
					$tran_qty =  0;
					//echo("no result");
				}
				else
				if (($Row = ibase_fetch_row($Result)))
				{
					$tran_qty =  $Row[0];
					if ($tran_qty == "")
					{
						// null result 
						$tran_qty =  0;
					}
					ibase_free_result($Result); 
					unset($Result); 
					$tran_qty = 0; /* dont want to  adjust up the qty */
					//echo("qtyA ". $tran_qty);
				}
				else
				{
					// no result 
					$tran_qty =  0;
					//echo("no result2");
				}
			}
		}
		$tran_qty = 0; /* dont want to  adjust up the qty */
		//echo("qty is $tran_qty");
		$Query = "SELECT TRANSFER_PROD_TO_METHOD FROM CONTROL" ;
		// echo($Query); 
		$wk_tranprod_2_meth =  "";
		if (($Result = ibase_query($Link, $Query)))
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				$wk_tranprod_2_meth =  $Row[0];
				ibase_free_result($Result); 
				unset($Result); 
			}
		}
	}
	elseif ($tran_type == "SSN")
	{
		/* if allowed to change the ssn
                   check that the issn exists
		   if not
		   try adding 10000000 to the ssn
		*/
		if ($wk_use_plus_10m == "T")
		{
			$my_object = checkISSN10m($Link, $ssn_from);
		} else {
			$my_object = $ssn_from;
			$my_object = $ssn_to;
		}
		$my_source = 'SSBSSKSSS';
		$tran_tranclass = "A";
		$tran_qty = 1;
		$tran_qty = 0;
		if ($my_object <> "")
		{
			if ($transaction_type == "TRLI")
			{
				$transaction_type = "TRIL";
				/* if the transaction is TRIL and is successfull and none left */
			}
		}
		else
		{
			if ($transaction_type == "TRLI")
			{
				/* if the transaction is TRLI and is successfull */
				$wk_reset_locn = True;
			}
		}
				
	}
	else
	{
		$my_object = '';
		$my_source = 'SSSBSSSSS';
		$tran_tranclass = "A";
		$tran_qty = 0;
	}

	$my_sublocn = "";
	$my_ref = "transfer" ;
	if (isset($reason))
	{
		$my_ref .= " " . $reason;
		if (strlen($my_ref) > 40)
		{
			$my_ref = substr($my_ref, 0, 40);
		}
	}

// about to run transaction
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " transaction loop start");
	//commit
	ibase_commit($dbTran);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " Pre  Commit 1");
	$wk_mymessage = "";
	$wk_db_error = False;
	set_error_handler('errorHandler2');
	// need to be able to start here again if a dead lock
	$wk_loop = 1;
	$wk_db_error_count = 0;
	$wk_isok = "T";
	while ( ($wk_loop <= 1 ) and ($wk_db_error_count <= $tran_deadlock_limit ) ) :
	{
		$my_message = "";
		//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if (isset($company))
		{
			$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $my_product, $company);
		} else {
			$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		}
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = " ";
		}
		if ($wk_db_error )
		{
			$my_responsemessage = "Deadlock ";
		} else {
			if (($my_responsemessage == " ") or
			    ($my_responsemessage == ""))
			{
				if ( $transaction_type  == "")
				{
					// no transaction details
				} else {
					$my_responsemessage = "Processed successfully ";
				}
			}
		}
		if ($my_responsemessage == "Processed successfully ")
		{
			//$my_responsemessage = "OK";	
			$wk_dummy = "";
		} else {
			$wk_isok = "F";
		}
		$wk_mymessage .= $transaction_type. ":" . $my_responsemessage;
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . $transaction_type . "  stop ");
	} /* end of for loop */
	if ($wk_db_error)
	{
		$wk_db_error_count++;
	} else {
		$wk_loop++;
		$wk_db_error_count = 0;
	}
	endwhile;
	// =================================
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . $my_responsemessage );
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		// ok failed in transfer
		if ($tran_type == "PRODUCT" )
		{
			$wk_ErrorPos = strpos($my_responsemessage , "Not");
			if ($wk_ErrorPos === false) {
				// not found
				header("Location: Transfer_Menu.php?" . $my_message);
			} else {
				header("Location: GetProductFrom.php?locationfrom=" . urlencode($location_from) . "&productfrom=" . urlencode($product_from) . "&" . "havedata=y" . "&" .  $my_message);
			}
		}
		elseif ($tran_type == "SSN")
		{
			// go to get the to location
			//header("Location: GetSSNLocnTo.php");
			$wk_ErrorPos = strpos($my_responsemessage , "Not");
			if ($wk_ErrorPos === false) {
				// not found
				header("Location: Transfer_Menu.php?" . $my_message);
			} else {
				header("Location: GetSSNFrom.php?havedata=y&" . $my_message);
			}
		}
		else
		{
			// location transfer - an error
			$wk_ErrorPos = strpos($my_responsemessage , "Not");
			if ($wk_ErrorPos === false) {
				// not found
				header("Location: Transfer_Menu.php?" . $my_message);
			} else {
				header("Location: GetLocnTo.php?locationfrom=" . urlencode($location_from) . "&" . "havedata=y" . "&" .  $my_message);
			}
		}
		//commit
		ibase_commit($dbTran);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " Post Commit 1 Not Successful");
	
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
logtime2( $Link,  __LINE__ . ":" . __FUNCTION__ . " Post Commit 2 Successful");
	
	//close
	//ibase_close($Link);
	// ok done transfer to hh
	// need to beep for good response
	if ($tran_type == "PRODUCT" )
	{
		// go to get the to location
		if ($wk_tranprod_2_meth == "LOCNPROD")
		{
			//header("Location: GetLocnTo.php?havedata=y");
			header("Location: GetLocnTo.php?havedata=y" . "&" .  $my_message);
		} else {
			//header("Location: GetProductFrom.php?havedata=y");
			header("Location: GetProductFrom.php?havedata=y" . "&" .  $my_message);
		}
	}
	elseif ($tran_type == "SSN")
	{
		// check howmany  on device
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
		$wk_my_qty =  getQtyOnDevice($Link );
		// clear out to location
		$bdcs_cookie = getBDCScookie($Link, $tran_device, "transfer") . "|||||||||";
		list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to, $ssn_to) = explode("|", $bdcs_cookie);
		$bdcs_cookie = implode("|", array($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, "", $qty_to, "") );
		setBDCScookie($Link, $tran_device, "transfer", $bdcs_cookie);
		setBDCScookie($Link, $tran_device, "locationto", "") ;
		//commit
		ibase_commit($dbTran);
		// if qty > 0
		// go to get the to location
		// else go to get ssn from
		if ($wk_my_qty > 0)
		{
			header("Location: GetLocnTo.php?havedata=y" . "&" .  $my_message);
		} else {
			//header("Location: GetSSNFrom.php?havedata=y");
			header("Location: GetSSNFrom.php?havedata=y&" . $my_message);
		}
	}
	else
	{
		// go to get the to location
		//header("Location: GetLocnTo.php");
		//header("Location: GetLocnFrom.php");
		header("Location: GetLocnFrom.php?havedata=y" . "&" .  $my_message);
	}
}
?>
