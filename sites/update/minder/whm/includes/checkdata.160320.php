<?php
require_once 'DB.php';
/* require_once 'db_access.php'; */
include 'db_access.php';
require_once 'logme.php'; 

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$startposn = 0;

/* ******************************************************* */

function checklogtime2( $Link,  $message)
{
	$Query = "";
	$log = fopen('/data/tmp/check.log' , 'a');
		$wk_current_time = "";
		$wk_current_time = date('Y-m-d h:i:s');
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

	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}

function checkForType($instring, $inre, $inprefix, $inmaxlen, $infixlen, $inprefixkey, $checkType) {
	/* # test whether a type input */
	//global $message, $startposn;
	global $message, $startposn, $Link;
	//if ( ereg($inre, $instring, $posn ))
	if ( preg_match ('/' . $inre . '/', $instring, $posn ))
	{ 
		//echo("matched");
	}
	else
	{ 
		$posn[0] = "";
		//print("not matched1");
	}
	$querytype = "none";
	$posnidx = 0;
	$mykeystr = "";
	$myinstr = "";
	$mylen = 0;
  	$message = $posn[0];
	if ($inprefix == "")
	{
		//echo("no prefix");
		if ((strlen($instring) == $inmaxlen) &&
		    ($infixlen == "T") &&
		    ($posn[0] == $instring))
		{
			$querytype = $checkType;
			$savestartposn = 0;
		}
		else
		{
			if ((strlen($instring) <= $inmaxlen) &&
		    	    ($posn[0] == $instring) &&
		    	    (($infixlen == "F") ||
		     	     ($infixlen == "")))
			{
				$querytype = $checkType;
				$savestartposn = 0;
			}
		}
	}	
	else
	{
		checklogtime2( $Link,  "have a prefix list");
		/* # type prefix exists terminated by ";"s */
		for ($myidx = 0; $myidx < (count($inprefixkey) -1); $myidx++)
		{
			checklogtime2( $Link,  "id:" . $myidx);
			$mykeystr = $inprefixkey[$myidx];
			checklogtime2( $Link,  $mykeystr);
			if ($mykeystr == "")
			{
				$mystartposn = true;
				checklogtime2( $Link,  "start posn true prefix empty");
			}
			else
			{
				$mystartposn = strpos($instring,$mykeystr); 
				checklogtime2( $Link,  "start posn " . $mystartposn);
				if ($mystartposn === false)
				{
					//print("doesnt start");
					$mystartposn = false;
					checklogtime2( $Link,  "dosnt start " );
				}
				else
				{
					//print("does start");
					checklogtime2( $Link,  "dos start " );
					if ($mystartposn == 0) 
					{
						//print("posn 0");
						$mystartposn = true;
						checklogtime2( $Link,  "but index 0 " );
					}
					else
					{
						//print("posn gt 0");
						$mystartposn = false;
						checklogtime2( $Link,  "index gt 0 set start false " );
					}
				}
			}
			if ($mystartposn)
			{
				/* # starts with prefix */
				checklogtime2( $Link,  "start with prefix " );
				$wk_list = var_export ($posn, true);
				checklogtime2 ($Link, $wk_list);

				$startposn = strlen($mykeystr);
				$mylen = strlen($instring) - $startposn;
				$myinstr = substr($instring,$startposn,$mylen);
				checklogtime2( $Link,  "startposn:" . $startposn );
				checklogtime2( $Link,  "mylen:" . $mylen );
				checklogtime2( $Link,  "myinstr:" . $myinstr );
				checklogtime2( $Link,  "inmaxlen:" . $inmaxlen );
				checklogtime2( $Link,  "infixlen:" . $infixlen );
				//if ( ereg($inre, $myinstr, $posn ))
				if ( preg_match('/' . $inre . '/', $myinstr, $posn ))
				{ 
					//print("matched");
				}
				else
				{ 
					$posn[0] = "";
					//print("not matched1");
				}
  				$message = $message + " " +  $posn[0];
				$wk_list = var_export ($posn, true);
				checklogtime2 ($Link, "posn:" . $wk_list);
				if (($mylen == $inmaxlen) &&
		    		    ($infixlen == "T") &&
		    		    ($posn[0] == $myinstr))
				{
					$querytype = $checkType;
					$savestartposn = $startposn;
				}
				else
				{
					if (($mylen <= $inmaxlen) &&
		    	    		    ($posn[0] == $myinstr) &&
		    	    		    (($infixlen == "F") ||
		     	     		    ($infixlen == "")))
					{
						if ($querytype == "none")
						{
							$querytype = $checkType;
							$savestartposn = $startposn;
						}
					}
				}
			}
		}
	}
	if (isset($savestartposn))
	{
		$startposn = $savestartposn;
	}
  	return $querytype;
}

