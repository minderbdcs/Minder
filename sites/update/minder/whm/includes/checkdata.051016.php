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
function checkForType($instring, $inre, $inprefix, $inmaxlen, $infixlen, $inprefixkey, $checkType) {
	/* # test whether a type input */
	global $message, $startposn;
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
		/* # type prefix exists terminated by ";"s */
		for ($myidx = 0; $myidx < (count($inprefixkey) -1); $myidx++)
		{
			$mykeystr = $inprefixkey[$myidx];
			if ($mykeystr == "")
			{
				$mystartposn = true;
			}
			else
			{
				$mystartposn = strpos($instring,$mykeystr); 
				if ($mystartposn === false)
				{
					//print("doesnt start");
					$mystartposn = false;
				}
				else
				{
					//print("does start");
					if ($mystartposn == 0) 
					{
						//print("posn 0");
						$mystartposn = true;
					}
					else
					{
						//print("posn gt 0");
						$mystartposn = false;
					}
				}
			}
			if ($mystartposn)
			{
				/* # starts with prefix */
				$startposn = strlen($mykeystr);
				$mylen = strlen($instring) - $startposn;
				$myinstr = substr($instring,$startposn,$mylen);
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
	//echo ($dbprefix);
	$dbprefixkey = explode(";", $dbprefix);
	//echo ($dbprefixkey);
	//echo("dbprefix:");
	//var_dump ($dbprefixkey);
	//echo("<br>");
	//echo ("return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype)");
	return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype);
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
