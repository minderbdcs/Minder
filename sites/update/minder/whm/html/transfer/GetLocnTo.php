<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get Location you are taking to</title>
<?php
include "viewport.php";
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
 <body bgcolor="#FFFFFF">

  <h4 align="left">Enter Location To</h4>

<?php
require_once 'DB.php';
include 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
//include "checkdatajs.php";
//include "logme.php";
require_once "logme.php";
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
//include "checkdata.php";
require_once "checkdata.php";

/**
 * log a message to logfile
 *
 * @param ibase_link $Link Connection to database
 * @param string $message
 */

function logtime2( $Link,  $message)
{
	$Query = "";
	$log = fopen('/data/tmp/transferGetLocnTO.log' , 'a');
		$wk_current_time = "";
		$Query = "select cast(cast('NOW' as timestamp) as char(24)) from control ";
		$Query = "select cast('NOW' as timestamp) from control ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table control<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_current_time =  $Row[0];
		}
		else
		{
			$wk_current_time = "";
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
		$wk_current_micro = microtime();
		$wk_current_time .= " " . $wk_current_micro;
		$wk_message = str_replace("'", "`", $message);
		
		// 1st try ip address of handheld
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s", $wk_current_time, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  ", $wk_current_time );
	}

	fwrite($log,"  ");
	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}


// <form action="PostTo.php" method="post" name=getlocn>
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
//$bdcs_cookie = $_COOKIE["BDCSData"] . "|||||||||";
$bdcs_cookie = getBDCScookie($Link, $tran_device, "transfer") . "|||||||||";
list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to) = explode("|", $bdcs_cookie);
if (isset($_POST['qtyto'])) 
{
	$have_qtyto = "Y";
}
if (isset($_GET['qtyto'])) 
{
	$have_qtyto = "Y";
}
$wk_ssnfrom = getBDCScookie($Link, $tran_device, "ssnfrom")  ;
if (isset($_POST['ssnfrom'])) 
{
	setBDCScookie($Link, $tran_device, "ssnfrom", $_POST['ssnfrom']) ;
	$wk_ssnfrom = $_POST['ssnfrom'];
}
if (isset($_GET['ssnfrom'])) 
{
	setBDCScookie($Link, $tran_device, "ssnfrom", $_GET['ssnfrom']) ;
	$wk_ssnfrom = $_GET['ssnfrom'];
}
if ($wk_ssnfrom <> '')
{
	$_POST['ssnfrom'] = $wk_ssnfrom;
}
$wk_locationfrom = getBDCScookie($Link, $tran_device, "locationfrom")  ;
if (isset($_POST['locationfrom'])) 
{
	setBDCScookie($Link, $tran_device, "locationfrom", $_POST['locationfrom']) ;
	$wk_locationfrom = $_POST['locationfrom'];
}
if (isset($_GET['locationfrom'])) 
{
	setBDCScookie($Link, $tran_device, "locationfrom", $_GET['locationfrom']) ;
	$wk_locationfrom = $_GET['locationfrom'];
}
if ($wk_locationfrom <> '')
{
	$_POST['locationfrom'] = $wk_locationfrom;
}
$wk_qtyto = getBDCScookie($Link, $tran_device, "qtyto")  ;
if (isset($_POST['qtyto'])) 
{
	setBDCScookie($Link, $tran_device, "qtyto", $_POST['qtyto']) ;
	$wk_qtyto = $_POST['qtyto'];
}
if (isset($_GET['qtyto'])) 
{
	setBDCScookie($Link, $tran_device, "qtyto", $_GET['qtyto']) ;
	$wk_qtyto = $_GET['qtyto'];
}
if ($wk_qtyto <> '')
{
	$_POST['qtyto'] = $wk_qtyto;
}
$wk_locationto = getBDCScookie($Link, $tran_device, "locationto")  ;
if (isset($_POST['locationto'])) 
{
	setBDCScookie($Link, $tran_device, "locationto", $_POST['locationto']) ;
	$wk_locationto = $_POST['locationto'];
}
if (isset($_GET['locationto'])) 
{
	setBDCScookie($Link, $tran_device, "locationto", $_GET['locationto']) ;
	$wk_locationto = $_GET['locationto'];
}
if ($wk_locationto <> '')
{
	// trim it
	$wk_locationto = trim($wk_locationto);
	$field_type = checkForTypein($wk_locationto, 'LOCATION' ); 
	if ($field_type != "none")
	{
		// a location
		$wk_locn_rep_check = $wk_locationto;
		if ($startposn > 0)
		{
			$wk_realdata = substr($wk_locationto,$startposn);
			$wk_locationto = $wk_realdata;
			setBDCScookie($Link, $tran_device, "locationto", $wk_locationto) ;
		}
	}
	$_POST['locationto'] = $wk_locationto;
}

