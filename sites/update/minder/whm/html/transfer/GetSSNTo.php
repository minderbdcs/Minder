<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Get SSN you are taking to</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
require_once('DB.php');
require('db_access.php');
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	header("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE!");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include "logme.php";
include "2buttons.php";

function checkButtons($Link )
{
	// get who called me
	$wk_fromwhere = basename($_SERVER['PHP_SELF']) ;
	// check whether button type
	$wk_dobutton = "";
	$Query = "select description from options where group_code = 'BUTTON' and code = '" . $wk_fromwhere . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table options<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_dobutton =  $Row[0];
	}
	if ($wk_dobutton == "") 
	{
		$wk_dobutton = "IMAGE";
	}
	return $wk_dobutton;
		
}

/* ============================================================================================== */

if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
//  <H3 ALIGN="LEFT">Enter SSN To</H3>
//echo('<h3 align="LEFT">Enter SSN To Or SEND for ALL</h3>');
echo('<h3 align="LEFT">Enter SSN To , Location Or SEND for ALL</h3>');

// <form action="PostTo.php" method="post" name=getlocn>
//$bdcs_cookie = $_COOKIE['BDCSData'] . "||||||||";
$bdcs_cookie = getBDCScookie($Link, $DBDevice, "transfer") . "|||||||||";
list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to) = explode("|", $bdcs_cookie);
if (isset($_POST['qtyto'])) 
{
	$have_qtyto = "Y";
}
if (isset($_GET['qtyto'])) 
{
	$have_qtyto = "Y";
}
echo("<form action=\"PostTo.php\" method=\"post\" name=gettossn>\n");
 echo("<P>\n");
if (isset($_POST['ssnfrom'])) 
{
	echo("SSN <input type=\"text\" readonly name=\"ssnfrom\" value=\"".$_POST['ssnfrom']."\" ><br>");
}
if (isset($_GET['ssnfrom'])) 
{
	echo("SSN <input type=\"text\" readonly name=\"ssnfrom\" value=\"".$_GET['ssnfrom']."\" ><br>");
}
if (isset($_POST['locationfrom'])) 
{
	echo("From <input type=\"text\" readonly name=\"locationfrom\" value=\"".$_POST['locationfrom']."\" ><br>");
}
if (isset($_GET['locationfrom'])) 
{
	echo("From <input type=\"text\" readonly name=\"locationfrom\" value=\"".$_GET['locationfrom']."\" ><br>");
}
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
if ($tran_type == "SSN")
{
	{
		$Query = "SELECT COUNT(*) FROM ISSN WHERE LOCN_ID = '$DBDevice'" ;
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
	echo('<BR>CNT<input type="text" name="ssncnt" readonly size="3" value="' . $gotmore . '">');
}
echo("<br>");

echo("SSN: <input type=\"text\" name=\"ssnto\"");
if (isset($_POST['ssnto'])) 
{
	echo(" value=\"".$_POST['ssnto']."\"");
}
if (isset($_GET['ssnto'])) 
{
	echo(" value=\"".$_GET['ssnto']."\"");
}
echo(" size=\"20\"");
echo(" maxlength=\"24\"><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</form>\n");
}
else
*/
/*
{
	// html 4.0 browser
	// Create a table.
	$alt = "Send";
	echo ("<table border=\"0\" align=\"left\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<input type=\"IMAGE\" ");  
*/
/* older
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
/*
	//echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo('SRC="/icons/whm/send.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
*/
/* older
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</form>\n");
*/
{
		// html 4.0 browser
		$wk_buttonType = checkButtons($Link);
 		echo("<table border=\"0\" align=\"left\">");
		if ($wk_buttonType == "IMAGE")
		{
			if (isset($have_qtyto))
			{
			    $wk_to_site = "GetLocnTo.php?havedata=" . $have_qtyto. "&locationto=";
			}
			else
			{
			    $wk_to_site = "GetLocnTo.php?havedata=" . "&locationto=";
			}
			whm2buttons('Send', $wk_to_site ,"Y", 'tolocation.gif' ,"ToLocn","accept.gif");
		} else {
			echo("<tr>\n");
			echo("<td>\n");
			echo("<button name=\"send\" value=\"Send!\" type=\"submit\">\n");
			//echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
			echo("Send</button>\n");
			echo("</form>\n");
			echo("</td><td>\n");
			echo("<button name=\"ToLocn\" type=\"button\" onfocus=\"location.href='GetLocnTo.php?havedata=" . $have_qtyto . "';\">\n");
			//echo("To Location<IMG SRC=\"/icons/hand.right.gif\" alt=\"tolocation\"></BUTTON>\n");
			echo("To Location</button>\n");
			echo("</td>\n");
			echo("</tr>\n");
			echo("</table>\n");
		}
}
//commit
ibase_commit($dbTran);
	
?>
</P>
<script type="text/javascript">
document.gettossn.ssnto.focus();
</script>
</body>
</html>

