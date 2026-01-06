<?php
header("Content-type: image/jpg");
require_once 'DB.php';
require 'db_access.php';

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Can't connect to DATABASE!");
	//exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:
//$Host = "192.168.61.131";
//$User = "sysdba";
//$Password = "masterkey";
//$DBName = "/data/asset.rf/wh.v39.gdb";
//$DBName = "$Host:d:/asset.rf/database/wh.v39.gdb";
$TableName = "control";

$Query = "SELECT company_logo from $TableName";
//$Query = "SELECT company_name1 from $TableName";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read rows!<BR>\n");
	exit();
}


// Fetch the results from the database.
while ($Row = ibase_fetch_row ($Result)) {
	$blobid = $Row[0] ;
	//echo $blobid;
	
	ibase_blob_echo($blobid);
	//$im = imagecreatefromwbmp("logo.bmp");	
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);

?>

