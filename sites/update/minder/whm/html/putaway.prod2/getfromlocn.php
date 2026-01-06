<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
//include "checkdatajs.php";

?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Putaway get Location From</title>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="getfromlocn.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="getfromlocn.css">');
}
?>
</head>
<body>

<?php
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

$message  = '';
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
/*
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
*/
  {
	return true;
  }
}
</script>

<?php
	
$Query = "select first " . $rxml_limit . " wh_id, locn_id, count(*) "; 
$Query .= "from issn  ";
$Query .= " where issn_status in ('PA','TS')" ;
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

echo("<FONT size=\"2\">\n");
echo("<form action=\"getfromssn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">\n");
echo ("<div id=\"locns3\">\n");
if ($message !== "") {
//echo ("<div id=\"message3\">\n");
	echo("<INPUT type=\"text\" name=\"message\" readonly size=\"20\" value=\"$message\" class=\"message\" >");
//echo ("</div>\n");
}

// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
echo("<th>WH</th>\n");
echo("<th>FROM Locn</th>\n");
echo("<th>Qty SSNs</th>\n");
echo ("</tr>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<tr>\n");
	echo("<td>".$Row[0]."</td>\n");
	echo("<td>".$Row[1]."</td>\n");
	echo("<td>".$Row[2]."</td>\n");
	//$tot_ssns += $Row[2];
	//$tot_locns += 1;
	echo ("</tr>\n");
}

echo ("</table>\n");

echo ("</div>\n");
echo ("<div id=\"locns4\">\n");
//release memory
ibase_free_result($Result);

/*
$Query = "select count(distinct wh_id || locn_id) "; 
$Query .= "from issn  ";
$Query .= " where issn_status in ('PA','TS')" ;
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
$Query .= " where issn_status in ('PA','TS')" ;
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
*/

//echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\">");
echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\"");
echo(" onchange=\"document.getlocn.submit();\">");

$Query = "select count(distinct wh_id || locn_id) "; 
$Query .= "from issn  ";
$Query .= " where issn_status in ('PA','TS')" ;
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

echo("<br>Total Locs <INPUT type=\"text\" readonly name=\"qtylocn\" size=\"4\" value=\"$tot_locns\" >");

//release memory
ibase_free_result($Result);

$Query = "select count(*) "; 
$Query .= "from issn  ";
$Query .= " where issn_status in ('PA','TS')" ;
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

//echo("Total SSNs <INPUT type=\"text\" readonly name=\"qtyssn\" size=\"4\" value=\"$tot_ssns\" ><BR>");
echo("SSNs <INPUT type=\"text\" readonly name=\"qtyssn\" size=\"4\" value=\"$tot_ssns\" ><BR>");

echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Scan Location to Start</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
echo("<table BORDER=\"0\">\n");
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
//echo("</form>\n");

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
	echo("<form action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Send!' );
	//whm2buttons('Accept', '../mainmenu.php',"Y","Back_50x100.gif","Back","accept.gif");
	whm2buttons('Accept', 'exportaway.php',"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo ("</div>\n");
?>
<script type="text/javascript">
	document.getlocn.location.focus();
</script>
</body>
</html>
