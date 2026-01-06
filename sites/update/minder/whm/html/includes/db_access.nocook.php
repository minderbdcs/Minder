<?php
// Set the variables for the database access:
$Host = "localhost";
$User = "sysdba";
$Password = "masterkey";

if (isset($save_user_type))
{
        unset($save_user_type);
}
if (isset($UserType))
{
        $save_user_type = $UserType;
}
if (isset($_GET['SaveUser']))
{
	$SaveUser = $_GET['SaveUser'];
}
if (isset($_POST['SaveUser']))
{
	$SaveUser = $_POST['SaveUser'];
}
//if (isset($_COOKIE['SaveUser']))
if (isset($SaveUser))
{
	//list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
	list($UserName, $DBDevice,$UserType) = explode("|", $SaveUser);
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
if (OS_WINDOWS)
{
	if ($UserType == "PR")
	{
		$DBName = "$Host/c:/asset.rf/database/wh.v39.gdb";
		$DBName2 = "$Host:c:/asset.rf/database/wh.v39.gdb";
	}
	else
	{
		$DBName = "$Host/c:/asset.rf/database/test.v39.gdb";
		$DBName2 = "$Host:c:/asset.rf/database/test.v39.gdb";
	}
}
else
{
	if ($UserType == "PR")
	{
		$DBName = "$Host//data/asset.rf/wh.v39.gdb";
		$DBName2 = "$Host:/data/asset.rf/wh.v39.gdb";
	}
	else
	{
		$DBName = "$Host//data/asset.rf/test.v39.gdb";
		$DBName2 = "$Host:/data/asset.rf/test.v39.gdb";
	}
}

$dsn = "ibase://$User:$Password@$DBName";
$rxml_limit = 10;
$rscr_limit = 5;
if (OS_WINDOWS)
{
	$printerPA = "c:/asset.rf/PA";
	$printerPB = "c:/asset.rf/PB";
	$printerPC = "c:/asset.rf/PC";
	$printerPD = "c:/asset.rf/PD";
}
else
{
	$printerPA = "/data/asset.rf/PA";
	$printerPB = "/data/asset.rf/PB";
	$printerPC = "/data/asset.rf/PC";
	$printerPD = "/data/asset.rf/PD";
}
if (OS_WINDOWS)
{
	if ($UserType == "PR")
	{
		$ftpimport = "c:/sysdata/iis/default/ftproot/import";
	}
	else
	{
		$ftpimport = "c:/sysdata/iis/default/ftproot/test_import";
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
?>
