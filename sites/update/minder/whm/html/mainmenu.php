<?php
session_start();
/*
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	// $demomode = 1;
	if (isset($demomode))
	{
		setcookie("LoginUser","BDCS|MA",time()+86400,"/");
		setcookie("SaveUser","BDCS|MA|TR",time()+1111000,"/");
		//setcookie("SaveUser","BDCS|MA|PR",time()+1111000,"/");
	}
	else
	{
		if (isset($_SESSION['LoginUser'])) 
		{
			setcookie("LoginUser",$_SESSION['LoginUser'],0,"/");
		} else {
			header ("Location: ./login/login.php?Message=LoggedOut");
		}
	}
}
if (isset($_SESSION["LoginTime"]))
{
	if (($_SESSION['LoginTime'] + 86400 ) < time())
	{
		// last login over a day old
		header ("Location: ./login/login.php?Message=LoggedOut");
	}
}
*/
$wk_header_prefix = ".";
include "login.inc";
?>
<html>
 <head>
  <title>Main Menu Page</title>
<?php
include "viewport.php";
include "db_access.php";
//  <meta name="viewport" content="width=device-width">
//  <meta name="viewport" content="width=device-width, initial-scale=1">
/*
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	//echo('<link rel=stylesheet type="text/css" href="core-style.css">');
	//echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
	if ((strpos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6") === false) and
//	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 4.01") === false) and
//	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 6.0") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "WebKit") === false))
	{
		echo('<link rel=stylesheet type="text/css" href="core-style.css">');
	} else {
		echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
	}
}
else
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
*/
if ($wkMyBW == "IE60")
{
	echo('<link rel=stylesheet type="text/css" href="core-style.css">');
} elseif ($wkMyBW == "IE65")
{
	echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
} elseif ($wkMyBW == "CHROME")
{
	echo('<link rel=stylesheet type="text/css" href="core-chrome.css">');
} elseif ($wkMyBW == "SAFARI")
{
	echo('<link rel=stylesheet type="text/css" href="core-chrome.css">');
} elseif ($wkMyBW == "NETFRONT")
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
echo('<link rel=stylesheet type="text/css" href="nopad.css">');
//echo('<link rel=stylesheet type="text/css" href="main.css">');

/**
 * check for Options
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return string
 */
function getOptions($Link, $groupCode, $code)
{
	{
		$Query = "select description, description2 from options where group_code='" . $groupCode . "' and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/tmp/mainmenu.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return $wk_data ;
} // end of function

?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php

require_once 'DB.php';
//require 'db_access.php';
include "2buttons.php";
//require_once "Mdr/Minder/Version.php";
require_once "Minder/Version.php";

echo("<div id=\"col0\" class=\"col0\" >");
//echo("<table border=\"0\" align=\"left\">");
echo("<table border=\"0\" >");
echo("<tbody>");
//echo("<tr><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>\n"); 
//echo("<tr><td>IMS v5.0");
$myVersion = new Minder_Version;
$thisVersion = $myVersion->getFull();
$thisVersionPart = explode(".", $thisVersion);
//echo("<tr><td>IMS ". $thisVersionPart[3] );
echo("<tr><td>IMS ". $thisVersionPart[2] );
//echo("<tr><td>". $thisVersionPart[2] );
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
/*
if ($UserType == "PR")
{
	echo(" Prod");
}
else
{
	echo(" Test");
}
*/
if (isset($DBDevice))
{
	echo(".".$DBDevice);
}
//echo ("." . $wkMyBW) ;
/*
login sets to default
if syadmin can select from all warehouse
otherwise can select from those in access_user
if only 1 then no select just display
if only 0 then no select just display
*/


if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Can't connect to DATABASE!");
	//exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// get image size for my browser
// code is "IMGSIZE|Browser type"
$wk_image_size = "";
$wk_image_code = "IMGSIZE|" . $wkMyBW ;
$wk_image_size = getOptions($Link, "HANDHELD", $wk_image_code);
if ($wk_image_size <> "") 
{
	$_SESSION["IMG_WIDTH"] = $wk_image_size;
	$rimg_width = $wk_image_size;
}
//echo ("." . $rimg_width) ;

if (isset($_POST['WH']))
{
	if ($_POST['WH'] <> '')
	{
		$newwh = $_POST['WH'];
		$_SESSION['CURRENT_WH_ID'] = $newwh;
		$Query = "update session set description = '" . $newwh . "' where device_id='" . $DBDevice . "' and code = 'CURRENT_WH_ID'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Sessions!<BR>\n");
			//exit();
		}
		//release memory
		//ibase_free_result($Result);
	}
}
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_WH_ID' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_wh = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		echo( " WH:" . $Row[0]);
		$current_wh = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
