<?php
include "../login.inc";
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Get Despatch Type</title>
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

	$Query = "select despatch_method from control ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		//exit();
	}
	$despatch_method = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$despatch_method = $Row[0];
		}
	}

	//release memory
	ibase_free_result($Result);

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
?>
  <H4 ALIGN="LEFT">Select Despatch:</H4>

<?php

{
	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",AllLOCN,", $despatch_method, "getalllocn.php", "alllocn", "/icons/whm/alllocations.gif", "All Locations");
	addMenuButton(",ONELOCN,", $despatch_method, "getonelocn.php", "onelocn", "/icons/whm/onelocation.gif", "One Location");
	addMenuButton(",AllSO,", $despatch_method, "getallso.php", "allso", "/icons/whm/allsalesorders.gif", "All SO");
	addMenuButton(",ONESO,", $despatch_method, "getoneso.php", "oneso", "/icons/whm/button.php?text=One+Order&fromimage=Blank_Button_50x100.gif", "One SO");
	addMenuButton(",CONNOTEADDR,", $despatch_method, "getaddrlabelsprt.php", "connoteaddresslabels", "/icons/whm/connoteaddresslabel.gif", "Connote 
Address 
Labels");
	addMenuButton(",OTHERADDR,", $despatch_method, "getaddrother.php", "personaddresslabels", "/icons/whm/sitemailaddresslabel.gif", "Site Address
 Labels");
	addMenuButton(",ORDERADDR,", $despatch_method, "getaddrorder.php", "orderspersonaddresslabels", "/icons/whm/orderaddresslabel.gif", "Site Orders 
Address 
Labels");
	addMenuButton(",PRODADDR,", $despatch_method, "addrprodlabel.php", "orderproductaddresslabels", "/icons/whm/button.php?text=Order+Product+Address&fromimage=Blank_Button_50x100.gif", "Order Prod 
Address Labels");
	addMenuButton(",EXIT,", $despatch_method, "getdespatchexit.php", "despatchexit", "/icons/whm/despatchexit.gif", "Despatch 
Exit");
	addMenuButton(",LOCNMANIFEST,", $despatch_method, "gettolocn.php", "manifest", "/icons/whm/button.php?text=Location+Manifest&fromimage=Blank_Button_50x100.gif", "Location 
manifest");
	addMenuButton(",PPMANIFEST,", $despatch_method, "getppmanifest.php", "manifest", "/icons/whm/button.php?text=PP+Manifest&fromimage=Blank_Button_50x100.gif", "PP manifest");
	addMenuButton(",SERIAL,", $despatch_method, "GetSerial.php", "serial", "/icons/whm/button.php?text=Serials&fromimage=Blank_Button_50x100.gif", "Serials");
	addMenuButton(",MODCARRIER,", $despatch_method, "ModCarrier.php", "carrier", "/icons/whm/button.php?text=Mod+Carrier&fromimage=Blank_Button_50x100.gif", "Carriers");
}
?>
    </tr>
  </table>
 </body>
</html>
