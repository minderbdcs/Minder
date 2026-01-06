<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Verify Product</title>
<link rel=stylesheet type="text/css" href="product.css">

 </head>
<script>
function processEdit(wk_prod, wk_dosub) {
  if ( document.getprod.scannedprod.value=="")
  {
  	alert("Must Enter the Product");
	document.getprod.scannedprod.focus()
  	return false
  }
  if (!(document.getprod.scannedprod.value==wk_prod))
  {
  	alert("Not the Correct Product");
  	/* alert(document.getprod.scannedprod.value); */
	document.getprod.scannedprod.value=""
	document.getprod.scannedprod.focus()
  	return false
  }
 if (wk_dosub=="T") {
 	document.getprod.submit();
 	return true
 }
}
function processEdit3() {
/* # check for valid alt product */
  var mytype;
  var instring = "";
  var mylen = 0;
  var myinstr = "" ;
   	/* alert("startposn " + startposn );  */
  mytype = checkAltprod(document.getprod.scannedprod.value); 
  if (mytype == "none")
  {
  	/* alert("not an alt prod startposn " + startposn );  */
	/* not an alt prod - try a prod */
	mytype = checkProd(document.getprod.scannedprod.value); 
	if (mytype == "none")
	{
  		alert("Not a Product");
		document.getprod.scannedprod.focus();
	  	return false;
	}
	else
	{
  		/* alert("a internal prod startposn " + startposn );  */
		instring = document.getprod.scannedprod.value;
		mylen = instring.length - startposn;
		myinstr = instring.substr(startposn,mylen);
		/* alert(myinstr); */
		document.getprod.scannedprod.value = myinstr; 
		return true;
	}
  }
  else
  {
  	/* alert("a alt prod startposn " + startposn );  */
	instring = document.getprod.scannedprod.value;
	mylen = instring.length - startposn;
	myinstr = instring.substr(startposn,mylen);
	document.getprod.scannedprod.value = myinstr;
	/* alert(myinstr); */
	return true;
  }
}
</script>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for product check
whm2scanvars($Link, 'prod','PROD_INTERNAL', 'PRODINTERNAL');
whm2scanvars($Link, 'altprod','ALT_PROD_INTERNAL', 'ALTPRODINTERNAL');

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
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $dummy, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
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
if (isset($_POST['scannedprod']))
{
	$scannedprod = $_POST['scannedprod'];
}
if (isset($_GET['scannedprod']))
{
	$scannedprod = $_GET['scannedprod'];
}
// want ssn label desc
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

echo("<h2>Replenish - Scan Product</h2>\n");
echo("<FONT size=\"2\">\n");
//echo("<FORM action=\"getfromprod.php\" method=\"post\" name=displayprod>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Line</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"4\" value=\"$label_no\"></TD>");
//echo("</TR></TABLE><BR><BR><BR>");
//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
//echo("<TR><TD>");
echo("<TD>");
{
	echo("Prod</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE><BR><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"60\" value=\"$description\" >");
	echo("</TD></TR></TABLE><BR><BR><BR>");
}
echo("Qty Reqd <INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\" ><BR>");

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo("<FORM action=\"getfromqty.php\" method=\"post\" onSubmit=\"return processEdit3() && processEdit('$prod_no','F');\" name=getprod>");
echo("Prod: <INPUT type=\"text\" name=\"scannedprod\" size=\"30\"");
//echo(" maxlength=\"30\" onfocus=\"document.getprod.scannedprod.value=''\" onchange=\"return processEdit3()\">");
echo(" maxlength=\"30\" onChange=\"return processEdit3();\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Product</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'getfromlocn.php',"Y","Back_50x100.gif","Back","accept.gif");
}
?>
<script>
<?php
{
	echo("document.getprod.scannedprod.focus();\n");
}
?>
</script>
</html>