$Query = "select sys_admin, editable from sys_user where user_id = '" . $UserName . "'"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read User!<BR>\n");
	//exit();
}
$sysadmin = "";
$editable = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$sysadmin = $Row[0];
	}
	if ($Row[1] > "")
	{
		$editable = $Row[1];
	}
}
/*
if ($sysadmin == 'T')
{
	echo( " Admin") ;
}
else
if ($editable == 'T')
{
	echo( " Editable");
}
*/
//release memory
ibase_free_result($Result);
echo( " " . $UserName );
//echo("</H4>\n");
//echo("</td></tr><tr><td>");
echo("</td><td>");
echo("<form action=\"./mainmenu.php\" method=\"post\" name=changewh>\n");
echo("<select name=\"WH\" onchange=\"document.changewh.submit()\" >\n");
//$Query = "select wh_id from warehouse where wh_id < 'X' or wh_id > 'X~' order by wh_id "; 
if ($sysadmin == 'T') {
	$Query = "select wh_id from warehouse where wh_id < 'X' or wh_id > 'X~' order by wh_id "; 
} else {
	$Query = "select warehouse.wh_id from warehouse join access_user on warehouse.wh_id = access_user.wh_id and access_user.user_id = '" . $UserName . "'  where warehouse.wh_id < 'X' or warehouse.wh_id > 'X~' order by warehouse.wh_id "; 
}
$wk_wh_list = 0;
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Warehouse!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $current_wh)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
	$wk_wh_list = 1;
}
//release memory
ibase_free_result($Result);
if ($wk_wh_list == 0)
{
	$Query = "select default_wh_id from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Warehouse!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] == $current_wh)
		{
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
		}
		$wk_wh_list = 1;
	}
	//release memory
	ibase_free_result($Result);
}
$default_method = ",DESPATCH,TRANSFER,STOCKTAKE,TEST,QUERY,LOGOUT,SALESORDER,RECEIVE,REPRINT,UTILITY,";
$default_method = ",DESPATCH,TRANSFER,STOCKTAKE,TEST,QUERY,LOGOUT,RECEIVE,REPRINT,UTILITY,";
$description_method = ",SCANISSN,DROPISSN,";
	$Query = "select pick_method, putaway_method, replenish_method,description_method, whm_default_method from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		//exit();
	}
	$pick_method = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$pick_method = $Row[0];
			$putaway_method = $Row[1];
			$replenish_method = $Row[2];
			$description_method = $Row[3];
			$default_method = $Row[4];
		}
	}
	//release memory
	ibase_free_result($Result);

