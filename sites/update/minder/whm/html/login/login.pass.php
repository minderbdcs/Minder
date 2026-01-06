<?php
$PageTitle = "Login Page Enter Password";
require ("header.php");
echo("<script language=\"JavaScript\" src=\"jstz.js\"></script>\n");
include "2buttons.php";
include "db_access.php";
if (isset($_GET['UserName']))
{
	$UserName = $_GET['UserName'];
	//echo("user is $UserName\n");
}
if (isset($_POST['UserName']))
{
	$UserName = $_POST['UserName'];
	//echo("user is $UserName\n");
}
echo ("<table BORDER=\"0\" align=\"left\" >");
echo ("<tbody >");
//echo ("<FORM ACTION=\"login.php\" METHOD=\"GET\" name=login>\n");
echo ("<form action=\"HandleLogin.php\" method=\"POST\" name=login>\n");
echo ("<input type=\"hidden\" name=\"UserName\" value=\"".$UserName."\" >\n");
echo ("<input type=\"hidden\" name=\"tz\"  >\n");
	echo ("<tr>");
	echo ("<th>");
	echo ("</th>");
	echo ("</tr>");
	echo ("<tr>");
	echo ("<td>");
echo ("Password: <input type=\"PASSWORD\" size=\"10\" maxlength=\"10\" name=\"Password\" class=\"default\" ><BR>\n");
	echo ("</td>");
	echo ("</tr>");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"submit\" value=\"Submit!\">\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"submit\" value=\"Submit!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
echo("</FORM>\n");
*/

$alt = "Send";
{
	// Create a table.
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	echo ("<td>");
	$menuName = "login";
	//echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
	echo('SRC="/icons/whm/send.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</tbody>");
	echo ("</table>");
}

echo("<script type=\"text/javascript\">\n");

echo("var tz = jstz.determine(); \n"); // Determines the time zone of the browser client
echo("var timezone = tz.name(); \n");

//echo("alert(timezone);\n");		
echo("document.login.tz.value = timezone; \n");
//echo("alert(timezone);\n");	

echo("</script>\n");

echo("<script type=\"text/javascript\">\n");
echo("document.login.Password.focus();\n");
echo("</script>\n");
require ("footer.php");
?>
