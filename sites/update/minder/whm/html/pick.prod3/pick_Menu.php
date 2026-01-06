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
include "logme.php";
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

//==============================================================

/*
login sets to default
*/


if (isset($_POST['PICKSEQ']))
{
	$newseq = $_POST['PICKSEQ'];
/*
	$Query = "update session set description = '" . $newseq . "' where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_SEQ'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Sessions!<BR>\n");
		//exit();
	}
	//release memory
	//ibase_free_result($Result);
*/
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_SEQ", $newseq );
}
if (isset($_POST['PICKDIR']))
{
	$newdir = $_POST['PICKDIR'];
/*
	$Query = "update session set description = '" . $newdir . "' where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_DIR'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Sessions!<BR>\n");
		//exit();
	}
	//release memory
	//ibase_free_result($Result);
*/
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR", $newdir );
}
echo("<table>");
echo("<tr><td>");
echo( "Seq:");
/*
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_SEQ' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_pickseq = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		//echo( " Seq:" . $Row[0]);
		$current_pickseq = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
*/
	$current_pickseq = getBDCScookie($Link, $tran_device, "CURRENT_PICK_SEQ"  );
echo("</td><td>");
echo("<form action=\"" .  basename($_SERVER["PHP_SELF"]) ."\" method=\"post\" name=changeseq>\n");
echo("<select name=\"PICKSEQ\" onchange=\"document.changeseq.submit()\" >\n");
$Query = "select code from options where group_code = 'PICK_SEQ'  order by code "; 

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Warehouse!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $current_pickseq)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);

echo("</select></form>");
echo("</td><td>");
echo( "Dir:");
/*
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_PICK_DIR' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_pickdir = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$current_pickdir = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
*/
	$current_pickdir = getBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR"  );
echo("</td><td>");
echo("<form action=\"" .  basename($_SERVER["PHP_SELF"])   ."\" method=\"post\" name=changedir>\n");
echo("<select name=\"PICKDIR\" onchange=\"document.changedir.submit()\" >\n");
$Query = "select code from options where group_code = 'PICK_DIR'  order by code "; 

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Warehouse!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $current_pickdir)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);

echo("</select></form>");
echo("</td></tr>");
echo("</table>");
//=============================================================
//echo (" <form action=\"GetPick.php\" method=\"post\" name=transferpick>");
	
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

echo('<h4 ALIGN="LEFT">Pick by Prod3 Menu ');
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
	$pick_method .= "PREPSCAN,"; /* prepare for address label */

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
	{
		// reprint order
		addMenuButton(",PREPSCAN,", $pick_method , "statusdc.php", "prepareforRescan", "/icons/whm/button.php?text=Prepare+for+Rescan&fromimage=Blank_Button_50x100.gif", "Prepare for 
Address 
Label");
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
/*
else
{
	echo("document.viewopen.focus();\n");
}
*/
?>
</script>
</body>
</html>
