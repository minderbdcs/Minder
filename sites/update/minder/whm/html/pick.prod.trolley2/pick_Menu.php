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
	echo ("<B><font COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
include "viewport.php";
require 'db_access.php';
include "2buttons.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include 'logme.php';

$Query = "select first 1 1 from pick_item ";
//$Query .= " where pick_line_status in ('AL','PG')";
$Query .= " where pick_line_status in ('AL','PG','Al','Pg')";
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
//$Query .= " where pick_line_status in ('PL')";
$Query .= " where pick_line_status in ('PL','Pl')";
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
$wk_sysuser = "";
{
	$Query = "SELECT sys_admin from sys_user";
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
	}
	//release memory
	ibase_free_result($Result);
}
	
$wk_restrict_by_zone = "F";
{
	$Query = "SELECT pick_restrict_by_zone FROM control";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Control!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_restrict_by_zone = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
	
setBDCScookie($Link, $tran_device, "picklocation", "");
setBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR", "A");
	
if (isset($_POST['PICKZONE']))
{
	$newZone = $_POST['PICKZONE'];
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE", $newZone );
}
if (isset($_GET['PICKZONE']))
{
	$newZone = $_GET['PICKZONE'];
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE", $newZone );
}
if (isset($_POST['PICKCMP']))
{
	$newCmp  = $_POST['PICKCMP'];
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY", $newCmp  );
}
if (isset($_GET['PICKCMP']))
{
	$newCmp = $_GET['PICKCMP'];
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY", $newCmp );
}
//commit
ibase_commit($dbTran);
	
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//close
//ibase_close($Link);

echo("<table border=\"0\">");
echo("<tbody>");
echo("<tr><td colspan=\"3\">");
//echo('<h4 ALIGN="LEFT">Pick Menu </h4>');
//echo('<h4 ALIGN="LEFT">Pick by Trolley2 Menu ');
echo('Pick by Trolley2 Menu ');
if (isset($tran_device))
{
	echo($tran_device );
}
//echo('</h4>');
echo('</td>');


if ($wk_restrict_by_zone == 'T')
{
	echo("</tr><tr>");
	echo("<td >");
	echo( "Zone:");
	$current_pickZone = getBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE"  );
	echo("</td><td>");
	echo("<form action=\"" .  basename($_SERVER["PHP_SELF"]) ."\" method=\"post\" name=changezone>\n");
	echo("<select name=\"PICKZONE\" onchange=\"document.changezone.submit()\" >\n");
	$Query = "select code from zone order by code "; 
	$wk_zone_cnt = 0;
	$wk_last_zone = "";
	
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Zone!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_zone_cnt = $wk_zone_cnt + 1;
		if ($Row[0] == $current_pickZone)
		{
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
		}
		$wk_last_zone = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wk_zone_cnt == 1)
	{
		setBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE", $wk_last_zone );
	}
	
	echo("</select></form>");
	echo("</td>");
	echo("<td>");
	echo( "Cmp:");
	$current_pickCmp = getBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY"  );
	echo("</td><td>");
	echo("<form action=\"" .  basename($_SERVER["PHP_SELF"]) ."\" method=\"post\" name=changecmp>\n");
	echo("<select name=\"PICKCMP\" onchange=\"document.changecmp.submit()\" >\n");
	$Query = "select company_id, name from company order by company_id "; 
	$wk_cmp_cnt = 0;
	$wk_last_cmp = "";
	
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Company!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_cmp_cnt = $wk_cmp_cnt + 1;
		if ($Row[0] == $current_pickCmp)
		{
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
			$wk_last_cmp = $Row[0];
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
			if ($wk_last_cmp == "")
			{
				$wk_last_cmp = $Row[0];
			}
		}
	}
	//release memory
	ibase_free_result($Result);
	
	if ($wk_cmp_cnt == 1)
	{
		setBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY", $wk_last_cmp );
	}
	echo("</select></form>");
	echo("</td>");
}
echo("</tr>");
echo("</tbody>");
echo("</table>");


	$pick_method = ",";
	//$pick_method .= "VIEWopen,VIEWORDER,"; /* get next order */
	//$pick_method .= "VIEWSLO,"; /* get next SLO order */
	//$pick_method .= "VIEWORDER2,"; /* get next known order */
	//$pick_method .= "VIEWISSN,"; /* scan issn */
	//$pick_method .= "CONTINUE,MOVEDESPATCH,"; /* continue buttons */
	//$pick_method .= "STATUS,WIP,CANCEL,"; /* admin buttons */
	//$pick_method .= "REVDS2PL,"; /* admin buttons */
{
	$Query = "SELECT description from options where group_code='PICK_OPT'";

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Options table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		$pick_method .=  $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
	
	//echo("options:" . $pick_method);

	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",VIEWOPEN,", $pick_method, "ViewType.php", "viewopen", "/icons/whm/viewpickswaiting.gif", "View Open");
	addMenuButton(",VIEWORDER,", $pick_method, "getorder.php", "vieworder", "/icons/whm/viewpickswaiting.gif", "View Orders");
	addMenuButton(",VIEWSLO,", $pick_method , "getorderslo.php", "SLO", "/icons/whm/button.php?text=View+SLO&fromimage=Blank_Button_50x100.gif", "View SLO 
Orders");
	addMenuButton(",VIEWORDER2,", $pick_method , "getorderkwn.php", "Matching", "/icons/whm/button.php?text=View+Matching+Orders&fromimage=Blank_Button_50x100.gif", "View 
Matching 
Orders");
	addMenuButton(",VIEWISSN,", $pick_method , "getfromissn.php", "scanissn", "/icons/whm/button.php?text=Scan+ISSN&fromimage=Blank_Button_50x100.gif", "Scan ISSN");
	addMenuButton(",VIEWLOCN,", $pick_method , "getfromlocnall.php", "alllocn", "/icons/whm/button.php?text=All+Location&fromimage=Blank_Button_50x100.gif", "All Location");

	if ($allocated_tot <> 0)
	{
		// have picks allocated already
		addMenuButton(",CONTINUE,", $pick_method , "transactionUA.php", "viewnext", "/icons/whm/continue_picks.gif", "Continue");
	}
	if ($despatch_tot <> 0)
	{
		// have picks ready to despatch
		addMenuButton(",MOVEDESPATCH,", $pick_method , "gettolocn.php", "despatch", "/icons/whm/pick.despatch.trolley.gif", "Move
Despatch");
	}
	if ($wk_sysuser == "T")
	{
		// view status
		addMenuButton(",STATUS,", $pick_method , "ViewStatus.php", "HowMany", "/icons/whm/button.php?text=View+Status&fromimage=Blank_Button_50x100.gif", "View Status");
		// view wip
		addMenuButton(",WIP,", $pick_method , "WipDay.php", "WIP", "/icons/whm/button.php?text=View+WIP&fromimage=Blank_Button_50x100.gif", "View WIP");
		//cancel menu
		addMenuButton(",CANCEL,", $pick_method , "cancel.php", "cancel", "/icons/whm/button.php?text=Cancel+Menu&fromimage=Blank_Button_50x100.gif", "Cancel");
		addMenuButton(",REVDS2PL,", $pick_method , "getDStoPL.php", "revertDStoPL", "/icons/whm/button.php?text=Revert+DS+PL&fromimage=Blank_Button_50x100.gif", "Revert DS  
To PL");
		addMenuButton(",REVDX2DS,", $pick_method , "getDXtoPL.php", "revertDXtoDS", "/icons/whm/button.php?text=Revert+DX+DS&fromimage=Blank_Button_50x100.gif", "Revert DX
to DS");
		if ($despatch_tot <> 0)
		{
			// have picks ready to despatch
			addMenuButton(",MOVEDESPATCH2,", $pick_method , "confirmto.php", "despatch", "/icons/whm/button.php?text=Move+To+Despatch&fromimage=Blank_Button_50x100.gif", "Move to
Despatch");
		}
	}
	// add a trolley location to an order 
	// transfer a trolley location to despatch
	if ($wk_label_posn > 0) 
	{
		echo("</tr>\n");
	}
//commit

ibase_commit($dbTran);
?>
</table>
</body>
</html>
