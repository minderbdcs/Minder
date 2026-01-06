<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.ord/TR/html4/framset.dtd">');
echo("\n<html>\n");
echo("<head>\n");
echo("<title>Shipping Order - Set Customer</title>\n");
echo("</head>\n");
echo("<body BGCOLOR=\"#0080FF\">\n");
function add_option (&$wk_output, $wk_select, $wk_desc)
{
	if (strlen($wk_desc) > 45)
	{
		$wk_desc = substr($wk_desc,0,45);
	}
	//echo( "<OPTION value=\"$wk_select\">$wk_desc\n");
	$wk_output .=  "<OPTION value=\"$wk_select\">$wk_desc\n";
	//echo( "</OPTION>\n");
	$wk_output .=  "</OPTION>\n";
}

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// what fields have been saved

$wk_person = "";
$wk_supplier = "";
$wk_company = "";
$wk_wh = "";

//phpinfo();
if (isset($_POST['salesorder'])) 
{
	$wk_salesorder = $_POST["salesorder"];
	//echo " got post salesorder  " . $wk_salesorder;
}
if (isset($_GET['salesorder'])) 
{
	$wk_salesorder = $_GET["salesorder"];
	//echo " got get salesorder  " . $wk_salesorder;
}
if (isset($wk_salesorder))
{
	setBDCScookie($Link, $tran_device, "SalesOrder", $wk_salesorder);
	//echo " set salesorder to " . $wk_salesorder;
}
else
{
	$wk_salesorder = getBDCScookie($Link, $tran_device, "SalesOrder");
	//echo " got salesorder  " . $wk_salesorder;
}
if (isset($_POST['cancelsalesorder'])) 
{
	$wk_cancelsalesorder = $_POST["cancelsalesorder"];
}
if (isset($_GET['cancelsalesorder'])) 
{
	$wk_cancelsalesorder = $_GET["cancelsalesorder"];
}
if (isset($_POST['confirmorder'])) 
{
	$wk_confirmorder = $_POST["confirmorder"];
}
if (isset($_GET['confirmorder'])) 
{
	$wk_confirmorder = $_GET["confirmorder"];
}
if (isset($_POST['approvepickorder'])) 
{
	$wk_approvepickorder = $_POST["approvepickorder"];
}
if (isset($_GET['approvepickorder'])) 
{
	$wk_approvepickorder = $_GET["approvepickorder"];
}
if (isset($_POST['approvedespatchorder'])) 
{
	$wk_approvedespatchorder = $_POST["approvedespatchorder"];
}
if (isset($_GET['approvedespatchorder'])) 
{
	$wk_approvedespatchorder = $_GET["approvedespatchorder"];
}
if (isset($_POST['person'])) 
{
	$wk_person = $_POST["person"];
}
if (isset($_GET['person'])) 
{
	$wk_person = $_GET["person"];
}
if (isset($_POST['supplier'])) 
{
	$wk_supplier = $_POST["supplier"];
}
if (isset($_GET['supplier'])) 
{
	$wk_supplier = $_GET["supplier"];
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

$wk_po_wh = "";
$wk_po_company = "";
$wk_po_person = "";
$wk_po_status = "";
$wk_po_status_desc = "";
{
	$Query4 = "SELECT wh_id, company_id, person_id, pick_status, supplier_id from pick_order";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table Pick Order!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_po_wh = $Row4[0];
		$wk_po_company = $Row4[1];
		$wk_po_person = $Row4[2];
		$wk_po_status = $Row4[3];
		$wk_po_supplier = $Row4[4];
	}
	//release memory
	ibase_free_result($Result4);
}

