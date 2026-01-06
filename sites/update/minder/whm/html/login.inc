<?php
session_start();
include "db_access.php";
require_once 'DB.php';

if (!isset($wk_header_prefix))
{
	$wk_header_prefix = "..";
}
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	// header ("Location: ../login/login.php?Message=LoggedOut");
	//$demomode = 1;
	if (isset($demomode))
	{
		setcookie("LoginUser","BDCS|MA",time()+86400,"/");
		//setcookie("SaveUser","BDCS|MA|TR",time()+1111000,"/");
		setcookie("SaveUser","BDCS|MA|PR",time()+1111000,"/");
	}
	else
	{
		if (isset($_SESSION['LoginUser'])) 
		{
			setcookie("LoginUser",$_SESSION['LoginUser'],0,"/");
		} else {
			//header ("Location: ../login/login.php?Message=LoggedOut");
			header ("Location: " . $wk_header_prefix . "/login/login.php?Message=LoggedOut");
			exit();
		}
	}
}
if (isset($_SESSION["LoginTime"]))
{
	if (($_SESSION['LoginTime'] + 86400 ) < time())
	{
		// last login over a day old
		//header ("Location: ../login/login.php?Message=LoggedOut");
		header ("Location: " . $wk_header_prefix . "/login/login.php?Message=LoggedOut");
		exit();
	}
}

// check the current device is still logged on
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Can't connect to DATABASE!");
	//exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// have		$_SESSION['CURRENT_IP_ADDRESS'] ;
//		$_SESSION['SaveUser']  ;
if (isset($_SESSION['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_SESSION['SaveUser']);

// check the sys_equip for the device is populated current_person and current_logged_on
// current person must be my person
	$wk_DBHasDevice = 0;
	$wk_query = "select 1 from sys_equip where device_id = '" . $DBDevice . "' and current_person = '" . $UserName . "' and (current_logged_on is not null)";
	if (!($Result2 = ibase_query($Link, $wk_query)))
	{
		echo("Unable to query tables!<BR>\n");
		exit();
	}
	else
	if (($Row2 = ibase_fetch_row($Result2)))
	{
		$wk_DBHasDevice =  $Row2[0];
	}
 
	//release memory
	ibase_free_result($Result2);

// also the session description for 'CURRENT_IP_ADDRESS'  has description = the session value
	$wk_query = "select 1 from session where device_id = '" . $DBDevice . "' and code = 'CURRENT_IP_ADDRESS'  and description = '" . $_SESSION['CURRENT_IP_ADDRESS'] . "'";
	$wk_DBHasIP = 0;
	if (!($Result2 = ibase_query($Link, $wk_query)))
	{
		echo("Unable to query tables!<BR>\n");
		exit();
	}
	else
	if (($Row2 = ibase_fetch_row($Result2)))
	{
		$wk_DBHasIP =  $Row2[0];
	}
 
	//release memory
	ibase_free_result($Result2);
//echo  "ip address " . $_SESSION['CURRENT_IP_ADDRESS'];
//echo "saveuser " .		$_SESSION['SaveUser']  ;
//var_dump($wk_DBHasDevice);
//var_dump($wk_DBHasIP);
	if (($wk_DBHasDevice == 0) or ($wk_DBHasIP == 0))
	{
//echo  "either device or ip mismatch " ;
		//  either a different ip address or a different user is using the device or device is logged out
		//header ("Location: ../login/login.php?Message=LoggedOut");
		header ("Location: " . $wk_header_prefix . "/login/login.php?Message=LoggedOut");
		exit();
	}
}
else
{
//	echo "dont have saveuser";
	//header ("Location: ../login/login.php?Message=LoggedOut");
	header ("Location: " . $wk_header_prefix . "/login/login.php?Message=LoggedOut");
	exit();
}
?>
