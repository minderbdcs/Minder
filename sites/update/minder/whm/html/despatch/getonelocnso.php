<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
?>
<html>
<head>
<title>Get One Sales Order Location</title>
<?php
include "viewport.php";
include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
  {
	return true;
  }
}
</script>
</head>
<body>
<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$location = '';
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

$Query = "select pick_item_detail.pick_label_no, pick_item.pick_order "; 
$Query .= "from issn  ";
$Query .= "join location on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
$Query .= "join pick_item_detail on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where issn.issn_status = 'DS' " ;
$Query .= " and location.store_area = 'DS' " ;
$Query .= " AND issn.wh_id  = '";
$Query .= substr($location,0,2)."'";
$Query .= " AND issn.locn_id = '";
$Query .= substr($location,2,strlen($location) - 2)."'";
$Query .= " group by pick_item.pick_order, pick_item_detail.pick_label_no " ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_lines = 0;
$tot_sos = 0;
$wk_lines = 0;
$last_order = '';

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"getcarrier.php\" method=\"post\" name=getorder\n>");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
//echo("<TH>Sales Order</TH>\n");
echo("<TH>Shipping Order</TH>\n");
echo("<TH>Lines</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	$wk_echo = 'n';
	if ($Row[1] <> $last_order)
	{
		$tot_sos++;
		$last_order = $Row[1];
		$wk_echo = 'y';
	}
	if ($wk_echo == 'y')
	{
		if ($wk_lines > 0)
		{
			echo("<TD>".$wk_lines."</TD>\n");
		 	echo ("</TR>\n");
		}
		$wk_lines = 1;
		echo ("<TR>\n");
		echo("<TD>".$Row[1]."</TD>\n");
	}
	else
	{
		$wk_lines ++;
	}
	$tot_lines ++;
}

if ($wk_lines > 0)
{
	echo("<TD>".$wk_lines."</TD>\n");
 	echo ("</TR>\n");
}

echo ("</TABLE>\n");
echo("S.O's <INPUT type=\"text\" readonly name=\"qtyso\" size=\"4\" value=\"$tot_sos\" ><BR>");
echo("Lines <INPUT type=\"text\" readonly name=\"qtylines\" size=\"4\" value=\"$tot_lines\" ><BR>");
echo("<INPUT type=\"hidden\" name=\"location\" value=\"$location\">");

echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Select Accept or Location</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
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
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php", "N");
	whm2buttons('Accept',"./despatch_menu.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

echo("<TR><TD colspan=\"2\"><FORM action=\"getonelocnso.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\"\n>");
//echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\">");
//echo("Location:<INPUT type=\"text\" name=\"location\" size=\"10\">");
echo("Location:<INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
echo("</FORM>\n");
echo("</TD></TR></TABLE>\n");
?>
<script type="text/javascript">
document.getlocn.location.focus();
</script>
</body>
</html>
