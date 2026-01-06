<?php
// Set the variables for the database access:
$Host = "192.168.61.107";
$User = "sa";
$Password = "bdcs";

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
if (OS_WINDOWS)
{
	if ($UserType == "PR")
	{
		$DBName = "$Host/minder";
		$DBName2 = "$Host:d:/asset.rf/database/wh.v39.gdb";
	}
	else
	{
		$DBName = "$Host/minder";
		$DBName2 = "$Host:d:/asset.rf/database/test.v39.gdb";
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

$dsn = "mssql://$User:$Password@$DBName";
$rxml_limit = 10;
$rscr_limit = 5;
if (OS_WINDOWS)
{
	$printerPA = "d:/asset.rf/PA";
	$printerPB = "d:/asset.rf/PB";
	$printerPC = "d:/asset.rf/PC";
}
else
{
	$printerPA = "/data/asset.rf/PA";
	$printerPB = "/data/asset.rf/PB";
	$printerPC = "/data/asset.rf/PC";
}
if (OS_WINDOWS)
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
?>
