<?php
session_start();
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Util Menu</title>
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
 <body BGCOLOR="#AAFFCC">

  <h4>Util Menu</h4>
<?php
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
require 'db_access.php';
include 'logme.php';
include '2buttons.php';

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
/*
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	
//release memory
//ibase_free_result($Result);
	
//commit
//ibase_commit($dbTran);
	
//close
//ibase_close($Link);

	$util_method = "";
	//$util_method = ",EXPORTLOCN,"; /* export  */
	//$util_method .= ",SEQUENCE,SEQUENCEPM,"; /* sequence locations  */
	//$util_method .= "MODLOCN,MODLOCNS,MARKLOCN,"; /* locations */
	$util_method .= ",DBQUERY,"; /* db query util  */
	//$util_method .= "BARCODE,REMOTE,MODUSER,"; /* other util  */
{
	$Query = "SELECT description from options where group_code='UTIL_OPT'";

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Options table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		$util_method .=  $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
	
	// Create a table.
	echo ("<table BORDER=\"0\">");
	echo ("<tr>");

	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",EXPORTLOCN,", $util_method, "ExportLocation.php", "ExportLocations", "/icons/whm/button.php?text=Export+Locations&fromimage=Blank_Button_50x100.gif" , "Export Locations");
	addMenuButton(",SEQUENCE,", $util_method, "SequenceLocation.php", "Sequence", "/icons/whm/button.php?text=Sequence+Locations&fromimage=Blank_Button_50x100.gif", "Sequence");
	addMenuButton(",SEQUENCEPM,", $util_method, "SequencePMLocation.php", "SequencePM", "/icons/whm/button.php?text=Sequence+PM+Locations&fromimage=Blank_Button_50x100.gif", "SequencePM");
	addMenuButton(",BARCODE,", $util_method , "barcode.php", "barcode", "/icons/whm/button.php?text=Barcodes&fromimage=Blank_Button_50x100.gif", "Barcode");
	addMenuButton(",DBQUERY,", $util_method , "dbquery.php", "dbquery", "/icons/whm/button.php?text=DBQuery&fromimage=Blank_Button_50x100.gif", "DBQuery");
	addMenuButton(",REMOTE,", $util_method , "remote.php", "remote", "/icons/whm/button.php?text=Remote&fromimage=Blank_Button_50x100.gif", "Remote");
	addMenuButton(",MODLOCNS,", $util_method , "ModLocns.php", "modlocns", "/icons/whm/button.php?text=Modify+Locations&fromimage=Blank_Button_50x100.gif", "Locations");
	addMenuButton(",MODLOCN,", $util_method , "ModLocn.php", "modlocn", "/icons/whm/button.php?text=Modify+Location&fromimage=Blank_Button_50x100.gif", "Location");
	addMenuButton(",MODUSER,", $util_method , "ModUser.php", "moduser", "/icons/whm/button.php?text=Modify+User&fromimage=Blank_Button_50x100.gif", "User");
	addMenuButton(",MARKLOCN,", $util_method , "MarkLocn.php", "marklocn", "/icons/whm/button.php?text=Mark+Location&fromimage=Blank_Button_50x100.gif", "Mark Location");
	addMenuButton(",FTPBATCH,", $util_method , "ftpbatch.php", "ftpbatch", "/icons/whm/button.php?text=Ftp+In+Batch&fromimage=Blank_Button_50x100.gif", "Ftp In");
	addMenuButton(",SLOTTING,", $util_method , "slotting.php", "slotting", "/icons/whm/button.php?text=Slotting&fromimage=Blank_Button_50x100.gif", "Slotting");
	addMenuButton(",BEDEVICE,", $util_method , "becomedevice.php", "bedevice", "/icons/whm/button.php?text=Become+Device&fromimage=Blank_Button_50x100.gif", "BeDevice");

?>
</tr>
</table>
</body>
</html>
