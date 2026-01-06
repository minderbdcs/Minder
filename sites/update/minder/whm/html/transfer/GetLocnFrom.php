<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Get Location you are taking from</title>
<?php
require_once 'DB.php';
require 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
//include "checkdatajs.php";
//include "logme.php";
require_once "logme.php";
// clear cookie
setBDCScookie($Link, $tran_device, "transfer", "");
setBDCScookie($Link, $tran_device, "BDCSData", "");
setBDCScookie($Link, $tran_device, "ssnfrom", "") ;
setBDCScookie($Link, $tran_device, "locationfrom", "") ;
setBDCScookie($Link, $tran_device, "qtyto","")  ;
setBDCScookie($Link, $tran_device, "locationto", "") ;
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
//include "checkdata.php";
require_once "checkdata.php";
if (isset($_POST['locationfrom'])) 
{
	$locationfrom = $_POST["locationfrom"];
}
if (isset($_GET['locationfrom'])) 
{
	$locationfrom = $_GET["locationfrom"];
}

$wk_ok_reason = "";
if (isset($locationfrom))
{
	// trim it
	$locationfrom = trim($locationfrom);
	$field_type = checkForTypein($locationfrom, 'LOCATION' ); 
	if ($field_type != "none")
	{
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($locationfrom,$startposn);
			$locationfrom = $wk_realdata;
		}
		{
			// check the wh id
			$Query = "SELECT WH_ID  FROM WAREHOUSE WHERE WH_ID = '" . substr($locationfrom,0,2)."'";
			// echo($Query); 
			$wk_wh_id_used = '';
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_wh_id_used =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
			if ($wk_wh_id_used == '')
			{
				$wk_ok_reason .= "Not a Valid Warehouse";
			} else {
				setBDCScookie($Link, $tran_device, "locationfrom", $locationfrom);
				//$wk_ok_reason .= "about to header";
				header ("Location: PostFrom.php?transaction_type=" . urlencode("TRLO") . "&tran_type=" . urlencode("LOCATION") . "&locationfrom=" . urlencode($locationfrom));
			}
		}
	} else {
		// not a location
		// try a device
		$field_type = checkForTypein($locationfrom, 'DEVICE' ); 
		if ($field_type != "none")
		{
			// a device
			if ($startposn > 0)
			{
				$wk_realdata = substr($locationfrom,$startposn);
				$locationfrom = $wk_realdata;
			}
			echo("device:" . $locationfrom);
			// get the transit location to use
			$Query = "SELECT FIRST 1 WH_ID || LOCN_ID AS LOCN_IN, CASE WHEN WH_ID = 'SY' THEN '00' ELSE WH_ID END AS DEVICE_SEQ  FROM LOCATION  WHERE LOCN_ID = '" . substr($locationfrom,0,2)."' ORDER BY DEVICE_SEQ";
			echo ($Query);
			$wk_device_locn_id_used = '';
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_device_locn_id_used =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
			if ($wk_device_locn_id_used == '')
			{
				$wk_ok_reason .= "Not a Valid Device";
			} else {
				$locationfrom = $wk_device_locn_id_used;
			}
			echo("device2:" . $locationfrom);
			// =========================================
			{
				// check the wh id
				$Query = "SELECT WH_ID  FROM WAREHOUSE WHERE WH_ID = '" . substr($locationfrom,0,2)."'";
				// echo($Query); 
				$wk_wh_id_used = '';
				if (($Result = ibase_query($Link, $Query)))
				{
					if (($Row = ibase_fetch_row($Result)))
					{
						$wk_wh_id_used =  $Row[0];
						ibase_free_result($Result); 
						unset($Result); 
					}
				}
				if ($wk_wh_id_used == '')
				{
					$wk_ok_reason .= "Not a Valid Warehouse";
				} else {
					setBDCScookie($Link, $tran_device, "locationfrom", $locationfrom);
					//$wk_ok_reason .= "about to header";
					header ("Location: PostFrom.php?transaction_type=" . urlencode("TRLO") . "&tran_type=" . urlencode("LOCATION") . "&locationfrom=" . urlencode($locationfrom));
				}
			}
		} else {
			// not a location or device
			$locationfrom = "";
			//$wk_ok_reason .= "Invalid Location";
			$wk_ok_reason .= "Not a Location Barcode";
		}
	}
}
/*
<script type="text/javascript">
function processEdit() {
-* # check for valid location *-
  var mytype;
  -* mytype = checkLocn(document.getlocn.locationfrom.value);  *-
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
  {
	return true;
  }
}
</script>
*/
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

  <h3 ALIGN="LEFT">Enter Location From</h3>

<?php
if ($wk_ok_reason != "")
{
	echo ("<b><font color=red>$wk_ok_reason</font></b>\n");
}
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
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
	}
 echo(" <br>\n");
//echo("<form action=\"PostFrom.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">\n");
echo("<form action=\"GetLocnFrom.php\" method=\"post\" name=getlocn >\n");
echo("<p>\n");
include "2buttons.php";
echo("<input type=\"hidden\" name=\"transaction_type\" value=\"TRLO\">");
echo("<input type=\"hidden\" name=\"tran_type\" value=\"LOCATION\">");
echo("Location: <input type=\"text\" name=\"locationfrom\"");
if (isset($_POST['locationfrom'])) 
{
	echo(" value=\"".$_POST['locationfrom']."\"");
}
if (isset($_GET['locationfrom'])) 
{
	echo(" value=\"".$_GET['locationfrom']."\"");
}
echo(" size=\"15\"");
//echo(" maxlength=\"15\" ONBLUR=\"return processEdit();\" ><BR>\n");
//echo(" maxlength=\"15\" onblur=\"document.getlocn.submit();\" ><BR>\n");
echo(" maxlength=\"15\"  ><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<table border=\"0\" align=\"left\">");
	whm2buttons("Send","Transfer_Menu.php","Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='Transfer_Menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

	//commit
	//$Link->commit();
	ibase_commit($dbTran);
	
	//close
	//$Link->disconnect();
	//ibase_close($Link);
?>
</p>
<script type="text/javascript">
document.getlocn.locationfrom.focus();
</script>
</body>
</html>
