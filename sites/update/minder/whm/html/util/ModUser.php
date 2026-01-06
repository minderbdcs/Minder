<html>
<head>
<?php
 include "viewport.php";
?>
<title>Modify a User</title>
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
  if ( document.showlocn.user_id.value=="")
  {
  	document.showlocn.message.value="Must Enter User ID";
	document.showlocn.user_id.focus();
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

$wk_password = "";
$wk_sequence = "";
$wk_direction = "";
$wk_sysadmin = "";
$wk_editable = "";
$wk_wh_id = "";
$wk_user_type = "";
$wk_inventory = "";
$wk_dome="";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['password'])) 
{
	$password = $_POST["password"];
}
if (isset($_POST['dome'])) 
{
	$wk_dome = $_POST["dome"];
}
if (isset($_POST['sequence'])) 
{
	$sequence = $_POST["sequence"];
}
if (isset($_POST['direction'])) 
{
	$direction = $_POST["direction"];
}
if (isset($_POST['sysadmin'])) 
{
	$sysadmin = $_POST["sysadmin"];
}
if (isset($_POST['editable'])) 
{
	$editable = $_POST["editable"];
}
if (isset($_POST['wh_id'])) 
{
	$wh_id = $_POST["wh_id"];
}
if (isset($_POST['user_type'])) 
{
	$user_type = $_POST["user_type"];
}
if (isset($_POST['inventory'])) 
{
	$inventory = $_POST["inventory"];
}
if (isset($_POST['user_id'])) 
{
	$user_id = $_POST["user_id"];
}

if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (isset($user_id))
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
		$Query = "select user_id, pass_word, pick_sequence, pick_direction, sys_admin, editable, default_wh_id, user_type, inventory_operator from sys_user ";
		$Query .= " where user_id = '" . $user_id . "'";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read user_id!<BR>\n");
			exit();
		}
		$wk_user_found = "F";
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_user_found = "T";
			$wk_user = $Row[0];
			$wk_password = $Row[1];
			$wk_sequence = $Row[2];
			$wk_direction = $Row[3];
			$wk_sysadmin = $Row[4];
			$wk_editable = $Row[5];
			$wk_wh_id = $Row[6];
			$wk_user_type = $Row[7];
			$wk_inventory = $Row[8];
		}
	}
}

if ($image_x == 0 and $image_y == 0 and $wk_message == "Working")
{
	/* if user_id changed then not working
	   else image_x = 1 and image y = 1
	*/
	if ($wk_dome == "dome")
	{
		// user_id changed
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
	if (isset($wk_user) and $wk_user <> "")
	{
		$wk_cnt = 0;
		$wk_update = "";
		if ($password <> $wk_password)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "pass_word = '" . $password . "' ";
			$wk_password = $password;
		}
		if ($sequence <> $wk_sequence)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "pick_sequence = '" . $sequence . "' ";
			$wk_sequence = $sequence;
		}
		if ($direction <> $wk_direction)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "pick_direction = '" . $direction . "' ";
			$wk_direction = $direction;
		}
		if ($sysadmin <> $wk_sysadmin)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "sys_admin = '" . $sysadmin . "' ";
			$wk_sysadmin = $sysadmin;
		}
		if ($editable <> $wk_editable)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "editable = '" . $editable . "' ";
			$wk_editable = $editable;
		}
		if ($wh_id <> $wk_wh_id)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "default_wh_id = '" . $wh_id . "' ";
			$wk_wh_id = $wh_id;
		}
		if ($user_type <> $wk_user_type)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "user_type = '" . $user_type . "' ";
			$wk_user_type = $user_type;
		}
		if ($inventory <> $wk_inventory)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "inventory_operator = '" . $inventory . "' ";
			$wk_inventory = $inventory;
		}
		if ($wk_update <> "")
		{
			$wk_cnt = $wk_cnt + 1;
			$Query2 = "update sys_user set " . $wk_update . " where user_id = '" . $wk_user . "'";
			//echo($Query2);
			if (!($Result2 = ibase_query($Link, $Query2)))
			{
				echo("Unable to Update user_id!<BR>\n");
				exit();
			}
		}
		$message = "Updated User";
	}
	else
	{
		if ($wk_user_found == "F")
		{
			// a new user
			$wk_cnt = 0;
			$wk_update = "'" . $user_id . "'";
			$wk_update_fields = "user_id";
			$wk_user = $user_id;
			//if ($password <> "")
			{
				$wk_update_fields .= ",pass_word ";
				$wk_update .= ", '" . $password . "' ";
				$wk_password = $password;
			}
			if ($sequence <> "")
			{
				$wk_update_fields .= ",pick_sequence ";
				$wk_update .= ", '" . $sequence . "' ";
				$wk_sequence = $sequence;
			}
			if ($direction <> "")
			{
				$wk_update_fields .= ",pick_direction ";
				$wk_update .= ", '" . $direction . "' ";
				$wk_direction = $direction;
			}
			if ($sysadmin <> "")
			{
				$wk_update_fields .= ",sys_admin ";
				$wk_update .= ", '" . $sysadmin . "' ";
				$wk_sysadmin = $sysadmin;
			}
			if ($editable <> "")
			{
				$wk_update_fields .= ",editable ";
				$wk_update .= ", '" . $editable . "' ";
				$wk_editable = $editable;
			}
			if ($wh_id <> "")
			{
				$wk_update_fields .= ",default_wh_id ";
				$wk_update .= ", '" . $wh_id . "' ";
				$wk_wh_id = $wh_id;
			}
			if ($user_type <> "")
			{
				$wk_update_fields .= ",user_type ";
				$wk_update .= ", '" . $user_type . "' ";
				$wk_user_type = $user_type;
			}
			if ($inventory <> "")
			{
				$wk_update_fields .= ",inventory_operator ";
				$wk_update .= ", '" . $inventory . "' ";
				$wk_inventory = $inventory;
			}
			{
				$wk_cnt = $wk_cnt + 1;
				$Query2 = "insert into sys_user (" . $wk_update_fields . ") values (" . $wk_update . ")";
				//echo($Query2);
				if (!($Result2 = ibase_query($Link, $Query2)))
				{
					echo("Unable to Update user_id!<BR>\n");
					exit();
				}
			}
			$message = "Added User";
		}
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
	echo ("User ID");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"user_id\"  ");
	echo(" size=\"10\" maxlength=\"10\" value=\"$user_id\" onchange=\"document.showlocn.dome.value=wk_dome;document.showlocn.submit();\" onfocus=\"document.showlocn.user_id.value=wk_empty;\">\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>WH");
	echo("</td>");
	echo("<td>$wk_wh_id");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Password");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"password\"  ");
	echo(" size=\"10\" maxlength=\"10\" value=\"$wk_password\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");

	echo("<tr>");
	echo("<td>");
	echo ("Sys Admin");
	echo("</td>");
	echo("<td colspan=\"2\">");
