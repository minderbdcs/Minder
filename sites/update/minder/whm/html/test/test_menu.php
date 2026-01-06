<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '../includes');
include "../login.inc";
setcookie("BDCSData","", time()+11186400, "/");
?>
<html>
 <head>
  <TITLE>Get Test Type</TITLE>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
  echo('<link rel=stylesheet type="text/css" href="test.css">');
}
else
{
  echo('<link rel=stylesheet type="text/css" href="test-netfront.css">');
}
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="GREEN">

<H4>Test - Select Test Type</H4>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
  echo("Can't connect to DATABASE!");
  //exit();
}
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
//release memory
$Query = "select test_method from control ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read Control!<BR>\n");
  //exit();
}
$test_method = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
  if ($Row[0] > "")
  {
    $test_method = $Row[0];
  }
}
$test_method .= ",BACK,";

//release memory
ibase_free_result($Result);
?>
    <!-- html 4.0 browser -->
<?php
/*
    <table BORDER="0">
    <tbody>
    <tr>
*/

{
	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tbody>");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", $test_method, "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",TEST,", $test_method, "./GetSSNFrom.php", "gettest", "/icons/whm/TEST_50x100.gif", "Test SSN");
	//addMenuButton(",SFTY,", $test_method, "./getsafety.php", "safety", "/icons/whm/location.gif", "Location+Safety");
	addMenuButton(",SFTY,", $test_method, "./getsafety.php", "safety", "/icons/whm/button.php?text=++Safety&fromimage=location.gif", "Location 
Safety");
	//addMenuButton(",CALIB,", $test_method, "./getcalib.php", "calibration", "/icons/whm/locations.gif", "Location+Calibration");
	addMenuButton(",CALIB,", $test_method, "./getcalib.php", "calibration", "/icons/whm/button.php?text=++Calibration&fromimage=location.gif", "Location 
Calibration");
}
/*
	echo('SRC="/icons/whm/button.php?text=Locations&fromimage=');
	echo('Blank_Button_50x100.gif" >');
*/

?>
    <td>
    </td>
    </tr>
  </tbody>
  </table>
 </body>
</html>
