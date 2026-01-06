<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Cancel Pick Menu</title>
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
 <body BGCOLOR="#F41F10">
<?php
echo("<h4 ALIGN=\"LEFT\">Cancel Allocated Picks</h4>");
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

$pick_method = ",UNPICK,REVDS2PL,";
{
 	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	if ($wk_sysuser == 'T')
	{
		echo ("<td>");
		echo("<form action=\"transactionCN.php\" method=\"post\" name=cancelall>\n");
		echo("<input type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
		addScreenButton( "cancelall", "/icons/whm/button.php?text=Unpick+All&fromimage=Blank_Button_50x100.gif", "Unpick All");
/*
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=Unpick+All&fromimage=');
		echo('Blank_Button_50x100.gif" alt="Unpick">');
*/
		echo("</form>");
		echo ("</td>");
		//$wk_label_posn = 1;
		addMenuButton(",REVDS2PL,", $pick_method , "getDStoPL.php", "revertDStoPL", "/icons/whm/button.php?text=Revert+DS+PL&fromimage=Blank_Button_50x100.gif", "Revert Order");
	}


	//echo ("</tr>");
	$buttonsOnLast = 0;
/*
	if ($wk_sysuser == 'T')
	{
		// repick order
		if ($buttonsOnLast < 1)
		{
			echo ("<tr>");
		}
		echo ("<td>");
		echo("<FORM action=\"RePickOrder.php\" method=\"post\" name=repick>\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=RePick+Order" alt="RePick+Order">');
		echo("</FORM>");
		echo ("</td>");
		if ($buttonsOnLast == 1)
		{
			echo ("</tr>");
			$buttonsOnLast = 0;
		}
		else
		{
			$buttonsOnLast = 1;
		}
	}
*/
/*
	if ($wk_sysuser == 'T')
	{
		// cancel order
		if ($buttonsOnLast < 1)
		{
			echo ("<tr>");
		}
		echo ("<td>");
		echo("<FORM action=\"CancelOrder.php\" method=\"post\" name=cancelorder>\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=Cancel+Order" alt="Cancel+Order">');
		echo("</FORM>");
		echo ("</td>");
		if ($buttonsOnLast == 1)
		{
			echo ("</tr>");
			$buttonsOnLast = 0;
		}
		else
		{
			$buttonsOnLast = 1;
		}
	}
*/
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=continue>\n");
	whm2buttons('Continue', 'pick_Menu.php',"Y","Back_50x100.gif","Back","continue_picks.gif");
/*
	echo("<BUTTON type=\"button\" accesskey=\"l\" name=\"all\" value=\"cancelall\" onclick=\"location.href='transactionCN.php?trans_type=PKCA';\">\n");
	echo("aLL Picks<IMG SRC=\"/icons/hand.up.gif\" alt=\"all\"></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"p\" name=\"cancelone\" value=\"current Pick\" onclick=\"location.href='transactionCN.php?trans_type=PKCN';\">\n");
	echo("current Pick<IMG SRC=\"/icons/compressed.gif\" alt=\"current\"></BUTTON>\n");
	echo("<BUTTON name=\"continue\" type=\"button\" onClick=\"location.href='getfromlocn.php';\">\n");
	echo("Continue<IMG SRC=\"/icons/forward.gif\" alt=\"back\"></BUTTON>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onClick=\"location.href='pick_Menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

?>
<?php
/*
<script type="text/javascript">
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.cancelall.cancelall.focus();\n");
}
else
{
	echo("document.cancelall.focus();\n");
}
</script>
*/
?>
</body>
</html>

