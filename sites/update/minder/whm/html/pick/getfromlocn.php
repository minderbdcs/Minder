<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo "<title>Pick From Location</title>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
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
	
$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order "; 
$Query .= "from pick_item p1 ";
$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id ";
$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
$Query .= " where p1.pick_line_status in ('AL','PG') " ;
$Query .= " and device_id = '".$tran_device."'";
$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.pick_location";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}

if (isset($_POST['locnfound']))
{
	$location_found = $_POST['locnfound'];
}
if (isset($_GET['locnfound']))
{
	$location_found = $_GET['locnfound'];
	//echo("locn found ".$location_found);
}
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;

// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] <> '')
	{
		//ssn
		$ssn = $Row[0];
		$description = $Row[3];
		$uom = "EACH";
	}
	$label_no = $Row[1];
	if ($Row[2] <> '')
	{
		//product
		$prod_no = $Row[2];
		$description = $Row[4];
		$uom = $Row[5];
	}
	$order_qty = $Row[6];
	$picked_qty = $Row[7];
	$required_qty = $order_qty - $picked_qty;
	$order = $Row[8];
}

//release memory
ibase_free_result($Result);

$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);

// want ssn label desc
if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1  ";
	$Query .= " where s1.ssn_id = '".$ssn."'";
}
else
{
	$Query = "select s3.locn_id, sum(s3.current_qty), s3.wh_id "; 
	//$Query = "select s3.locn_id, s3.current_qty, s3.wh_id "; 
	$Query .= "from issn s3  ";
	$Query .= " where s3.prod_id = '".$prod_no."'";
	$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query .= " group by s3.wh_id, s3.locn_id";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_ssn = 0;
echo "<body>";
echo("<FONT size=\"2\">\n");
echo ("<DIV>\n");
echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Pick</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></TD><TD>");
echo("SO</TD><TD><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></TD></TR></TABLE><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
if ($ssn <> '')
{
	echo("SSN</TD><TD><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("</TD></TR></TABLE><BR><BR>");
}
else
{
	echo("Part</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("</TD></TR></TABLE><BR><BR>");
}
// echo headers
echo ("<TABLE BORDER=\"1\" ALIGN=\"LEFT\">\n");
echo ("<TR>\n");
echo("<TH>WH</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Qty Available</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo ("</TR>\n");
	$rcount++;
}

echo ("</TABLE><BR><BR>\n");
echo ("</DIV>\n");
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<BR>");
}
echo ("<DIV ID=\"col1\">\n");
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo ("<TR><TD>\n");
echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></TD></TR></TABLE><BR><BR>");
//release memory
ibase_free_result($Result);

echo ("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
echo ("<TR><TD>\n");
echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\"");
echo("onchange=\"document.getlocn.submit\">");
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE ALIGN=\"BOTTOM\">\n");
echo ("<TR>\n");
echo("<TH>Scan Location</TH>\n");
echo ("</TR>\n");
echo ("</TABLE><BR><BR>\n");
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
// if and status 'PL' items for this device then allow
// despatch button
$Query = "select first 1 pick_label_no "; 
$Query .= "from pick_item ";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and pick_line_status = 'PL'";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$have_despatch = "N";
if ( ($Row = ibase_fetch_row($Result)) ) {
	$have_despatch = "Y";
/*
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		// html 3.2 browser
		echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<INPUT type=\"submit\" name=\"despatch\" value=\"Despatch\">\n");
		echo("</FORM>\n");
	}
	else
	{
		// html 4.0 browser
		echo("<BUTTON name=\"despatch\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
		echo("Despatch<IMG SRC=\"/icons/image2.gif\" alt=\"despatch\"></BUTTON>\n");
	}
*/
}

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
	echo("<FORM action=\"cancel.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='cancel.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'cancel.php', "N");
	if ($have_despatch == "Y")
	{
		$alt = "Despatch";
		echo ("<TR>");
		echo ("<TD>");
		echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		echo ("</TR>");
	}
	echo ("</TABLE>");
echo ("</DIV>\n");
?>
<SCRIPT>
<?php
{
	if (isset($location_found))
	{
		if ($location_found == 0)
		{
			echo("alert(\"Wrong Location\");\n");
		}
		else
		{
			echo("alert(\"Location Found\");\n");
		}
	}
	echo("document.getlocn.location.focus();\n");
}
?>
</SCRIPT>
</body>
</html>
