<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
	/*
	No cache!!
	*/
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	// always modified
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	/*
	End of No cache
	*/
//setcookie("BDCSData","");
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Get SSN you are working on</title>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront2") === false)
{
	echo('<link rel=stylesheet type="text/css" href="consign.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="consign-netfront.css">');
}
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
  <h4 ALIGN="LEFT">Enter SSN</h4>

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	include "logme.php";
	if (isset($_COOKIE['SaveUser']))
	{
		list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
	}
	if (isset($_GET['havedata']))
	{
		$havedata = "Y";
	}
	if (isset($_POST['havedata']))
	{
		$havedata = "Y";
	}
	$gotmore = 0;
	{
		{
			$Query = "SELECT COMPANY_ID FROM CONTROL" ;
			if (($Result = ibase_query($Link, $Query)))
			{
					ibase_free_result($Result); 
					unset($Result); 
			}
			//commit
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
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
	}

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
/*
		if ($message == 'connect')
		{
			echo ("<B><FONT COLOR=RED>Can't Connect to DATABASE!</FONT></B>\n");
		}
		else
		if ($message == 'query')
		{
			echo ("<B><FONT COLOR=RED>Can't Query ISSN!</FONT></B>\n");
		}
		else
		if ($message == 'nossn')
		{
			echo ("<B><FONT COLOR=RED>SSN Not Found!</FONT></B>\n");
		}
		else
		{
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
*/
		if ($message == 'connect')
		{
			$message = "Can't Connect to DATABASE!";
		}
		elseif ($message == 'query')
		{
			$message = "Can't Query ISSN!";
		}
		elseif ($message == 'nossn')
		{
			$message = "SSN Not Found!";
		} 
		$message = trim($message);
		if ($message <> "Processed successfully") {
			echo ("<b><font color=RED>$message</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			}
		} else {
			echo ("<b><font color=GREEN>$message</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			}
		}
	} /* end of get message */

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
?>

 <form action="PostFrom.php" method="post" name=getssn>
 <P>
<?php
echo("<input type=\"hidden\" name=\"transaction_type\" value=\"TROL\">");
echo("<input type=\"hidden\" name=\"tran_type\" value=\"SSN\">");
if ($gotmore > 0)
{
	$havedata = "Y";
	echo('CNT<input type="text" name="ssncnt" readonly size="3" value="' . $gotmore . '">');
	echo("<BR>");
}
else 
{
	if (isset($havedata))
	{
		unset ($havedata);
	}
}
echo("SSN:      <input type=\"text\" name=\"ssnfrom\"");
if (isset($_POST['ssnfrom'])) 
{
	echo(" value=\"".$_POST['ssnfrom']."\"");
}
if (isset($_GET['ssnfrom'])) 
{
	echo(" value=\"".$_GET['ssnfrom']."\"");
}
echo(" size=\"20\"");
//echo(" maxlength=\"20\"><BR>\n");
echo(" maxlength=\"25\" onchange=\"document.getssn.submit()\"><BR>\n");
if (isset($havedata))
{
/*
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		// html 3.2 browser
		echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
		echo("</FORM>\n");
		echo("<FORM action=\"GetLocnTo.php\" method=\"post\" name=goto>\n");
		echo("<INPUT type=\"hidden\" name=\"havedata\" value=\"$havedata\">\n");
		echo("<INPUT type=\"submit\" name=\"tolocation\" value=\"To Location\">\n");
		echo("</FORM>\n");
	}
	else
*/
	{
		// html 4.0 browser
		$wk_buttonType = checkButtons($Link);
 		echo("<table border=\"0\" align=\"left\">");
		if ($wk_buttonType == "IMAGE")
		{
			/* whm2buttons('Send', 'GetLocnTo.php?havedata=$havedata',"Y", 'button.php?text=To+Location&fromimage=Blank_Button_50x100.gif' ,"ToLocn","accept.gif"); */
			$wk_to_site = "GetLocnTo.php?havedata=" . $havedata;
			whm2buttons('Send', $wk_to_site ,"Y", 'tolocation.gif' ,"ToLocn","accept.gif");
		} else {
			echo("<tr>\n");
			echo("<td>\n");
			echo("<button name=\"send\" value=\"Send!\" type=\"submit\">\n");
			//echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
			echo("Send</button>\n");
			echo("</form>\n");
			echo("</td><td>\n");
			echo("<button name=\"ToLocn\" type=\"button\" onfocus=\"location.href='GetLocnTo.php?havedata=" . $havedata . "';\">\n");
			//echo("To Location<IMG SRC=\"/icons/hand.right.gif\" alt=\"tolocation\"></BUTTON>\n");
			echo("To Location</button>\n");
			echo("</td>\n");
			echo("</tr>\n");
			echo("</table>\n");
		}
	}
}
else
{
/*
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		// html 3.2 browser
		echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
		echo("</FORM>\n");
		echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
		echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
		echo("</FORM>\n");
	}
	else
*/
	{
		// html 4.0 browser
		$wk_buttonType = checkButtons($Link);
 		echo("<table border=\"0\" align=\"left\">");
		if ($wk_buttonType == "IMAGE")
		{
			whm2buttons('Send', 'Transfer_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
		} else {
			echo("<tr>\n");
			echo("<td>\n");
			echo("<button name=\"send\" value=\"Send!\" type=\"submit\">\n");
			//echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
			echo("Send</button>\n");
			echo("</form>\n");
			echo("</td><td>\n");
			echo("<button name=\"back\" type=\"button\" onfocus=\"location.href='Transfer_Menu.php';\">\n");
			//echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
			echo("Back</button>\n");
			echo("</td>\n");
			echo("</tr>\n");
			echo("</table>\n");
		}
	}
}
//commit
ibase_commit($dbTran);
?>
</p>
<script type="text/javascript">
document.getssn.ssnfrom.focus();
</script>
</body>
</html>