if (isset($wk_cancelsalesorder))
{
	// cancel this line
	$Value_issn = $wk_cancelsalesorder;
	{
		//echo $Value_issn;
		//if ($Value_issn <> "")
		{
			// get a reason to use
			$Value_reason = "";
			$Query = "SELECT first 1 description from options where group_code = 'CANORDER' ";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query Options table!<BR>\n");
				exit();
			}
			// Fetch the results from the database.
			if (($Row = ibase_fetch_row($Result)) )
			{
				$Value_reason = $Row[0];
			}
			//release memory
			ibase_free_result($Result);
			$Query = "EXECUTE PROCEDURE CANCEL_SALE_ORDER ('";
			$Query .= $Value_issn . "','";
			$Query .= $Value_reason . "')";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Cancel this Order!<BR>\n");
				exit();
			}
			//commit
			ibase_commit($dbTran);
			$Pager_Commit = 'T';
		}
	}
}

if (isset($wk_confirmorder))
{
	// confirm this order
	$Value_issn = $wk_confirmorder;
	if (($wk_po_status == "UC") or ($wk_po_status == ""))  
	{
		//echo $Value_issn;
		//if ($Value_issn <> "")
		{
			// get a reason to use
			$Value_reason = "";
			$Query = "select valid_so from UPDATE_SALE_ORDER_STATUS ('";
			$Query .= $Value_issn . "','RS','CF','";
			$Query .= $tran_user . "')";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Confirm this Order!<BR>\n");
				exit();
			}
			// if valid_so is 'F' then hilight the lines unconfirmed
			// Fetch the results from the database.
			if (($Row = ibase_fetch_row($Result)) )
			{
				$wk_so_status = $Row[0];
			}
			//release memory
			ibase_free_result($Result);
			//commit
			ibase_commit($dbTran);
		}
	}
	else
	{
		$message = "Cannot Confirm - Order Status not Unconfirmed";
	}
}

if (isset($wk_approvepickorder))
{
	// approve for picking this order
	$Value_issn = $wk_approvepickorder;
	if ($wk_po_status == "CF")   
	{
		//echo $Value_issn;
		//if ($Value_issn <> "")
		{
			// get a reason to use
			$Value_reason = "";
			$Query = "select valid_so from APPROVE_SALES_ORDER ('";
			$Query .= $Value_issn . "','RS','OP','";
			$Query .= $tran_user . "')";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Approve this Order for Picking !<BR>\n");
				exit();
			}
			// if valid_so is 'F' then hilight the lines unconfirmed
			// Fetch the results from the database.
			if (($Row = ibase_fetch_row($Result)) )
			{
				$wk_so_status = $Row[0];
			}
			//release memory
			ibase_free_result($Result);
			//commit
			ibase_commit($dbTran);
		}
	}
	else
	{
		$message = "Cannot Approve for Picking - Order Status not Confirmed";
	}
}

if (isset($wk_approvedespatchorder))
{
	// approve for picking this order
	$Value_issn = $wk_approvedespatchorder;
	if ($wk_po_status == "OP")   
	{
		//echo $Value_issn;
		//if ($Value_issn <> "")
		{
			// get a reason to use
			$Value_reason = "";
			$Query = "select valid_so from APPROVE_SALES_ORDER ('";
			$Query .= $Value_issn . "','RS','DA','";
			$Query .= $tran_user . "')";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Approve this Order for Despatch !<BR>\n");
				exit();
			}
			// if valid_so is 'F' then hilight the lines unconfirmed
			// Fetch the results from the database.
			if (($Row = ibase_fetch_row($Result)) )
			{
				$wk_so_status = $Row[0];
			}
			//release memory
			ibase_free_result($Result);
			//commit
			ibase_commit($dbTran);
		}
	}
	else
	{
		$message = "Cannot Approve for Despatch - Order Status not Approved for Picking";
	}
}

{
	$Query4 = "SELECT pick_status from pick_order";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table Pick Order - Status!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_po_status = $Row4[0];
	}
	//release memory
	ibase_free_result($Result4);
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
$wk_creditman = "";
$wk_salesman = "";
{
	$Query = "SELECT sys_admin, company_id, inventory_operator, sale_manager, credit_manager from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table User!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
		$wk_usercomp = $Row[1];
		$wk_inventoryop = $Row[2];
		$wk_salesman = $Row[3];
		$wk_creditman = $Row[4];
	}
	//release memory
	ibase_free_result($Result);
}

