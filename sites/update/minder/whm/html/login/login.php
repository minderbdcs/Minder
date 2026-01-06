<?php
$PageTitle = "Login Page";
require ("header.php");
include "2buttons.php";
include "db_access.php";
//$rimg_width = 90;
$Message = "";
$status = "";
{
	//$log = fopen('/tmp/loginV.log' , 'a');
	//$log = '/tmp/loginV.log';
	$log = '/data/tmp/loginV.log';
	//$phpinfo = phpinfo(INFO_MODULES );

	ob_start();
	phpinfo(INFO_MODULES);
	$phpinfo = ob_get_contents();
	ob_end_clean();

	file_put_contents($log, " remote_addr: " . $_SERVER['REMOTE_ADDR'] .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );

	file_put_contents($log, $phpinfo . "\n", FILE_APPEND);
}
echo('<link rel=stylesheet type="text/css" href="login.css">');
if (isset($_GET['Message']))
{
	$Message = $_GET['Message'];
}
if (isset($_POST['Message']))
{
	$Message = $_POST['Message'];
}
if (isset($_GET['status']))
{
	$status = $_GET['status'];
}
if (isset($_POST['status']))
{
	$status = $_POST['status'];
}
if ($Message == "Invalid") {
	//echo ("<p><B><CENTER><FONT COLOR=RED>Username/Password are Invalid. Please try again!</FONT></CENTER></B></p>\n");
	echo ("<p class=\"invalid\"><B>Username/Password are Invalid. Please try again!</B></p>\n");
}
else
{
	if ($Message == "LoggedOut") {
		//echo ("<B><CENTER><FONT COLOR=BLUE>Your Login has expired Please Login again!</FONT></CENTER></B>\n");
		echo ("<p class=\"expired\"><B>Your Login has expired Please Login again!</B></p>\n");
	}
	else
	{
		if ($Message == "NoDevice") {
			//echo ("<B><CENTER><FONT COLOR=PURPLE>No More Free Devices. Please try again!</FONT></CENTER></B>\n");
			echo ("<p class=\"nodevice\"><B>No More Free Devices. Please try again!</B></p>\n");
		}
		else
		{
			if ($Message <> "")
			{
				//echo ("<B><CENTER><FONT COLOR=GREEN>Status:" . $status . $Message . ". Please try again!</FONT></CENTER></B>\n");
				echo ("<p class=\"main\"><B>Status:" . $status . $Message . ". Please try again!</B></p>\n");
			}
		}
	}
}
echo ("<br>\n");
echo ("<div id=\"col3\" >");
echo ("<table BORDER=\"0\" align=\"left\" >");
echo ("<tbody >");
echo ("<form ACTION=\"login.pass.php\" METHOD=\"POST\" name=login>\n");
	echo ("<tr>");
	echo ("</tr>");
	echo ("<tr>");
	echo ("<td>");
echo ("Username: <input type=\"TEXT\" SIZE=\"10\" maxlength=\"10\" NAME=\"UserName\" class=\"default\"><BR>\n");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
	echo ("</div>");
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
echo("</form>\n");
echo ("<form ACTION=\"logoutall.php\" METHOD=\"POST\" name=logoutall>\n");
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"logoutall\" value=\"Logout All!\">\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"logoutall\" value=\"Logout All!\" type=\"submit\">\n");
	echo("Logout All<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
echo("</form>\n");
*/

	$wk_label_posn = 0;
$alt = "Send";
echo ("<div id=\"col9\" >");
{
	// Create a table.
	echo ("<table BORDER=\"0\" >");
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
	echo ("<td>");
	echo("<form action=\"logoutall.php\" method=\"post\" name=logoutall>\n");
	$menuName = "logoutall";
	$alt = "Logout All";
	//echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Logout+All&fromimage=');
	echo('Blank_Button_50x100.gif" alt="LogoutAll"></INPUT>');
*/
	echo('SRC="/icons/whm/logoutall.gif" alt="LogoutAll">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"logoutUser.php\" method=\"post\" name=logoutuser>\n");
	$menuName = "logoutuser";
	$alt = "Logout 
User";
	//echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=Logout+User&fromimage=');
	echo('Blank_Button_50x100.gif" alt="LogoutUser">');
	//echo('LogOut_50x100.gif" alt="LogoutUser">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"logoutDevice.php\" method=\"post\" name=logoutdevice>\n");
	$menuName = "logoutdevice";
	$alt = "Logout 
Device";
	//echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=Logout+Device&fromimage=');
	echo('Blank_Button_50x100.gif" alt="LogoutDevice">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</tbody>");
	echo ("</table>");
	echo ("</div>");
}

echo("<script type=\"text/javascript\">\n");
echo("document.login.UserName.focus();\n");
echo("</script>\n");
require ("footer.php");
?>
