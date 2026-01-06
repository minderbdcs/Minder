<?php
session_start();
require_once 'DB.php';
require 'db_access.php';

function getdevice($Link, $user)
{
	$norows = TRUE;
	$DBDevice = "";
	$DBStatus = 0;
	$DBMessage = "";
/*
	// 1st get default handheld generic
	$Query = "select handheld_generic_code from control";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table control<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$handheld_generic =  $Row[0];
		$norows = FALSE;
	}
	else
	{
	 	$handheld_generic = 'HANDHELD';
		$norows = TRUE;
	}
	
	//release memory
	//$Result->free();
	ibase_free_result($Result);
	
	// 1st try ip address of handheld
	$Query = "select se.device_id
		from sys_equip se join  ssn sn on sn.ssn_id = se.ssn_id
		where sn.generic = '".$handheld_generic."'
		and sn.ip_address = '".$_SERVER['REMOTE_ADDR']."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table ip!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$DBDevice =  $Row[0];
		$norows = FALSE;
	}
	else
	{
		$norows = TRUE;
	}
	
	//release memory
	//$Result->free();
	ibase_free_result($Result);
	
	if ($norows)
	{
	// then try DHCP - get next free
		$Query = "select se.device_id
			from sys_equip se join  ssn sn on sn.ssn_id = se.ssn_id
			where sn.generic = '".$handheld_generic."'
			and sn.ip_address = 'DHCP'
			and 0 = (select count(*) from sys_user su where su.device_id = se.device_id)";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table DHCP!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$DBDevice =  $Row[0];
			$norows = FALSE;
		}
		else
		{
			$norows = TRUE;
		}
	
		//release memory
		//$Result->free();
		ibase_free_result($Result);
	
	}
*/
	$LogFile = "/tmp/login.log";
	$LogFile = "/data/tmp/login.log";
	$Query = "select device_id, lg_status, lg_message from login_device('";
	//$Query .= $_SERVER['REMOTE_ADDR']."','','" . $user . "')";
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		//print("have http_x_forwarded_for " . $_SERVER['HTTP_X_FORWARDED_FOR']);
		$Query .= $_SERVER['HTTP_X_FORWARDED_FOR']."','','" . $user . "')";
		file_put_contents($LogFile, "LOGIN user:" . $user . " http_x_forwarded_for: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " remote_addr: " . $_SERVER['REMOTE_ADDR'] .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		$wk_my_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		//print("have remote_addr " . $_SERVER['REMOTE_ADDR']);
		$Query .= $_SERVER['REMOTE_ADDR']."','','" . $user . "')";
		file_put_contents($LogFile, "LOGIN user:" . $user . " remote_addr: " . $_SERVER['REMOTE_ADDR'] .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		$wk_my_ip = $_SERVER['REMOTE_ADDR'];
	}
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query login<BR>\n");
		file_put_contents($LogFile, "unable to query login " . date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$DBDevice =  $Row[0];
		$DBStatus =  $Row[1];
		$DBMessage =  $Row[2];
		if ($DBStatus > 0)
		{
			$norows = FALSE;
		}
	}
	file_put_contents($LogFile, "device " . $DBDevice . "|" . $DBStatus . "|" . $DBMessage . "|" . $norows . date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
	return array($norows, $DBDevice, $DBStatus, $DBMessage, $wk_my_ip);
}

function getDeviceInfo($Link, $deviceId)
{
	$DBBrand = "";
	$DBModel = "";
	$Query = "SELECT se_brand, se_model,default_pr_printer from sys_equip where device_id = '".$deviceId."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query SYS_EQUIP!<BR>\n");
		exit();
	} else {
		if (($Row = ibase_fetch_row($Result)))
		{
			$DBBrand =  $Row[0];
			$DBModel =  $Row[1];
			$DBPrinter =  $Row[2];
		}
		//release memory
		ibase_free_result($Result);
	}
	if ($DBBrand == "")
	{
		// no Brand so use DEFAULT
		$DBBrand = "DEFAULT";
	} 
	if ($DBModel == "")
	{
		// no Model so use DEFAULT
		$DBModel = "DEFAULT";
	} 
	return array($DBBrand, $DBModel );
}
/* end of function */

$UserType = 'PR';

