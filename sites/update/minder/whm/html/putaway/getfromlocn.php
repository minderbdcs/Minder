<?php
include "../login.inc";
echo("<html>\n");
echo("<head>\n");
include "viewport.php";
echo("</head>\n");

require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$Query = "select first " . $rxml_limit . " wh_id, locn_id, count(*) "; 
$Query .= "from issn  ";
$Query .= " where issn_status = 'PA'" ;
$Query .= " group by wh_id, locn_id" ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_ssns = 0;
$tot_locns = 0;
echo("<body>\n");
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"getfromssn.php\" method=\"post\" name=getlocn>\n");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>WH</TH>\n");
echo("<TH>FROM Locn</TH>\n");
echo("<TH>Qty SSNs</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	//$tot_ssns += $Row[2];
	//$tot_locns += 1;
	echo ("</TR>\n");
}

echo ("</TABLE>\n");

//release memory
ibase_free_result($Result);

$Query = "select count(distinct wh_id || locn_id) "; 
$Query .= "from issn  ";
$Query .= " where issn_status = 'PA'" ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$tot_locns = $Row[0];
}

echo("Total Locs <INPUT type=\"text\" readonly name=\"qtylocn\" size=\"4\" value=\"$tot_locns\" ><BR>");

//release memory
ibase_free_result($Result);

$Query = "select count(*) "; 
$Query .= "from issn  ";
$Query .= " where issn_status = 'PA'" ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$tot_ssns = $Row[0];
}

echo("Total SSNs <INPUT type=\"text\" readonly name=\"qtyssn\" size=\"4\" value=\"$tot_ssns\" ><BR>");

//echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\">");
echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\"");
echo(" onchange=\"document.getlocn.submit();\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Location to Start</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo("<TABLE BORDER=\"0\">\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo total
//echo("</FORM>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Send!' );
	whm2buttons('Send',"../mainmenu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
	document.getlocn.location.focus();
</script>
</body>
</html>
