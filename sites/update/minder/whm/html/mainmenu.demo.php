<?php
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	$demomode = 1;
	if (isset($demomode))
	{
		setcookie("LoginUser","BDCS|MA",time()+86400,"/");
		setcookie("SaveUser","BDCS|MA|TR",time()+1111000,"/");
		//setcookie("SaveUser","BDCS|MA|PR",time()+1111000,"/");
	}
	else
	{
		header ("Location: ./login/login.php?Message=LoggedOut");
	}
}
?>
<HTML>
 <HEAD>
  <TITLE>Main Menu Page</TITLE>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="core-style.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
?>
 </HEAD>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <BODY BGCOLOR="#FFFFFF">

<?php
//echo("<H4 ALIGN=\"LEFT\">Inventory v1.");
//echo("<H4>Inventory v1.");
echo("<table border=\"0\" align=\"left\">");
echo("<tr><td>IMS v1.");
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
if ($UserType == "PR")
{
	echo("Prod");
}
else
{
	echo("Test");
}
if (isset($DBDevice))
{
	echo(".".$DBDevice);
}
/*
login sets to default
if syadmin can select from all warehouse
otherwise can select from those in access_user
if only 1 then no select just display
if only 0 then no select just display
*/

require_once 'DB.php';
require 'db_access.php';

/*
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Can't connect to DATABASE!");
	//exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
if (isset($_POST['WH']))
{
	$newwh = $_POST['WH'];
	$Query = "update session set description = '" . $newwh . "' where device_id='" . $DBDevice . "' and code = 'CURRENT_WH_ID'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Sessions!<BR>\n");
		//exit();
	}
	//release memory
	//ibase_free_result($Result);
}
$Query = "select description from session where device_id='" . $DBDevice . "' and code = 'CURRENT_WH_ID' "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sessions!<BR>\n");
	//exit();
}
$current_wh = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		echo( " WH:" . $Row[0]);
		$current_wh = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
*/
//echo("</H4>\n");
echo("</td><td>");
echo("<FORM action=\"./mainmenu.php\" method=\"post\" name=changewh>\n");
echo("<SELECT name=\"WH\" onchange=\"document.changewh.submit()\" >\n");
$Query = "select wh_id from warehouse where wh_id < 'X' or wh_id > 'X~' order by wh_id "; 

//echo($Query);
/*
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Warehouse!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $current_wh)
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
*/

