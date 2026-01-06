<?php
	if (isset($_COOKIE['BDCSData']))
	{
		list( $dummy, $location_from, $ssn_from , $orig_type ) = explode("|", $_COOKIE["BDCSData"]);
	}
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	if (!isset($type))
	{
		$type = $orig_type;
	}
	//print ("type:".$type." orig ".$orig_type);
	if (isset($_GET['seq']))
	{
		$seq = $_GET['seq'];
	}
	if (isset($_POST['seq']))
	{
		$seq = $_POST['seq'];
	}
	//print ("seq:".$seq);
	if (isset($_GET['note']))
	{
		$note = $_GET['note'];
	}
	if (isset($_POST['note']))
	{
		$note = $_POST['note'];
	}
	if (!isset($note))
	{
		$note = "";
	}
	// remove quotes from notes field
	$note = addslashes($note);
	//print($note);
	if (isset($_GET['answer']))
	{
		$answer = $_GET['answer'];
	}
	if (isset($_POST['answer']))
	{
		$answer = $_POST['answer'];
	}
	list( $last_question, $last_response, $last_branch ) = explode("-", $answer);

	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	//print(" in add_trans");
	$my_object = $ssn_from;
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "A";
	$tran_qty = 1;
	$transaction_type = "QANS";
	$my_sublocn = sprintf("%05d%05d", $last_question , $last_response);
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');

	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		//print  ("Location: GetSSNFrom.php?message="."Can t connect to DATABASE!");
		header("Location: GetSSNFrom.php?message="."Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// write transaction 
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
	$Query .= $note."',";
	$Query .= $tran_qty.",'F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";

	//print ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		//print  ("Location: GetSSNFrom.php?message="."Unable to Add Transaction!");
		header("Location: GetSSNFrom.php?message="."Unable+to+Add+Transaction!");
		exit();
	}
	// ibase_free_result($Result); 
	unset($Result); 
	$tran_error = NULL;
	$tran_complete = NULL;
	$tran_recordid = NULL;
	// must get the record id just created 
	$Query = "SELECT RECORD_ID,ERROR_TEXT,COMPLETE FROM TRANSACTIONS WHERE WH_ID ='";
	{
		$Query .= substr($location_from,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location_from,2,strlen($location_from) - 2)."' AND OBJECT = '";
	}
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_TYPE = '";
	$Query .= $transaction_type."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	//print($Query); 
	if (($Result = ibase_query($Link, $Query)))
	{
		if (($Row = ibase_fetch_row($Result)))
		{
			$tran_recordid =  $Row[0];
			$tran_error =  $Row[1];
			$tran_complete =  $Row[2];
			ibase_free_result($Result); 
			unset($Result); 
		}
	}

	if (isset($tran_recordid))
	{
		if (isset($tran_complete))
		{
			//print($tran_error);
			if ($tran_complete == "F")
			{
				$Query = "UPDATE TRANSACTIONS SET complete='T' WHERE RECORD_ID=".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					//commit
					ibase_commit($dbTran);
					//print ("Location: GetSSNFrom.php?message="."Unable to Update Transaction!");
					header("Location: GetSSNFrom.php?message="."Unable+to+Update+Transaction!");
					exit();
				}
				// ibase_free_result($Result); 
				unset($Result);

			}
		}
		else
		{
			$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			if (!($Result = ibase_query($Link, $Query)))
			{
				//commit
				ibase_commit($dbTran);
				//print ("Location: GetSSNFrom.php?message="."Unable to Update Transaction!");
				header("Location: GetSSNFrom.php?message="."Unable+to+Update+Transaction!");
				exit();
			}
			// ibase_free_result($Result); 
			unset($Result);
		}
		if (isset($tran_error))
		{
			if ($tran_error != "")
			{
				//commit
				ibase_commit($dbTran);
				//print ("Location: GetSSNFrom.php?message=".$tran_error);
				header("Location: GetSSNFrom.php?message=".urlencode($tran_error));
				exit();
			}
		}
		// ibase_free_result($Result); 
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

	//print("finished processi");

	// once transaction is written then
	if ($last_branch == "")
	{
		//print ("Location: GetSSNFrom.php?message="."End of Questions");
		//header("Location: GetSSNFrom.php?message="."End+of+Questions");
		header("Location: tran_QANE.php");
		exit();
	}
	else
	{
		//print ("Location: GetQuestion.php?seq=" . $seq . "&type=" . $type . "&answer=" . $answer);
		header("Location: GetQuestion.php?seq=".$seq."&type=".urlencode($type)."&answer=".$answer);
		exit();
	}
?>
