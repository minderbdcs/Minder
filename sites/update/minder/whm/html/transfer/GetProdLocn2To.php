<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get Location you are taking to</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
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

  <h4 align="left">Enter Location To</h4>

<script type="text/javascript">
function processEdit() {
  var mytype;
  mytype = checkLocn(document.getlocn.locationto.value); 
  if (mytype == "none")
  {
	/* alert("Not a Location"); */
  	document.getlocn.message.value="Not a Location";
  	return false;
  }
  if ( document.getlocn.locationto.value=="")
  {
  	document.getlocn.message.value="Must Enter the Location";
	document.getlocn.locationto.focus();
  	return false;
  }
  return true;
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
require_once "logme.php";
$wk_product_to = "";
//include "checkdatajs.php";
require_once "checkdata.php";
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');

// <form action="PostTo.php" method="post" name=getlocn>
//$bdcs_cookie = $_COOKIE["BDCSData"] . "|||||||||";
$bdcs_cookie = getBDCScookie($Link, $DBDevice, "transfer") . "||||||||||";
list($tran_type, $transaction_type, $location_from, $ssn_from, $product_from, $qty_from, $transaction2_type, $location_to, $qty_to) = explode("|", $bdcs_cookie);


	$owner = getBDCScookie($Link, $tran_device, "company" );
//var_dump($owner);
	if (isset($_POST['productto'])) 
	{
		$product_to = $_POST['productto'];
	}
	if (isset($_GET['productto'])) 
	{
		$product_to = $_GET['productto'];
	}
//var_dump($product_to);
	if (isset($product_to))
	{
	        // trim it
		$product_to = trim($product_to);
		if ($product_to <> "")
		{
			//echo ("productto:" . $product_to);
			// perhaps a product internal
			$field_type = checkForTypein($product_to, 'PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
//echo ("not a prod internal");
				// perhaps a product 13
				$field_type = checkForTypein($product_to, 'PROD_13' ); 
				if ($field_type == "none")
				{
//echo ("not a prod 13");
					// check prod exists for this
					$Query = "SELECT 1 from prod_profile where prod_id = '";
					$Query .= $product_to ."' and company_id= '";
					$Query .= $owner . "'";
					//$Result = $Link->query($Query);
					$wk_found = 0;
					if (($Result = ibase_query($Link, $Query)))
					{
						if (($Row = ibase_fetch_row($Result)))
						{
							$wk_found = $Row[0];
						}
					}
					ibase_free_result($Result);
					ibase_commit($dbTran);
					$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
					if ($wk_found == 0)
					{
//echo ("not in prod_profile");
						// a dont know
						unset($product_to);
						//echo ("dont know");
						header("Location: GetProdLocn1To.php?&message=Not+a+Product");
						exit();
					}
				}
				else
				{
//echo (" a prod 13");
					if ($startposn > 0)
					{
						$wk_realdata = substr($product_to,$startposn);
						$product_to = $wk_realdata;
					}
				}
			}
			else
			{
//echo (" a prod internal");
				if ($startposn > 0)
				{
					$wk_realdata = substr($product_to,$startposn);
					$product_to = $wk_realdata;
				}
			}
			//echo ("productto:" . $product_to);
			// check prod exists for this
			$Query = "SELECT 1 from prod_profile where prod_id = '";
			$Query .= $product_to ."' and company_id= '";
			$Query .= $owner . "'";
			//$Result = $Link->query($Query);
			$wk_found = 0;
			if (($Result = ibase_query($Link, $Query)))
			{
				if (($Row = ibase_fetch_row($Result)))
				{
					$wk_found = $Row[0];
				}
			}
			ibase_free_result($Result);
			ibase_commit($dbTran);
			$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
			if ($wk_found == 0)
			{
//echo ("not in prod_profile 2");
				// a dont know
				unset($product_to);
				//echo ("dont know");
				header("Location: GetProdLocn1To.php?&message=Not+a+Product");
				exit();
			}
		}
	}

if (isset($_POST['qtyto'])) 
{
	$have_qtyto = "Y";
}
if (isset($_GET['qtyto'])) 
{
	$have_qtyto = "Y";
}
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
	 			echo("<form action=\"PostTo.php\" ");
			}
		}
		else
		{
	 		echo("<form action=\"PostTo.php\" ");
		}
	}
 	else
 	{
 		echo("<form action=\"PostTo.php\" ");
 	}
 }
 echo(" METHOD=\"POST\" NAME=getlocn ONSUBMIT=\"return processEdit();\">\n");
 echo("<P>\n");
 echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\" ><br>\n");
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
if (isset($_POST['productto'])) 
{
	if ($_POST['productto'] <> '')
	{
		echo("Product: <input type=\"text\" name=\"productto\"");
		//echo(" value=\"".$_POST['productto']."\"");
		echo(" value=\"".$product_to."\"");
		echo(" size=\"30\"");
		echo(" maxlength=\"30\"><br>\n");
	} else {
		echo("Product: <input type=\"text\" name=\"producttoseen\"");
		echo(" value=\"All\"");
		echo(" size=\"30\"");
		echo(" maxlength=\"30\">\n");
		echo("<input type=\"hidden\" name=\"productto\"");
		echo(" value=\"".$_POST['productto']."\"");
		echo(" ><br>\n");
	}
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"" . $_POST['transaction2_type']. "\">");
	$wk_product_to = $_POST['productto'];
}
if (isset($_GET['productto'])) 
{
	if ($_GET['productto'] <> '')
	{
		echo("Product: <input type=\"text\" name=\"productto\"");
		//echo(" value=\"".$_GET['productto']."\"");
		echo(" value=\"".$product_to."\"");
		echo(" size=\"30\"");
		echo(" maxlength=\"30\"><BR>\n");
	} else {
		echo("Product: <input type=\"text\" name=\"producttoseen\"");
		echo(" value=\"All\"");
		echo(" size=\"30\"");
		echo(" maxlength=\"30\">\n");
		echo("<input type=\"hidden\" name=\"productto\"");
		echo(" value=\"".$_GET['productto']."\"");
		echo(" ><br>\n");
	}
	$wk_product_to = $_GET['productto'];
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"" . $_GET['transaction2_type']. "\">");
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
/*
if (isset($have_qtyto))
{
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"TRIS\">");
}
else
{
	echo("<input type=\"hidden\" name=\"transaction2_type\" value=\"TRLI\">");
}
*/
if ($wk_product_to > "")
{
        //        WHERE LOCATION.PROD_ID = '" . $wk_product_to . "'
        $Query ="SELECT FIRST 1 LOCATION.WH_ID, LOCATION.LOCN_ID
                FROM LOCATION
                JOIN ZONE ON ZONE.CODE = LOCATION.ZONE_C
                WHERE LOCATION.PROD_ID = '" . $product_to . "'
                AND (ZONE.DEFAULT_DEVICE_ID IS NOT NULL)
                ORDER BY LOCATION.LOCN_SEQ";
	// echo($Query); 
	if (($Result = ibase_query($Link, $Query)))
	{
		echo ("<table border=\"0\" align=\"left\">");
		echo ("<tr>");
		echo ("<td>Picks</td>");
		while (($Row = ibase_fetch_row($Result)))
		{
			echo ("<td>");
			echo ( $Row[0] . $Row[1]);

			echo ("</td>");
		}
		ibase_free_result($Result); 
		unset($Result); 
		echo ("</tr>");
		echo ("</table><br><br>");
	}
	//display a list of recommended locations 
	//$Query = "SELECT wh_id,locn_id from location where prod_id = '" . $wk_product_to . "' order by wh_id,locn_id " ;
	$Query = "SELECT wh_id,locn_id from location where prod_id = '" . $product_to . "' order by wh_id,locn_id " ;
	// echo($Query); 
	if (($Result = ibase_query($Link, $Query)))
	{
		echo ("<table border=\"0\" align=\"left\">");
		echo ("<tr>");
		echo ("<td>Home</td>");
		while (($Row = ibase_fetch_row($Result)))
		{
			echo ("<td>");
			echo ( $Row[0] . $Row[1]);

			echo ("</td>");
		}
		ibase_free_result($Result); 
		unset($Result); 
		echo ("</tr>");
		echo ("</table><br><br>");
	}
}
echo("Location: <input type=\"text\" name=\"locationto\"");
if (isset($_POST['locationto'])) 
{
	echo(" value=\"".$_POST['locationto']."\"");
}
if (isset($_GET['locationto'])) 
{
	echo(" value=\"".$_GET['locationto']."\"");
}
echo(" size=\"10\"");
//echo(" maxlength=\"10\"><BR>\n");
echo(" maxlength=\"14\" onchange=\"document.getlocn.submit()\"><BR>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</form>\n");
}
else
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
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\" id=>\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</form>\n");
*/
}
?>
</P>
<script type="text/javascript">
document.getlocn.locationto.focus();
</script>
</body>
</html>
