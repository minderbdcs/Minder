<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php
include "viewport.php";
?>
<title>Transfer Replenish</title>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "transaction.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['devicefrom'])) 
{
	$devicefrom = $_POST["devicefrom"];
}
if (isset($_POST['deviceto'])) 
{
	$deviceto = $_POST["deviceto"];
}
$wk_sysuser = "F";
// check user is capable
{
	$Query = "SELECT sys_admin from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}

echo (" <form action=\"GetReplenish.php\" method=\"post\" name=transreplenish>");
if (isset($devicefrom))
{
	if (isset($deviceto))
	{
		if ($deviceto <> $devicefrom)
		{
			//ok do update
			/* here do the transaction */
			$docommit = 1;
			$my_source = 'SSBSSKSSS';
			$my_message = "";
			$my_message = dotransaction_response("TRRT", "K", "", $devicefrom, $deviceto,"Transfer Replenish", 0, $my_source, $tran_user, $tran_device);
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
				$message = $my_responsemessage;
				echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
				//header("Location: Transfer_Menu.php?".$my_message);
				//exit();
			}
			else
			{
				echo("<h4>" . $my_responsemessage . "</h4>\n");
			}
		}
		else
		{
			echo("<h4>Select the To Device</h4>\n");
		}
	}
	else
	{
		echo("<h4>Select the To Device</h4>\n");
	}
}
else
{
	echo("<h4>Select the From Device</h4>\n");
}
echo("From Device:<select name=\"devicefrom\" size=\"1\" class=\"sel2\"");
echo(" onchange=\"document.transreplenish.submit()\" ><br>\n");
{
	$Query = "SELECT device_id from sys_equip";
	$Query .= " where device_type in ('HH','PC') order by device_id";
	// Create a table.
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		echo( "<OPTION value=\"" . $Row[0] . "\"");
		if (isset($devicefrom))
		{
			if ($devicefrom == $Row[0])
			{
				echo(" selected ");	
			}
		}
		echo(">" . $Row[0] . "\n");
	}
	
	//release memory
	ibase_free_result($Result);
}
echo("</select>");
	
if (isset($devicefrom))
{
	echo("<br>To Device:<select name=\"deviceto\" size=\"1\" class=\"sel2\"");
	echo(" onchange=\"document.transreplenish.submit()\" ><br>\n");
	$Query = "SELECT device_id from sys_equip";
	$Query .= " where device_type in ('HH','PC') order by device_id";
	// Create a table.
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		echo( "<OPTION value=\"" . $Row[0] . "\"");
		if (isset($deviceto))
		{
			if ($deviceto == $Row[0])
			{
				echo(" selected ");	
			}
		}
		echo(">" . $Row[0] . "\n");
	}
	
	//release memory
	ibase_free_result($Result);
	echo("</select>");
}
{
	//commit
	ibase_commit($dbTran);
	
	//close
	ibase_close($Link);
	echo("<table border=\"0\" align=\"left\">\n");
}
if ($wk_sysuser != "T")
{
	whm2buttons('Accept', 'Transfer_Menu.php?message=Not+Available+to+You',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.Back.submit();</script>");
}
else
{
	whm2buttons('Accept', 'Transfer_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
}
?>
</body>
</html>

