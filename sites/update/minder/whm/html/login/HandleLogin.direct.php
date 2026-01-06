<?php

require_once 'DB.php';

function getdevice($Link)
{
	$norows = TRUE;
	$DBDevice = "";
	// 1st get default handheld generic
	$Query = "select handheld_generic_code from control";
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
	//	echo("Unable to query table control!<BR>\n");
	//	exit();
	//}
	//if ( ($Row = $Result->fetchRow()) ) {
	// 	$handheld_generic = $Row[0];
	//	$norows = FALSE;
	//}
	//else
	//{
	// 	$handheld_generic = 'HANDHELD';
	//	$norows = TRUE;
	//}
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
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
	//	echo("Unable to query table ip!<BR>\n");
	//	exit();
	//}
	//if ( ($Row = $Result->fetchRow()) ) {
	// 	$DBDevice = $Row[0];
	//	$norows = FALSE;
	//}
	//else
	//{
	//	$norows = TRUE;
	//}
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
		//$Result = $Link->query($Query);
		//if (DB::isError($Result))
		//{
		//	echo("Unable to query table DHCP!<BR>\n");
		//	exit();
		//}
		//if ( ($Row = $Result->fetchRow()) ) {
		// 	$DBDevice = $Row[0];
		//	$norows = FALSE;
		//}
		//else
		//{
		//	$norows = TRUE;
		//}
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
	return array($norows, $DBDevice);
}
/* end of function */

require 'db_access.php';

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
//echo("$dsn");
//$Link = DB::connect($dsn,true);
//if (DB::isError($Link))
//{
//	echo("Unable to Connect!<BR>\n");
//	echo($Link->getMessage());
//	exit();
//}
//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
if (!($Link = ibase_connect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);


$Query = "SELECT pass_word, user_id, user_type, default_wh_id from sys_user where user_id = '".$UserName."'";

//$Result = $Link->query($Query);
//if (DB::isError($Result))
//{
//	echo("Unable to query table!<BR>\n");
//	exit();
//}
//if ( ($Row = $Result->fetchRow()) ) {
// 	$DBUserPassword = $Row[0];
// 	$DBLoginUser = $Row[1];
// 	$DBLoginType = $Row[2];
//}

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
}
else
{
	$DBLoginUser = "";
	$DBUserPassword = "";
}

if (($UserName == $DBLoginUser) && ($UserPassword == $DBUserPassword)) {
	//release memory
	//$Result->free();
	ibase_free_result($Result);

	list ($norows, $DBDevice) = getdevice($Link);
	if ($norows)
	{
		header ("Location: login.php?Message=NoDevice");
	}
	else
	{
		//setcookie("LoginUser","$UserName|$DBDevice",time()+86400,"/");
		setcookie("LoginUser","$UserName|$DBDevice",time()+86400,"/");
		//setcookie("LoginUser","$UserName|$DBDevice",time()+186400,"/");
		setcookie("SaveUser","$UserName|$DBDevice|$DBLoginType",time()+1111000,"/");
		//echo("set 2ccokie");
		$tran_trandate = date("Y-M-d H:i:s");
		$Query = "UPDATE sys_user set device_id='".$DBDevice."', login_date='".$tran_trandate."' where user_id = '".$UserName."'";
		//$Result = $Link->query($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update sys_user!<BR>\n");
		}
		$Query = "UPDATE sys_equip set current_person='".$UserName."', current_logged_on='".$tran_trandate."' where device_id = '".$DBDevice."'";
		//$Result = $Link->query($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update sys_equip!<BR>\n");
		}
		$Query = "SELECT 1 from session where device_id = '".$DBDevice."' and code = 'CURRENT_WH_ID'";
		//$Result = $Link->query($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Session!<BR>\n");
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
		//header ("Location: ../mainmenu.php?LoginUser=$UserName");
		header ("Location: ../mainmenu.php");
	}
} else {
	//echo("Entered User $UserName vs $DBLoginUser\n");
	//echo("Entered Passwd $UserPassword vs $DBUserPassword\n");
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
