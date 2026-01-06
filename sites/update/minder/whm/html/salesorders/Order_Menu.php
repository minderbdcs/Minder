<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Shipping Orders Menu</title>
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
 <body BGCOLOR="#0080FF">

<?php
//echo("  <h2>Sales Orders</h2> \n");
// get the order type to use
$wk_order_type = "SO";
// then only use that order type in choosing orders
// prefix orders by the 1st char of the order type
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


 setBDCScookie($Link, $tran_device, "OrderType", $wk_order_type );
	
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

echo('<h4 ALIGN="LEFT">Shipping Orders Menu ');
if (isset($tran_device))
{
	echo($tran_device );
}
echo('</h4>');


//echo (" <FORM action=\"GetPick.php\" method=\"post\" name=transferpick>");
	
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

	$sale_method = ',NEWORDER,CHOOSEORDER,SHOWORDER,';
	//echo("options:" . $sale_method);

	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",NEWORDER,", $sale_method, "neworder.php", "neworder", "/icons/whm/button.php?text=New+Order&fromimage=Blank_Button_50x100.gif", "New Order");
	addMenuButton(",CHOOSEORDER,", $sale_method, "ChooseOrder.php", "ChooseOrder", "/icons/whm/button.php?text=Choose+Order&fromimage=Blank_Button_50x100.gif", "Choose");
	addMenuButton(",SHOWORDER,", $sale_method, "getorder.php", "ShowOrder", "/icons/whm/button.php?text=Show+Order&fromimage=Blank_Button_50x100.gif", "Show Order");

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
		$wk_query = "select pick_order,created_date from pick_order";
		echo("value=\"" . urlencode($wk_query) . "\"> ");  
		$menuName = "ExportHowMany";
		$menuAlt  = "Export Wip";
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"buttons\" />\n");
		//echo("<INPUT type=\"IMAGE\" ");  
		//echo('SRC="/icons/whm/button.php?text=Export+WIP" alt="ExportWIP">');
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
?>
<?php
/*
echo("<script type=\"text/javascript\">\n");
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("document.getopen.viewopen.focus();\n");
}
*/
/*
else
{
	echo("document.viewopen.focus();\n");
}
echo("</script>\n");
*/
?>
</tr>
</table>
</body>
</html>
