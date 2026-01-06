<?php
function dotransaction($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $wk_docommit='Y', $my_prod_id='', $my_company_id='', $my_order_no='', $my_order_type='', $my_order_sub_type='', $my_tran_class='')
{
	global $Link, $dbTran;
	$my_message = "";
	/* write transaction */
	//if (($my_prod_id != '') or ($my_company_id != '')) {
	if (($my_prod_id != '') or ($my_company_id != '') or
	    ($my_order_no != '') or ($my_order_type != '') or 
	    ($my_order_sub_type != '') or ($my_tran_class != '') ) {
		$tran_version = 'V6';
		$Query = "EXECUTE PROCEDURE ADD_TRAN_V6('";
	} else {
		$tran_version = 'V3';
		$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	}
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $tran_type."','";
	$Query .= $tran_tranclass."','";
	//$tran_trandate = date("Y-M-d H:i:s");
	$tran_trandate = gmdate("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $my_ref."','";
	}
	$Query .= $tran_qty."','F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	//$Query .= $tran_device."')";
	if ($tran_version == "V6") {
		$Query .= $tran_device."','";
		$Query .= $my_prod_id."','";
		//$Query .= $my_company_id."')";
		$Query .= $my_company_id."','";
		$Query .= $my_order_no."','";
		$Query .= $my_order_type."','";
		$Query .= $my_order_sub_type."','";
		$Query .= $my_tran_class."')";
	} else {
		$Query .= $tran_device."')";
	}

	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		//$my_message = "message=Unable+to+Add+Transaction!" ;
		$my_message = "message=" . urlencode("Unable to Add Transaction!") ;
		$wk_db_error_msg = ibase_errmsg();
                $my_message = $my_message . urlencode($wk_db_error_msg);

		return $my_message;
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	/* must get the record id just created */
	if ($my_tran_class == '')
	{
		$wk_table = "TRANSACTIONS";
		$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	} else {
		$wk_table = "TRANSACTIONS_" . $my_tran_class;
		$Query = "SELECT RECORD_ID FROM " . $wk_table . " WHERE WH_ID ='";
	}
	//if (isset($location))
	{
		$Query .= substr($location,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location,2,strlen($location) - 2)."' AND OBJECT = '";
	}
/*
	else
	if (isset($order))
	{
		$Query .= substr($order,0,2)."' AND LOCN_ID = '";
		$Query .= substr($order,2,strlen($order) - 2)."' AND OBJECT = '";
	}
*/
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_TYPE = '";
	$Query .= $tran_type."' AND TRN_CODE = '";
	$Query .= $tran_tranclass."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	if ($tran_version == "V6") {
		//$Query .= " AND PROD_ID = '" . $my_prod_id . "' AND COMPANY_ID = '" . $my_company_id . "'";
		$Query .= " AND PROD_ID = '" . $my_prod_id . "' AND COMPANY_ID = '" . $my_company_id . "' AND ORDER_NO = '" . $my_order_no . "' AND ORDER_TYPE = '" . $my_order_type . "' AND ORDER_SUB_TYPE = '" . $my_order_sub_type . "'" ;
	}
	//echo($Query); 
	$tran_recordid = NULL;
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

	/* echo("got record id ".$tran_recordid); */
	/* process procedure */
	if (isset($tran_recordid))
	{
		/* must get the record id just updated */
		if ($my_tran_class == '')
		{
			$Query = "SELECT ERROR_TEXT,COMPLETE,RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
		} else {
			$Query = "SELECT ERROR_TEXT,COMPLETE,RECORD_ID FROM " . $wk_table . " WHERE RECORD_ID = ".$tran_recordid;
		}
		/* echo($Query); */
		if (!($Result = ibase_query($Link, $Query)))
		{
			$my_message = "message=Unable+to+Query+Transaction!" ;
			return $my_message;
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
		}
		else
		{
			if ($my_tran_class == '')
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			} else {
				$Query = "UPDATE " . $wk_table . " SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			}
			//echo($Query); 
			if (!($Result = ibase_query($Link, $Query)))
			{
				$my_message = "message=Unable+to+Update+Transaction!" ;
				return $my_message;
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

						$my_message = "message=".urlencode($tran_error) ;
						return $my_message;
					}
				}
			}
		}
		/* ibase_free_result($Result); */
		unset($Result); 
	}
	if ($wk_docommit == 'Y')
	{
		//echo "commited";
		//commit
		ibase_commit($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}
	return $my_message;
	// end of function
}

function dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $my_prod_id='', $my_company_id='', $my_order_no='', $my_order_type='', $my_order_sub_type='', $my_tran_class='')
{
	global $Link, $dbTran;
	$my_message = "";
	/* write transaction */
	//if (($my_prod_id != '') or ($my_company_id != '')) {
	if (($my_prod_id != '') or ($my_company_id != '') or
	    ($my_order_no != '') or ($my_order_type != '') or 
	    ($my_order_sub_type != '') or ($my_tran_class != '') ) {
		$tran_version = 'V6';
		$Query = "SELECT RESPONSE_TEXT FROM ADD_TRAN_RESPONSE_V6('";
	} else {
		$tran_version = 'V3';
		$Query = "SELECT RESPONSE_TEXT FROM ADD_TRAN_RESPONSE('";
	}
	//$Query = "SELECT RESPONSE_TEXT FROM ADD_TRAN_RESPONSE('";
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $tran_type."','";
	$Query .= $tran_tranclass."','";
	//$tran_trandate = date("Y-M-d H:i:s");
	$tran_trandate = gmdate("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $my_ref."','";
	}
	$Query .= $tran_qty."','F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	if ($tran_version == "V6") {
		$Query .= $tran_device."','";
		$Query .= $my_prod_id."','";
		//$Query .= $my_company_id."')";
		$Query .= $my_company_id."','";
		$Query .= $my_order_no."','";
		$Query .= $my_order_type."','";
		$Query .= $my_order_sub_type."','";
		$Query .= $my_tran_class."')";
	} else {
		$Query .= $tran_device."')";
	}

	if (!($Result = ibase_query($Link, $Query)))
	{
		$my_message = "message=Unable+to+Add+Transaction!" ;
		return $my_message;
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$tran_response =  $Row[0];
		$my_message = "message=".urlencode($tran_response) ;
		return $my_message;
		ibase_free_result($Result); 
		unset($Result); 
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	return $my_message;
	// end of function
}

?>
