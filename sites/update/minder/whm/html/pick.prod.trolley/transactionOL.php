<?php
session_start();

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKOL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include "logme.php";
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$ssn = '';
	$label_no = '';
	$order = '';
	$prod_no = '';
	$description = '';
	$uom = '';
	$order_qty = 0;
	$picked_qty = 0;
	$required_qty = 0;
	$scanned_ssn = '';
	
	$my_object = '';
	if (isset($_COOKIE['BDCSData']))
	{
		list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
	}
		

	$qty = $picked_qty;
	if ($ssn <> "")
	{
		$my_object = $scanned_ssn;
		$my_sublocn = $label_no;
	}
	else
	{
		// a product
		$my_object = $prod_no;
		$my_sublocn = "";
	}

	
	//$location is the device
	$my_ref = '';
	if (isset($_POST['reference']))
	{
		$my_ref = $_POST['reference'];
	}
	if (isset($_GET['reference']))
	{
		$my_ref = $_GET['reference'];
	}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_source = 'SSBSSKSSS';
	if ($ssn <> "")
	{
		$tran_tranclass = "B";
	}
	else
	{
		$tran_tranclass = "P";
	}

	$tran_qty = $qty;

	/* write transaction */
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $tran_type."','";
	$Query .= $tran_tranclass."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $my_ref."',";
	}
	$Query .= $tran_qty.",'F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";

	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: pick_Menu.php?message=Unable+to+Add+Transaction!");
		exit();
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	/* must get the record id just created */
	$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	{
		$Query .= substr($location,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location,2,strlen($location) - 2)."' AND OBJECT = '";
	}
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_CODE = '";
	$Query .= $tran_tranclass."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	//print($Query); 
	$tran_recordid = NULL;
	if (!($Result = ibase_query($Link, $Query)))
	{
/*
		header("Location: pick_Menu.php?message=Unable+to+Read+Transaction!");
		exit();
*/
		// processed ok
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$tran_recordid =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}

	/* print("got record id ".$tran_recordid); */
	/* process procedure */
	if (isset($tran_recordid))
	{
		/* must get the record id just updated */
		$Query = "SELECT ERROR_TEXT,COMPLETE,RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
		/* print($Query); */
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: pick_Menu.php?message=Unable+to+Query+Transaction!");
			exit();
		}
		$tran_error = NULL;
		$tran_complete = NULL;
		if (($Row = ibase_fetch_row($Result)))
		{
			$tran_error =  $Row[0];
			$tran_complete =  $Row[1];
		}
		//release memory
		ibase_free_result($Result); 
		unset($Result); 
		if (isset($tran_complete))
		{
			//print($tran_complete);
			//print($tran_error);
			/*
			if ($tran_complete == "F")
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					header("Location: pick_Menu.php?message=Unable+to+Update+Transaction!");
					exit();
				}
				/* ibase_free_result($Result); *-
				unset($Result);
			}
			*/
		}
		else
		{
			$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: pick_Menu.php?message=Unable+to+Update+Transaction!");
				exit();
			}
			/* ibase_free_result($Result); */
			unset($Result);
		}
		if (isset($tran_complete))
		{
			if ($tran_complete == "F")
			{
				if (isset($tran_error))
				{
					if ($tran_error != "")
					{

						header("Location: pick_Menu.php?message=".urlencode($tran_error));
						exit();
					}
				}
			}
		}
		/* ibase_free_result($Result); */
		unset($Result); 
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	/*
	if product was NOPROD
	must to a PKIL using the default desp locn from the control
	*/
	if (($my_object == "NOPROD") or
	   ($qty == 0)) /* must include zero picked lines here */
	{
		// now must do a pick despatch
		$Query = "select default_despatch_location from control";
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to Read Total!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_default_desp =  $Row[0];
		}
	
		//release memory
		ibase_free_result($Result);
		//commit
		ibase_commit($dbTran);

		header("Location: transactionIL.php?order=" . urlencode($prod_no) . "&ttype=P" . "&location=" . urlencode($wk_default_desp));
		exit();
		
	}
	$cookiedata = $location;
	setBDCScookie($Link, $tran_device, "picklocation", $cookiedata);
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_label_no = '" . $label_no . "'";
	$Query .= " and pick_line_status = 'PG'";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$continue_this_line = 0;
	$continue_cnt = 0;

	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_this_line =  $Row[0];
		$continue_cnt = 1;
	}
	
	//release memory
	ibase_free_result($Result);
	//commit
	ibase_commit($dbTran);
	if ($continue_cnt == 0)
	{
		$Query = "select first 1 1, pick_label_no from pick_item ";
		$Query .= " where pick_line_status in ('AL', 'PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//print($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to Read Pick Item!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$continue_cnt = 1;
			$continue_label = $Row[1];
		}
	
		//release memory
		ibase_free_result($Result);
	}

	$Query = "select first 1 1, pick_label_no from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	if ($ssn <> "")
	{
		$Query .= " and ssn_id = '" . $ssn . "'";
	}
	else
	{
		$Query .= " and prod_id = '" . $prod_no . "'";
	}
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$despatch_cnt = 0;
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  1;
		$despatch_label = $Row[1];
	}
	
	//release memory
	ibase_free_result($Result);
	
	//commit
	//ibase_commit($dbTran);
	//echo("continue line " . $continue_this_line . " cont " . $continue_cnt . " cont label " . $continue_label . " despatch " . $despatch_cnt . " label " . $despatch_label . "L");
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	//header("Location: transactionUA.php");
	//want to go to pick screen
	// if this current line is still pg then go to ssn
	if ($continue_this_line > 0)
	{
		header("Location: getfromlocn.php");

	}
	else
	{
		if ($despatch_cnt > 0)
		{
			if ($ssn <> "")
			{
				header("Location: gettolocn.php?order=" . urlencode($ssn) . "&ttype=I");
			}
			else
			{
				header("Location: gettolocn.php?order=" . urlencode($prod_no) . "&ttype=I");
			}
		}
		else
		{
			if ($continue_cnt > 0)
			{
				header("Location: getfromlocn.php");
			}	
			else
			{
				header("Location: pick_Menu.php");
			}	
		}
	}
}
?>