if (isset($_POST['UserName']))
{
	$UserName = $_POST['UserName'];
}
if (isset($_POST['Password']))
{
	$UserPassword = $_POST['Password'];
}
if (isset($_GET['UserName']))
{
	$UserName = $_GET['UserName'];
}
if (isset($_GET['Password']))
{
	$UserPassword = $_GET['Password'];
}
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// check that user device fields exist
$QueryField = ' select f.rdb$field_name from rdb$relation_fields f join rdb$relations r on f.rdb$relation_name = r.rdb$relation_name and r.rdb$view_blr is null and (r.rdb$system_flag is null or r.rdb$system_flag = 0) where f.rdb$relation_name = ' . "'SYS_USER'" . ' and f.rdb$field_name = ' . "'USER_EXTRA_DEVICE_ID'" . ' order by 1, f.rdb$field_position';
if (!($Result2 = ibase_query($Link, $QueryField)))
{
	echo("Unable to query tables!<BR>\n");
	exit();
}
else
if (($Row = ibase_fetch_row($Result2)))
{
	$DBHasExtraDevice =  $Row[0];
}
else
{
	$DBHasExtraDevice = "";
}
 
//release memory
//$Result->free();
ibase_free_result($Result2);

if ($DBHasExtraDevice == "")
	$Query = "SELECT pass_word, user_id, user_type, default_wh_id, pick_sequence, pick_direction from sys_user where user_id = '".$UserName."'";
else
	$Query = "SELECT pass_word, user_id, user_type, default_wh_id, pick_sequence, pick_direction, user_login_device_id, user_extra_device_id from sys_user where user_id = '".$UserName."'";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query tables!<BR>\n");
	exit();
}
else
if (($Row = ibase_fetch_row($Result)))
{
	$DBUserPassword =  $Row[0];
	$DBLoginUser =  $Row[1];
	$DBLoginType =  $Row[2];
	$DBWH_ID =  $Row[3];
	$DBPickSeq =  $Row[4];
	$DBPickDir =  $Row[5];
	$DBUserLoginDevice =  $Row[6];
	$DBUserExtraDevice =  $Row[7];
}
else
{
	$DBLoginUser = "";
	$DBUserPassword = "";
	$DBUserLoginDevice =  "";
	$DBUserExtraDevice =  "";
}

