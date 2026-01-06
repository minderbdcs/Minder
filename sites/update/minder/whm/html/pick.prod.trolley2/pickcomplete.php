<?php
include "../login.inc";
?>
<html>
 <head>
  <TITLE>Pick Complete</TITLE>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
echo("<h4 ALIGN=\"LEFT\">Is Pick Complete?</h4>");
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
$scannedssn = '';
if (isset($_COOKIE['BDCSData']))
{
	//list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
{
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $dummy2) = explode("|", $wk_cookie);
}
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"transactionOL.php\" method=\"post\" name=no>\n");
	echo("<INPUT type=\"submit\" name=\"no\" value=\"NO\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"getOLreason.php\" method=\"post\" name=yes>\n");
	echo("<INPUT type=\"submit\" name=\"yes\" value=\"YES\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"getfromssn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo("<FORM action=\"transactionOL.php\" method=\"post\" name=no>\n");
	if ($ssn <> "")
	{
		//whm2buttons('NO', 'getfromssn.php',"N");
		whm2buttons('NO',"getfromssn.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"no.gif");
	}
	else
	{
		//whm2buttons('NO', 'getfromqty.php',"N");
		whm2buttons('NO',"getfromqty.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"no.gif");
	}
	$alt = "YES";
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"getOLreason.php\" method=\"post\" name=yes>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
	echo('SRC="/icons/whm/yes.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON type=\"button\" accesskey=\"N\" name=\"no\" value=\"no\" onclick=\"location.href='transactionOL.php';\">\n");
	echo("NO<IMG SRC=\"/icons/hand.up.gif\" alt=\"no\"></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"y\" name=\"yes\" value=\"yes\" onclick=\"location.href='getOLreason.php';\">\n");
	echo("YES<IMG SRC=\"/icons/compressed.gif\" alt=\"yes\"></BUTTON>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onClick=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

?>
<script type="text/javascript">
<?php
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.no.no.focus();\n");
}
else
{
	echo("document.no.focus();\n");
}
	echo("document.no.NO.focus();\n");
*/
?>
</script>
</body>
</html>

