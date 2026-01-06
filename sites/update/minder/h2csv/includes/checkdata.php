<?php


$startposn = 0;

function checkForType($instring, $inre, $inprefix, $inmaxlen, $infixlen, $inprefixkey, $checkType) {
	/* # test whether a type input */
	global $message, $startposn;
	//if ( ereg($inre, $instring, $posn ))
	if ( preg_match ('/' . $inre . '/', $instring, $posn ))
	{ 
		//print("matched");
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
		//print("no prefix");
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
						$querytype = $checkType;
						$savestartposn = $startposn;
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

function checkForTypein($instring, $intype, $outtype = '', $indata ) {
	// indata is an array with the values to use
	if ($outtype == '')
	{
		$outtype = $intype;
	}
	$Query = "select symbology_prefix, max_length, data_expression, fixed_length, data_id "; 
	//print($Query);
	
	
	$rcount = 0;
	// Fetch the results from the database.
	{
		$dbprefix = $indata['symbology_prefix'];
		$dbmaxlen = $indata['max_length'];
		if ($indata['data_expression'] > "")
		{
			$dbre = $indata['data_expression'];
		}
		else
		{
			$dbre = ".+";
		}
		$dbfixlen = $indata['fixed_length'];
	}
	$dbprefixkey = explode(";", $dbprefix);
	//echo ($dbprefixkey);
	//echo ("return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype)");
	return checkForType($instring, $dbre, $dbprefix, $dbmaxlen, $dbfixlen, $dbprefixkey, $outtype);
}

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