$LogFile = "/tmp/login.log";
$LogFile = "/data/tmp/login.log";
if (($UserName == $DBLoginUser) && ($UserPassword == $DBUserPassword)) {
	//release memory
	//$Result->free();
	ibase_free_result($Result);

	//list ($norows, $DBDevice, $DBStatus, $DBMessage) = getdevice($Link, $UserName);
	list ($norows, $DBDevice, $DBStatus, $DBMessage, $wk_my_ip) = getdevice($Link, $UserName);
	//if ($norows) 
	if (($norows) && ($DBUserExtraDevice == ""))
	{
		file_put_contents($LogFile, "device " . $DBDevice . "|" . $DBStatus . "|" . $DBMessage . "|" . $norows . date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		header ("Location: login.php?status=$DBStatus&Message=".urlencode($DBMessage));
	}
	else
	{
		if ($norows) 
		{
			$DBDevice = $DBUserExtraDevice;
		}
		setcookie("BDCSDATA","",time()+864000,"/");
		if ($wkMyBW == "NETFRONT") {
			setcookie("LoginUser","$UserName|$DBDevice",time()+864000,"/");
		} else {
			setcookie("LoginUser","$UserName|$DBDevice",time()+86400,"/");
		}
		$_SESSION['LoginUser'] =  "$UserName|$DBDevice" ;
		if ($wkMyBW == "NETFRONT") {
			setcookie("SaveUser","$UserName|$DBDevice|$DBLoginType",time()+11110000,"/");
		} else {
			setcookie("SaveUser","$UserName|$DBDevice|$DBLoginType",time()+1111000,"/");
		}
		$_SESSION['SaveUser'] =  "$UserName|$DBDevice|$DBLoginType" ;
		file_put_contents($LogFile, "setcookiei user and saveuser " .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		$_SESSION['LoginTime'] =  time();
		//echo("set 2ccokie");
		//$tran_trandate = date("Y-M-d H:i:s");
		//$Query = "UPDATE sys_user set device_id='".$DBDevice."', login_date='".$tran_trandate."' where user_id = '".$UserName."'";
		//if (!($Result = ibase_query($Link, $Query)))
		//{
		//	echo("Unable to Update sys_user!<BR>\n");
		//}
		//$Query = "UPDATE sys_equip set current_person='".$UserName."', current_logged_on='".$tran_trandate."' where device_id = '".$DBDevice."'";
		//if (!($Result = ibase_query($Link, $Query)))
		//{
		//	echo("Unable to Update sys_equip!<BR>\n");
		//}
		$Query = "SELECT 1 from session where device_id = '".$DBDevice."' and code = 'CURRENT_WH_ID'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Session (1)!<BR>\n");
		}
		else
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				// have a row
				$Query = "UPDATE session set description = '" . $DBWH_ID . "', create_date='NOW' where device_id = '".$DBDevice."' and code = 'CURRENT_WH_ID'";
			}
			else
			{
				// no row
				$Query = "INSERT into session(device_id, code, description, create_date) values('" . $DBDevice . "','CURRENT_WH_ID','" . $DBWH_ID . "','NOW')";
			}
			ibase_free_result($Result);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo($Query);
				echo("Unable to Update session!<BR>\n");
			}
		}
		$_SESSION['CURRENT_WH_ID'] = $DBWH_ID;
		$Query = "SELECT 1 from session where device_id = '".$DBDevice."' and code = 'CURRENT_PICK_SEQ'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Session (2)!<BR>\n");
		}
		else
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				// have a row
				$Query = "UPDATE session set description = '" . $DBPickSeq . "', create_date='NOW' where device_id = '".$DBDevice."' and code = 'CURRENT_PICK_SEQ'";
			}
			else
			{
				// no row
				$Query = "INSERT into session(device_id, code, description, create_date) values('" . $DBDevice . "','CURRENT_PICK_SEQ','" . $DBPickSeq . "','NOW')";
			}
			ibase_free_result($Result);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo($Query);
				echo("Unable to Update session!<BR>\n");
			}
		}
		$_SESSION['CURRENT_PICK_SEQ'] = $DBPickSeq;
		$Query = "SELECT 1 from session where device_id = '".$DBDevice."' and code = 'CURRENT_PICK_DIR'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Session (3)!<BR>\n");
		}
		else
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				// have a row
				$Query = "UPDATE session set description = '" . $DBPickDir . "', create_date='NOW' where device_id = '".$DBDevice."' and code = 'CURRENT_PICK_DIR'";
			}
			else
			{
				// no row
				$Query = "INSERT into session(device_id, code, description, create_date) values('" . $DBDevice . "','CURRENT_PICK_DIR','" . $DBPickDir . "','NOW')";
			}
			ibase_free_result($Result);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo($Query);
				echo("Unable to Update session!<BR>\n");
			}
		}
		$_SESSION['CURRENT_PICK_DIR'] = $DBPickDir;
		$Query = "SELECT 1 from session where device_id = '".$DBDevice."' and code = 'CURRENT_IP_ADDRESS'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Session (4)!<BR>\n");
		}
		else
		{
			if (($Row = ibase_fetch_row($Result)))
			{
				// have a row
				$Query = "UPDATE session set description = '" . $wk_my_ip . "', create_date='NOW' where device_id = '".$DBDevice."' and code = 'CURRENT_IP_ADDRESS'";
			}
			else
			{
				// no row
				$Query = "INSERT into session(device_id, code, description, create_date) values('" . $DBDevice . "','CURRENT_IP_ADDRESS','" . $wk_my_ip . "','NOW')";
			}
			ibase_free_result($Result);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo($Query);
				echo("Unable to Update session!<BR>\n");
			}
		}
		$_SESSION['CURRENT_IP_ADDRESS'] = $wk_my_ip;
                {
                    $wk_buffer = "LOGIN:WHM Login Device " . $DBDevice . " User " . $UserName . " ";
                    $query = "INSERT INTO
                              LOG(DESCRIPTION )
                              VALUES('" . $wk_buffer . "' || CAST(CAST('NOW' AS TIMESTAMP) AS CHAR(24)))"; 
                    $result = ibase_query($Link, $query);
                    if (false === $result) {
                        echo $query ;
                        echo "Unable to Update log!\n";
                    }
                }
		$DBBrand = "";
		$DBModel = "";
		$DBPrinter = "";
		list ($DBBrand, $DBModel) = getDeviceInfo($Link, $DBDevice);
		$_SESSION['CURRENT_BRAND'] = $DBBrand;
		$_SESSION['CURRENT_MODEL'] = $DBModel;
		if (is_null($DBPrinter))
		{
			$_SESSION['printer'] = $DBPrinter;
		}
		header ("Location: ../mainmenu.php");
	}
} else {
	header ("Location: login.php?Message=Invalid");
	//release memory
	//$Result->free();
	ibase_free_result($Result);
}

//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
ibase_close($Link);

exit;
?>
