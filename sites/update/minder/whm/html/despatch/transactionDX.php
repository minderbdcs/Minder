<?php


require('logme.php');
/**
 * get params data_id starting passed code
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkStarts
 * @return array
 */
function getParamStarting ($Link, $wkStarts, $tran_device )
{
	if (isset($tran_device))
	{
		$wk_device_brand = getBDCScookie($Link, $tran_device, "CURRENT_BRAND" );
		$wk_device_model = getBDCScookie($Link, $tran_device, "CURRENT_MODEL" );
	} else {
		$wk_device_brand = "DEFAULT";
		$wk_device_model = "DEFAULT"; 
	}
	$wkResult = array();
	$Query = "select p1.data_id 
                  from param p1       
                  where p1.data_id starting '"  . $wkStarts . "'
	          and data_brand = '" . $wk_device_brand . "' 
	          and data_model = '" . $wk_device_model . "' 
                  and   (p1.data_expression is not null )
                  order by p1.data_id ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Param!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult[]  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	return $wkResult;
}
//  =====================================================================================================================

	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getdespatchexit.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "DSDX";
	
	$location = "          ";
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['infield']))
	{
		$wkinfield = $_POST['infield'];
	}
	if (isset($_GET['infield']))
	{
		$wkinfield = $_GET['infield'];
	}

	include "checkdata.php";

	if (isset($wkinfield))
	{
		if ($wkinfield <> "")
		{
			/*
	either data is one of  connote_param_id in carrier
	or 'CONNOTE' for the null connote_param_id
	so use a data_id starting 'CONNOTE'
	or an awb_connote_no so what data_id will these use ?
			*/
			$wkParams = array();
			$wkParams =  getParamStarting ($Link, "CONNOTE", $tran_device );
			$wk_ok = False ;
			$wk_in_data_type = "none";
			$wk_pack_consignment = "";
			foreach ($wkParams as $wk_param_id => $wk_param2)
			{
				$field_type = checkForTypein($wkinfield, $wk_param2 ); 
				if ($field_type == "none")
				{
				}
				else
				{
					$wk_infield_data = substr($wkinfield, $startposn);
					$wkinfield = $wk_infield_data;
					$wk_ok = True;
					$wk_in_data_type = $wk_param2;
				}
			
			}
			if ($wk_in_data_type == "none")
			{
				// not a connote 
				header("Location: getdespatchexit.php?message=" . urlencode("Not an Consignment or Pack"));
				exit();
			}
			// now try to  work out the connote if this was a pack label
			$Query = "SELECT PICK_DESPATCH.AWB_CONSIGNMENT_NO FROM PACK_ID JOIN PICK_DESPATCH ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID WHERE PACK_ID.DESPATCH_LABEL_NO  =  '$wk_infield' AND PICK_DESPATCH.DESPATCH_STATUS='DC'";
			//echo($Query);

			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Query Packs for Consignment!<BR>\n");
				exit();
			}

			while ( ($Row = ibase_fetch_row($Result)) ) {
				$wk_pack_consignment = $Row[0];
			}
			//release memory
			ibase_free_result($Result); 
			if ($wk_pack_consignment == "") {
				// now try to  work out the connote if this was a connote no
				$Query = "SELECT PICK_DESPATCH.AWB_CONSIGNMENT_NO FROM PICK_DESPATCH  WHERE PICK_DESPATCH.AWB_CONSIGNMENT_NO  =  '$wk_infield' and PICK_DESPATCH.DESPATCH_STATUS='DC' ";
				//echo($Query);
	
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to Query Packs for Consignment!<BR>\n");
					exit();
				}
	
				while ( ($Row = ibase_fetch_row($Result)) ) {
					$wk_pack_consignment = $Row[0];
				}
				//release memory
				ibase_free_result($Result); 
			}
			if ($wk_pack_consignment != "") {
				$wk_consignment = $wk_pack_consignment ;
			}
		}
	}
	// if consignment is empty use the infield
	$class = 'D';

	$my_object = '';
		
	$my_sublocn = '';
	$my_ref = $consignment;


	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = 0;

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
		header("Location: getdespatchexit.php?message=Unable+to+Add+Transaction!");
		exit();
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	/* must get the record id just created */
	$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	if (isset($location))
	{
		$Query .= substr($location,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location,2,strlen($location) - 2)."' AND OBJECT = '";
	}
	else
	if (isset($order))
	{
		$Query .= substr($order,0,2)."' AND LOCN_ID = '";
		$Query .= substr($order,2,strlen($order) - 2)."' AND OBJECT = '";
	}
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_CODE = '";
	$Query .= $tran_tranclass."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	//echo($Query); 
	$tran_recordid = NULL;
	if (!($Result = ibase_query($Link, $Query)))
	{
/*
		header("Location: getdespatchexit.php?message=Unable+to+Read+Transaction!");
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

	/* echo("got record id ".$tran_recordid); */
	/* process procedure */
	if (isset($tran_recordid))
	{
		/* must get the record id just updated */
		$Query = "SELECT ERROR_TEXT,COMPLETE,RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
		/* echo($Query); */
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: getdespatchexit.php?message=Unable+to+Query+Transaction!");
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
			//echo($tran_complete);
			//echo($tran_error);
			/*
			if ($tran_complete == "F")
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					header("Location: getdespatchexit.php?message=Unable+to+Update+Transaction!");
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
				header("Location: getdespatchexit.php?message=Unable+to+Update+Transaction!");
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

						header("Location: getdespatchexit.php?message=".urlencode($tran_error));
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
		
	
	//commit
	ibase_commit($dbTran);
	
	$Query = "SELECT count(*) FROM PACK_ID JOIN PICK_DESPATCH ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID WHERE PACK_ID.DESPATCH_LABEL_NO IS NULL  AND PICK_DESPATCH.AWB_CONSIGNMENT_NO = '$consignment'";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Query Packs for Consignment!<BR>\n");
		exit();
	}

	while ( ($Row = ibase_fetch_row($Result)) ) {
		$unscanned_qty = $Row[0];
	}
	//ibase_close($Link);

	//want to go to coninuing of exits 
	{
		header("Location: getdespatchexit.php");
	}
}
?>
