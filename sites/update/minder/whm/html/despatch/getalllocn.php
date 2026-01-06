<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>All Locations</title>
</head>
<?php
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
	
/*
$Query = "select issn.wh_id, issn.locn_id, pick_item_detail.pick_label_no  "; 
$Query .= "from issn  ";
$Query .= "join location on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
$Query .= "join pick_item_detail on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where issn.issn_status = 'DS' " ;
$Query .= " and location.store_area = 'DS' " ;
$Query .= " group by issn.wh_id, issn.locn_id, pick_item_detail.pick_label_no " ;
*/
$Query = "select issn.wh_id, issn.locn_id, pick_item_detail.pick_label_no  "; 
$Query .= "from pick_item_detail  ";
$Query .= "join issn on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where pick_item_detail.pick_detail_status = 'DS' " ;
$Query .= " group by issn.wh_id, issn.locn_id, pick_item_detail.pick_label_no " ;
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
$last_wh = '';
$last_locn = '';
$wk_locn_option = '';

echo("<body>\n");
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"getcarrier.php\" method=\"post\" name=getlocn\n>");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>WH</TH>\n");
echo("<TH>Location</TH>\n");
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
	//echo(":$Row[0]:$Row[1]:$Row[2]:");
	//echo(":$wk_lines:$tot_lines:");
	if ($Row[0] <> $last_wh)
	{
		$wk_echo = 'y';
		$last_wh = $Row[0];
	}
	if ($Row[1] <> $last_locn)
	{
		$wk_echo = 'y';
		$last_locn = $Row[1];
	}
	//echo(":$wk_print:");
	if ($wk_echo == 'y')
	{
		if ($wk_lines > 0)
		{
			echo("<TD>".$wk_lines."</TD>\n");
		 	echo ("</TR>\n");
		}
		$wk_lines = 1;
		echo ("<TR>\n");
		echo("<TD>".$Row[0]."</TD>\n");
		echo("<TD>".$Row[1]."</TD>\n");
		$wk_locn_option .= "<OPTION value=\"" . $Row[0] . $Row[1] . "\">$Row[1]\n";
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

//release memory
ibase_free_result($Result);

/*
$Query = "select pick_item.pick_order  "; 
$Query .= "from issn  ";
$Query .= "join location on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
$Query .= "join pick_item_detail on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where issn.issn_status = 'DS' " ;
$Query .= " and location.store_area = 'DS' " ;
$Query .= " group by pick_item.pick_order " ;
*/
$Query = "select pick_item.pick_order  "; 
$Query .= "from pick_item_detail  ";
$Query .= "join issn on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where pick_item_detail.pick_detail_status = 'DS' " ;
$Query .= " group by pick_item.pick_order " ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}

while ( ($Row = ibase_fetch_row($Result)) ) {
	$tot_sos++;
}
echo ("</TABLE>\n");
echo("S.O's <INPUT type=\"text\" readonly name=\"qtyso\" size=\"4\" value=\"$tot_sos\" ><BR>");
echo("Lines <INPUT type=\"text\" readonly name=\"qtylines\" size=\"4\" value=\"$tot_lines\" ><BR>");
echo("<SELECT name=\"location\" size=\"1\">\n");
echo($wk_locn_option);
echo("</SELECT>\n");

//echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Select Location</TH>\n");
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
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
{
	echo("document.getlocn.location.focus();\n");
}
?>
</script>
</body>
</html>
