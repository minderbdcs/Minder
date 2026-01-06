<html>
<head>
<?php
 include "viewport.php";
?>
<title>Modify a Location</title>
</head>
 <body BGCOLOR="#AAFFCC">
<script type="text/javascript">
var wk_empty = "";
var wk_dome = "dome";
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
  if ( document.showlocn.location.value=="")
  {
  	document.showlocn.message.value="Must Enter Location";
	document.showlocn.location.focus();
  	return false;
  }
  document.showlocn.message.value="Working";
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

$minqty="";
$maxqty="";
$reorderqty="";
$prod_id="";
$replenish="";
$wk_dome="";
$name="";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['name'])) 
{
	$name = $_POST["name"];
}
if (isset($_POST['dome'])) 
{
	$wk_dome = $_POST["dome"];
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
if (isset($_POST['replenish'])) 
{
	$replenish = $_POST["replenish"];
}
if (isset($_POST['location'])) 
{
	$location = $_POST["location"];
}

if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (isset($location))
{
/*
	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/
	{
		$Query = "select l1.wh_id,l1.locn_id,l1.locn_name,l1.min_qty,l1.max_qty,l1.reorder_qty,l1.prod_id,l1.replenish,p1.short_desc from location l1 left outer join prod_profile p1 on l1.prod_id = p1.prod_id ";
		$Query .= " where wh_id = '" . substr($location,0,2) . "'";
		$Query .= " and locn_id = '" ; 
		$Query .= substr($location,2,strlen($location) - 2)."'";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Location!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_wh = $Row[0];
			$wk_locn = $Row[1];
			$wk_name = $Row[2];
			$wk_minqty = $Row[3];
			$wk_maxqty = $Row[4];
			$wk_reorderqty = $Row[5];
			$wk_prod_id = $Row[6];
			$wk_replenish = $Row[7];
			$wk_prod_desc = $Row[8];
		}
	}
}

if ($image_x == 0 and $image_y == 0 and $wk_message == "Working")
{
	/* if location changed then not working
	   else image_x = 1 and image y = 1
	*/
	if ($wk_dome == "dome")
	{
		// location changed
		$wk_message = "";
	}
	else
	{
		$image_x = 1;
		$image_y = 1;
	}

}
if ($image_x > 0 and $image_y > 0 and $wk_message == "Working")
{
	//echo('got image_x');
	//echo($image_x);
	//echo('got image_y');
	//echo($image_y);
	if (isset($wk_wh) and isset($wk_locn))
	{
		$wk_cnt = 0;
		$wk_update = "";
		if ($minqty <> $wk_minqty)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "min_qty = '" . $minqty . "' ";
			$wk_minqty = $minqty;
		}
		if ($maxqty <> $wk_maxqty)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "max_qty = '" . $maxqty . "' ";
			$wk_maxqty = $maxqty;
		}
		if ($reorderqty <> $wk_reorderqty)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "reorder_qty = '" . $reorderqty . "' ";
			$wk_reorderqty = $reorderqty;
		}
		if ($prod_id <> $wk_prod_id)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "prod_id = '" . $prod_id . "' ";
			$wk_prod_id = $prod_id;
		}
		if ($replenish <> $wk_replenish)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "replenish = '" . $replenish . "' ";
			$wk_replenish = $replenish;
		}
		if ($name <> $wk_name)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "locn_name = '" . $name . "' ";
			$wk_name = $name;
		}
		if ($wk_update <> "")
		{
			$wk_cnt = $wk_cnt + 1;
			$Query2 = "update location set " . $wk_update . " where wh_id = '" . $wk_wh . "' and locn_id = '" . $wk_locn . "'";
			//echo($Query2);
			if (!($Result2 = ibase_query($Link, $Query2)))
			{
				echo("Unable to Update Location!<BR>\n");
				exit();
			}
		}
		$message = "Updated Location";
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
	echo (" <FORM action=\"" .  basename($_SERVER["PHP_SELF"]) . "\" method=\"post\" name=showlocn onsubmit=\"return processQty()\" >");
	echo ("<INPUT type=\"hidden\" name=\"dome\" value=\"\" >\n");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly >\n");
	echo("<tr>");
	echo("<td>");
	echo ("Location");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"location\"  ");
	echo(" size=\"12\" maxlength=\"10\" value=\"$location\" onchange=\"document.showlocn.dome.value=wk_dome;document.showlocn.submit();\" onfocus=\"document.showlocn.location.value=wk_empty;\">\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>WH");
	echo("</td>");
	//echo("<td>$wk_wh");
	echo("<td>");
	if (isset($location))
	{
		echo("$wk_wh");
	}
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Locn");
	echo("</td>");
	//echo("<td>$wk_locn");
	if (isset($location))
	{
		echo("$wk_locn");
	}
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Name");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"name\"  ");
	echo(" size=\"50\" maxlength=\"50\" value=\"$wk_name\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");

	echo("<tr>");
	echo("<td>");
	echo ("Min Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"minqty\"  ");
	echo(" size=\"6\" value=\"$wk_minqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("ReOrder Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"reorderqty\"  ");
	echo(" size=\"6\" value=\"$wk_reorderqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Max Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"maxqty\"  ");
	echo(" size=\"6\" value=\"$wk_maxqty\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Product");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"prod\"  ");
	echo(" size=\"30\" value=\"$wk_prod_id\" >\n");
	echo("</td>");
	echo("</td>");
	echo("<td colspan=\"2\">");
	//echo ($wk_prod_desc);
	if (isset($wk_prod_desc))
	{
		echo ($wk_prod_desc);
	}
	echo("</td>\n");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Replenish");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"replenish\"  ");
	echo(" size=\"1\" value=\"$wk_replenish\" >\n");
	echo("</td>");
	echo("</tr>");
	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	if (!isset($location))
	{
		echo("<script type=\"text/javascript\">\n");
		echo("document.showlocn.location.focus();</script>");
	}
}
?>
</body>
</html>

