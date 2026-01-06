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

	
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

echo('<h4 ALIGN="LEFT">Pick by Prod2 Menu ');
if (isset($tran_device))
{
	echo($tran_device );
}
echo('</h4>');


	$pick_method = ",";
	$pick_method .= "NEXTPROD,"; /* get next order */
	$pick_method .= "EXPORTPICKS,"; /* export  */
	$pick_method .= "WIP2,"; /* get wip by day */
	$pick_method .= "CONTINUE,MOVEDESPATCH,"; /* continue buttons */
	$pick_method .= "STATUS,WIP,REPRINT,"; /* admin buttons */

	// Create a table.
	echo ("<table BORDER=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",NEXTPROD,", $pick_method, "nextprod.php", "nextprod", "/icons/whm/nextproduct.gif", "Get Next 
Product");
	addMenuButton(",STATUS,", $pick_method, "ViewAllocate.php", "HowMany", "/icons/whm/button.php?text=Status", "Status");

	if ($wk_sysuser == 'T')
	{
		if ($wk_label_posn < 1)
		{
			echo ("<tr>");
		}
		echo ("<td>");
		echo("<form action=\"../util/dbquery.csv\" method=\"post\" name=ExportHowMany>\n");
		echo("<input type=\"hidden\" name=\"ExportAs\" value=\"CSV\"> ");  
		echo("<input type=\"hidden\" name=\"Query\" ");  
		$wk_query = "select * from pick_wip_export";
		echo("value=\"" . urlencode($wk_query) . "\"> ");  
		//echo("<input type=\"IMAGE\" ");  
		//echo('SRC="/icons/whm/button.php?text=Export+WIP" alt="ExportWIP">');
		$alt = "Export WIP";
		$menuName = "ExportHowMany";
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
		echo("</form>");
		echo ("</td>");
		if ($wk_label_posn == 1)
		{
			echo ("</tr>");
			$wk_label_posn = 0;
		}
		else
		{
			$wk_label_posn = 1;
		}
	}
	if ($wk_sysuser == 'T')
	{
		addMenuButton(",EXPORTPICKS,", $pick_method , "ExportPicks.csv", "ExportHowMany2", "/icons/whm/button.php?text=Export+WIP+ISSNs&fromimage=Blank_Button_50x100.gif", "Export WIP 
with Issns");
	}
	if ($allocated_tot <> 0)
	{
		// have picks allocated already
		addMenuButton(",CONTINUE,", $pick_method , "transactionUA.php", "viewnext", "/icons/whm/continue_picks.gif", "Continue");
	}
	if ($despatch_tot <> 0)
	{
		// have picks ready to despatch
		addMenuButton(",MOVEDESPATCH,", $pick_method , "confirmto.php", "despatch", "/icons/whm/pick.despatch.gif", "Despatch");
	}

	if ($wk_sysuser == 'T')
	{
		// view wip
		addMenuButton(",WIP,", $pick_method , "WipDay.php", "WIP", "/icons/whm/button.php?text=Picks+Today&fromimage=Blank_Button_50x100.gif", "Picks Today");
		addMenuButton(",WIP2,", $pick_method , "WipDay2.php", "wipday2", "/icons/whm/button.php?text=Picks+by+Day&fromimage=Blank_Button_50x100.gif", "Picks by 
Day");
		// reprint order
		addMenuButton(",REPRINT,", $pick_method , "PrintOrder.php", "reprintorder", "/icons/whm/button.php?text=Reprint+Order&fromimage=Blank_Button_50x100.gif", "Reprint 
Order");
	}
	if ($wk_label_posn == 1)
	{
		echo ("</tr>");
		$wk_label_posn = 0;
	}
	echo ("</table>");
?>
<script type="text/javascript">
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.getopen.viewopen.focus();\n");
}
?>
</script>
</body>
</html>
