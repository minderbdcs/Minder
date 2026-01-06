<html>
<head>
<?php
include "viewport.php";
?>
<title>Modify a Group of Locations</title>
</head>
 <body BGCOLOR="#AAFFCC">
<script type="text/javascript">
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processQty() {
  if ( document.showstock.wh.value=="")
  {
  	document.showstock.message.value="Must Enter Warehouse";
	document.showstock.wh.focus();
  	return false;
  }
  if ( document.showstock.alfrom.value=="")
  {
  	document.showstock.message.value="Must Enter Aisle From";
	document.showstock.alfrom.focus();
  	return false;
  }
  if ( document.showstock.alto.value=="")
  {
  	document.showstock.message.value="Must Enter Aisle To";
	document.showstock.alto.focus();
  	return false;
  }
  if ( document.showstock.byfrom.value=="")
  {
  	document.showstock.message.vbyue="Must Enter Bay From";
	document.showstock.byfrom.focus();
  	return false;
  }
  if ( document.showstock.byto.value=="")
  {
  	document.showstock.message.value="Must Enter Bay To";
	document.showstock.byto.focus();
  	return false;
  }
  if ( document.showstock.shfrom.value=="")
  {
  	document.showstock.message.vshue="Must Enter Shelf From";
	document.showstock.shfrom.focus();
  	return false;
  }
  if ( document.showstock.shto.value=="")
  {
  	document.showstock.message.value="Must Enter Shelf To";
	document.showstock.shto.focus();
  	return false;
  }
  if ( document.showstock.cmfrom.value=="")
  {
  	document.showstock.message.vshue="Must Enter Compartment From";
	document.showstock.cmfrom.focus();
  	return false;
  }
  if ( document.showstock.cmto.value=="")
  {
  	document.showstock.message.value="Must Enter Compartment To";
	document.showstock.cmto.focus();
  	return false;
  }
  if ( document.showstock.alfrom.value > document.showstock.alto.value )
  {
  	document.showstock.message.value="From Aisle Greater than To Aisle";
	document.showstock.alto.focus();
  	return false;
  }
  if ( document.showstock.byfrom.value > document.showstock.byto.value )
  {
  	document.showstock.message.value="From Bay Greater than To Bay";
	document.showstock.byto.focus();
  	return false;
  }
  if ( document.showstock.shfrom.value > document.showstock.shto.value )
  {
  	document.showstock.message.value="From Shelf Greater than To Shelf";
	document.showstock.shto.focus();
  	return false;
  }
  if ( document.showstock.cmfrom.value > document.showstock.cmto.value )
  {
  	document.showstock.message.value="From Compartment Greater than To Compartment";
	document.showstock.cmto.focus();
  	return false;
  }
  document.showstock.message.value="Working";
  return true;
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$alfrom="00";
$byfrom="00";
$shfrom="00";
$cmfrom="00";
$alto="99";
$byto="99";
$shto="99";
$cmto="99";
$minqty="";
$maxqty="";
$reorderqty="";
$prod_id="";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
$wh = "00";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['alfrom'])) 
{
	$alfrom = $_POST["alfrom"];
}
if (isset($_POST['alto'])) 
{
	$alto = $_POST["alto"];
}
if (isset($_POST['byfrom'])) 
{
	$byfrom = $_POST["byfrom"];
}
if (isset($_POST['byto'])) 
{
	$byto = $_POST["byto"];
}
if (isset($_POST['shfrom'])) 
{
	$shfrom = $_POST["shfrom"];
}
if (isset($_POST['shto'])) 
{
	$shto = $_POST["shto"];
}
if (isset($_POST['cmfrom'])) 
{
	$cmfrom = $_POST["cmfrom"];
}
if (isset($_POST['cmto'])) 
{
	$cmto = $_POST["cmto"];
}
if (isset($_POST['minqty'])) 
{
	$minqty = $_POST["minqty"];
}
if (isset($_POST['maxqty'])) 
{
	$maxqty = $_POST["maxqty"];
}
if (isset($_POST['reorderqty'])) 
{
	$reorderqty = $_POST["reorderqty"];
}
if (isset($_POST['prod'])) 
{
	$prod_id = $_POST["prod"];
}
if (isset($_POST['wh'])) 
{
	$wh = $_POST["wh"];
}

