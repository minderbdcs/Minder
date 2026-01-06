<html>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo("<h4>Revert DS Orders Back to PL</h4>\n");
$Query = "select pick_order  
          from pick_item 
          where pick_line_status = 'DS'  
          or ( pick_line_status = 'PL' and despatch_location is null)
          group by pick_order ";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Orders for Choice!<BR>\n");
	exit();
}

//echo("<FONT size=\"2\">\n");
echo("<form action=\"transactionUB.php\" method=\"post\" name=getexit\n>");

echo("Order:<select name=\"connote\" size=\"1\" >\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
}
echo("</select>\n");
echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Select Order</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
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
//echo("</form>\n");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./pick_Menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
document.getexit.connote.focus();
</script>
</html>
