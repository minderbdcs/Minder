<html>
<head>
<title>Test</title>
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
<body>
<?php
require_once 'DB.php';
require 'db_access.php';

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
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
if (isset($_POST['label']))
{
	$label = $_POST['label'];
}
if (isset($_GET['label']))
{
	$label = $_GET['label'];
}
if (isset($printer) and isset($label))
{
	// create label file here
	echo("I'm Written\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo("<FONT size=\"2\">\n");
{
	echo("<FORM action=\"test.php\" method=\"post\" name=getlabel\n>");
}

echo("<INPUT type=\"hidden\" name=\"qty\" value=\"0\"><BR>");

$Query = "select device_id, equipment_description_code from sys_equip where device_type='PR' ";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Printers for Labels!<BR>\n");
	exit();
}

echo("Printer:<SELECT name=\"printer\" size=\"1\" >\n");
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $printer)
	{
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[0] $Row[1]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[1]\n");
	}
}
echo("</SELECT>\n");

echo("Labels:<SELECT name=\"label\" size=\"1\" >\n");

	echo( "<OPTION value=\"1\" >One\n");
	echo( "<OPTION value=\"2\" >Two\n");
	echo( "<OPTION value=\"3\" >Three\n");
	echo( "<OPTION value=\"4\" >Four\n");
	echo( "<OPTION value=\"5\" >Five\n");
	echo( "<OPTION value=\"6\" >Six\n");
	echo( "<OPTION value=\"7\" >Seven\n");
	echo( "<OPTION value=\"8\" >Eight\n");
	echo( "<OPTION value=\"9\" >Nine\n");
	echo("</SELECT>\n");
echo ("<TABLE>\n");
echo ("<TR>\n");
{
	echo("<TH>Enter Printer and Label for Print/Reprint</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
// Create a table.
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<TR>");
echo ("<TD>");
echo("<INPUT type=\"IMAGE\" ");  
{
	{
		echo('SRC="/icons/whm/Print_50x100.gif" alt="Print" onClick=\"setPrint()\"></INPUT>');
		echo ("</TD>");
		echo ("<TD>");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/REPRINT_50x100.gif" alt="RePrint" onClick=\"setReprint()\"></INPUT>');
		echo ("</TD>");
		echo ("</TR>");
		echo ("<TR>");
	}
}
echo("</FORM>\n");

{
	// html 4.0 browser
	$backto = "../mainmenu.php";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);

?>
<script type="text/javascript">
<?php
{
	{
		echo("document.getlabel.printer.focus();\n");
	}
}
?>
</script>
</body>
</html>

