<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Get Print Type</title>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

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

$Query = "select printer_method from control ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read Control!<BR>\n");
  //exit();
}
$printer_method = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
  if ($Row[0] > "")
  {
    $printer_method = $Row[0];
  }
}

//release memory
ibase_free_result($Result);

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>" . $message . "</FONT></B>\n");
	}
?>
   <H4 ALIGN="LEFT">Enter Report Type</H4>

<?php

{
	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",PROD,", $printer_method, "GetProductLabel.php", "product", "/icons/whm/prodlabel.gif", "Product");
	addMenuButton(",ORDER,", $printer_method, "GetPROrder.php", "order", "/icons/whm/reprintorder.gif", "Order");
	addMenuButton(",STOCK,", $printer_method, "GetStock.php", "stock", "/icons/whm/prodonsite.gif", "Stock");
	addMenuButton(",DESPATCH,", $printer_method, "GetOldStock.php", "oldstock", "/icons/whm/view_despatched.gif", "Despatched");
	addMenuButton(",LABEL,", $printer_method, "dir.php", "labels", "/icons/whm/REPRINT_50x100.gif", "Reprint 
Labels");
	addMenuButton(",LOCATION,", $printer_method, "GetLocationLabel.php", "Location", "/icons/whm/location.gif", "Location");
	addMenuButton(",ISSN,", $printer_method, "GetISSNLabel.php", "ISSN", "/icons/whm/SSNS_50x100.gif", "Issn");
	addMenuButton(",SSCC,", $printer_method, "GetSSCC.php", "SSCC", "/icons/whm/Pick_50x100.gif", "Sscc");
	addMenuButton(",SSCC,", $printer_method, "GetSSCC.Qry.php", "SSCCQ", '/icons/whm/button.php?text=SSCC+Qry&fromimage=Blank_Button_50x100.gif' , "SSCC Split");



}
?>
    </tr>
  </table>
</body>
</html>
