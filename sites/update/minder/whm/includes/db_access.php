<?php
// Set the variables for the database access:
$Host = "localhost";
$HostTest = "localhost";
  $mdrExists = False;
/* ========================================= */
  // expect in document root something like "/var/sites/sitename/html"
  // so the 4th entry is the sitename
  $mdrDocRoot = explode("/", $_SERVER['DOCUMENT_ROOT']);
  $mdrSitename = $mdrDocRoot[3];
  $mdrConfig = "/etc/Minder/" . $mdrSitename . "/Minder.ini";
  $mdrConfig = strtolower($mdrConfig);
  $mdrExists = False;
  if(file_exists($mdrConfig) ) {
  	$mdrExists = True;
	//echo "$mdrConfig found";
  } else {
	echo "$mdrConfig not found";
	exit();
  }
  $mdrLoopCnt = 1;
/* ========================================= */
  $mdr =  parse_ini_file($mdrConfig);
  $mdrDB = explode(":", $mdr['dsn.main']);
  $Host = $mdrDB[0];
  $DBAlias = $mdrDB[1];
  $mdrTestDB = explode(":", $mdr['dsn.test']);
  $HostTest = $mdrTestDB[0];
  $DBAliasTest = $mdrDB[1];
  $mdrInstanceName = $mdr['instance'];
  $mdrUser = $mdr['dsn.user'];
  $mdrPassword = $mdr['dsn.password'];
$User = "MINDER";
$Password = "mindeR";
  if (!empty($mdrUser))
  {
     $User = $mdrUser;
  }
  if (!empty($mdrPassword))
  {
     $Password = $mdrPassword;
  }

//$system_type = "WINDOWS";
$system_type = "LINUX";
//if (OS_WINDOWS)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
{
	$system_type = "WINDOWS";
}

if (isset($save_user_type))
{
        unset($save_user_type);
}
if (isset($UserType))
{
        $save_user_type = $UserType;
}
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
        if (isset($save_user_type))
        {
                $UserType = $save_user_type;
        }
}
else
{	
	if (!isset($UserType))
	{
		$UserType = "PR"; //production
	}
}
//if (OS_WINDOWS)
if ($system_type == "WINDOWS")
{
	if ($UserType == "PR")
	{
		$DBName = "$Host/d:/asset.rf/database/wh.v39.gdb";
		$DBName = "$Host/minder";
		$DBName = "$Host/$DBAlias";
		$DBName2 = "$Host:d:/asset.rf/database/wh.v39.gdb";
		$DBName2 = "$Host:minder";
		$DBName2 = "$Host:$DBAlias";
	}
	else
	{
		$DBName = "$Host/d:/asset.rf/database/test.v39.gdb";
		$DBName = "$Host/test";
		$DBName = "$HostTest/$DBAliasTest";
		$DBName2 = "$Host:d:/asset.rf/database/test.v39.gdb";
		$DBName2 = "$Host:test";
		$DBName2 = "$HostTest:$DBAliasTest";
/*
		$DBName = "$Host/d:/asset.rf/database/wh.v39.gdb";
		$DBName2 = "$Host:d:/asset.rf/database/wh.v39.gdb";
*/
	}
}
else
{
	if ($UserType == "PR")
	{
		$DBName = "$Host//data/asset.rf/wh.v39.gdb";
		$DBName = "$Host/minder";
		$DBName = "$Host/$DBAlias";
		$DBName2 = "$Host:/data/asset.rf/wh.v39.gdb";
		$DBName2 = "$Host:minder";
		$DBName2 = "$Host:$DBAlias";
	}
	else
	{
		$DBName = "$Host//data/asset.rf/test.v39.gdb";
		$DBName = "$Host/test";
		$DBName = "$HostTest/$DBAliasTest";
		$DBName2 = "$Host:/data/asset.rf/test.v39.gdb";
		$DBName2 = "$Host:test";
		$DBName2 = "$HostTest:$DBAliasTest";
	}
}

$dsn = "ibase://$User:$Password@$DBName";
$wkMyBW = "IE60";
if (stripos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6.5") !== false)  
{
	$wkMyBW = "IE65";
}
if (stripos($_SERVER['HTTP_USER_AGENT'] , "Windows Phone 6.5") !== false)  
{
	$wkMyBW = "IE65";
}
if (stripos($_SERVER['HTTP_USER_AGENT'] , "IEMobile 6.8") !== false)  
{
	$wkMyBW = "IE65";
}
if (stripos($_SERVER['HTTP_USER_AGENT'] , "Chrome") !== false) 
{
	$wkMyBW = "CHROME";
}
if (stripos($_SERVER['HTTP_USER_AGENT'] , "Mobile Safari") !== false) 
{
	$wkMyBW = "SAFARI";
}
if (stripos($_SERVER['HTTP_USER_AGENT'] , "NetFront") !== false) 
{
	$wkMyBW = "NETFRONT";
}
$rxml_limit = 10;
$rscr_limit = 5;
if ($wkMyBW == "IE60")
{
	$rimg_width = 80;
} elseif ($wkMyBW == "IE65")
{
	$rimg_width = 80;
} elseif ($wkMyBW == "SAFARI")
{
	$rimg_width = 70;
} elseif ($wkMyBW == "CHROME")
{
	$rimg_width = 60;
} elseif ($wkMyBW == "NETFRONT")
{
	$rimg_width = 40;
}
if (isset($_SESSION["IMG_WIDTH"]))
{
	$rimg_width = $_SESSION["IMG_WIDTH"] ;
} else {
	$_SESSION["IMG_WIDTH"] = $rimg_width;
}
//if (OS_WINDOWS)
if ($system_type == "WINDOWS")
{
	$printerPA = "d:/asset.rf/PA";
	$printerPB = "d:/asset.rf/PB";
	$printerPC = "d:/asset.rf/PC";
	$printerPD = "d:/asset.rf/PD";
}
else
{
	$printerPA = "/data/asset.rf/PA";
	$printerPB = "/data/asset.rf/PB";
	$printerPC = "/data/asset.rf/PC";
	$printerPD = "/data/asset.rf/PD";
}
//if (OS_WINDOWS)
if ($system_type == "WINDOWS")
{
	if ($UserType == "PR")
	{
		$ftpimport = "d:/sysdata/iis/default/ftproot/import";
	}
	else
	{
		$ftpimport = "d:/sysdata/iis/default/ftproot/test_import";
	}
}
else
{
	if ($UserType == "PR")
	{
		$ftpimport = "/sysdata/iis/default/ftproot/import";
	}
	else
	{
		$ftpimport = "/sysdata/iis/default/ftproot/test_import";
	}
}
$rsound_ok_repeats = 3;
$rsound_notok_repeats =  2;
?>
