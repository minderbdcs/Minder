<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Pick by ISSN Menu</title>
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
	print ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	print("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select first 1 1 from pick_item ";
$Query .= " where pick_line_status in ('AL','PG')";
$Query .= " and device_id = '".$tran_device."'";
//print($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to Read Total!<BR>\n");
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
$Query .= " where pick_line_status in ('AL','PG')";
$Query .= " and device_id = '".$tran_device."'";
$Query .= " and (not prod_id is NULL)";
$Query .= " and (prod_id > '')";
//print($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to Read Total!<BR>\n");
	exit();
}
	
$prod_tot = 0;
	
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$prod_tot = 0;
		}
		else
		{
			$prod_tot = $Row[$i];
		}
	}
}
	
//release memory
ibase_free_result($Result);
	
$Query = "select first 1 1 from pick_item ";
$Query .= " where pick_line_status in ('PL')";
$Query .= " and device_id = '".$tran_device."'";
//print($Query);
	
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to Read Total!<BR>\n");
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

//echo('<h4 ALIGN="LEFT">Pick Menu </h4>');
echo('<h4 ALIGN="LEFT">Pick by ISSN Menu ');
if (isset($tran_device))
{
	echo($tran_device );
}
echo('</h4>');

	$default_method = "";
	$default_method .= ",VIEWOPEN,";
	$default_method .= ",ALLOCATE,";
	$default_method .= ",CONTINUE,";
	$default_method .= ",REPRINT,";
	$default_method .= ",DESPATCH,";

	// Create a table.
	echo ("<table BORDER=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",VIEWOPEN,", $default_method, "ViewOpen.php", "viewopen", "/icons/whm/button.php?text=View+Waiting+Picks&fromimage=Blank_Button_50x100.gif", "View 
Waiting Picks");
	addMenuButton(",ALLOCATE,", $default_method, "transactionAL.php", "allocate", "/icons/whm/button.php?text=Allocate+Picks&fromimage=Blank_Button_50x100.gif", "Allocate 
Picks");
	if ($allocated_tot <> 0)
	{
		addMenuButton(",CONTINUE,", $default_method, "transactionUA.php", "viewnext", "/icons/whm/continue_picks.gif", "Continue"); 
		if ($prod_tot <> 0)
		{
			addMenuButton(",REPRINT,", $default_method, "getprinter.php?reprint=y", "reprint", "/icons/whm/REPRINT_50x100.gif", "RePrint"); 
		}
	}
	if ($despatch_tot <> 0)
	{
		addMenuButton(",DESPATCH,", $default_method, "gettomethod.php", "despatch", "/icons/whm/Despatch_50x100.gif", "Despatch"); 
	}

	if ($wk_label_posn > 1)
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
	print("document.getopen.viewopen.focus();\n");
}
/*
else
{
	print("document.viewopen.focus();\n");
}
*/
?>
</script>
</body>
</html>
