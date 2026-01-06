<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.ord/TR/html4/framset.dtd">');
echo("\n<html>\n");
echo("<head>\n");
echo("<title>Get Orders Customer</title>\n");
echo("</head>\n");

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['salesorder'])) 
{
	$wk_salesorder = $_POST["salesorder"];
}
if (isset($_GET['salesorder'])) 
{
	$wk_salesorder = $_GET["salesorder"];
}
echo("<frameset rows=\"40%, 60%\">\n");
echo("<frame name=\"top\" src=\"x.GetOrdTop.php\">\n");
echo("<frame name=\"detail\" src=\"ChooseOrder.php\">\n");
setBDCScookie($Link, $tran_device, "SalesOrder",$wk_salesorder);

echo("</frameset>\n");

?>
</html>