echo("<script type=\"text/javascript\">\n");
echo("var salesman = \"" . $wk_salesman . "\";\n");
echo("var creditman = \"" . $wk_creditman . "\";\n");
echo("var sysman = \"" . $wk_sysuser . "\";\n");
?>
function checkSalesman() {
	// if im not a sales or credit manager - not allowed
	if ((salesman == "T") or (creditman == "T") or (sysman == "T"))
	{
		//alert ("a salesman");
		return true;
	}
	else
	{
		alert ("Only a Sales Manager Can Run This");
		return false;
	}
}
function notMe() {
	var nothere="Not Available yet";
	alert("in notMe");
	alert(nothere);
	return false;
}
</script>
<?php

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
	//$Query = "select company_id, name from company  order by company_id ";
	$Query = "select company_id, name from company  order by name ";
}
else
{
	/* if im not sysadmin then can only access companys in access company */
	//$Query = "select company_id, name from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') order by company_id ";
	$Query = "select company_id, name from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') order by name ";
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

//$Query3 = "select person_id, first_name, last_name, address_line1 from person where person_type starting 'C' and person_type <> 'CA'  order by first_name, person_id ";
$Query3 = "select person_id, first_name, last_name, address_line1 from person where person_type starting 'C' and person_type <> 'CA'  order by first_name, last_name ";
$Query5 = "select person_id, first_name, last_name, address_line1 from person where (person_type starting 'C' and person_type <> 'CA') or (person_type = 'LE')  order by first_name, last_name ";

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
if (!($Result5 = ibase_query($Link, $Query5)))
{
	echo("Unable to Get Suppliers for Choice!<BR>\n");
	exit();
}

//echo($Query);


{
	$Query4 = "SELECT description  from options";
	$Query4 .= " where group_code = 'PICK_STATU' and code = '" . $wk_po_status . "'";
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table Options!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_po_status_desc = $Row4[0];
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

if ($wk_po_supplier <> "")
{
	// default the supplier to the orders
	if ($wk_supplier == "")
	{
		$wk_supplier = $wk_po_supplier;
	}
}

//echo "comp " . $wk_company;
//echo "wh " . $wk_wh;
//echo "person " . $wk_person;

echo("<h2>Shipping Order - Get Customer</h2>\n");

if (isset($message))
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
echo ("<TABLE>\n");
echo ("<TR>\n");

echo ("<TD>");
echo("<FORM action=\"./GetOrdCust.php\" method=\"post\" name=CancelOrder>\n");
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\"  ");  
echo('SRC="/icons/whm/button.php?text=' . "Cancel+Order&fromimage=");
echo('Blank_Button_50x100.gif" alt="CancelOrder">');
echo("<INPUT type=\"hidden\" name=\"cancelsalesorder\" value=\"$wk_salesorder\" > ");  
echo("</FORM>");
echo ("</TD>");
echo ("<TD>");
echo("<FORM action=\"./CanceledLines.php\" method=\"post\" name=Canceledlines>\n");
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "See+Cancel'd+Lines&fromimage=");
echo('Blank_Button_50x100.gif" alt="CanceledLines">');
echo("</FORM>");
echo ("</TD>");
echo ("<TD>");
echo("<FORM action=\"./GetOrdCust.php\" method=\"post\" name=Confirmlines>\n");
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "Confirm&fromimage=");
echo('Blank_Button_50x100.gif" alt="ConfirmLines">');
echo("<INPUT type=\"hidden\" name=\"confirmorder\" value=\"$wk_salesorder\" > ");  
echo("</FORM>");
echo ("</TD>");
echo ("<TD>");
echo("<FORM action=\"./GetOrdCust.php\" method=\"post\" name=ApprovePick>\n");
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "Approve+Pick+Lines&fromimage=");
echo('Blank_Button_50x100.gif" alt="ApprovePick">');
echo("<INPUT type=\"hidden\" name=\"approvepickorder\" value=\"$wk_salesorder\" > ");  
echo("</FORM>");
echo ("</TD>");
echo ("<TD>");
echo("<FORM action=\"./GetOrdCust.php\" method=\"post\" name=\"ApproveDespatch\" onsubmit=\"return checkSalesman()\">\n");
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "Approve+Despatch&fromimage=");
echo('Blank_Button_50x100.gif" alt="ApproveDespatch">');
echo("<INPUT type=\"hidden\" name=\"approvedespatchorder\" value=\"$wk_salesorder\" > ");  
echo("</FORM>");
echo ("</TD>");
echo ("<TD>");
echo('<FORM action="GetOrdCust.php" method="post" name="Connote" onsubmit="return notMe();" >');
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "See+Connotes&fromimage=");
echo('Blank_Button_50x100.gif" alt="Connotes">');
echo("</FORM>\n");
echo ("</TD>\n");
echo ("<TD>");
echo('<FORM action="RsvProdLine.php" method="post" name="Reserve" onsubmit="return checkSalesman();" >');
//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo("<INPUT type=\"IMAGE\" ");  
echo('SRC="/icons/whm/button.php?text=' . "Reserve+Products&fromimage=");
echo('Blank_Button_50x100.gif" alt="Reserve">');
echo("</FORM>\n");
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

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
echo ("</TR>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("Status\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seenstatus\" value=\"$wk_po_status\" readonly > ");  
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seenstatusdesc\" value=\"$wk_po_status_desc\" readonly > ");  
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE >\n");
//echo ("<TABLE width=\"80%\">\n");
echo ("<TABLE >\n");
echo ("<COLGROUP>\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"5%\">\n");
echo ("<COL >\n");
echo ("<COL >\n");
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
echo ("<TH>\n");
echo("Postal\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("Delivery\n");
echo ("</TH>\n");
echo ("</TR>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("<SELECT name=\"company\" size=\"5\" onchange=\"document.getorder.submit()\" >\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (in_array($Row[0],$wk_company))
	if ($wk_company == $Row[0])
	{
		//echo( "<OPTION value=\"$Row[0]\" selected>$Row[0] $Row[1]\n");
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[1]\n");
	}
	else
	{
		//echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[1]\n");
		echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
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
	$wk_select = $Row3[1] . " " . $Row3[2];
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
	$Query4 .= "last_name, ";

	$Query4 .= "mail_address1, ";
	$Query4 .= "mail_address2, ";
	$Query4 .= "mail_city, ";
	$Query4 .= "mail_state, ";
	$Query4 .= "mail_post_code, ";
	$Query4 .= "mail_country, ";
	$Query4 .= "mail_phone ";
	$Query4 .= "FROM person ";

	$Query4 .= " where person_id = '" . $wk_person . "'";
	
	//echo $Query4;
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table Person!<BR>\n");
		exit();
	}
	
	// echo headers
	$wk_pn_addr1 = "";
	$wk_pn_addr2 = "";
	$wk_pn_addr3 = "";
	$wk_pn_addr4 = "";
	$wk_pn_addr5 = "";
	$wk_pn_type = "";
	$wk_pn_city = "";
	$wk_pn_state = "";
	$wk_pn_post_code = "";
	$wk_pn_country = "";
	$wk_pn_phone = "";
	$wk_pn_con_first_name = "";
	$wk_pn_con_last_name = "";
	$wk_pn_first_name = "";
	$wk_pn_last_name = "";
	$wk_pn_contact = $wk_pn_con_first_name . $wk_pn_con_last_name;
	$wk_pn_maddr1 = "";
	$wk_pn_maddr2 = "";
	$wk_pn_mcity = "";
	$wk_pn_mstate = "";
	$wk_pn_mpost_code = "";
	$wk_pn_mcountry = "";
	$wk_pn_mphone = "";
	$wk_pn_stype = "";
	$wk_pn_saddr1 = "";
	$wk_pn_saddr2 = "";
	$wk_pn_saddr3 = "";
	$wk_pn_saddr4 = "";
	$wk_pn_saddr5 = "";
	$wk_pn_scity = "";
	$wk_pn_sstate = "";
	$wk_pn_spost_code = "";
	$wk_pn_scountry = "";
	$wk_pn_sphone = "";
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
		$wk_pn_maddr1 = $Row4[15];
		$wk_pn_maddr2 = $Row4[16];
		$wk_pn_mcity = $Row4[17];
		$wk_pn_mstate = $Row4[18];
		$wk_pn_mpost_code = $Row4[19];
		$wk_pn_mcountry = $Row4[20];
		$wk_pn_mphone = $Row4[21];
	}
	//release memory
	ibase_free_result($Result4);

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
	$Query4 .= "phone_no ";
	$Query4 .= "FROM person ";

	$Query4 .= " where person_id = '" . $wk_supplier . "'";
	
	//echo $Query4;
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query table Person Supplier!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row4 = ibase_fetch_row($Result4)) )
	{
		$wk_pn_saddr1 = $Row4[0];
		$wk_pn_saddr2 = $Row4[1];
		$wk_pn_saddr3 = $Row4[2];
		$wk_pn_saddr4 = $Row4[3];
		$wk_pn_saddr5 = $Row4[4];
		$wk_pn_stype = $Row4[5];
		$wk_pn_scity = $Row4[6];
		$wk_pn_sstate = $Row4[7];
		$wk_pn_spost_code = $Row4[8];
		$wk_pn_scountry = $Row4[9];
		$wk_pn_sphone = $Row4[10];
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
	$Query4 .= " p_same_as_invoice_to = 'T', ";
	$Query4 .= " d_address_line1 = '" . $wk_pn_maddr1 . "', ";
	$Query4 .= " d_address_line2 = '" . $wk_pn_maddr2 . "', ";
	//$Query4 .= " d_person_type = '" . $wk_pn_mtype . "', ";
	$Query4 .= " d_city = '" . $wk_pn_mcity . "', ";
	$Query4 .= " d_state = '" . $wk_pn_mstate . "', ";
	$Query4 .= " d_post_code = '" . $wk_pn_mpost_code . "', ";
	$Query4 .= " d_country = '" . $wk_pn_mcountry . "', ";
	$Query4 .= " d_phone = '" . $wk_pn_mphone . "', ";

	$Query4 .= " supplier_id = '" . $wk_supplier . "', ";
	$Query4 .= " s_person_id = '" . $wk_supplier . "', ";
	$Query4 .= " s_address_line1 = '" . $wk_pn_saddr1 . "', ";
	$Query4 .= " s_address_line2 = '" . $wk_pn_saddr2 . "', ";
	$Query4 .= " s_address_line3 = '" . $wk_pn_saddr3 . "', ";
	$Query4 .= " s_address_line4 = '" . $wk_pn_saddr4 . "', ";
	$Query4 .= " s_address_line5 = '" . $wk_pn_saddr5 . "', ";
	$Query4 .= " s_person_type = '" . $wk_pn_stype . "', ";
	$Query4 .= " s_city = '" . $wk_pn_scity . "', ";
	$Query4 .= " s_state = '" . $wk_pn_sstate . "', ";
	$Query4 .= " s_post_code = '" . $wk_pn_spost_code . "', ";
	$Query4 .= " s_country = '" . $wk_pn_scountry . "', ";
	$Query4 .= " s_phone = '" . $wk_pn_sphone . "' ";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	//echo $Query4;
	
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Update Order!<BR>\n");
		exit();
	}
	
	//release memory
	//ibase_free_result($Result4);

} /* end of dodata */

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
	$Query4 .= " p_same_as_invoice_to, ";
	$Query4 .= " d_address_line1 , ";
	$Query4 .= " d_address_line2 , ";
	$Query4 .= " d_city , ";
	$Query4 .= " d_state , ";
	$Query4 .= " d_post_code , ";
	$Query4 .= " d_country,  ";
	$Query4 .= " d_phone , ";
	$Query4 .= " s_address_line1 , ";
	$Query4 .= " s_address_line2 , ";
	$Query4 .= " s_city , ";
	$Query4 .= " s_state , ";
	$Query4 .= " s_post_code , ";
	$Query4 .= " s_country, ";
	$Query4 .= " s_phone  ";
	$Query4 .= " from pick_order ";
	$Query4 .= " where pick_order = '" . $wk_salesorder . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query Order!<BR>\n");
		exit();
	}
}
$wk_p_select = "<SELECT name=\"paddress\" size=\"5\" >\n";
$wk_d_select = "<SELECT name=\"daddress\" size=\"5\" >\n";
$wk_s_select = "<SELECT name=\"saddress\" size=\"5\" >\n";