echo("</select></form>");
echo("</td></tr>");
echo("</tbody>");
//echo("</table><br><br><br><br><br><br><br>");
echo("</table>");
echo("</div >");
//if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
//{
	// html 4.0 browser
	echo("<div id=\"col9\" class=\"col9\" >");
	// Create a table.
	//echo ("<table border=\"0\" align=\"left\">");
	echo ("<table border=\"0\" >");
	echo("<tbody>");
	echo ("<tr>");
	$wk_label_posn = 0;
	$wk_menu_output = "IMAGE";
	//$rimg_width = 80;
	//$rimg_width = "200%";
	addMenuButton(",LOGOUT,", $default_method, "./login/logout.php", "getout", "/icons/whm/LogOut_50x100.gif", "Logout");
	addMenuButton(",SCANISSN,", $description_method, "./description/GetLocn.php", "getdescription", "/icons/whm/Description_50x100.gif", "Description
by Menu");
	addMenuButton(",DESPATCH,", $default_method, "./despatch/despatch_menu.php", "getdespatch", "/icons/whm/Despatch_50x100.gif", "Despatch");
	addMenuButton(",DROPISSN,", $description_method, "./description/desc_menu.php", "getdescmenu", "/icons/whm/Description_50x100.gif", "Description Menu");
	addMenuButton(",TRANSFER,", $default_method, "./transfer/Transfer_Menu.php", "gettransfer", "/icons/whm/TRANSFER_50x100.gif", "Transfer");
	addMenuButton(",STOCKTAKE,", $default_method, "./stocktake/Stocktake_menu.php", "getstock", "/icons/whm/Stocktake_50x100.gif", "Stocktake");
	//addMenuButton(",TEST,", $default_method, "./test/GetSSNFrom.php", "gettest", "/icons/whm/TEST_50x100.gif", "Test");
	addMenuButton(",TEST,", $default_method, "./test/test_menu.php", "gettest", "/icons/whm/TEST_50x100.gif", "Test");
/*
	$Query = "select pick_method, putaway_method, replenish_method,description_method from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		//exit();
	}
	$pick_method = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$pick_method = $Row[0];
			$putaway_method = $Row[1];
			$replenish_method = $Row[2];
			$description_method = $Row[3];
		}
	}
	//release memory
	ibase_free_result($Result);
*/
	list($pick_method0, $pick_method1, $pick_method2, $pick_method3, $pick_method4, $pick_method5, $pick_method6, $pick_method7, $pick_method8, $pick_method9) = explode(',', $pick_method . ",,,,,,,,,");
	list($putaway_method0, $putaway_method1, $putaway_method2 ) = explode(',', $putaway_method . ",,,,");
	list($replenish_method0, $replenish_method1, $replenish_method2, $replenish_method3 ) = explode(',', $replenish_method . ",,,,");
//	$wk_label_posn = 1;
	addMenuButton(",ISSN,", $putaway_method, "./putaway/getfromlocn.php", "getputaway", "/icons/whm/putawayssn.gif", "Putaway");
	addMenuButton(",ISSNTEST,", $putaway_method, "./putaway.prod/getfromlocn.php", "getputaway2", "/icons/whm/putawaytest.gif", "Putaway 
with Test");
	addMenuButton(",PROD,", $putaway_method, "./putaway.prod2/getfromlocn.php", "getputaway3", "/icons/whm/putawayssn.gif", "Putaway 
Product");
	addMenuButton(",IS,", $pick_method, "./pick/pick_Menu.php", "getpick", "/icons/whm/pickssn.gif", "Pick ISSN");
	addMenuButton(",PL,", $pick_method, "./pick.prod/pick_Menu.php", "getpick2", "/icons/whm/pickproduct.gif", "Pick Prod");
	addMenuButton(",PT,", $pick_method, "./pick.prod.trolley/pick_Menu.php", "getpick3", "/icons/whm/pick2trolley.gif", "Pick 
Trolley");
	addMenuButton(",PT2,", $pick_method, "./pick.prod.trolley2/pick_Menu.php", "getpick11", "/icons/whm/pick2trolley.gif", "Pick 
Trolley2");
	addMenuButton(",PO,", $pick_method, "./pick.prod.order/pick_Menu.php", "getpick4", "/icons/whm/pick2despatch.gif", "Pick Order");
	addMenuButton(",PO2,", $pick_method, "./pick.prod.order2/pick_Menu.php", "getpick5", "/icons/whm/Pick_50x100.gif", "Pick Order2");
	addMenuButton(",PO3,", $pick_method, "./pick.prod.order3/pick_Menu.php", "getpick6", "/icons/whm/Pick_50x100.gif", "Pick Order3");
	addMenuButton(",PO4,", $pick_method, "./pick.prod.order4/pick_Menu.php", "getpick7", "/icons/whm/Pick_50x100.gif", "Pick Order4");
	addMenuButton(",PO5,", $pick_method, "./pick.prod.order5/pick_Menu.php", "getpick8", "/icons/whm/Pick_50x100.gif", "Pick Order5");
	addMenuButton(",PL2,", $pick_method, "./pick.prod2/pick_Menu.php", "getpick9", "/icons/whm/pickproduct.gif", "Pick Prod2");
	addMenuButton(",PL3,", $pick_method, "./pick.prod3/pick_Menu.php", "getpick10", "/icons/whm/pickproduct.gif", "Pick Prod3");
	addMenuButton(",QUERY,", $default_method, "./query/query.php", "getquery", "/icons/whm/Query_50x100.gif", "Query");
	if ($sysadmin == 'T')
	{
		addMenuButton(",SALESORDER,", $default_method, "./salesorders/Order_Menu.php", "getorder", "/icons/whm/57c_salesorder.gif", "Ship");
	}
	addMenuButton(",RECEIVE,", $default_method, "./receive/receive_menu.php", "getreceive", "/icons/whm/RECEIVE_50X100.gif", "Receive");
	addMenuButton(",REPRINT,", $default_method, "./printer/print_Menu.php", "getreprint", "/icons/whm/REPRINT_50x100.gif", "Reprint");
	if ($sysadmin == 'T')
	{
		addMenuButton(",UTILITY,", $default_method, "./util/util_Menu.php", "util", "/icons/whm/71c_utility.gif", "Util");
	}
	addMenuButton(",RP00,", $replenish_method, "./replenish/replenish_Menu.php", "getreplenish", "/icons/whm/button.php?text=Replenish&fromimage=Blank_Button_50x100.gif" , "Replenish");
	addMenuButton(",RP01,", $replenish_method, "./replenish2/replenish_Menu.php", "getreplenish", "/icons/whm/button.php?text=Replenish+Cmp+1&fromimage=Blank_Button_50x100.gif" , "Replenish");
	addMenuButton(",RP02,", $replenish_method, "./replenish3/replenish_Menu.php", "getreplenish", "/icons/whm/button.php?text=Replenish+Cmp+2&fromimage=Blank_Button_50x100.gif" , "Replenish");
	addMenuButton(",RP03,", $replenish_method, "./replenish.order/replenish_Menu.php", "getreplenish", "/icons/whm/button.php?text=Replenish+Order&fromimage=Blank_Button_50x100.gif" , "Replenish");
	echo ("<td>");
	echo ("</td>");
	echo ("</tr>");
	echo("</tbody>");
	echo("</table>");
	//echo("</div>");
//}
//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
ibase_close($Link);

?>
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<script type=\"text/javascript\">\n");
	echo("document.getdescription.description.focus();\n");
	echo("</script>\n");
}
echo("</div >");
?>
 </body>

</html>
