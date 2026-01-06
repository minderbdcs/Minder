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

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"transactionCN.php\" method=\"post\" name=cancelall>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
	echo("<INPUT type=\"submit\" name=\"cancelall\" value=\"All Picks\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"transactionCN.php\" method=\"post\" name=cancelone>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCN\">\n");
	echo("<INPUT type=\"submit\" name=\"cancelone\" value=\"Current Pick\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=goforward>\n");
	echo("<INPUT type=\"submit\" name=\"continue\" value=\"Continue\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"pick_Menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"transactionCN.php\" method=\"post\" name=cancelall>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=All+Picks&fromimage=');
	echo('Blank_Button_50x100.gif" alt="all"></INPUT>');
*/
	echo('SRC="/icons/whm/allpicks.gif" alt="all"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"transactionCN.php\" method=\"post\" name=cancelone>\n");
	echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCN\">\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Current+Pick&fromimage=');
	echo('Blank_Button_50x100.gif" alt="current"></INPUT>');
*/
	echo('SRC="/icons/whm/currentpick.gif" alt="current"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=continue>\n");
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

