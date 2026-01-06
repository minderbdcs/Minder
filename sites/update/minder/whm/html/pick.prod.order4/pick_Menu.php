<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Pick Menu</title>
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
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include "logme.php";

logme($Link, $tran_user, $tran_device, "start pick menu");
$Query = "select first 1 1 from pick_item ";
$Query .= " where pick_line_status in ('AL','PG')";
$Query .= " and device_id = '".$tran_device."'";
//echo($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}
	
$allocated_tot = 0;
	
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$allocated_tot = 0;
		}
		else
		{
			$allocated_tot = $Row[$i];
		}
	}
}
	
//release memory
ibase_free_result($Result);

$Query = "select first 1 1 from pick_item ";
$Query .= " where pick_line_status in ('PL')";
$Query .= " and device_id = '".$tran_device."'";
//echo($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}
	
$despatch_tot = 0;
	
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$despatch_tot = 0;
		}
		else
		{
			$despatch_tot = $Row[$i];
		}
	}
}
	
//release memory
ibase_free_result($Result);
	
$Query = "select first 1 1 from pick_item ";
$Query .= " where pick_line_status in ('DS','DC')";
$Query .= " and device_id = '".$tran_device."'";
//echo($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}
	
$addressed_tot = 0;
	
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$addressed_tot = 0;
		}
		else
		{
			$addressed_tot = $Row[$i];
		}
	}
}
	
//release memory
ibase_free_result($Result);
	
$wk_sysuser = "";
$wk_saleuser = "";
{
	$Query = "SELECT sys_admin, sale_manager from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
		$wk_saleuser = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
}

//echo (" <FORM action=\"GetPick.php\" method=\"post\" name=transferpick>");
	
logme($Link, $tran_user, $tran_device, "end  prepare pick menu");
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

echo('<h2 ALIGN="LEFT">Pick by Order4 Menu ');
if (isset($tran_device))
{
	echo($tran_device );
}
echo('</h2>');


	$pick_method = ",";
	$pick_method .= "VIEWOPEN,NEXTORDER,"; /* get next order */
	$pick_method .= "VIEWPROD,NEXTPROD,"; /* get next prod  */
	//$pick_method .= "EXPORTPICKS,"; /* export  */
	$pick_method .= "WIP2,"; /* get wip by day */
	$pick_method .= "ADDRESS,"; /* get address label */
	$pick_method .= "CONTINUE,MOVEDESPATCH,"; /* continue buttons */
	$pick_method .= "STATUS,WIP,REPRINT,"; /* admin buttons */
	$pick_method .= "CANCEL,"; /* admin buttons */


	// Create a table.
	echo ("<table BORDER=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",NEXTOPEN,", $pick_method, "nextorder.php", "nextorder", "/icons/whm/button.php?text=Allocate+Next+Order&fromimage=Blank_Button_50x100.gif", "Allocate 
Next Order");

	if ($wk_sysuser == 'T' or $wk_saleuser == 'T')
	{
		addMenuButton(",VIEWOPEN,", $pick_method, "getorder.php", "getorder", "/icons/whm/button.php?text=View+Waiting+Orders&fromimage=Blank_Button_50x100.gif", "View 
Next Order");
	}
	addMenuButton(",NEXTPROD,", $pick_method, "allocatenextprod.php", "nextprod", "/icons/whm/button.php?text=Allocate+Next+Product&fromimage=Blank_Button_50x100.gif", "Allocate 
Next Product");
	if ($wk_sysuser == 'T' or $wk_saleuser == 'T')
	{
		addMenuButton(",VIEWPROD,", $pick_method, "allocategetprod.php", "getprod", "/icons/whm/button.php?text=View+Waiting+Products&fromimage=Blank_Button_50x100.gif", "View 
Next Product");
	}
	if ($wk_sysuser == 'T' or $wk_saleuser == 'T')
	{
		addMenuButton(",STATUS,", $pick_method, "ViewStatus.php", "HowMany", "/icons/whm/button.php?text=Status&fromimage=Blank_Button_50x100.gif", "Status");
	}
	if ($allocated_tot <> 0)
	{
		addMenuButton(",CONTINUE,", $pick_method, "getfromlocn.php", "viewnext", "/icons/whm/continue_picks.gif", "Continue");
	}
	if ($despatch_tot <> 0)
	{
		// have picks ready to despatch
		addMenuButton(",MOVEDESPATCH,", $pick_method , "confirmto.php", "despatch", "/icons/whm/pick.despatch.gif", "Despatch");
	}


	if ($addressed_tot <> 0)
	{
		// have picks already  addressed
		addMenuButton(",ADDRESS,", $pick_method , "addrprodlabel.php", "address", "/icons/whm/connoteaddresslabel.gif", "Address");
	}
	if ($wk_sysuser == 'T')
	{
		// view wip
		addMenuButton(",WIP,", $pick_method , "WipDay.php", "WIP", "/icons/whm/button.php?text=Picks+Today&fromimage=Blank_Button_50x100.gif", "Picks Today");
		addMenuButton(",WIP2,", $pick_method , "WipDay2.php", "wipday2", "/icons/whm/button.php?text=Picks+by+Day&fromimage=Blank_Button_50x100.gif", "Picks by 
Day");
		addMenuButton(",CANCEL,", $pick_method , "cancel.php", "cancel", "/icons/whm/button.php?text=Cancel+Menu&fromimage=Blank_Button_50x100.gif", "Cancel");
	}
	if ($wk_label_posn == 1)
	{
		echo ("</tr>");
		$wk_label_posn = 0;
	}
?>
<script type="text/javascript">
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.getopen.viewopen.focus();\n");
}
/*
else
{
	echo("document.viewopen.focus();\n");
}
*/
?>
</script>
</table>
</body>
</html>
