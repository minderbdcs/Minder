<html>
<head>
<title>Retrieving Data from a Database</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

// Set the variables for the database access:
//$Host = "localhost";
//$User = "sysdba";
//$Password = "masterkey";
//$DBName = "localhost:/data/asset.rf/wh.v39.gdb";
$TableName = "ssn";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	print("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "SELECT * from ".$TableName;
// Create a table.
print ("<TABLE BORDER=\"1\">\n");

if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query table!<BR>\n");
	exit();
}

// print headers
print ("<TR>\n");
for ($i=0; $i<ibase_num_fields($Result); $i++)
{
	$info = ibase_field_info($Result, $i);
	print("<TH>{$info["name"]}</TH>\n");
}
print ("</TR>\n");

// Fetch the results from the database.
while ($Row = ibase_fetch_row($Result)) {
 	print ("<TR>\n");
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
 		print ("<TD>$Row[$i]</TD>\n");
	}
 	print ("</TR>\n");
}
print ("</TABLE>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</body>
</html>