/*
	echo ("<INPUT type=\"text\" name=\"sysadmin\"  ");
	echo(" size=\"1\" maxlength=\"1\" value=\"$wk_sysadmin\" >\n");
*/
	echo("<select name=\"sysadmin\" >\n");
		if ("F" == $wk_sysadmin)
		{
			echo( "<option value=\"F\" selected >F\n");
		}
		else
		{
			echo( "<option value=\"F\">F\n");
		}
		if ("T" == $wk_sysadmin)
		{
			echo( "<option value=\"T\" selected >T\n");
		}
		else
		{
			echo( "<option value=\"T\">T\n");
		}
	echo("</select>");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Editable");
	echo("</td>");
	echo("<td colspan=\"2\">");
/*
	echo ("<INPUT type=\"text\" name=\"editable\"  ");
	echo(" size=\"1\" maxlength=\"1\" value=\"$wk_editable\" >\n");
*/
	echo("<select name=\"editable\" >\n");
		if ("F" == $wk_editable)
		{
			echo( "<option value=\"F\" selected >F\n");
		}
		else
		{
			echo( "<option value=\"F\">F\n");
		}
		if ("T" == $wk_editable)
		{
			echo( "<option value=\"T\" selected >T\n");
		}
		else
		{
			echo( "<option value=\"T\">T\n");
		}
	echo("</select>");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Default WH ID");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"wh_id\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$wk_wh_id\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("User Type");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"user_type\"  ");
	echo(" size=\"2\" maxlength=\"2\" value=\"$wk_user_type\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Inventory Op");
	echo("</td>");
	echo("<td colspan=\"2\">");
/*
	echo ("<INPUT type=\"text\" name=\"inventory\"  ");
	echo(" size=\"1\" maxlength=\"1\" value=\"$wk_inventory\" >\n");
*/
	echo("<select name=\"inventory\" >\n");
		if ("F" == $wk_inventory)
		{
			echo( "<option value=\"F\" selected >F\n");
		}
		else
		{
			echo( "<option value=\"F\">F\n");
		}
		if ("T" == $wk_inventory)
		{
			echo( "<option value=\"T\" selected >T\n");
		}
		else
		{
			echo( "<option value=\"T\">T\n");
		}
	echo("</select>");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Pk Sequence");
	echo("</td>");
	echo("<td colspan=\"2\">");
/*
	echo ("<INPUT type=\"text\" name=\"sequence\"  ");
	echo(" size=\"2\" value=\"$wk_sequence\" >\n");
*/

	echo("<select name=\"sequence\" >\n");
	$Query = "select code,description from options where group_code = 'PICK_SEQ'  order by code "; 
	
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Sequence!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] == $wk_sequence)
		{
			echo( "<option value=\"$Row[0]\" selected >$Row[0] Starts $Row[1]\n");
		}
		else
		{
			echo( "<option value=\"$Row[0]\">$Row[0] Starts $Row[1]\n");
		}
	}
	//release memory
	ibase_free_result($Result);
	
	echo("</select>");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Pk Direction");
	echo("</td>");
	echo("<td colspan=\"2\">");
/*
	echo ("<INPUT type=\"text\" name=\"direction\"  ");
	echo(" size=\"1\" value=\"$wk_direction\" >\n");
*/

	echo("<select name=\"direction\" >\n");
	$Query = "select code,description from options where group_code = 'PICK_DIR'  order by code "; 
	
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Direction!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] == $wk_direction)
		{
			echo( "<option value=\"$Row[0]\" selected >$Row[0] $Row[1]\n");
		}
		else
		{
			echo( "<option value=\"$Row[0]\">$Row[0] $Row[1]\n");
		}
	}
	//release memory
	ibase_free_result($Result);
	
	echo("</select>");

	echo("</td>");
	echo("</tr>");
	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	if (!isset($user_id))
	{
		echo("<script type=\"text/javascript\">\n");
		echo("document.showlocn.user_id.focus();</script>");
	}
}
?>
</body>
</html>

