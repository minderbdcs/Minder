<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.ord/TR/html4/framset.dtd">');
echo("\n<html>\n");
echo("<head>\n");
echo("<title>Get Orders Customer</title>\n");
echo("</head>\n");
function add_option ($wk_select, $wk_desc)
{
	if (strlen($wk_desc) > 45)
	{
		$wk_desc = substr($wk_desc,0,45);
	}
	echo( "<OPTION value=\"$wk_select\">$wk_desc\n");
	echo( "</OPTION>\n");
}

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

$wk_person = "";
$wk_company = "";
$wk_wh = "";

//phpinfo();
if (isset($_POST['salesorder'])) 
{
	$wk_salesorder = $_POST["salesorder"];
}
if (isset($_GET['salesorder'])) 
{
	$wk_salesorder = $_GET["salesorder"];
}
if (!isset($wk_sale_order))
{
	$wk_salesorder = getBDCScookie($Link, $tran_device, "SalesOrder");
}
if (isset($_POST['person'])) 
{
	$wk_person = $_POST["person"];
}
if (isset($_GET['person'])) 
{
	$wk_person = $_GET["person"];
}
if (isset($_POST['company'])) 
{
	$wk_company = $_POST["company"];
}
if (isset($_GET['company'])) 
{
	$wk_company = $_GET["company"];
}

if (isset($_POST['wh'])) 
{
	$wk_wh = $_POST["wh"];
}
if (isset($_GET['wh'])) 
{
	$wk_wh = $_GET["wh"];
}

$wk_dodata = "N";
if (isset($_POST['dodata'])) 
{
	$wk_dodata = $_POST["dodata"];
}
if (isset($_GET['dodata'])) 
{
	$wk_dodata = $_GET["dodata"];
}


$wk_syscomp = "";
{
	$Query = "SELECT company_id from control";
	
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
	}
	//release memory
	ibase_free_result($Result);
}

$wk_sysuser = "";
$wk_usercomp = "";
$wk_inventoryop = "";
{
	$Query = "SELECT sys_admin, company_id, inventory_operator from sys_user";
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
		$wk_usercomp = $Row[1];
		$wk_inventoryop = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
}

$wk_default_wh = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID");

$wk_defaulted_comp = "F";
if ($wk_company == "")
{
	if (($wk_inventoryop <> "T") and ($wk_sysuser <> "T"))
	{
		// try to default this
		if ($wk_usercomp == "")
		{
			// no user company - try the system
			$wk_company = $wk_syscomp;
		}
		else
		{
			$wk_company = $wk_usercomp;
		}
	}
	$wk_defaulted_comp = "T";
}

$wk_defaulted_wh = "F";
if ($wk_wh == "")
{
	// no wh - try the current one
	$wk_wh = $wk_default_wh;
	$wk_defaulted_wh = "T";
}

if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
{
	/* if im sysadmin then can access  all companys */
	$Query = "select company_id, name from company  order by company_id ";
}
else
{
	/* if im not sysadmin then can only access companys in access company */
	$Query = "select company_id, name from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') order by company_id ";
}
//echo($Query);

if ($wk_sysuser == "T") 
{
	/* if im sysadmin then can access  all warehouses */
	$Query2 = "select wh_id, description from warehouse  order by wh_id ";
}
else
{
	/* if im not sysadmin then can only access companys in access user */
	$Query2 = "select wh_id, description from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') order by wh_id ";
}

$Query3 = "select person_id, first_name, last_name, address_line1 from person where person_type starting 'C'  order by first_name, person_id ";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Company for Choice!<BR>\n");
	exit();
}
if (!($Result2 = ibase_query($Link, $Query2)))
{
	echo("Unable to Get Warehouse for Choice!<BR>\n");
	exit();
}
if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Get Customers for Choice!<BR>\n");
	exit();
}

//echo($Query);

$wk_po_wh = "";
$wk_po_company = "";
$wk_po_person = "";
{
	$Query4 = "SELECT wh_id, company_id, person_id from pick_order";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_po_wh = $Row4[0];
		$wk_po_company = $Row4[1];
		$wk_po_person = $Row4[2];
	}
	//release memory
	ibase_free_result($Result4);
}