/* ******************************************************* */
function checkForTypein($instring, $intype, $outtype = '' ) {
	global $Link;
	if ($outtype == '')
	{
		$outtype = $intype;
	}
	if (isset($_COOKIE["LoginUser"]))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	} else {
		list($tran_user, $tran_device) = explode("|", $_SESSION["LoginUser"]);
	}
	$Query = "select symbology_prefix, max_length, data_expression, fixed_length, data_id "; 
	$Query .= "from param  ";
	$Query .= " where data_id = '" . $intype . "' " ;
	if (isset($tran_device))
	{
		$wk_device_brand = getBDCScookie($Link, $tran_device, "CURRENT_BRAND" );
		$wk_device_model = getBDCScookie($Link, $tran_device, "CURRENT_MODEL" );
		if (!isset($wk_device_brand))
		{
			$wk_device_brand = "DEFAULT";
		}
		if (!isset($wk_device_model))
		{
			$wk_device_model = "DEFAULT";
		}
		if ($wk_device_brand == "")
		{
			$wk_device_brand = "DEFAULT";
		}
		if ($wk_device_model == "")
		{
			$wk_device_model = "DEFAULT";
		}
	} else {
		$wk_device_brand = "DEFAULT";
		$wk_device_model = "DEFAULT"; 
	}
	$Query .= " and data_brand = '" . $wk_device_brand . "' ";
	$Query .= " and data_model = '" . $wk_device_model . "' ";
	//print($Query);
	checklogtime2( $Link,  $Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Param!<BR>\n");
		exit();
	}
	
	$rcount = 0;
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[4] == $intype)
		{
			$dbprefix = $Row[0];
			$dbmaxlen = $Row[1];
			if ($Row[2] > "")
			{
				$dbre = $Row[2];
			}
			else
			{
				$dbre = ".+";
			}
			$dbfixlen = $Row[3];
		}
	}
	checklogtime2( $Link,  $instring);
	checklogtime2 ($Link, $dbre);
	checklogtime2 ($Link, $dbprefix);
	$dbprefixkey = explode(";", $dbprefix);
	//echo ($dbprefixkey);
	//echo("dbprefix:");
	$wk_list = var_export ($dbprefixkey, true);
	checklogtime2 ($Link, $wk_list);
	//echo("<br>");
	//echo ("return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype)");
	checklogtime2 ($Link, " checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype)");
	//return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype);
	$wk_result =  checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype);
	checklogtime2 ($Link, $wk_result);
	return $wk_result;
}

/* ******************************************************* */
function checkfield($infield) {
/* # check field for ssn */
	global $field_type, $field_starts, $startposn;
	$field_type = checkForTypein($infield, 'BARCODE', 'SSN'); 
	if ($field_type == "none")
	{
		$field_type = checkForTypein($infield, 'LOCATION'); 
	}
	if ($field_type == "none")
	{
		$field_type = checkForTypein($infield, 'PICK'); 
	}
	if ($field_type == "none")
	{
		$field_type = checkForTypein($infield, 'PROD_INTERNAL', 'PRODINTERNAL'); 
	}
	if ($field_type == "none")
	{
		$field_type = checkForTypein($infield, 'DEVICE'); 
	}
	if ($field_type == "none")
	{
		$field_type = checkForTypein($infield, 'SERIAL'); 
	}
	$field_starts = $startposn;
}
/* ******************************************************* */

/* **** want to use the params data_type_id rather than data_id **** */
function getDataTypesin($intype ) {
	global $Link;
	$outtype = array();
	if (isset($_COOKIE["LoginUser"]))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	} else {
		list($tran_user, $tran_device) = explode("|", $_SESSION["LoginUser"]);
	}

	$Query = "select data_id "; 
	$Query .= "from param  ";
	$Query .= " where data_type_id = '" . $intype . "' " ;
	if (isset($tran_device))
	{
		$wk_device_brand = getBDCScookie($Link, $tran_device, "CURRENT_BRAND" );
		$wk_device_model = getBDCScookie($Link, $tran_device, "CURRENT_MODEL" );
	} else {
		$wk_device_brand = "DEFAULT";
		$wk_device_model = "DEFAULT"; 
	}
	$Query .= " and data_brand = '" . $wk_device_brand . "' ";
	$Query .= " and data_model = '" . $wk_device_model . "' ";
	//print($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Param!<BR>\n");
		exit();
	}
	
	$rcount = 0;
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$dbtype = $Row[0];
		$outtype [] = $dbtype;
	}
	return $outtype;
}

/* ******************************************************* */
function checkfieldstype($infield,$intype) {
/* # check field for ssn */
	global $field_type, $field_starts, $startposn;
	$intypes = array();
	$intypes = getDataTypesin($intype);
	$field_type = "none";
	foreach ($intypes as $Key_types => $Value_types) 
	{
		$field_type = checkForTypein($infield, $Value_types); 
		if ($field_type != "none")
		{
			break;
		}
	}
	$field_starts = $startposn;
}
/* ******************************************************* */
