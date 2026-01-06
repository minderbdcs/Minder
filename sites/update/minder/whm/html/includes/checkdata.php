<?php
require_once 'DB.php';
require 'db_access.php';

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	print("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$consignment = '';
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}

$Query = "select symbology_prefix, max_length, data_expression, fixed_length, data_id "; 
$Query .= "from param  ";
$Query .= " where data_id in ('BARCODE','LOCATION','PICK','PROD_INTERNAL','DEVICE','SERIAL') " ;
//print($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to Read Param!<BR>\n");
	exit();
}

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[4] == "BARCODE")
	{
		$ssnprefix = $Row[0];
		$ssnmaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$ssnre = $Row[2];
		}
		else
		{
			$ssnre = ".+";
		}
		$ssnfixlen = $Row[3];
	}
	if ($Row[4] == "LOCATION")
	{
		$locnprefix = $Row[0];
		$locnmaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$locnre = $Row[2];
		}
		else
		{
			$locnre = ".+";
		}
		$locnfixlen = $Row[3];
	}
	if ($Row[4] == "PICK")
	{
		$pickprefix = $Row[0];
		$pickmaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$pickre = $Row[2];
		}
		else
		{
			$pickre = ".+";
		}
		$pickfixlen = $Row[3];
	}
	if ($Row[4] == "PROD_INTERNAL")
	{
		$prodinternalprefix = $Row[0];
		$prodinternalmaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$prodinternalre = $Row[2];
		}
		else
		{
			$prodinternalre = ".+";
		}
		$prodinternalfixlen = $Row[3];
	}
	if ($Row[4] == "DEVICE")
	{
		$deviceprefix = $Row[0];
		$devicemaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$devicere = $Row[2];
		}
		else
		{
			$devicere = ".+";
		}
		$devicefixlen = $Row[3];
	}
	if ($Row[4] == "SERIAL")
	{
		$serialprefix = $Row[0];
		$serialmaxlen = $Row[1];
		if ($Row[2] > "")
		{
			$serialre = $Row[2];
		}
		else
		{
			$serialre = ".+";
		}
		$serialfixlen = $Row[3];
	}
}

$ssnprefixkey = explode(";", $ssnprefix);
$locnprefixkey = explode(";", $locnprefix);
$pickprefixkey = explode(";", $pickprefix);
$prodinternalprefixkey = explode(";", $prodinternalprefix);
$deviceprefixkey = explode(";", $deviceprefix);
$serialprefixkey = explode(";", $serialprefix);
$startposn = 0;

function checkForType($instring, $inre, $inprefix, $inmaxlen, $infixlen, $inprefixkey, $checkType) {
	/* # test whether a type input */
	global $message, $startposn;
	if ( ereg($inre, $instring, $posn ))
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
				if ( ereg($inre, $myinstr, $posn ))
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

function checkSsn($instring ) {
	global $ssnre, $ssnprefix, $ssnmaxlen, $ssnfixlen, $ssnprefixkey;
	return checkForType($instring, $ssnre, $ssnprefix, $ssnmaxlen, $ssnfixlen, $ssnprefixkey, "SSN");
}

function checkLocn($instring ) {
	global $locnre, $locnprefix, $locnmaxlen, $locnfixlen, $locnprefixkey;
	return checkForType($instring, $locnre, $locnprefix, $locnmaxlen, $locnfixlen, $locnprefixkey, "LOCATION");
}

function checkPick($instring ) {
	global $pickre, $pickprefix, $pickmaxlen, $pickfixlen, $pickprefixkey;
	return checkForType($instring, $pickre, $pickprefix, $pickmaxlen, $pickfixlen, $pickprefixkey, "PICK");
}

function checkProdInternal($instring ) {
	global $prodinternalre, $prodinternalprefix, $prodinternalmaxlen, $prodinternalfixlen, $prodinternalprefixkey;
	return checkForType($instring, $prodinternalre, $prodinternalprefix, $prodinternalmaxlen, $prodinternalfixlen, $prodinternalprefixkey, "PRODINTERNAL");
}

function checkDevice($instring ) {
	global $devicere, $deviceprefix, $devicemaxlen, $devicefixlen, $deviceprefixkey;
	return checkForType($instring, $devicere, $deviceprefix, $devicemaxlen, $devicefixlen, $deviceprefixkey, "DEVICE");
}

function checkSerial($instring ) {
	global $serialre, $serialprefix, $serialmaxlen, $serialfixlen, $serialprefixkey;
	return checkForType($instring, $serialre, $serialprefix, $serialmaxlen, $serialfixlen, $serialprefixkey, "SERIAL");
}

function checkfield() {
/* # check field for ssn */
	global $consignment, $type, $starts, $startposn;
	$type = checkSsn($consignment); 
	if ($type == "none")
	{
		$type = checkLocn($consignment); 
	}
	if ($type == "none")
	{
		$type = checkPick($consignment); 
	}
	if ($type == "none")
	{
		$type = checkProdInternal($consignment); 
	}
	if ($type == "none")
	{
		$type = checkDevice($consignment); 
	}
	if ($type == "none")
	{
		$type = checkSerial($consignment); 
	}
	$starts = $startposn;
}
