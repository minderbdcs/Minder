<?php
if (isset($_COOKIE['BDCSData']))
{
	list( $dummy, $location_from, $ssn_from , $orig_type ) = explode("|", $_COOKIE["BDCSData"]);
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: GetSSNFrom.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	{
		$my_object = $ssn_from;
		$my_source = 'SSBSSKSSS';
		$tran_tranclass = "A";
		$tran_qty = 1;
		$tran_procedure = "PC_NIXX";
		$transaction_type = "NITP";
	}

	/* write transaction */
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	{
		$Query .= substr($location_from,0,2)."','";
		$Query .= substr($location_from,2,strlen($location_from) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $transaction_type."','";
	$Query .= $tran_tranclass."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $type."',";
	}
	$Query .= $tran_qty.",'F','','MASTER',0,'','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";

	//print ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: GetSSNFrom.php?message=Unable+to+Add+Transaction!");
		exit();
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	/* must get the record id just created */
	$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	{
		$Query .= substr($location_from,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location_from,2,strlen($location_from) - 2)."' AND OBJECT = '";
	}
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_CODE = '";
	$Query .= $tran_tranclass."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	$tran_recordid = NULL;
	/* print($Query); */
	if (!($Result = ibase_query($Link, $Query)))
	{
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
			header("Location: GetSSNFrom.php?message=Unable+to+Query+Transaction!");
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
			//print($tran_error);
			if ($tran_complete == "F")
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					header("Location: GetSSNFrom.php?message=Unable+to+Update+Transaction!");
					exit();
				}
				/* ibase_free_result($Result); */
				unset($Result);
			}
		}
		else
		{
			$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: GetSSNFrom.php?message=Unable+to+Update+Transaction!");
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
					header("Location: GetSSNFrom.php?message=".urlencode($tran_error));
					exit();
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
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	// ok done transfer to hh
	{
		// go to get the 1st question
		header("Location: GetQuestion.php?seq=-1&type=".urlencode($type));
	}
}
?>
