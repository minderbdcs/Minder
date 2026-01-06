<?php
require_once 'DB.php';
require 'db_access.php';

if (isset($_GET['Message']))
{
	$Message = $_GET['Message'];
}

// if (isset($_COOKIE['LoginUser']))
// if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
	//$Link = DB::connect($dsn,true);
	//if (DB::isError($Link))
	//{
	//	print("Unable to Connect!<BR>\n");
	//	print($Link->getMessage());
	//	exit();
	//}
	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		print("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	$Query = "UPDATE sys_user set device_id=NULL, login_date=NULL ";
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
	//	print("Unable to update sys_user!<BR>\n");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to update sys_user!<BR>\n");
		exit();
	}
	
	//release memory
	//ibase_free_result($Result);

	$Query = "UPDATE sys_equip set current_person=NULL, current_logged_on=NULL ";
	
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
	//	print("Unable to update sys_equip!<BR>\n");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to update sys_equip!<BR>\n");
		exit();
	}
	
        {
            $wk_buffer = "LOGIN:WHM Logout All Devices " ;
	    $wk_buffer .= "Remote Addr " . $_SERVER['REMOTE_ADDR'] . " ";
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	    {
		//print("have http_x_forwarded_for " . $_SERVER['HTTP_X_FORWARDED_FOR']);
		$wk_buffer  .= " forwarded for " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " ";
	    }
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
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	    {
		file_put_contents($LogFile, "LOGOUT All :" . " http_x_forwarded_for: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " remote_addr: " . $_SERVER['REMOTE_ADDR'] . " Device " . $DBDevice .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
	    } else {
		file_put_contents($LogFile, "LOGOUT All :" . " remote_addr: " . $_SERVER['REMOTE_ADDR'] . " Device " . $DBDevice .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
	    }
        }
	//commit
	//$Link->commit();
	//ibase_commit($dbTran);
	
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
