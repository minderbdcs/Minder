<?php
require_once 'DB.php';
require 'db_access.php';

if (isset($_GET['Message']))
{
	$Message = $_GET['Message'];
}

// if (isset($_COOKIE['LoginUser']))
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	//$Query = "UPDATE sys_user set device_id=NULL, login_date=NULL where user_id = '".$UserName."'";
	//if (!($Result = ibase_query($Link, $Query)))
	//{
	//	echo("Unable to update sys_user!<BR>\n");
	//	exit();
	//}
	
	//release memory
	//ibase_free_result($Result);

	//$Query = "UPDATE sys_equip set current_person=NULL, current_logged_on=NULL where device_id = '".$DBDevice."'";
	//if (!($Result = ibase_query($Link, $Query)))
	//{
	//	echo("Unable to update sys_equip!<BR>\n");
	//	exit();
	//}

	$Query = "select lg_status, lg_message from logout_device('" . $DBDevice . "')";
                {
                    $wk_buffer = "LOGIN:WHM attempt Logout Device " . $DBDevice . " User " . $UserName . " ";
                    $query = "INSERT INTO
                              LOG(DESCRIPTION )
                              VALUES('" . $wk_buffer . "' || CAST(CAST('NOW' AS TIMESTAMP) AS CHAR(24)))"; 
                    $result = ibase_query($Link, $query);
                    if (false === $result) {
                        echo $query ;
                        echo "Unable to Update log!\n";
                    }
		//$LogFile = "/tmp/login.log";
		$LogFile = "/data/tmp/login.log";
		file_put_contents($LogFile, "LOGOUT user:" . $UserName . " http_x_forwarded_for: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " remote_addr: " . $_SERVER['REMOTE_ADDR'] . " Device " . $DBDevice .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
                }
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to logout from device!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$DBStatus =  $Row[0];
		$DBMessage =  $Row[1];
		if ($DBStatus <> 0)
		{
			//echo ("<B><FONT COLOR=RED>$DBMessage</FONT></B>\n");
			//exit();
		}
	}
	
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
	
	//close
	//$Link->disconnect();
	ibase_close($Link);


}

setcookie("LoginUser","");
if (!isset($Message))
{
	$Message = "";
}
if ($Message == "LoggedOut") {
	header ("Location: ./login.php?Message=LoggedOut");
}
else
{
	header ("Location: ./login.php");
}
exit;
?>
