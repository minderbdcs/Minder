<?php
/* 29/04/2012
   change to get/set BDCS cookie to use php session variables
   except for those code values passed from the db 
*/
session_start();
function logme($Link, $user, $device, $message)
{
	// get who called me
	$wk_fromwhere = basename($_SERVER['PHP_SELF']) ;
	// check whether to log me
	$wk_dolog = "";
	$Query = "select description from options where group_code = 'LOG' and code = '" . $wk_fromwhere . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table options<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_dolog =  $Row[0];
	}
		
	if ($wk_dolog == 'T')
	{
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
		$Query = "insert into log(description) values ('".$wk_current_time." Page ".$wk_fromwhere." Dev ".$device." User ".$user." ".$wk_message."')";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to log message!<BR>\n");
		        //$log = fopen('/tmp/logme.log' , 'a');
		        $log = fopen('/data/tmp/logme.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			exit();
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
	}
	
}
/* end of function */
function getBDCScookie($Link, $device, $code)
{
	switch($code) {
	case "CURRENT_WH_ID" : /* in PKOL is read */
        case "CURRENT_GRN" : /* in GRND is set */
        case "picklocation" : /* in PKUA is read */
        case "receivecomplete" : /* in GRNV is read */
        case "uom" : /* in GRNV is read */
	case "CURRENT_PICK_DIR" : /* in PKOL is read */
	case "CURRENT_IP_ADDRESS" : /*  is read */
	case "TZ" : /*  is read */
	case "DBTZ" : /*  is read */
		$Query = "select description from session where device_id='" . $device . "' and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Sessions!<BR>\n");
			//$log = fopen('/tmp/logme.log' , 'a');
			$log = fopen('/data/tmp/logme.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
		}
		//release memory
		ibase_free_result($Result);
		//echo $wk_data;
		//logme($Link, "", $device, "getBDCScookie " . $code . "[" . $wk_data . "]");
		break;
	default :
		if (isset( $_SESSION[$code])) {
			$wk_data = $_SESSION[$code];
		} else {
			$wk_data = "";
		}
	} 
	return $wk_data;
}
/* end of function */
function setBDCScookie($Link, $device, $code, $data)
{

	switch($code) {
	case "CURRENT_WH_ID" : /* in PKOL is read */
        case "CURRENT_GRN" : /* in GRND is set */
        case "picklocation" : /* in PKUA is read */
        case "receivecomplete" : /* in GRNV is read */
        case "uom" : /* in GRNV is read */
	case "CURRENT_PICK_DIR" : /* in PKOL is read */
	case "CURRENT_IP_ADDRESS" : /*  is read */
	case "TZ" : /*  is read */
	case "DBTZ" : /*  is read */

		$Query = "select 1, description from session where device_id='" . $device . "' and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Sessions!<BR>\n");
			//exit();
		}
		$wk_data = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
		}
		$wk_data2 = str_replace("'", "`", $data);
		//release memory
		ibase_free_result($Result);
		//echo $wk_data;
		if ($wk_data == 1)
		{
			$Query = "update session set description = '" . $wk_data2 . "' where device_id='" . $device . "' and code = '" . $code . "'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				//echo("Unable to Update Sessions!<BR>\n");
			        //$log = fopen('/tmp/logme.log' , 'a');
			        $log = fopen('/data/tmp/logme.log' , 'a');
				fwrite($log, $Query);
				fwrite($log,"Unable to Update Sessions!\n");
				fclose($log);
				//exit();
			}
		}
		else
		{
			$Query = "insert into session(device_id, code, description) values ('" . $device . "','" . $code . "','" . $wk_data2 ."') ";
			if (!($Result = ibase_query($Link, $Query)))
			{
				//echo("Unable to Add Sessions!<BR>\n");
		        	//$log = fopen('/tmp/logme.log' , 'a');
			        $log = fopen('/data/tmp/logme.log' , 'a');
				fwrite($log, $Query);
				fwrite($log,"Unable to Write Sessions!\n");
				fclose($log);
				//exit();
			}
		}
		//release memory
		//ibase_free_result($Result);
		//logme($Link, "", $device, "setBDCScookie " . $code . "[" . $data . "]");
		break;
	default :
		$_SESSION[$code] = $data;
	} 
}
/* end of function */

?>
