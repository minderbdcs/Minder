<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.ord/TR/html4/framset.dtd">
<html>
<head>
<title>Show Cancelled Shipping Lines</title>
</head>
<body BGCOLOR="#0080FF" >
<?php
function dopager( $Query )
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	require 'adodb/adodb-pager.inc.php';
	//require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);
	//$conn->connect($Host,'sysdba','masterkey','d:/asset.rf/database/wh.v39.gdb');
	//$conn->connect($Host,$User,$Password,'/data/asset.rf/wh.v39.gdb');
	$conn->connect($Host,$User,$Password,$mydb);

	if ($DoCommit <> 'F')
	{
		$conn->CommitTrans();
	}
	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesLine',true);
	$pager->Render(5);

}

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

$Pager_Commit = 'F';
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// what fields have been saved

$wk_salesorder = getBDCScookie($Link, $tran_device, "SalesOrder");

$wk_product = "";

if (isset($_POST['product'])) 
{
	$wk_product = $_POST["product"];
}
if (isset($_GET['product'])) 
{
	$wk_product = $_GET["product"];
}

//echo "product:";
//print_r($wk_product);
//print_r($_POST['product']);

echo("<h3>Shipping Order - Show Cancelled Lines</h3>\n");
echo("<FORM action=\"CanceledLines.php\" method=\"post\" name=getissn\n>");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("Order\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seensalesorder\" value=\"$wk_salesorder\" readonly > ");  
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
?>
<div id="tophalf">
<?php

if (isset($_GET['SalesLine_next_page']))
{
	$wk_query1 = getBDCScookie($Link, $tran_device, "msaleslinel1");
 	$wk_query2 = getBDCScookie($Link, $tran_device, "msaleslinel2");
 	$wk_query3 = getBDCScookie($Link, $tran_device, "msaleslinel3");
 	$wk_query4 = getBDCScookie($Link, $tran_device, "msaleslinel4");
	$Query1 = $wk_query1 . $wk_query2 . $wk_query3 . $wk_query4;
	$Query = str_replace("~","'",$Query1);
	//echo $Query;
	if ($Query <> "")
	{
		dopager($Query );
	}
}
else
{
	// do the ssn lines
	$Query2 = "select description, label from query_layout where code = 'msaleline' order by sequence";
	
	if (!($Result3 = ibase_query($Link, $Query2)))
	{
		echo("Unable to query layout!<BR>\n");
		exit();
	}
	
	$rcount = 0;
	$Query = "select first 5 ";
	$Query = "select ";
	while (($Row = ibase_fetch_row($Result3))) {
		//$fields[$rcount] = $Row[1];
	 	//$Query .= $Row[0] . ",";
 		$Query .= $Row[0] . ' as ' . $Row[1] . ',';
		$rcount++;
	}
	
	ibase_free_result($Result3);
	
	//echo("[$Query]\n");
	$Query = substr($Query,0,strlen($Query) - 1);
	$Query .= " from pick_item_cancel pick_item  ";
	$Query .= " left outer join issn  on pick_item.ssn_id = issn.ssn_id ";
	$Query .= " left outer join prod_profile on issn.prod_id = prod_profile.prod_id";
	$Query .= " where pick_item.pick_order = '" . $wk_salesorder . "'";
	$Query .= " and pick_item.ssn_id is not null ";
	
	// do the product lines
	$Query2 = "select description, label from query_layout where code = 'msaleline2' order by sequence";
	
	if (!($Result3 = ibase_query($Link, $Query2)))
	{
		echo("Unable to query layout!<BR>\n");
		exit();
	}
	
	$rcount = 0;
	$Query .= " union select ";
	while (($Row = ibase_fetch_row($Result3))) {
		//$fields[$rcount] = $Row[1];
	 	$Query .= $Row[0] . ",";
 		//$Query .= $Row[0] . ' as ' . $Row[1] . ',';
		$rcount++;
	}
	
	ibase_free_result($Result3);
	
	//echo("[$Query]\n");
	$Query = substr($Query,0,strlen($Query) - 1);
	$Query .= " from pick_item_cancel pick_item ";
	$Query .= " left outer join prod_profile on pick_item.prod_id = prod_profile.prod_id";
	$Query .= " where pick_item.pick_order = '" . $wk_salesorder . "'";
	$Query .= " and pick_item.ssn_id is null ";
	
	//echo $Query;
	$Query1 = str_replace("'","~",$Query);
	//echo "len query = " . strlen($Query);
	
	if (strlen($Query1) > 250)
	{
		$wk_query1 = substr($Query1,0,250);
		//echo "len query1 = " . strlen($wk_query1);
		$wk_query5 = substr($Query1,250);
		//echo "len query5 = " . strlen($wk_query5);
		if (strlen($wk_query5) > 250)
		{
			$wk_query2 = substr($wk_query5,0,250);
			//echo "len query2 = " . strlen($wk_query2);
			$wk_query6 = substr($wk_query5,250);
			//echo "len query6 = " . strlen($wk_query6);
			if (strlen($wk_query6) > 250)
			{
				$wk_query3 = substr($wk_query6,0,250);
				//echo "len query3 = " . strlen($wk_query3);
				$wk_query7 = substr($wk_query6,250);
				//echo "len query6 = " . strlen($wk_query6);
			}
			else
			{
				$wk_query3 = $wk_query6;
				$wk_query7 = "";
			}
		}
		else
		{
			$wk_query2 = $wk_query5;
			$wk_query3 = "";
			$wk_query7 = "";
		}
	}
	else
	{
		$wk_query1 = $Query1;
		$wk_query2 = "";
		$wk_query3 = "";
		$wk_query7 = "";
	}
	
	$Query = str_replace("~","'",$Query1);
	
	 setBDCScookie($Link, $tran_device, "msaleslinel1", $wk_query1);
	 setBDCScookie($Link, $tran_device, "msaleslinel2", $wk_query2);
	 setBDCScookie($Link, $tran_device, "msaleslinel3", $wk_query3);
	 setBDCScookie($Link, $tran_device, "msaleslinel4", $wk_query7);
	
	dopager($Query );
	
} /* end of else */

{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./GetOrdCust.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif","N");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
	echo ("<TD>");
	echo("<FORM action=\"./ShowLine.php\" method=\"post\" name=ShowLines>\n");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=Continue&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Continue"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo("<TD>");
	echo("<FORM action=\"../login/logout.php\" method=\"post\" name=getout>\n");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/LogOut_50x100.gif" alt="Logout">');
	echo("</FORM>");
	echo("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
}
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);
?>
</div>
</body>
</html>