// ==========================================================
{
	// how many on the device
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	$gotmore =  0;
	$Query = "SELECT COUNT(*) FROM ISSN WHERE LOCN_ID = '$DBDevice' AND (WH_ID NOT STARTING 'X')" ;
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
if (isset($_GET['message']))
{
	$wk_my_message = $_GET['message'];
	$wk_my_message = trim($wk_my_message);
}
if (isset($_POST['message']))
{
	$wk_my_message = $_POST['message'];
	$wk_my_message = trim($wk_my_message);
}
if ($gotmore ==  0)
{
 	echo("<form action=\"Transfer_Menu.php\" ");
} else {
 if ($tran_type == "SSN")
 {
	if (isset($have_qtyto))
	{
		if ($have_qtyto == "Y")
		{
 			echo("<form action=\"PostTo.php\" ");
		}
		else
		{
 			echo("<form action=\"GetSSNTo.php\" ");
		}
	}
	else
	{
 		echo("<form action=\"GetSSNTo.php\" ");
	}
 }
 else
 {
	 if ($tran_type == "PRODUCT")
	 {
		if (isset($have_qtyto))
		{
			if ($have_qtyto == "Y")
			{
	 			echo("<form action=\"PostTo.php\" ");
			}
			else
			{
	 			echo("<form action=\"GetProductTo.php\" ");
			}
		}
		else
		{
	 		echo("<form action=\"GetProductTo.php\" ");
		}
	}
 	else
 	{
 		echo("<form action=\"PostTo.php\" ");
 	}
 }
}
 echo(" METHOD=\"POST\" NAME=sendlocn >\n");

if (isset($_POST['ssnfrom'])) 
{
	echo("<input type=\"hidden\"  name=\"ssnfrom\" value=\"".$_POST['ssnfrom']."\" >");
}
elseif (isset($_GET['ssnfrom'])) 
{
	echo("<input type=\"hidden\" name=\"ssnfrom\" value=\"".$_GET['ssnfrom']."\" >");
}
if (isset($_POST['locationfrom'])) 
{
	echo("<input type=\"hidden\"  name=\"locationfrom\" value=\"".$_POST['locationfrom']."\" >");
}
elseif (isset($_GET['locationfrom'])) 
{
	echo("<input type=\"hidden\"  name=\"locationfrom\" value=\"".$_GET['locationfrom']."\" >");
}
if (isset($_POST['qtyto'])) 
{
	echo("<input type=\"hidden\"  name=\"qtyto\" value=\"".$_POST['qtyto']."\" >");
	$have_qtyto = "Y";
}
elseif (isset($_GET['qtyto'])) 
{
	echo("<input type=\"hidden\"  name=\"qtyto\" value=\"".$_GET['qtyto']."\" >");
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
echo("<input type=\"hidden\" name=\"locationto\"");
if (isset($_POST['locationto'])) 
{
	echo(" value=\"".$_POST['locationto']."\"");
}
elseif (isset($_GET['locationto'])) 
{
	echo(" value=\"".$_GET['locationto']."\"");
}
echo(" >\n");
if ($gotmore ==  0)
{
	echo("<input type=\"hidden\" name=\"message\"");
	if (isset($_GET['message']))
	{
		$wk_my_message = $_GET['message'];
		$wk_my_message = trim($wk_my_message);
		echo(" value=\"" . $wk_my_message . "\"");
		//echo "have get message";
	} elseif (isset($_POST['message'])) {
		$wk_my_message = $_POST['message'];
		$wk_my_message = trim($wk_my_message);
		//echo(" value=\"" . $wk_my_message . "\"");
		//echo "have post message";
		if ($wk_my_message == "")
		{
			echo(" value=\"None to Transfer\"");
		} else {
			echo(" value=\"" . $wk_my_message . "\"");
		}
	} else {
		echo(" value=\"None to Transfer\"");
	}
	echo(" >\n");
}
echo("</form>");
// ==================================================================================
$wk_ok_reason = "";
//if (isset($wk_locationto))
if ($wk_locationto != "")
{
	if (isset( $wk_locn_rep_check ))
	{
		$wk_locationto = $wk_locn_rep_check ;

	}
	$field_type = checkForTypein($wk_locationto, 'LOCATION' ); 
	if ($field_type != "none")
	{
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($wk_locationto,$startposn);
			$wk_locationto = $wk_realdata;
			setBDCScookie($Link, $tran_device, "locationto", $wk_locationto) ;
		}
		{
			// check the wh id
			$Query = "SELECT WH_ID  FROM WAREHOUSE WHERE WH_ID = '" . substr($wk_locationto,0,2)."'";
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
				$wk_log_message =  'Is a Location - Not a Valid Warehouse [' . $wk_locationto . ']';
				logtime2( $Link,  $wk_log_message );
			} else {
				$wk_do_submit = True;
				if (isset($wk_my_message))
				{
					if ($wk_my_message <> "")
					{
						$wk_do_submit = False;
						$wk_log_message =  'Is a Location  Valid WH_ID  [' . $wk_locationto . '] but have error message ' . $wk_my_message;
						logtime2( $Link,  $wk_log_message );
					}
				}
				if ($wk_do_submit)
				{
					$wk_log_message =  'Is a Location - [' . $wk_locationto . ']';
					logtime2( $Link,  $wk_log_message );
					echo("<script type=\"text/javascript\">\n");
					echo("document.sendlocn.submit();\n");
					echo("</script>\n");
				}
			}
		}
	} else {
		//$wk_ok_reason .= "Invalid Location";
		//$wk_ok_reason .= "Not a Location Barcode";
		// could be an old prechecked location
		// check the wh id
		$Query = "SELECT WH_ID  FROM WAREHOUSE WHERE WH_ID = '" . substr($wk_locationto,0,2)."'";
		// echo($Query); 
		$wk_wh_id_used = '';
		$wk_locn_id_used = '';
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
			$wk_ok_reason .= " Not a Location Barcode";
			$wk_log_message =  'Not a Location Not a Valid Warehouse [' . $wk_locationto . ']';
			logtime2( $Link,  $wk_log_message );
		} else {
			// check the locn id
			$Query = "SELECT LOCN_ID  FROM LOCATION WHERE WH_ID = '" . substr($wk_locationto,0,2)."'" ;
			$Query .= " AND LOCN_ID  = '" . substr($wk_locationto,2,strlen($wk_locationto) - 2)."'";
;
			// echo($Query); 
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_locn_id_used =  $Row[0];
					ibase_free_result($Result); 
					unset($Result); 
				}
			}
			if ($wk_locn_id_used == '')
			{
				$wk_ok_reason .= "Not a Location Barcode";
				$wk_log_message =  'Not a Location  Valid WH_ID  LOCN_ID not found [' . $wk_locationto . ']';
				logtime2( $Link,  $wk_log_message );
			} else {
				$wk_do_submit = True;
				if (isset($wk_my_message))
				{
					if ($wk_my_message <> "")
					{
						$wk_do_submit = False;
						$wk_log_message =  'Not a Location  Valid WH_ID  Valid LOCN_ID  [' . $wk_locationto . '] but have error message ' . $wk_my_message;
						logtime2( $Link,  $wk_log_message );
					}
				}
				if ($wk_do_submit)
				{
					$wk_log_message =  'Not a Location  Valid WH_ID  LOCN_ID found [' . $wk_locationto . ']';
					logtime2( $Link,  $wk_log_message );
					echo("<script type=\"text/javascript\">\n");
					echo("document.sendlocn.submit();\n");
					echo("</script>\n");
				}
			}
		}
	}
}
// ==================================================================================
echo("<form action=\"GetLocnTo.php\" ");
// echo(" METHOD=\"POST\" NAME=getlocn ONSUBMIT=\"return processEdit();\">\n");
 echo(" METHOD=\"POST\" NAME=getlocn >\n");