if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (strlen($alfrom) < 2)
{
	$alfrom = sprintf("%-2.2s", $alfrom);
}
if (strlen($alto) < 2)
{
	$alto = sprintf("%-2.2s", $alto);
}
if (strlen($byfrom) < 2)
{
	$byfrom = sprintf("%-2.2s", $byfrom);
}
if (strlen($byto) < 2)
{
	$byto = sprintf("%-2.2s", $byto);
}
if (strlen($shfrom) < 2)
{
	$shfrom = sprintf("%-2.2s", $shfrom);
}
if (strlen($shto) < 2)
{
	$shto = sprintf("%-2.2s", $shto);
}
if (strlen($cmfrom) < 2)
{
	$cmfrom = sprintf("%-2.2s", $cmfrom);
}
if (strlen($cmto) < 2)
{
	$cmto = sprintf("%-2.2s", $cmto);
}
if ($image_x > 0 and $image_y > 0 and $wk_message == "Working")
{
	//echo('got image_x');
	//echo($image_x);
	//echo('got image_y');
	//echo($image_y);
/*
	if (!($Link = ibase_connect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/

	{
		$Query = "select locn_id from location";
		$Query .= " where wh_id = '" . $wh . "'";
		$Query .= " and substr(locn_id,1,2) between '" . $alfrom . "' and '" . $alto . "'";
		$Query .= " and substr(locn_id,3,4) between '" . $byfrom . "' and '" . $byto . "'";
		$Query .= " and substr(locn_id,5,6) between '" . $shfrom . "' and '" . $shto . "'";
		$Query .= " and substr(locn_id,7,8) between '" . $cmfrom . "' and '" . $cmto . "'";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Location!<BR>\n");
			exit();
		}
		$wk_cnt = 0;
		$wk_update = "";
		if ($minqty <> "")
		{
			$wk_update .= "min_qty = '" . $minqty . "' ";
		}
		if ($maxqty <> "")
		{
			$wk_update .= ",max_qty = '" . $maxqty . "' ";
		}
		if ($reorderqty <> "")
		{
			$wk_update .= ",reorder_qty = '" . $reorderqty . "' ";
		}
		if ($prod_id <> "")
		{
			$wk_update .= ",prod_id = '" . $prod_id . "' ";
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_locn = $Row[0];
			if ($wk_update <> "")
			{
				$wk_cnt = $wk_cnt + 1;
				$Query2 = "update location set " . $wk_update . " where wh_id = '" . $wh . "' and locn_id = '" . $wk_locn . "'";
				if (!($Result2 = ibase_query($Link, $Query2)))
				{
					echo("Unable to Update Location!<BR>\n");
					exit();
				}
			}
		}
		$message = "Updated " . $wk_cnt . " Locations";
	}
	//release memory
	ibase_free_result($Result);

	//commit
	ibase_commit($dbTran);

	//close
	//ibase_close($Link);

}
//else
{
	echo("<H4>Enter Values to Update</H4>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo (" <FORM action=\"" .  basename($_SERVER["PHP_SELF"]) . "\" method=\"post\" name=showstock onsubmit=\"return processQty()\" >");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly >\n");
	echo("<tr>");
	echo("<td>");
	echo ("Warehouse");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"wh\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$wh\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<th>");
	echo("</th>");
	echo("<th>");
	echo("From");
	echo("</th>");
	echo("<th>");
	echo("To");
	echo("</th>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Aisle");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"alfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$alfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"alto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$alto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Bay");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"byfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$byfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"byto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$byto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Shelf");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"shfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$shfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"shto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$shto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Compartment");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"cmfrom\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$cmfrom\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"cmto\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$cmto\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Min Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"minqty\"  ");
	echo(" size=\"6\" value=\"$minqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("ReOrder Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"reorderqty\"  ");
	echo(" size=\"6\" value=\"$reorderqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Max Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"maxqty\"  ");
	echo(" size=\"6\" value=\"$maxqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Product");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"prod\"  ");
	echo(" size=\"30\" value=\"$prod_id\" >\n");
	echo("</td>");
	echo("</tr>");
	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.showstock.wh.focus();</script>");
}
?>
</body>
</html>

