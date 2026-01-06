<html>
<head>
<?php
 include "viewport.php";
?>
<title>Mark a Location</title>
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
include "checkdata.php";
// Set the variables for the database access:

//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

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
if (isset($_POST['location'])) 
{
	$location = $_POST["location"];
}
if (isset($_GET['location'])) 
{
	$location = $_GET["location"];
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
	$field_type = checkForTypein($location, 'LOCATION' ); 
	if ($field_type != "none")
	{
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($location,$startposn);
			$location = $wk_realdata;
		}
	}
	else
	{
		// not a location
	}
}
if (isset($location))
{
	{
		$Query = "select l1.wh_id,l1.locn_id,l1.locn_name,mark_date from location l1  ";
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
			$wk_mark_date = $Row[3];
		}
	}
}

//if ($image_x == 0 and $image_y == 0 and $wk_message == "Working")
if (isset($location))
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
//if ($image_x > 0 and $image_y > 0 and $wk_message == "Working")
if ($image_x > 0 and $image_y > 0 )
{
	//echo('got image_x');
	//echo($image_x);
	//echo('got image_y');
	//echo($image_y);
	if (isset($wk_wh) and isset($wk_locn))
	{
		$wk_cnt = 0;
		$wk_update = "";
		$wk_update .= "mark_date = 'NOW'  ";
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
		$message = "Updated Location" . $wk_wh . "-" . $wk_locn;
	}
	if (isset($Result))
	{
		//release memory
		ibase_free_result($Result);
	}

	//commit
	ibase_commit($dbTran);

	//close
	//ibase_close($Link);

}
//else
{
	echo("<H4>Enter Values to Mark</H4>\n");
	echo (" <FORM action=\"" .  basename($_SERVER["PHP_SELF"]) . "\" method=\"post\" name=showlocn onsubmit=\"return processQty()\" >");
	echo ("<INPUT type=\"hidden\" name=\"dome\" value=\"\" >\n");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly ><br>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<tr>");
	echo("<td>");
	echo ("Location");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"location\"  ");
	if (isset($wk_wh))
	{
		echo(" size=\"15\" maxlength=\"15\" value=\"$location\" onchange=\"document.showlocn.dome.value=wk_dome;document.showlocn.submit();\" onfocus=\"document.showlocn.location.value=wk_empty;\">\n");
	} else {
		echo(" size=\"15\" maxlength=\"15\" value=\"\" onchange=\"document.showlocn.dome.value=wk_dome;document.showlocn.submit();\" onfocus=\"document.showlocn.location.value=wk_empty;\">\n");
	}
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>WH");
	echo("</td>");
	if (isset($wk_wh))
	{
		echo("<td>$wk_wh");
	} else {
		echo("<td>");
	}
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Locn");
	echo("</td>");
	if (isset($wk_wh))
	{
		echo("<td>$wk_locn");
	} else {
		echo("<td>");
	}
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Name");
	echo("</td>");
	echo("<td>");
	if (isset($wk_name))
	{
		echo("$wk_name");
	}
	echo("</td>");
	echo("</tr>");

	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	//if (!isset($location))
	{
		echo("<script type=\"text/javascript\">\n");
		echo("document.showlocn.location.focus();</script>");
	}
}
?>
</body>
</html>

