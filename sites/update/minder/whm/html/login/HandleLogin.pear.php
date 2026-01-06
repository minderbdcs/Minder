<?php

require_once 'DB.php';

function getdevice($Link)
{
	$norows = TRUE;
	$DBDevice = "";
	// 1st get default handheld generic
	$Query = "select handheld_generic_code from control";
	$Result = $Link->query($Query);
	if (DB::isError($Result))
	{
		print("Unable to query table control!<BR>\n");
		exit();
	}
	if ( ($Row = $Result->fetchRow()) ) {
	 	$handheld_generic = $Row[0];
		$norows = FALSE;
	}
	else
	{
	 	$handheld_generic = 'HANDHELD';
		$norows = TRUE;
	}
	
	//release memory
	$Result->free();
	
	// 1st try ip address of handheld
	$Query = "select se.device_id
		from sys_equip se join  ssn sn on sn.ssn_id = se.ssn_id
		where sn.generic = '".$handheld_generic."'
		and sn.ip_address = '".$_SERVER['REMOTE_ADDR']."'";
	$Result = $Link->query($Query);
	if (DB::isError($Result))
	{
		print("Unable to query table ip!<BR>\n");
		exit();
	}
	if ( ($Row = $Result->fetchRow()) ) {
	 	$DBDevice = $Row[0];
		$norows = FALSE;
	}
	else
	{
		$norows = TRUE;
	}
	
	//release memory
	$Result->free();
	
	if ($norows)
	{
	// then try DHCP - get next free
		$Query = "select se.device_id
			from sys_equip se join  ssn sn on sn.ssn_id = se.ssn_id
			where sn.generic = '".$handheld_generic."'
			and sn.ip_address = 'DHCP'
			and 0 = (select count(*) from sys_user su where su.device_id = se.device_id)";
		$Result = $Link->query($Query);
		if (DB::isError($Result))
		{
			print("Unable to query table DHCP!<BR>\n");
			exit();
		}
		if ( ($Row = $Result->fetchRow()) ) {
		 	$DBDevice = $Row[0];
			$norows = FALSE;
		}
		else
		{
			$norows = TRUE;
		}
	
		//release memory
		$Result->free();
	
	}
	return array($norows, $DBDevice);
}
/* end of function */

require 'db_access.php';

print("<HTML>\n");
print("<HEAD>\n");
print("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
if (isset($_POST['UserName']))
{
	$UserName = $_POST['UserName'];
}
if (isset($_POST['Password']))
{
	$Password = $_POST['Password'];
}
if (isset($_GET['UserName']))
{
	$UserName = $_GET['UserName'];
}
if (isset($_GET['Password']))
{
	$Password = $_GET['Password'];
}
//print("$dsn");
$Link = DB::connect($dsn,true);
if (DB::isError($Link))
{
	print("Unable to Connect!<BR>\n");
	print($Link->getMessage());
	exit();
}
//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "SELECT pass_word, user_id, user_type from sys_user where user_id = '".$UserName."'";

$Result = $Link->query($Query);
if (DB::isError($Result))
{
	print("Unable to query table!<BR>\n");
	exit();
}
if ( ($Row = $Result->fetchRow()) ) {
 	$DBUserPassword = $Row[0];
 	$DBLoginUser = $Row[1];
 	$DBLoginType = $Row[2];
}

if (($UserName == $DBLoginUser) && ($Password == $DBUserPassword)) {
	//release memory
	$Result->free();

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
		$tran_trandate = date("Y-M-d H:i:s");
		$Query = "UPDATE sys_user set device_id='".$DBDevice."', login_date='".$tran_trandate."' where user_id = '".$UserName."'";
		$Result = $Link->query($Query);
		$Query = "UPDATE sys_equip set current_person='".$UserName."', current_logged_on='".$tran_trandate."' where device_id = '".$DBDevice."'";
		$Result = $Link->query($Query);
		//header ("Location: ../mainmenu.php?LoginUser=$UserName");
		header ("Location: ../mainmenu.php");
	}
} else {
	header ("Location: login.php?Message=Invalid");
	//release memory
	$Result->free();
}
print("</HEAD>\n");

//commit
$Link->commit();

//close
$Link->disconnect();

exit;
?>
<BODY>
</BODY>
</HTML>

