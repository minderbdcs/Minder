<?php
session_start();
?>
<html>
<head>
<title>Become Device</title>
<?php
include "viewport.php";
?>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

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

echo (" <FORM action=\"becomedevice.php\" method=\"post\" name=becomedevice>");
if (isset($devicefrom))
{
	$LogFile = "/tmp/login.log";
	//file_put_contents($LogFile, "LOGIN user:" . $user . " remote_addr: " . $_SERVER['REMOTE_ADDR'] .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
	list($save_tran_user, $save_tran_device, $save_login_type) = explode("|", $_COOKIE["SaveUser"]);
	$DBDevice = $devicefrom;
	$UserName = $tran_user;
	$DBLoginType = $save_login_type;
	setcookie("LoginUser","$UserName|$DBDevice",time()+86400,"/");
	setcookie("SaveUser","$UserName|$DBDevice|$DBLoginType",time()+1111000,"/");
        $_SESSION['LoginUser'] =  "$UserName|$DBDevice" ;
	$_SESSION['SaveUser'] =  "$UserName|$DBDevice|$DBLoginType" ;
	file_put_contents($LogFile, "become device " .$DBDevice . "  from " . $save_tran_device . " on " .$_SERVER['REMOTE_ADDR'] . " setcookie user and saveuser " .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND     );
	$_SESSION['LoginTime'] =  time();
	echo "You are Now Device " . $DBDevice;

}
else
{
	echo("<h4>Select the Device</h4>\n");
}
echo("Device:<SELECT name=\"devicefrom\" size=\"1\" class=\"sel2\"");
echo(" onchange=\"document.becomedevice.submit()\" ><br>\n");
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
echo("</SELECT>");
	
{
	//commit
	ibase_commit($dbTran);
	
	//close
	ibase_close($Link);
	echo("<br><TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
}
if ($wk_sysuser != "T")
{
	whm2buttons('Accept', 'util_Menu.php?message=Not+Available+to+You',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.Back.submit();</script>");
}
else
{
	whm2buttons('Accept', 'util_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
}
?>
</body>
</html>
