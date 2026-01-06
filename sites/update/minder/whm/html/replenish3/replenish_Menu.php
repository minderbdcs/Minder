<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Replenish Menu</title>
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
 <body BGCOLOR="#FFFFF0">
 <h2>Replenish Menu</h2>

<?php
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

// my screen id
$wk_replen_screen = 'RP02';

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select first 1 1 from transfer_request ";
$Query .= " where trn_status in ('AL','PG')";
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

$Query = "select first 1 1 from transfer_request ";
$Query .= " where trn_status in ('PL','PG')";
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

$wk_company = "";
{
	$Query = "SELECT description from options";
	$Query .= " where group_code = 'CMPREPLEN' and code = '" . $wk_replen_screen . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Options!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_company = $Row[0];
 		echo("<h2>$wk_company</h2>\n");
	}
	//release memory
	ibase_free_result($Result);
}
	
//commit
ibase_commit($dbTran);
	$replenish_method = "";
	$replenish_method .= ",NEXTPROD,";
	$replenish_method .= ",STATUS,";
	$replenish_method .= ",WIPDAY,";
	$replenish_method .= ",LOCATION,";
	
//close
//ibase_close($Link);

	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",NEXTPROD,", $replenish_method, "nextprod.php", "nextprod", "/icons/whm/nextproduct.gif", "Next 
Product");
	addMenuButton(",STATUS,", $replenish_method, "ViewAllocate.php", "howmany", "/icons/whm/button.php?text=Status", "Status"); 
	if ($allocated_tot <> 0)
	{
		addMenuButton(",CONTINUE,", ",CONTINUE,", "getfromlocn.php", "viewnext", "/icons/whm/button.php?text=Continue+Replenish", "Continue"); 
	}
	if ($despatch_tot <> 0)
	{
		addMenuButton(",DESPATCH,", ",DESPATCH,", "gettoso.php", "despatch", "/icons/whm/TRANSFER_50x100.gif", "Despatch"); 
	}


	{
		if ($wk_label_posn < 1)
		{
			echo ("<tr>");
		}
		echo ("<td>");
		echo("<form action=\"../util/dbquery.csv\" method=\"post\" name=ExportHowMany>\n");
		echo("<input type=\"hidden\" name=\"ExportAs\" value=\"CSV\"> ");  
		echo("<input type=\"hidden\" name=\"Query\" ");  
		$wk_query = "select r1.to_wh_id,r1.to_locn_id,r1.prod_id,p1.short_desc,r1.required_qty as max_qty,r1.current_qty as locn_qty,r1.wh_qty,r1.unpicked_order_qty,(r1.unpicked_order_qty - r1.current_qty) as fulfill_qty from replenish_locn r1 join prod_profile p1 on p1.prod_id=r1.prod_id order by r1.trn_priority,r1.to_wh_id,r1.to_locn_id";
		echo("value=\"" . urlencode($wk_query) . "\"> ");  
		//echo("<INPUT type=\"IMAGE\" ");  
		//echo('SRC="/icons/whm/button.php?text=Export+WIP&fromimage=');
		//echo('Blank_Button_50x100.gif" alt="ExportWIP">');
		$menuName = "ExportHowMany";
		$menuAlt = "Export Wip";
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"buttons\" />\n");
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
		addMenuButton(",CANCEL,", ",CANCEL,", "cancel.php", "Cancel", "/icons/whm/button.php?text=Cancel+Menu&fromimage=Blank_Button_50x100.gif", "Cancel"); 
		addMenuButton(",WIPDAY,", $replenish_method, "WipDay.php", "wipday", "/icons/whm/button.php?text=Done+Today&fromimage=Blank_Button_50x100.gif", "Replenishes 
Today"); 
	}
	addMenuButton(",LOCATION,", $replenish_method, "ModLocn.php", "modlocn2", "/icons/whm/button.php?text=Modify+Location&fromimage=Blank_Button_50x100.gif", "Modify
Location"); 

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
