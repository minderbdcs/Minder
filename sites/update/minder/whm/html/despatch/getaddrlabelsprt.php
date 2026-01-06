<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Print Address Labels</title>
<script type="text/javascript">
function setecho() {
 document.getlabel.qty.value="0";
 document.forms.getlabel.submit();
 return true
}
function setReecho() {
 document.getlabel.qty.value="1";
 document.forms.getlabel.submit();
 return true
}
</script>
</head>
<?php
require_once 'DB.php';
require 'db_access.php';

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (!isset($printer))
{
	$printer = "PC";
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
echo("<body>\n");
echo("<FONT size=\"2\">\n");
if (isset($consignment))
{
	echo("<FORM action=\"transactionAD.php\" method=\"post\" name=getlabel\n>");
}
else
{
	echo("<FORM action=\"getaddrlabelsprt.php\" method=\"post\" name=getlabel\n>");
}

echo("<INPUT type=\"hidden\" name=\"qty\" value=\"0\"><BR>");
if (isset($consignment))
{
	echo("Consignment <INPUT type=\"text\" name=\"consignment\" readonly size=\"16\" value=\"$consignment\"><BR>");
}
else
{
	$Query = "select awb_consignment_no from pick_despatch where despatch_status = 'DC' group by awb_consignment_no ";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Connote for Choice!<BR>\n");
		exit();
	}

	echo("Consignment:<SELECT name=\"consignment\" size=\"1\" >\n");

	while ( ($Row = ibase_fetch_row($Result)) ) {
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
	echo("</SELECT>\n");
}
echo("Printer <INPUT type=\"text\" name=\"printer\" size=\"2\" value=\"$printer\"></BR>");
if (isset($consignment))
{
	/*
	$Query = "select sum(pickd_address_qty) from pick_despatch where awb_consignment_no = '$consignment' and despatch_status = 'DC' ";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Connote Label Qty for Connote!<BR>\n");
		exit();
	}

	while ( ($Row = ibase_fetch_row($Result)) ) {
		$label_qty = $Row[0];
	}
	echo("Qty Labels <INPUT type=\"text\" name=\"label_qty\" readonly size=\"3\" value=\"$label_qty\">");
	*/

	$Query = "select pack_id.despatch_label_no from pick_despatch join pack_id on pack_id.despatch_id = pick_despatch.despatch_id where pick_despatch.awb_consignment_no = '$consignment' and pick_despatch.despatch_status = 'DC' order by pack_id.despatch_label_no ";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Labels for Connote!<BR>\n");
		exit();
	}

	echo("Labels:<SELECT name=\"label\" size=\"1\" >\n");

	while ( ($Row = ibase_fetch_row($Result)) ) {
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
	echo( "<OPTION value=\"ALL\" selected >All Selected\n");
	echo("</SELECT>\n");
}
echo ("<TABLE>\n");
echo ("<TR>\n");
if (isset($consignment))
{
	echo("<TH>Enter Printer and Label for Print/Reprint</TH>\n");
}
else
{
	echo("<TH>Select Consignment to Print</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
// Create a table.
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<TR>");
echo ("<TD>");
echo("<INPUT type=\"IMAGE\" ");  
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	if (isset($consignment))
	{
		echo("<INPUT type=\"submit\" name=\"print\" value=\"Print\">\n");
		echo("<INPUT type=\"submit\" name=\"reprint\" value=\"RePrint\">\n");
	}
	else
	{
		echo("<INPUT type=\"submit\" name=\"submit\" value=\"Accept\">\n");
	}
}
else
*/
{
	if (isset($consignment))
	{
		echo('SRC="/icons/whm/Print_50x100.gif" alt="Print" onClick=\"setPrint()\"></INPUT>');
		echo ("</TD>");
		echo ("<TD>");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/REPRINT_50x100.gif" alt="RePrint" onClick=\"setReprint()\"></INPUT>');
		echo ("</TD>");
		echo ("</TR>");
		echo ("<TR>");
/*
		echo("<BUTTON name=\"print\" value=\"Print\" type=\"button\" onclick=\"setPrint()\">\n");
		echo("Print<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
		echo("<BUTTON name=\"reprint\" value=\"RePrint\" type=\"button\" onclick=\"setReprint()\">\n");
		echo("RePrint<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
	else
	{
		$alt = "Accept";
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '"></INPUT>');
		echo ("</TD>");
/*
		echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
		echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
}
echo("</FORM>\n");

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
	$backto = "./despatch_menu.php";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>
<script type="text/javascript">
<?php
{
	if (isset($consignment))
	{
		echo("document.getlabel.printer.focus();\n");
	}
	else
	{
		echo("document.getlabel.consignment.focus();\n");
	}
}
?>
</script>
</body>
</html>

