<?php
include "../login.inc";
?>
<html>
 <HEAD>
  <TITLE>Cancel Replenish Menu</TITLE>
 </HEAD>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#F41F10">

<?php
echo("<H4 ALIGN=\"LEFT\">Cancel Allocated Replenishs");
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
{
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
/*
	if ($wk_sysuser == 'T')
	{
		echo ("<TD>");
		echo("<FORM action=\"transactionCN.php\" method=\"post\" name=cancelall>\n");
		echo("<INPUT type=\"hidden\" name=\"trans_type\" value=\"PKCA\">\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=Unpick+All&fromimage=');
		echo('Blank_Button_50x100.gif" alt="Unpick">');
		echo("</FORM>");
		echo ("</TD>");
	}
*/
	echo ("<TD>");
	echo("<FORM action=\"transactionTR.php\" method=\"post\" name=dolater>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=Do+Later&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Later">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	$buttonsOnLast = 0;
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=continue>\n");
	whm2buttons('Continue', 'replenish_Menu.php',"Y","Back_50x100.gif","Back","continue_picks.gif");
}

//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

?>
</body>
<?php
/*
<SCRIPT>
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.cancelall.cancelall.focus();\n");
}
else
{
	echo("document.cancelall.focus();\n");
}
</SCRIPT>
*/
?>
</html>

