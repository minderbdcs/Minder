<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php
 include "viewport.php";
?>
</head>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "transaction.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['locnto']))
{
	$locnto = $_POST['locnto'];
}
if (isset($_GET['locnto']))
{
	$locnto = $_GET['locnto'];
}
if (isset($_POST['locnfrom']))
{
	$locnfrom = $_POST['locnfrom'];
}
if (isset($_GET['locnfrom']))
{
	$locnfrom = $_GET['locnfrom'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select load_prefix from control ";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Control !<BR>\n");
	exit();
}

//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

echo("<FONT size=\"2\">\n");
if (isset($locnto) )
{
	echo("<form action=\"GetMobile.php\" method=\"post\" name=getmobile\n>");
}
else
{
	echo("<form action=\"GetMobile.php\" method=\"post\" name=getmobile\n>");
}

if (isset($locnto))
{
	$Query = "select locn_name from location";
	$Query .= " where wh_id = '" .  substr($locnto,0,2);
	$Query .= "' and locn_id = '" . substr($locnto,2,strlen($locnto) - 2)."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Location !<BR>\n");
		echo("To Location:<input type=\"text\" name=\"locnto\" size=\"10\" maxlength=\"10\" value=\"$locnto\">");
		unset($locnto);
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			echo("To Location <input type=\"text\" name=\"locnto\" readonly size=\"10\" value=\"$locnto\">");
			echo(" <input type=\"text\" name=\"locntoname\" readonly value=\"$Row[0]\"><BR>");
		}
		else
		{
			echo("Invalid Location !<BR>\n");
			echo("To Location:<input type=\"text\" name=\"locnto\" size=\"10\" maxlength=\"10\" value=\"$locnto\">");
			unset($locnto);
		}

	}
}
else
{
	echo("To Location:<input type=\"text\" name=\"locnto\" size=\"10\" maxlength=\"10\" value=\"\">");

}
$docommit = 0;
if (isset($locnto))
{
	if (isset($locnfrom))
	{
		$Query = "select locn_name from location";
		$Query .= " where wh_id = '" .  substr($locnfrom,0,2);
		$Query .= "' and locn_id = '" . substr($locnfrom,2,strlen($locnfrom) - 2)."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Get Location !<BR>\n");
			echo("Mobile Location:<input type=\"text\" name=\"locnfrom\" size=\"10\" maxlength=\"10\" value=\"$locnfrom\" >\n");
			unset($locnfrom);
		}
		else
		{
			if ( ($Row = ibase_fetch_row($Result)) ) {
				echo("Mobile Location:<input type=\"text\" name=\"locnfrom\" size=\"10\" maxlength=\"10\"  >\n");
				echo(" <input type=\"text\" name=\"locntoname\" readonly value=\"$Row[0]\"><BR>");
				/* here do the transaction */
				$docommit = 1;
				$my_source = 'SSBSSKSSS';
				$my_message = "";
				$my_message = dotransaction_response("TRMI", "L", "", $locnto, $locnfrom, "transfer mobile location", 0, $my_source, $tran_user, $tran_device);
				if ($my_message > "")
				{
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				}
				else
				{
					$my_responsemessage = "";
				}
				if ($my_responsemessage <> "Processed successfully ")
				{
					$message .= $my_responsemessage;
					echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
					//header("Location: Transfer_Menu.php?".$my_message);
					//exit();
				}
			}
			else
			{
				echo("Invalid Location !<BR>\n");
				echo("Mobile Location:<input type=\"text\" name=\"locnfrom\" size=\"10\" maxlength=\"10\" value=\"$locnfrom\" >\n");
				unset($locnfrom);
			}
	
		}
	}
	else
	{
		echo("Mobile Location:<input type=\"text\" name=\"locnfrom\" size=\"10\" maxlength=\"10\" >\n");
	}
}

echo ("<table>\n");
echo ("<tr>\n");
if (isset($locnto))
{
	echo("<th>Enter the Mobile Location to Use</th>\n");
}
else
{
	echo("<th>Enter the To Location</th>\n");
}
echo ("</tr>\n");
echo ("</table>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<input type=\"submit\" name=\"submit\" value=\"Accept\">\n");
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
	echo("<form action=\"./Transfer_Menu.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<table border=\"0\" align=\"left\">");
	whm2buttons('Accept', './Transfer_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./Transfer_Menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
if (!isset($locnto) )
{
	//release memory
	ibase_free_result($Result);

	//commit
	//ibase_commit($dbTran);
}
if ($docommit == 1)
{
	$Query = "select load_prefix from control ";
	//echo($Query);
	
	//release memory
	//ibase_free_result($Result);

	//commit
	//ibase_commit($dbTran);
}

//close
//ibase_close($Link);

?>
<script type="text/javascript">
<?php
{
	if (isset($locnto))
	{
		echo("document.getmobile.locnfrom.focus();\n");
	}
	else
	{
		echo("document.getmobile.locnto.focus();\n");
	}
}
?>
</script>
</html>

