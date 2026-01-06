<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "checkdatajs.php";

echo "<head>";
echo "<title>Pick - Get Printer</title>";
echo '<link rel=stylesheet type="text/css" href="printer.css">';

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for device check
whm2scanvars($Link, 'prt','DEVICE', 'DEVICE');
?>

<script type="text/javascript">
var checklocn="T";
function noCheckLocn()
{
	checklocn = "N";
}
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processEdit() {
/* # check for valid printer */
  var mytype;
  mytype = checkPrt(document.getprint.scannedprinter.value); 
  if (mytype == "none")
  {
	if (document.getprint.scannedprinter.value !== "")
	{
		alert("Not a Printer");
		document.getprint.scannedprinter.value = "";
	}
  	return false;
  }
  {
	return true;
  }
}
</script>

 </head>

<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
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
if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
}
	

$got_ssn = 0;

echo("<FORM action=\"transactionOL.php\" method=\"post\" name=getprint ONSUBMIT=\"return processEdit();\">");
//echo("Pick<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
echo("SO<INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\">");
echo("<H2>Go to Despatch Area</H2>\n");
// echo headers
//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo("Printer:<INPUT type=\"text\" name=\"scannedprinter\" size=\"3\" ONBLUR=\"return processEdit();\">");
echo("<H4>Scan Printer for Split</H4>\n");
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
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Print', 'getfromlocn.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<SCRIPT>
<?php
{
	echo("document.getprint.scannedprinter.focus();\n");
	//echo("document.getprint.scannedprinter.focus();\n");
}
?>
</SCRIPT>
</HTML>