if ($gotmore > 0)
{
	$havedata = "Y";
	echo('CNT<input type="text" name="ssncnt" readonly size="3" value="' . $gotmore . '">');
	echo("<br>");
}
 echo("<P>\n");
 echo("<input type=\"text\" name=\"message\" readonly size=\"20\" class=\"message\" ");
if ($wk_ok_reason != "")
{
	echo ("value=\"" . $wk_ok_reason . "\"");
}
 echo(" ><br>\n");
	if (isset($_GET['message']))
	{
		$wk_my_message = $_GET['message'];
		//echo ("<B><FONT COLOR=RED>$wk_my_message</FONT></B>\n");
		$wk_my_message = trim($wk_my_message);
		if ($wk_my_message <> "Processed successfully") {
			echo ("<b><font color=RED>$wk_my_message</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			}
		} else {
			echo ("<b><font color=GREEN>$wk_my_message</font></b>\n");
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
if (isset($_POST['ssnfrom'])) 
{
	echo("SSN <input type=\"text\" readonly name=\"ssnfrom\" value=\"".$_POST['ssnfrom']."\" ><br>");
} 
elseif (isset($_GET['ssnfrom'])) 
{
	echo("SSN <input type=\"text\" readonly name=\"ssnfrom\" value=\"".$_GET['ssnfrom']."\" ><br>");
}
if (isset($_POST['locationfrom'])) 
{
	echo("From <input type=\"text\" readonly name=\"locationfrom\" value=\"".$_POST['locationfrom']."\" ><br>");
}
elseif (isset($_GET['locationfrom'])) 
{
	echo("From <input type=\"text\" readonly name=\"locationfrom\" value=\"".$_GET['locationfrom']."\" ><br>");
}
if (isset($_POST['qtyto'])) 
{
	echo("Qty <input type=\"text\" readonly name=\"qtyto\" value=\"".$_POST['qtyto']."\" ><br>");
	$have_qtyto = "Y";
}
elseif (isset($_GET['qtyto'])) 
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
echo("Location: <input type=\"text\" name=\"locationto\"");
if (isset($_POST['locationto'])) 
{
	echo(" value=\"".$_POST['locationto']."\"");
}
elseif (isset($_GET['locationto'])) 
{
	echo(" value=\"".$_GET['locationto']."\"");
}
echo(" size=\"15\" ");
//echo(" maxlength=\"10\"><BR>\n");
echo(" maxlength=\"15\" onchange=\"document.getlocn.submit()\"><br>\n");
/*
*/
{
	// html 4.0 browser
	// Create a table.
	$alt = "Send";
	echo ("<table border=\"0\" align=\"left\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<input type=\"IMAGE\" ");  
/*
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
/*
*/
}
//commit
ibase_commit($dbTran);
?>
</p>
<script type="text/javascript">
document.getlocn.locationto.focus();
</script>
</body>
</html>

