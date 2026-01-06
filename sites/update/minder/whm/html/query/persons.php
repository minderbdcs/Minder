<?php
include "../login.inc";
?>
<html>
<head>
<title>Retrieving Persons</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_POST['person'])) 
{
	$lastperson = $_POST["person"];
}
else
if (isset($_GET['person'])) 
{
	$lastperson = $_GET["person"];
}
else
{
	$lastperson  = "";
}
$rcount = 0;
$Link = DB::connect($dsn,true);
if (DB::isError($Link))
{
	echo("Unable to Connect!<BR>\n");
	echo($Link->getMessage());
	exit();
}
$TableName = "person";
//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
$Query = "SELECT PERSON_ID,FIRST_NAME,LAST_NAME,EMAIL FROM PERSON WHERE PERSON_ID >='".$lastperson."'";
//echo($Query);
$Result = $Link->query($Query);
if (DB::isError($Result))
{
	echo("Unable to query persons!<BR>\n");
	echo($Result->getMessage());
	exit();
}
// view a table.
echo ("<TABLE BORDER=1 WIDTH=\"75%\" CELLSPACING=2 CELLPADDING=2 ALIGN=LEFT>\n");
echo ("<TR ALIGN=LEFT VALIGN=TOP>\n");
echo("<TH>Name</TH>\n");
echo("<TH>Email Address</TH>\n");
echo("<TH>Id</TH>\n");
echo ("</TR>\n");

// Fetch the results from the database.
while ( ($Row = $Result->fetchRow()) && ($rcount < 5) ) {
	$lastperson = $Row[0];
 	echo ("<TR ALIGN=LEFT VALIGN=TOP>\n");
 	echo ("<TD ALIGN=LEFT VALIGN=TOP>$Row[1] $Row[2]</TD>\n");
 	echo ("<TD ALIGN=LEFT VALIGN=TOP>$Row[3]</TD>\n");
 	echo ("<TD ALIGN=LEFT VALIGN=TOP>$Row[0]</TD>\n");
 	echo ("</TR>\n");
	$rcount++;
}
echo ("</TABLE>\n");
//release memory
$Result->free();
//commit
$Link->commit();
//close
$Link->disconnect();

echo (" <BR>");
echo (" <FORM action=\"persons.php\" method=\"post\" name=showperson>");
echo ("<INPUT type=\"hidden\" name=\"person\" value = \"".$lastperson."\"> ");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"more\" value=\"More!\">\n");
	echo("</FORM>\n");
	//echo("<FORM action=\"../query/query.php\" method=\"post\" name=goback>\n");
	echo("<FORM action=\"./query.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo ("<TABLE BORDER=\"0\" ALIGN=LEFT>\n");
	//whm2buttons('More', '../query/query.php' );
	whm2buttons('More',"../query/query.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"more.gif");
/*
	echo("<BUTTON name=\"more\" value=\"More!\" type=\"submit\">\n");
	echo("More<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../query/query.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</body>
</html>
