<?php
include "../login.inc";
?>
<HTML>
 <HEAD>
  <TITLE>Pick Menu</TITLE>
 </HEAD>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <BODY BGCOLOR="#FFFFFF">

<?php
echo("<H4 ALIGN=\"LEFT\">Cancel Allocated Picks");
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

{
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"transactionTR.php\" method=\"post\" name=dolater>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=Do+Later&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Later">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo("<FORM action=\"transactionUA.php\" method=\"post\" name=continue>\n");
	whm2buttons('Continue', 'pick_Menu.php',"Y","Back_50x100.gif","Back","continue_picks.gif");
/*
	echo("<BUTTON type=\"button\" accesskey=\"l\" name=\"all\" value=\"cancelall\" onclick=\"location.href='transactionCN.php?trans_type=PKCA';\">\n");
	echo("aLL Picks<IMG SRC=\"/icons/hand.up.gif\" alt=\"all\"></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"p\" name=\"cancelone\" value=\"current Pick\" onclick=\"location.href='transactionCN.php?trans_type=PKCN';\">\n");
	echo("current Pick<IMG SRC=\"/icons/compressed.gif\" alt=\"current\"></BUTTON>\n");
	echo("<BUTTON name=\"continue\" type=\"button\" onClick=\"location.href='getfromlocn.php';\">\n");
	echo("Continue<IMG SRC=\"/icons/forward.gif\" alt=\"back\"></BUTTON>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onClick=\"location.href='pick_Menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

?>
</BODY>
<?php
/*
<SCRIPT>
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.cancelall.cancelall.focus();\n");
}
else
{
	echo("document.cancelall.focus();\n");
}
</SCRIPT>
*/
?>
</HTML>