//echo "comp " . $wk_company;
//echo "wh " . $wk_wh;
//echo "person " . $wk_person;
if ($wk_po_wh <> "")
{
	// default the wh to the orders
	if ($wk_defaulted_wh == "T")
	{
		// have defaulted the wh
		// but want to use the po_wh instead
		$wk_wh = $wk_po_wh;
	}
}
if ($wk_po_company <> "")
{
	// default the company to the orders
	if ($wk_defaulted_comp == "T")
	{
		// have defaulted the company
		// but want to use the po_company instead
		$wk_company = $wk_po_company;
	}
}
if ($wk_po_person <> "")
{
	// default the person to the orders
	if ($wk_person == "")
	{
		$wk_person = $wk_po_person;
	}
}

//echo "comp " . $wk_company;
//echo "wh " . $wk_wh;
//echo "person " . $wk_person;

echo("<h2>Shipping Order - Get Customer</h2>\n");

echo("<FORM action=\"GetOrdCust.php\" method=\"post\" name=getorder\n>");

echo("<INPUT type=\"hidden\" name=\"salesorder\" value=\"$wk_salesorder\" > ");  
echo ("<TABLE >\n");
echo ("<COLGROUP>\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL >\n");
echo ("</COLGROUP>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("Order\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seensalesorder\" value=\"$wk_salesorder\" readonly > ");  
echo ("</TD>\n");
echo ("</TABLE >\n");
//echo ("<TABLE width=\"80%\">\n");
echo ("<TABLE >\n");
echo ("<COLGROUP>\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"5%\">\n");
echo ("<COL >\n");
echo ("</COLGROUP>\n");
echo ("<TR>\n");
echo ("<TH>\n");
echo("Company\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("WH\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("Customer\n");
echo ("</TH>\n");
echo ("</TR>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("<SELECT name=\"company\" size=\"5\" onchange=\"document.getorder.submit()\" >\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (in_array($Row[0],$wk_company))
	if ($wk_company == $Row[0])
	{
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[0] $Row[1]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[1]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<SELECT name=\"wh\" size=\"5\" onchange=\"document.getorder.submit()\" >\n");

while ( ($Row2 = ibase_fetch_row($Result2)) ) {
	//if (in_array($Row2[0],$wk_wh))
	if ($wk_wh == $Row2[0])
	{
		echo( "<OPTION value=\"$Row2[0]\" selected>$Row2[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row2[0]\">$Row2[0]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<SELECT name=\"person\" size=\"5\" onchange=\"document.getorder.submit()\" >\n");

while ( ($Row3 = ibase_fetch_row($Result3)) ) {
	$wk_select = $Row3[0] . " " . $Row3[1] . " " . $Row3[2];
	if (strlen($wk_select) > 45)
	{
		$wk_select = substr($wk_select,0,45);
	}
	//if (in_array($Row3[0],$wk_person))
	if ($wk_person == $Row3[0])
	{
		echo( "<OPTION value=\"$Row3[0]\" selected>$wk_select\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row3[0]\">$wk_select\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("<TD>\n");
{
	$Query4 = "SELECT  ";
	$Query4 .= " p_address_line1 , ";
	$Query4 .= " p_address_line2 , ";
	$Query4 .= " p_address_line3 , ";
	$Query4 .= " p_address_line4 , ";
	$Query4 .= " p_address_line5 , ";
	$Query4 .= " p_city , ";
	$Query4 .= " p_state , ";
	$Query4 .= " p_post_code , ";
	$Query4 .= " p_country , ";
	$Query4 .= " p_phone , ";
	$Query4 .= " contact_name , ";
	$Query4 .= " p_first_name , ";
	$Query4 .= " p_last_name , ";
	$Query4 .= " p_same_as_invoice_to ";
	$Query4 .= " from pick_order ";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query Order!<BR>\n");
		exit();
	}
}
echo("<SELECT name=\"address\" size=\"5\" readonly >\n");

while ( ($Row4 = ibase_fetch_row($Result4)) ) {
	add_option($wk_salesorder, $Row4[10]. " ph " . $Row4[9]); // phone
	//add_option($wk_salesorder, $Row4[11] . $Row4[12]); // name
	if ($Row4[0] <> "")
		add_option($wk_salesorder, $Row4[0]); // line1
	if ($Row4[1] <> "")
		add_option($wk_salesorder, $Row4[1]); // line2
	if ($Row4[2] <> "")
		add_option($wk_salesorder, $Row4[2]); // line3
	if ($Row4[3] <> "")
		add_option($wk_salesorder, $Row4[3]); // line4
	if ($Row4[4] <> "")
		add_option($wk_salesorder, $Row4[4]); // line5
	if ($Row4[5] <> "" or $Row4[6] <> "" or $Row4[7]<> "")
		add_option($wk_salesorder, $Row4[5] . "  " . $Row4[6] . " " . $Row4[7]);
	if ($Row4[8] <> "")
		add_option($wk_salesorder, $Row4[8]); // country
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE>\n");
echo ("<TR>\n");

echo("<TH>Select Customer</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

echo ("<input type=\"hidden\" name=\"dodata\" value=\"1\">\n");

if ($wk_dodata == "1")
{

	$Query4 = "SELECT address_line1, ";
	$Query4 .= "address_line2, ";
	$Query4 .= "address_line3, ";
	$Query4 .= "address_line4, ";
	$Query4 .= "address_line5, ";
	$Query4 .= "person_type, ";
	$Query4 .= "city, ";
	$Query4 .= "state, ";
	$Query4 .= "post_code, ";
	$Query4 .= "country, ";
	$Query4 .= "phone_no, ";
	$Query4 .= "contact_first_name, ";
	$Query4 .= "contact_last_name, ";
	$Query4 .= "first_name, ";
	$Query4 .= "last_name ";
	$Query4 .= "FROM person ";
	$Query4 .= " where person_id = '" . $wk_person . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_pn_addr1 = $Row4[0];
		$wk_pn_addr2 = $Row4[1];
		$wk_pn_addr3 = $Row4[2];
		$wk_pn_addr4 = $Row4[3];
		$wk_pn_addr5 = $Row4[4];
		$wk_pn_type = $Row4[5];
		$wk_pn_city = $Row4[6];
		$wk_pn_state = $Row4[7];
		$wk_pn_post_code = $Row4[8];
		$wk_pn_country = $Row4[9];
		$wk_pn_phone = $Row4[10];
		$wk_pn_con_first_name = $Row4[11];
		$wk_pn_con_last_name = $Row4[12];
		$wk_pn_first_name = $Row4[13];
		$wk_pn_last_name = $Row4[14];
		$wk_pn_contact = $wk_pn_con_first_name . $wk_pn_con_last_name;
		if (strlen($wk_pn_contact) > 50)
		{
			$wk_pn_contact = substr($wk_pn_contact,0,50);
		}
	}
	//release memory
	ibase_free_result($Result4);

	$Query4 = "update pick_order set wh_id = '" . $wk_wh . "', ";
	$Query4 .= " company_id = '" . $wk_company . "', ";
	$Query4 .= " person_id = '" . $wk_person . "', ";
	$Query4 .= " p_person_id = '" . $wk_person . "', ";
	$Query4 .= " p_address_line1 = '" . $wk_pn_addr1 . "', ";
	$Query4 .= " p_address_line2 = '" . $wk_pn_addr2 . "', ";
	$Query4 .= " p_address_line3 = '" . $wk_pn_addr3 . "', ";
	$Query4 .= " p_address_line4 = '" . $wk_pn_addr4 . "', ";
	$Query4 .= " p_address_line5 = '" . $wk_pn_addr5 . "', ";
	$Query4 .= " p_person_type = '" . $wk_pn_type . "', ";
	$Query4 .= " p_city = '" . $wk_pn_city . "', ";
	$Query4 .= " p_state = '" . $wk_pn_state . "', ";
	$Query4 .= " p_post_code = '" . $wk_pn_post_code . "', ";
	$Query4 .= " p_country = '" . $wk_pn_country . "', ";
	$Query4 .= " p_phone = '" . $wk_pn_phone . "', ";
	$Query4 .= " contact_name = '" . $wk_pn_contact . "', ";
	$Query4 .= " p_first_name = '" . $wk_pn_first_name . "', ";
	$Query4 .= " p_last_name = '" . $wk_pn_last_name . "', ";
	$Query4 .= " p_same_as_invoice_to = 'T' ";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Update Order!<BR>\n");
		exit();
	}
	
	//release memory
	ibase_free_result($Result4);

} /* end of dodata */

{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./Order_Menu.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif","N");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
	echo ("<TD>");
	echo("<FORM action=\"../util/dbquery.csv\" method=\"post\" name=history>\n");
	echo("<INPUT type=\"hidden\" name=\"Query\" ");  
	$wk_query = $Query ;
	echo("value=\"" . urlencode($wk_query) . "\"> ");  
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=Export&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Export"></INPUT>');
	echo("<INPUT type=\"hidden\" name=\"ExportAs\" value=\"CSV\"> ");  
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
?>
</html>