echo("</select></form>");
echo("</td></tr>");
echo("</table><br><br><br>");
//if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
//{
	// html 4.0 browser
	//echo("<div id=\"col1\">");
	// Create a table.
	echo ("<table border=\"0\" align=\"left\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./description/GetLocn.php\" method=\"post\" name=getdescription>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Description_50x100.gif" alt="Description"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"./despatch/despatch_menu.php\" method=\"post\" name=getdespatch>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Despatch_50x100.gif" alt="Despatch"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./transfer/Transfer_Menu.php\" method=\"post\" name=gettransfer>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="Transfer"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	//echo("<FORM action=\"./stocktake/GetSTKLocn.php\" method=\"post\" name=getstock>\n");
	echo("<FORM action=\"./stocktake/Stocktake_menu.php\" method=\"post\" name=getstock>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Stocktake_50x100.gif" alt="Stocktake"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./test/GetSSNFrom.php\" method=\"post\" name=gettest>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/TEST_50x100.gif" alt="Test"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	$Query = "select pick_method, putaway_method from control "; 
	//echo($Query);
/*
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		//exit();
	}
	$pick_method = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$pick_method = $Row[0];
			$putaway_method = $Row[1];
		}
	}
	//release memory
	ibase_free_result($Result);
*/
	list($pick_method0, $pick_method1, $pick_method2, $pick_method3, $pick_method4) = explode(',', $pick_method . ",,,,");
	list($putaway_method0, $putaway_method1, $putaway_method2 ) = explode(',', $putaway_method . ",,,,");
	$wk_label_posn = 0;
	if ($putaway_method1 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($putaway_method1 )
		{
			case "ISSN":
				echo("<FORM action=\"./putaway/getfromlocn.php\" method=\"post\" name=getputaway>\n");
				break;
			case "ISSNTEST":
				echo("<FORM action=\"./putaway.prod/getfromlocn.php\" method=\"post\" name=getputaway>\n");
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($putaway_method1 )
		{
			case "ISSN":
				echo('SRC="/icons/whm/putawayssn.gif" alt="Putaway"></INPUT>');
				break;
			case "ISSNTEST":
				echo('SRC="/icons/whm/putawaytest.gif" alt="Putaway"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/Putaway_50x100.gif" alt="Putaway"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}
	if ($putaway_method2 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($putaway_method2 )
		{
			case "ISSN":
				echo("<FORM action=\"./putaway/getfromlocn.php\" method=\"post\" name=getputaway>\n");
				$alt = "Putaway SSNs";
				break;
			case "ISSNTEST":
				echo("<FORM action=\"./putaway.prod/getfromlocn.php\" method=\"post\" name=getputaway>\n");
				$alt = "Putaway and Test";
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($putaway_method2 )
		{
			case "ISSN":
				echo('SRC="/icons/whm/putawayssn.gif" alt="Putaway"></INPUT>');
				break;
			case "ISSNTEST":
				echo('SRC="/icons/whm/putawaytest.gif" alt="Putaway"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/Putaway_50x100.gif" alt="Putaway"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}
/*
	echo ("<TD>");
	echo("<FORM action=\"./putaway/getfromlocn.php\" method=\"post\" name=getputaway>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Putaway_50x100.gif" alt="Putaway"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
*/
	if ($pick_method1 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($pick_method1 )
		{
			case "IS":
				echo("<FORM action=\"./pick/pick_Menu.php\" method=\"post\" name=getpick>\n");
				break;
			case "PL":
				echo("<FORM action=\"./pick.prod/pick_Menu.php\" method=\"post\" name=getpick>\n");
				break;
			case "PT":
				echo("<FORM action=\"./pick.prod.trolley/pick_Menu.php\" method=\"post\" name=getpick>\n");
				break;
			case "PO":
				echo("<FORM action=\"./pick.prod.order/pick_Menu.php\" method=\"post\" name=getpick>\n");
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($pick_method1 )
		{
			case "PT":
				echo('SRC="/icons/whm/pick2trolley.gif" alt="Pick"></INPUT>');
				break;
			case "PO":
				echo('SRC="/icons/whm/pick2despatch.gif" alt="Pick"></INPUT>');
				break;
			case "PL":
				echo('SRC="/icons/whm/pickproduct.gif" alt="Pick"></INPUT>');
				break;
			case "IS":
				echo('SRC="/icons/whm/pickssn.gif" alt="Pick"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/Pick_50x100.gif" alt="Pick"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}
	if ($pick_method2 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($pick_method2 )
		{
			case "IS":
				echo("<FORM action=\"./pick/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick SSN";
				break;
			case "PL":
				echo("<FORM action=\"./pick.prod/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Location";
				break;
			case "PT":
				echo("<FORM action=\"./pick.prod.trolley/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Trolley";
				break;
			case "PO":
				echo("<FORM action=\"./pick.prod.order/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Order";
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($pick_method2 )
		{
			case "PL":
				echo('SRC="/icons/whm/pickproduct.gif" alt="Pick"></INPUT>');
				break;
			case "IS":
				echo('SRC="/icons/whm/pickssn.gif" alt="Pick"></INPUT>');
				break;
			case "PT":
				echo('SRC="/icons/whm/pick2trolley.gif" alt="Pick"></INPUT>');
				break;
			case "PO":
				echo('SRC="/icons/whm/pick2despatch.gif" alt="Pick"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
				echo('Blank_Button_50x100.gif" alt="Pick2"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}
	if ($pick_method3 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($pick_method3 )
		{
			case "IS":
				echo("<FORM action=\"./pick/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick SSN";
				break;
			case "PL":
				echo("<FORM action=\"./pick.prod/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Location";
				break;
			case "PT":
				echo("<FORM action=\"./pick.prod.trolley/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Trolley";
				break;
			case "PO":
				echo("<FORM action=\"./pick.prod.order/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Order";
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($pick_method3 )
		{
			case "PL":
				echo('SRC="/icons/whm/pickproduct.gif" alt="Pick"></INPUT>');
				break;
			case "IS":
				echo('SRC="/icons/whm/pickssn.gif" alt="Pick"></INPUT>');
				break;
			case "PT":
				echo('SRC="/icons/whm/pick2trolley.gif" alt="Pick"></INPUT>');
				break;
			case "PO":
				echo('SRC="/icons/whm/pick2despatch.gif" alt="Pick"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
				echo('Blank_Button_50x100.gif" alt="Pick3"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}

	if ($pick_method4 > "")
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		switch ($pick_method4 )
		{
			case "IS":
				echo("<FORM action=\"./pick/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick SSN";
				break;
			case "PL":
				echo("<FORM action=\"./pick.prod/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Location";
				break;
			case "PT":
				echo("<FORM action=\"./pick.prod.trolley/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Trolley";
				break;
			case "PO":
				echo("<FORM action=\"./pick.prod.order/pick_Menu.php\" method=\"post\" name=getpick>\n");
				$alt = "Pick Product Order";
				break;
		}

		echo("<INPUT type=\"IMAGE\" ");  
		switch ($pick_method4 )
		{
			case "PL":
				echo('SRC="/icons/whm/pickproduct.gif" alt="Pick"></INPUT>');
				break;
			case "IS":
				echo('SRC="/icons/whm/pickssn.gif" alt="Pick"></INPUT>');
				break;
			case "PT":
				echo('SRC="/icons/whm/pick2trolley.gif" alt="Pick"></INPUT>');
				break;
			case "PO":
				echo('SRC="/icons/whm/pick2despatch.gif" alt="Pick"></INPUT>');
				break;
			default:
				echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
				echo('Blank_Button_50x100.gif" alt="Pick3"></INPUT>');
				break;
		}
		echo("</FORM>");
		echo ("</TD>");
	}

	$wk_label_posn = $wk_label_posn + 1;
	if ($wk_label_posn > 1)
	{
		echo ("</TR>");
		echo ("<TR>");
		$wk_label_posn = 0;
	}
	echo ("<TD>");
	echo("<FORM action=\"./query/query.php\" method=\"post\" name=getquery>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Query_50x100.gif" alt="Query"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	$wk_label_posn = $wk_label_posn + 1;
	if ($wk_label_posn > 1)
	{
		echo ("</TR>");
		echo ("<TR>");
		$wk_label_posn = 0;
	}
	echo ("<TD>");
	echo("<FORM action=\"./login/logout.php\" method=\"post\" name=getout>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/LogOut_50x100.gif" alt="Logout"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	$wk_label_posn = $wk_label_posn + 1;
	if ($wk_label_posn > 1)
	{
		echo ("</TR>");
		echo ("<TR>");
		$wk_label_posn = 0;
	}
	echo ("<TD>");
	echo("<FORM action=\"./receive/receive_menu.php\" method=\"post\" name=getreceive>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/RECEIVE_50X100.gif" alt="Receive"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
/*
	$wk_label_posn = $wk_label_posn + 1;
	if ($wk_label_posn > 1)
	{
		echo ("</TR>");
		echo ("<TR>");
		$wk_label_posn = 0;
	}
	echo ("<TD>");
	echo("<FORM action=\"./cancel/getssn.php\" method=\"post\" name=getcancel>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/SSN_Cancel_50x100.gif" alt="CancelSSN"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
*/
	$wk_label_posn = $wk_label_posn + 1;
	if ($wk_label_posn > 1)
	{
		echo ("</TR>");
		echo ("<TR>");
		$wk_label_posn = 0;
	}
	echo ("<TD>");
	//echo("<FORM action=\"./printer/dir.php\" method=\"post\" name=getreprints>\n");
	echo("<FORM action=\"./printer/print_Menu.php\" method=\"post\" name=getreprints>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/REPRINT_50x100.gif" alt="Reprint"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo("</table>");
	//echo("</div>");
//}
//commit
//$Link->commit();
//ibase_commit($dbTran);

//close
//$Link->disconnect();
//ibase_close($Link);

?>
 </BODY>
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<SCRIPT>\n");
	echo("document.getdescription.description.focus();\n");
	echo("</SCRIPT>\n");
}
?>

</HTML>
