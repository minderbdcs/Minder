<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
  <title>Get Product you are Placing</title>
<link rel=stylesheet type="text/css" href="product.css">
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
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
require_once('DB.php');
require('db_access.php');
include "logme.php";
//include "checkdatajs.php";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	header("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE!");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	print ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
echo('<h3 align="left">Enter Product To Place Or Accept for ALL</h3>');
// create js for product check
//whm2scanvars($Link, 'prodint','PROD_INTERNAL', 'PRODINTERNAL');
//whm2scanvars($Link, 'prodaltint','ALT_PROD_INTERNAL', 'ALTPRODINTERNAL');
//whm2scanvars($Link, 'prod13','PROD_13', 'PROD13');

/*
<script type="text/javascript">
function processEdit() {
-* # check for valid product *-
  var mytype;
  mytype = checkProdint(document.gettoproduct.productto.value); 
  if (mytype == "none")
  {
  	mytype = checkProd13(document.gettoproduct.productto.value); 
  	if (mytype == "none")
  	{
  		mytype = checkProdaltint(document.gettoproduct.productto.value); 
		if (mytype == "none")
		{
			-* alert("Not a Product"); *-
			document.gettoproduct.message.value = "Not a Product";
  			return false;
		}
  	}
	else
	{
		return true;
	}
  }
  else
  {
	return true;
  }
}
</script>
*/

// <form action="PostTo.php" method="post" name=getlocn>
//$bdcs_cookie = $_COOKIE['BDCSData'] . "||||||||";
$bdcs_cookie = getBDCScookie($Link, $DBDevice, "transfer");
list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to) = explode("|", $bdcs_cookie . "|||||||||||");
if (isset($_POST['qtyto'])) 
{
	$have_qtyto = "Y";
}
if (isset($_GET['qtyto'])) 
{
	$have_qtyto = "Y";
}
//echo("<form action=\"GetProdLocn2To.php\" method=\"post\" name=gettoproduct onsubmit=\"return ProcessEdit();\" >\n");
echo("<form action=\"GetProdLocn2To.php\" method=\"post\" name=gettoproduct >\n");
 echo("<p>\n");
if (isset($_POST['qtyto'])) 
{
	echo("Qty <input type=\"text\" readonly name=\"qtyto\" value=\"".$_POST['qtyto']."\" ><br>");
	$have_qtyto = "Y";
}
if (isset($_GET['qtyto'])) 
{
	echo("Qty <input type=\"text\" readonly name=\"qtyto\" value=\"".$_GET['qtyto']."\" ><br>");
	$have_qtyto = "Y";
}
if (isset($have_qtyto))
{
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"TRIS\">");
}
else
{
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"TRLI\">");
}
echo("To: <input type=\"text\" name=\"locationto\"");
if (isset($_POST['locationto'])) 
{
	echo(" value=\"".$_POST['locationto']."\"");
}
if (isset($_GET['locationto'])) 
{
	echo(" value=\"".$_GET['locationto']."\"");
}
echo(" size=\"10\">\n");
$gotmore = 0;
if ($tran_type == "PRODUCT")
{
	{
		//$Query = "SELECT COUNT(distinct prod_id) FROM ISSN WHERE LOCN_ID = '$DBDevice'" ;
		$Query = "SELECT COUNT(distinct prod_id) FROM ISSN WHERE LOCN_ID = '$DBDevice' AND (WH_ID NOT STARTING 'X')" ;
		// echo($Query); 
		if (($Result = ibase_query($Link, $Query)))
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				$gotmore =  $Row[0];
				ibase_free_result($Result); 
				unset($Result); 
			}
		}
	}
	echo('<br>CNT<input type="text" name="ssncnt" readonly size="3" value="' . $gotmore . '">');
}
echo("<br>");

echo("Product: <input type=\"text\" name=\"productto\"");
if (isset($_POST['productto'])) 
{
	echo(" value=\"".$_POST['productto']."\"");
}
if (isset($_GET['productto'])) 
{
	echo(" value=\"".$_GET['productto']."\"");
}
echo(" size=\"34\"");
echo(" maxlength=\"34\"><br>\n");
echo("<input type=\"text\" name=\"message\" readonly");
echo(" size=\"20\"");
echo(" ><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	// Create a table.
	$alt = "Send";
	echo ("<table border=\"0\" align=\"left\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<input type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</form>\n");
*/
}
//commit
ibase_commit($dbTran);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
?>
</p>
<script type="text/javascript">
document.gettoproduct.productto.focus();
</script>
</body>
</html>

