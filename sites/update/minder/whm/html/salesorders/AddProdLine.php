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
<title>Get Lines Product</title>
</head>
<body BGCOLOR="#0080FF">

<?php
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

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

if ($wk_product == "")
{
	$wk_product = array("");
}

if (isset($wk_product))
{
	$wk_added_prod = 'F';
	foreach ($wk_product as $Key_issn => $Value_issn)
	{
		//echo $Value_issn;
		if ($Value_issn <> "")
		{
			// add a pick_item for this issn
			$wk_current_qty = 1;
			$Query = "EXECUTE PROCEDURE ADD_PICK_ITEMS('";
			$Query .= $wk_salesorder . "','T','T','";
			$Query .= $Value_issn . "','','','";
			$Query .= $wk_current_qty  . "','NOW','";
			//$Query .= $tran_user . "')";
			$Query .= $tran_user . "','0')";
			echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Add Item to Pick Items table!<BR>\n");
				exit();
			}
			$wk_added_prod = 'T';
	
		}
	}
	if ($wk_added_prod == "T")
	{
		$Query = "update pick_order set pick_status = 'UC' ";
		$Query .= " where pick_order = '" . $wk_salesorder . "' ";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Order table!<BR>\n");
			exit();
		}
		//commit
		ibase_commit($dbTran);
		// go to get the to location
		header("Location: ShowLine.php");
		exit;
	}
}


$wk_dodata = "N";

$wk_syscomp = "";
$wk_import_status = "";
{
	$Query = "SELECT company_id, PICK_IMPORT_SSN_STATUS from control";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query control table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_syscomp = $Row[0];
		$wk_import_status = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
}


$Query3 = "select prod_id, short_desc from prod_profile  order by short_desc ";

if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Get Products for Choice!<BR>\n");
	exit();
}

echo("<h3>Shipping Line Add a Product</h3>\n");
echo("<FORM action=\"AddProdLine.php\" method=\"post\" name=getissn\n>");
?>
<div id="tophalf">
<?php
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
echo ("<TABLE width=\"80%\">\n");
echo ("<COLGROUP>\n");
echo ("<COL>\n");
echo ("</COLGROUP>\n");
echo ("<TR>\n");
echo ("</TR>\n");
echo ("<TR>\n");

echo ("<TD>\n");
echo("<SELECT multiple name=\"product[]\" size=\"15\" >\n");

while ( ($Row3 = ibase_fetch_row($Result3)) ) {
	//if ($wk_product == $Row3[0])
	if (in_array($Row3[0],$wk_product))
	{
		//echo( "<OPTION value=\"$Row3[0]\" selected>$Row3[1] $Row3[0]\n");
		echo( "<OPTION value=\"$Row3[0]\" selected>$Row3[1]\n");
	}
	else
	{
		//echo( "<OPTION value=\"$Row3[0]\">$Row3[1] $Row3[0]\n");
		echo( "<OPTION value=\"$Row3[0]\">$Row3[1]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE>\n");
echo ("<TR>\n");

echo("<TH>Select Product to Add</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

	
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
	//echo("<FORM action=\"./Order_Menu.php\" method=\"post\" name=ShowLines>\n");
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
echo("<FORM action=\"ShowLine.php\" method=\"post\" name=getperson\n>");
echo("<INPUT type=\"hidden\" name=\"salesline\"> ");  

echo("</FORM>");
}
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);
?>
</div>
</body>
</html>
