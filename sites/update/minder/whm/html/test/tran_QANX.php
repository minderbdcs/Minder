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

	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	//print(" in add_trans");
	$my_object = $ssn_from;
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = "A";
	$tran_qty = 1;
	$transaction_type = "QANX";
	$my_sublocn = "";
	$my_ref = "undo test result";
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
	$Query .= $my_ref."',";
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

	$Query = "SELECT QUESTION_ID, ANSWER_ID FROM SSN WHERE SSN_ID = '$ssn_from'";
	if (($Result = ibase_query($Link, $Query)))
	{
		if (($Row = ibase_fetch_row($Result)))
		{
			$last_question =  $Row[0];
			$last_response =  $Row[1];
			ibase_free_result($Result); 
			unset($Result); 
		}
	}
	if (isset($last_response))
	{
		if ($last_response > 0)
		{
			$Query = "SELECT BRANCH_TO FROM VALID_RESPONSES WHERE RESPONSE_ID = $last_response";
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$last_branch =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
		}
		else
		{
			unset($last_response);
		}
	}
	if (isset($last_question))
	{
		if ($last_question > 0)
		{
			$Query = "SELECT SEQUENCE FROM TEST_QUESTIONS WHERE QUESTION_ID = $last_question";
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$seq =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
		}
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
	if (!isset($seq))
	{
		$seq = -1;
	}
	if (isset($last_response))
	{
		$answer = $last_question . "-" . $last_response . "-" . $last_branch;
		header("Location: GetQuestion.php?seq=".$seq."&type=".urlencode($type)."&answer=".$answer);
	}
	else
	{
		header("Location: GetQuestion.php?seq=-1&type=".urlencode($type));
	}
	exit();
?>
