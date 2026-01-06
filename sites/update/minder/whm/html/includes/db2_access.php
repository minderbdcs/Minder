<?php
// Set the variables for the database access:
$Host = "localhost";
$User = "sysdba";
$Password = "masterkey";
if (OS_WINDOWS)
{
	$DBName = "$Host/c:/asset.rf/database/wh.v39.gdb";
	$DBName2 = "$Host:c:/asset.rf/database/wh.v39.gdb";
}
else
{
	$DBName = "$Host//data/asset.rf/wh.v39.gdb";
	$DBName2 = "$Host:/data/asset.rf/wh.v39.gdb";
}

$dsn = "ibase://$User:$Password@$DBName";
$rxml_limit = 10;
$rscr_limit = 5;
?>
