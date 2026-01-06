<?php
include "../login.inc";
	/*
	No cache!!
	*/
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	// always modified
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	/*
	End of No cache
	*/
setcookie("BDCSData","");
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Stocktake Menu</title>
<style type="text/css">
body {
     font-family: Verdana, Helvetica,  Arial, sans-serif;
     font-size: .8em;
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

include "logme.php";
$Query = "select stocktake_method from control ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
  echo("Unable to Read Control!<BR>\n");
  //exit();
}
$stocktake_method = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
  if ($Row[0] > "")
  {
    $stocktake_method = $Row[0];
  }
}

//release memory
ibase_free_result($Result);

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
setBDCScookie($Link, $tran_device, "product", "");
setBDCScookie($Link, $tran_device, "company", "");
setBDCScookie($Link, $tran_device, "prodlocn", "");
?>
  <h4 ALIGN="LEFT">Choose Stocktake Type</h4>
<?php

{
	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",SSN,", $stocktake_method, "GetSTKLocn.php", "getstock", "/icons/whm/locations.gif", "SSNs by 
Location");
	addMenuButton(",PROD,", $stocktake_method, "GetSSNProd.php", "getssnprod", "/icons/whm/prodonsite.gif", "Products 
by SSN and
Location
");
	addMenuButton(",ADJPROD,", $stocktake_method, "GetProductAdj.php", "product", "/icons/whm/adjustprod.gif", "Adjust 
Product");
	addMenuButton(",ADJSSN,", $stocktake_method, "GetSSNAdj.php", "ssn", "/icons/whm/adjustssn.gif", "Adjust 
SSN");
	addMenuButton(",ADDKIT,", $stocktake_method, "AddProductKit.php", "kit", "/icons/whm/addkit.gif", "Add 
Kit");
	addMenuButton(",EXPORT,", $stocktake_method, "STKExport.php", "export", "/icons/whm/button.php?text=Export+Variance", "Export");
	addMenuButton(",PRODSTK,", $stocktake_method, "ProdLocn.php", "prodlocn", "/icons/whm/prodonsite.gif", "Products
by 
Location
");
	addMenuButton(",ISSNSTK,", $stocktake_method, "GetSTKISSN.php", "ssnlocn", "/icons/whm/button.php?text=ISSN+Stocktake&fromimage=Blank_Button_50x100.gif", "ISSNs
by 
Location");
	addMenuButton(",ISSNSTK2,", $stocktake_method, "GetSTKISSN2.php", "issnlocn", "/icons/whm/button.php?text=+ISSN+Stocktake&fromimage=Blank_Button_50x100.gif", "ISSNs 
by 
Location2");
	addMenuButton(",ISSNSTK3,", $stocktake_method, "GetSTKISSN3.php", "issnlocn", "/icons/whm/button.php?text=+ISSN+Stocktake&fromimage=Blank_Button_50x100.gif", "ISSNs Seen
by 
Location");

//commit
ibase_commit($dbTran);
}
?>
  </table>
 </body>
</html>