while ( ($Row4 = ibase_fetch_row($Result4)) ) {
	add_option($wk_p_select, $wk_salesorder, $Row4[10]. " ph " . $Row4[9]); // phone
	//add_option($wk_salesorder, $Row4[11] . $Row4[12]); // name
	if ($Row4[0] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[0]); // line1
	if ($Row4[1] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[1]); // line2
	if ($Row4[2] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[2]); // line3
	if ($Row4[3] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[3]); // line4
	if ($Row4[4] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[4]); // line5
	if ($Row4[5] <> "" or $Row4[6] <> "" or $Row4[7]<> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[5] . "  " . $Row4[6] . " " . $Row4[7]);
	if ($Row4[8] <> "")
		add_option($wk_p_select, $wk_salesorder, $Row4[8]); // country
	add_option($wk_d_select, $wk_salesorder, $Row4[20]. " ph " . $Row4[20]); // phone
	if ($Row4[14] <> "")
		add_option($wk_d_select, $wk_salesorder, $Row4[14]); // line1
	if ($Row4[15] <> "")
		add_option($wk_d_select, $wk_salesorder, $Row4[15]); // line2
	if ($Row4[16] <> "" or $Row4[17] <> "" or $Row4[18]<> "")
		add_option($wk_d_select, $wk_salesorder, $Row4[16] . "  " . $Row4[17] . " " . $Row4[18]);
	if ($Row4[19] <> "")
		add_option($wk_d_select, $wk_salesorder, $Row4[19]); // country
	add_option($wk_s_select, $wk_salesorder, $Row4[20]. " ph " . $Row4[27]); // phone
	if ($Row4[21] <> "")
		add_option($wk_s_select, $wk_salesorder, $Row4[21]); // line1
	if ($Row4[22] <> "")
		add_option($wk_s_select, $wk_salesorder, $Row4[22]); // line2
	if ($Row4[23] <> "" or $Row4[24] <> "" or $Row4[25]<> "")
		add_option($wk_s_select, $wk_salesorder, $Row4[23] . "  " . $Row4[24] . " " . $Row4[25]);
	if ($Row4[26] <> "")
		add_option($wk_s_select, $wk_salesorder, $Row4[26]); // country
}
$wk_p_select .= "</SELECT>\n";
$wk_d_select .= "</SELECT>\n";
$wk_s_select .= "</SELECT>\n";
echo $wk_p_select;
echo ("</TD>\n");
echo ("<TD>\n");
echo $wk_d_select;
echo ("</TD>\n");
echo ("</TR>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo ("<b>Supplier</b>");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<SELECT name=\"supplier\" size=\"5\" onchange=\"document.getorder.submit()\" >\n");

while ( ($Row5 = ibase_fetch_row($Result5)) ) {
	$wk_select = $Row5[1] . " " . $Row5[2];
	if (strlen($wk_select) > 45)
	{
		$wk_select = substr($wk_select,0,45);
	}
	if ($wk_supplier == $Row5[0])
	{
		echo( "<OPTION value=\"$Row5[0]\" selected>$wk_select\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row5[0]\">$wk_select\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo $wk_s_select;
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE>\n");
echo ("<TR>\n");

echo("<TH>Select Customer</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<input type=\"hidden\" name=\"dodata\" value=\"1\">\n");

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
	//echo("<FORM action=\"./AddLine.php\" method=\"post\" name=lines>\n");
	echo("<FORM action=\"./ShowLine.php\" method=\"post\" name=lines>\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/button.php?text=Continue&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Lines">');
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
</body>
</html>
