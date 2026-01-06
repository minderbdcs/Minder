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
	//$Link = DB::connect($dsn,true);
	//if (DB::isError($Link))
	//{
	//	print("Unable to Connect!<BR>\n");
	//	print($Link->getMessage());
	//	exit();
	//}
	if (!($Link = ibase_connect($DBName2, $User, $Password)))
	{
		print("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	$Query = "UPDATE sys_user set device_id=NULL, login_date=NULL where user_id = '".$UserName."'";
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

	$Query = "UPDATE sys_equip set current_person=NULL, current_logged_on=NULL where device_id = '".$DBDevice."'";
	
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
