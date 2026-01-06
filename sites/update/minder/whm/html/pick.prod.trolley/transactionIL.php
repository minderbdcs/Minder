<?php
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKIL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	$qty = 1;
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$my_object = '';
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$type='';
	$order='';
	$order_no='';
	$label='';
	if (isset($_POST['ttype']))
	{
		$type = $_POST['ttype'];
	}
	if (isset($_GET['ttype']))
	{
		$type = $_GET['ttype'];
	}
	$tran_tranclass = $type;
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	$my_object = $order;
	if (isset($_POST['order_no']))
	{
		$order_no = $_POST['order_no'];
	}
	if (isset($_GET['order_no']))
	{
		$order_no = $_GET['order_no'];
	}
	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['label']))
	{
		$label = $_POST['label'];
	}
	if (isset($_GET['label']))
	{
		$label = $_GET['label'];
	}
	if ($type == 'M')
	{
		$my_sublocn = $tran_device;
	}
	else
	{
		$my_sublocn = $label;
	}
	if (isset($_POST['qty']))
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty']))
	{
		$qty = $_GET['qty'];
	}
	// must check that location is empty
	// or only holds this product
	if ($type == 'P')
	{
		$Query = "select first 1 prod_id "; 
		$Query .= "from pick_item  ";
		$Query .= " where prod_id  <> '".$order."'";
		$Query .= " and pick_line_status = 'PL'";
		$Query .= " and despatch_location = '" ;
		$Query .= substr($location,2,strlen($location) - 2)."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			//echo("Unable to Read Picks!2<BR>\n");
			header("Location: gettolocn.php?message=" . urlencode("Unable to Read Picks2") . "&ttype=I&order=".urlencode($order));
			exit();
		}
	
		$got_prod = 0;
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$got_prod = 1;
			$wk_bad_prod = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if ($got_prod == 1)
		{
			// location has a different product
			header("Location: gettolocn.php?message=" . urlencode("Location " . $location . " already has " . $wk_bad_prod . " in it") . "&ttype=I&order=".urlencode($order));
			exit();
		}
		$Query = "select move_stat "; 
		$Query .= "from location  ";
		$Query .= " where wh_id = '";
		$Query .= substr($location,0,2)."'";
 		$Query .= " and locn_id = '" ;
		$Query .= substr($location,2,strlen($location) - 2)."'";
 		$Query .= " and (not move_stat is null)" ;
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: gettolocn.php?message=" . urlencode("Unable to Read Location") . "&ttype=I&order=".urlencode($order));
		}
		$got_locn = 0;
		$wk_moves = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$got_locn = 1;
			$wk_moves = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if (($got_locn == 1) and ($wk_moves <> "DS"))
		{
			// location has a non null move stat
			header("Location: gettolocn.php?message=" . urlencode("Location " . $location . " is Not a Trolley or Despatch Location ") . "&ttype=I&order=".urlencode($order));
			exit();
		}
	
	}
	$tran_qty = $qty;

	/* write transaction */
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";	}
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

	//print($Query);
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
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$despatch_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
*/
	
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_line_status in ('AL','PG')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	$continue_cnt = 0;
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_cnt =  $Row[0];
	}
	
	//release memory
	ibase_free_result($Result);
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	if ($continue_cnt > 0)
	{
		header("Location: getfromlocn.php");
	}
	else
	{
		header("Location: pick_Menu.php");
	}
}
?>
