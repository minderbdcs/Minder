<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

/*
use passed pick label no
	and company and warehouse that the user can get to
product comes from the pick_label_no passed
as they add issn's reduce the order qty by the issn qty involved
then the pick line order qty is zero - cancel the line 
then go to the RsvProdLine screen
check at end goes back to rsvprodline

the none selects for company and wh
	should not be the nulls
	but all the companys and wh's for the user
the select of available issns 
	should Not include the ones you've already added to the order

want supplier , rather than owner

In reserving - when issn is for more than the from line
	the added line is for the difference 
	currently just shifts up the order qty
	ie force to split on this issn

*/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.ord/TR/html4/framset.dtd">
<html>
<head>
<title>Add SSN Lines</title>
</head>
<body BGCOLOR="#0080FF">
<?php
function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);
	//$conn->connect($Host,'sysdba','masterkey','d:/asset.rf/database/wh.v39.gdb');
	//$conn->connect($Host,$User,$Password,'/data/asset.rf/wh.v39.gdb');
	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesLine',true);
	$pager->Render(7);

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

$wk_salesorder = getBDCScookie($Link, $tran_device, "SalesOrder");

$wk_company = "";
$wk_wh = "";
$wk_product = "";
$wk_ssn_type = "";
$wk_generic = "";
$wk_brand = "";

if (isset($_POST['pick_label_no'])) 
{
	$pick_label_no = $_POST["pick_label_no"];
}
if (isset($_GET['pick_label_no'])) 
{
	$pick_label_no = $_GET["pick_label_no"];
}
if (isset($pick_label_no))
{
	//echo " pick_label " . $pick_label_no;
	$Query = "select prod_id, pick_order_qty, pick_line_status from pick_item ";
	$Query .= " where pick_label_no = '" . $pick_label_no . "'";
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Pick Item table!<BR>\n");
		exit();
	}

	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_product = array($Row[0]);
		$wk_order_qty = $Row[1];
		$wk_status = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
}
if (isset($_POST['ssn_type'])) 
{
	$wk_ssn_type = $_POST["ssn_type"];
}
if (isset($_GET['ssn_type'])) 
{
	$wk_ssn_type = $_GET["ssn_type"];
}
if (isset($_POST['generic'])) 
{
	$wk_generic = $_POST["generic"];
}
if (isset($_GET['generic'])) 
{
	$wk_generic = $_GET["generic"];
}
/*
if (isset($_POST['product'])) 
{
	$wk_product = $_POST["product"];
}
if (isset($_GET['product'])) 
{
	$wk_product = $_GET["product"];
}
*/
if (isset($_POST['brand'])) 
{
	$wk_brand = $_POST["brand"];
}
if (isset($_GET['brand'])) 
{
	$wk_brand = $_GET["brand"];
}

if (isset($_POST['salesline'])) 
{
	$wk_reserveme = array($_POST["salesline"]);
}
if (isset($_GET['salesline'])) 
{
	$wk_reserveme = array($_GET["salesline"]);
}
//echo "product:";
//print_r($wk_product);
//print_r($_POST['product']);

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

if (isset($wk_reserveme))
{
	$wk_added_ssn = "F";
	//echo json_encode($wk_reserveme);
	//print_r($wk_reserveme);
	$wk_new_qty = $wk_order_qty;
	foreach ($wk_reserveme as $Key_issn => $Value_issn)
	{
		//echo $Value_issn;
		if ($Value_issn <> "")
		{
			//echo "reserveme key " . $Key_issn;
			//echo " reserveme value " . $Value_issn;
			//print_r($Value_issn);
			// add a pick_item for this issn
			// use all the qty 
			$wk_current_qty = 0;
			$Query = "SELECT current_qty from issn where ssn_id = '" . $Value_issn . "' ";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query Issn table!<BR>\n");
				exit();
			}
	
			// Fetch the results from the database.
			if (($Row = ibase_fetch_row($Result)) )
			{
				$wk_current_qty = $Row[0];
			}
			//release memory
			ibase_free_result($Result);
			if ($wk_current_qty >  $wk_new_qty)
			{
				// can only use wk_new_qty of this current_qty
				$wk_use_qty = $wk_new_qty;
			}
			else
			{
				// can use all of this current_qty
				$wk_use_qty = $wk_current_qty;
			}

			$Query = "EXECUTE PROCEDURE ADD_PICK_SSN_ITEMS('";
			$Query .= $wk_salesorder . "','T','T','";
			$Query .= $Value_issn . "','','";
			//$Query .= $wk_current_qty ;
			$Query .= $wk_use_qty ;
			$Query .= "','NOW','";
			//$Query .= $tran_user . "')";
			$Query .= $tran_user . "','0')";
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Add Item to Pick Items table!<BR>\n");
				exit();
			}
			$wk_added_ssn = "T";
			// update parent item with reduced qty
			$wk_new_qty = $wk_new_qty - $wk_current_qty;
	
		}
	}
	if ($wk_added_ssn == "T")
	{
		$Query = "update pick_order set pick_status = 'UC' ";
		$Query .= " where pick_order = '" . $wk_salesorder . "' ";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Order table!<BR>\n");
			exit();
		}
		if ($wk_new_qty <= 0)
		{
			$wk_new_status = 'CN';
			$wk_new_qty = 0;
		}
		else
		{
			$wk_new_status = $wk_status;
		}
		$Query = "update pick_item set pick_line_status = '" . $wk_new_status . "'" ;
		$Query .= " , pick_order_qty = '" . $wk_new_qty . "'" ;
		$Query .= " where pick_label_no = '" . $pick_label_no . "' ";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Item table!<BR>\n");
			exit();
		}

		//commit
		ibase_commit($dbTran);
		$wk_status = $wk_new_status;
		$wk_order_qty = $wk_new_qty;
		// go to get the to location
		if ($wk_new_qty == 0)
		{
			echo("<FORM action=\"RsvProdLine.php\" method=\"post\" name=getback>\n");
			echo("<INPUT type=\"submit\" value=\"submit\">\n");  
			echo("</FORM>");
			echo("<script type=\"text/javascript\">\n");
			echo("document.getback.submit();\n");
			//header("Location: RsvProdLine.php");
			echo("</script>\n");
			exit;
		}

	}
}
?>
<script type="text/javascript">
function saveMe(myissn) {
/* # save my line */
  	document.getissn.salesline.value = myissn; 
  	document.getissn.submit(); 
	return true;
}
</script>
<?php
/*
want to use the users rights - not those for the order
{
	$Query = "SELECT company_id, wh_id from pick_order where pick_order = '" . $wk_salesorder . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Order table!<BR>\n");
		exit();
	}
	
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_company = $Row[0];
		$wk_wh = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
}
*/

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

if ($wk_company == "")
{
	if (($wk_inventoryop <> "T") and ($wk_sysuser <> "T"))
	{
		// try to default this
		if ($wk_usercomp == "")
		{
			// no user company - try the system
			$wk_company = array($wk_syscomp);
		}
		else
		{
			$wk_company = array($wk_usercomp);
		}
	}
	else
	{
		$wk_company = array("");
	}

}

if ($wk_wh == "")
{
	// no wh - try the current one
	$wk_wh = array($wk_default_wh);
}

if ($wk_ssn_type == "")
{
	$wk_ssn_type = array("");
}

if ($wk_generic == "")
{
	$wk_generic = array("");
}

if ($wk_brand == "")
{
	$wk_brand = array("");
}

if ($wk_product == "")
{
	$wk_product = array("");
}


{
	//$Query = "select code, substr(description,1,10) from ssn_type  order by code ";
	$Query = "select code, substr(description,1,10) from ssn_type  order by description ";
}
//echo($Query);

{
	//$Query2 = "select code, substr(description,1,10) from generic  order by code ";
	$Query2 = "select code, substr(description,1,10) from generic  order by description ";
}

//$Query3 = "select prod_id, substr(short_desc,1,40) from prod_profile  order by short_desc ";

//$Query4 = "select code, substr(description,1,10) from brand  order by code ";
$Query4 = "select code, substr(description,1,10) from brand  order by description ";

if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
{
	/* if im sysadmin then can access  all companys */
	//$Query = "select company_id, name from company  order by company_id ";
	$Query5 = "select company_id, name from company  order by name ";
}
else
{
	/* if im not sysadmin then can only access companys in access company */
	//$Query = "select company_id, name from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') order by company_id ";
	$Query5 = "select company_id, name from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') order by name ";
}
//echo($Query);

if ($wk_sysuser == "T") 
{
	/* if im sysadmin then can access  all warehouses */
	$Query6 = "select wh_id, description from warehouse  order by wh_id ";
}
else
{
	/* if im not sysadmin then can only access companys in access user */
	$Query6 = "select wh_id, description from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') order by wh_id ";
}
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get SSN_TYPE for Choice!<BR>\n");
	exit();
}
if (!($Result2 = ibase_query($Link, $Query2)))
{
	echo("Unable to Get Generics for Choice!<BR>\n");
	exit();
}
/*
if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Get Products for Choice!<BR>\n");
	exit();
}
*/

if (!($Result4 = ibase_query($Link, $Query4)))
{
	echo("Unable to Get Brands for Choice!<BR>\n");
	exit();
}

if (!($Result5 = ibase_query($Link, $Query5)))
{
	echo("Unable to Get Companys for Choice!<BR>\n");
	exit();
}

if (!($Result6 = ibase_query($Link, $Query6)))
{
	echo("Unable to Get Warehouses for Choice!<BR>\n");
	exit();
}

echo("<h3>Shipping Line Add an SSN for a Product</h3>\n");
echo("<FORM action=\"RsvSSNLine.php\" method=\"post\" name=getissn\n>");
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
echo ("<TR>\n");
echo ("<TD>\n");
echo("Label\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"pick_label_no\" value=\"$pick_label_no\" readonly > ");  
echo ("</TD>\n");
echo ("<TD>\n");
echo("Product\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seenproduct\" value=\"$wk_product[0]\" readonly > ");  
echo ("</TD>\n");
echo ("<TD>\n");
echo("Required Qty\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seenqty\" value=\"$wk_order_qty\" readonly > ");  
echo ("</TD>\n");
echo ("<TD>\n");
echo("Status\n");
echo ("</TD>\n");
echo ("<TD>\n");
echo("<INPUT type=\"text\" name=\"seenstatus\" value=\"$wk_status\" readonly > ");  
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE width=\"80%\">\n");
echo ("<COLGROUP>\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"20%\">\n");
echo ("<COL width=\"5%\">\n");
echo ("</COLGROUP>\n");
echo ("<TR>\n");
echo ("<TH>\n");
echo("Type\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("Generic\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("Brand\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("Company\n");
echo ("</TH>\n");
echo ("<TH>\n");
echo("WH\n");
echo ("</TH>\n");
echo ("</TR>\n");
echo ("<TR>\n");
echo ("<TD>\n");
echo("<SELECT multiple name=\"ssn_type[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	//if ($ssn_type == $Row[0])
	if (in_array($Row[0],$wk_ssn_type))
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
echo("<SELECT multiple name=\"generic[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");

while ( ($Row2 = ibase_fetch_row($Result2)) ) {
	//if ($wk_wh == $Row2[0])
	if (in_array($Row2[0],$wk_generic))
	{
		//echo( "<OPTION value=\"$Row2[0]\" selected>$Row2[0] $Row2[1]\n");
		echo( "<OPTION value=\"$Row2[0]\" selected>$Row2[1]\n");
	}
	else
	{
		//echo( "<OPTION value=\"$Row2[0]\">$Row2[0] $Row2[1]\n");
		echo( "<OPTION value=\"$Row2[0]\">$Row2[1]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");

echo ("<TD>\n");
echo("<SELECT multiple name=\"brand[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");

while ( ($Row4 = ibase_fetch_row($Result4)) ) {
	//if ($wk_brand == $Row2[0])
	if (in_array($Row4[0],$wk_brand))
	{
		//echo( "<OPTION value=\"$Row4[0]\" selected>$Row4[0] $Row4[1]\n");
		echo( "<OPTION value=\"$Row4[0]\" selected>$Row4[1]\n");
	}
	else
	{
		//echo( "<OPTION value=\"$Row4[0]\">$Row4[0] $Row4[1]\n");
		echo( "<OPTION value=\"$Row4[0]\">$Row4[1]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");

echo ("<TD>\n");
echo("<SELECT multiple name=\"company[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");
echo( "</OPTION>\n");

while ( ($Row5 = ibase_fetch_row($Result5)) ) {
	//if ($wk_company == $Row5[0])
	if (in_array($Row5[0],$wk_company))
	{
		//echo( "<OPTION value=\"$Row5[0]\" selected>$Row5[0] $Row5[1]\n");
		echo( "<OPTION value=\"$Row5[0]\" selected>$Row5[1]\n");
	}
	else
	{
		//echo( "<OPTION value=\"$Row5[0]\">$Row5[0] $Row5[1]\n");
		echo( "<OPTION value=\"$Row5[0]\">$Row5[1]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");

echo ("<TD>\n");
echo("<SELECT multiple name=\"wh[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");
echo( "</OPTION>\n");

while ( ($Row6 = ibase_fetch_row($Result6)) ) {
	//if ($wk_wh == $Row6[0])
	if (in_array($Row6[0],$wk_wh))
	{
		echo( "<OPTION value=\"$Row6[0]\" selected>$Row6[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row6[0]\">$Row6[0]\n");
	}
	echo( "</OPTION>\n");
}
echo("</SELECT>\n");
echo ("</TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE>\n");
echo ("<TR>\n");

echo("<TH>Select Type  Generic Brand Company and WH </TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

echo ("<input type=\"hidden\" name=\"dodata\" value=\"1\">\n");

if ($wk_dodata == "1")
{
	$Query2 = "select description, label from query_layout where code = 'mssn' order by sequence";
	
	if (!($Result3 = ibase_query($Link, $Query2)))
	{
		echo("Unable to query layout!<BR>\n");
		exit();
	}
	
	$rcount = 0;
	$Query = "select first 5 ";
	$Query = "select ";
	while (($Row = ibase_fetch_row($Result3))) {
		$fields[$rcount] = $Row[1];
	 	//$Query .= $Row[0] . ",";
 		$Query .= $Row[0] . ' as ' . $Row[1] . ',';
		$rcount++;
	}
	
	ibase_free_result($Result3);
	
	//echo("[$Query]\n");
	$Query = substr($Query,0,strlen($Query) - 1);
	$Query .= " from issn join ssn on issn.original_ssn = ssn.ssn_id";
	$Query .= " left outer join prod_profile on issn.prod_id = prod_profile.prod_id";
	$Query .= " left outer join grn on ssn.grn = grn.grn";
	//$Query .= " where issn.ssn_id = '".$ssn_id."'";
	
	// the where  
	
	$wk_where = " where ";
	$wk_wh_in = " issn.wh_id in (";
	$wk_product_in = " issn.prod_id in (";
	$wk_company_in = " issn.company_id in (";
	$wk_type_in = " ssn.ssn_type in (";
	$wk_generic_in = " ssn.generic in (";
	$wk_brand_in = " ssn.brand in (";
	
	$wk_have_wh = "N";
	$wk_wh_none = "N";
	foreach ($wk_wh as $Key_wh => $Value_wh)
	{
		//echo $Value_wh;
		if ($Value_wh <> "")
		{
			$wk_wh_in .=  "'" . $Value_wh . "',";
			$wk_have_wh = "Y";
		}
		else
		{
			$wk_wh_none = "Y";
		}
	}
	$wk_wh_in .= "'None')";
	if ($wk_have_wh == "N")
	{
		$wk_wh_in = "";
	}
	$wk_have_company = "N";
	$wk_company_none = "N";
	foreach ($wk_company as $Key_company => $Value_company)
	{
		//echo $Value_company;
		if ($Value_company <> "")
		{
			$wk_company_in .=  "'" . $Value_company . "',";
			$wk_have_company = "Y";
		}
		else
		{
			$wk_company_none = "Y";
		}
	}
	$wk_company_in .= "'None')";
	if ($wk_have_company == "N")
	{
		$wk_company_in = "";
	}
	$wk_have_prod = "N";
	foreach ($wk_product as $Key_product => $Value_product)
	{
		//echo $Value_product;
		if ($Value_product <> "")
		{
			$wk_product_in .= "'" . $Value_product . "',";
			$wk_have_prod = "Y";
		}
	}
	$wk_product_in .= "'None')";
	if ($wk_have_prod == "N")
	{
		$wk_product_in = "";
	}
	
	$wk_have_type = "N";
	foreach ($wk_ssn_type as $Key_code => $Value_desc)
	{
		//echo $Value_desc;
		if ($Value_desc <> "")
		{
			$wk_type_in .= "'" . $Value_desc . "',";
			$wk_have_type = "Y";
		}
	}
	$wk_type_in .=  "'None')";
	if ($wk_have_type == "N")
	{
		$wk_type_in = "";
	}
	
	$wk_have_generic = "N";
	foreach ($wk_generic as $Key_code => $Value_desc)
	{
		//echo $Value_desc;
		if ($Value_desc <> "")
		{
			$wk_generic_in .= "'" . $Value_desc . "',";
			$wk_have_generic = "Y";
		}
	}
	$wk_generic_in .=  "'None')";
	if ($wk_have_generic == "N")
	{
		$wk_generic_in = "";
	}
	
	$wk_have_brand = "N";
	foreach ($wk_brand as $Key_code => $Value_desc)
	{
		//echo $Value_gene;
		if ($Value_desc <> "")
		{
			$wk_brand_in .= "'" . $Value_desc . "',";
			$wk_have_brand = "Y";
		}
	}
	$wk_brand_in .=  "'None')";
	if ($wk_have_brand == "N")
	{
		$wk_brand_in = "";
	}
	
	//$Query .= $wk_where . $wk_wh_in . ' and ' . $wk_company_in ;
	$Query .= $wk_where  ;
	$Query .= " issn.current_qty > 0 and issn.pick_order is null ";
	$Query .= " and issn.ssn_id not in (select ssn_id from pick_item where pick_order = '" . $wk_salesorder . "' and pick_item.ssn_id is not null ) ";
	if ($wk_company_in <> "")
	{
		if ($wk_wh_none == "Y")
		{
			$Query .= ' and (' . $wk_company_in . ' or issn.company_id is null)';
		}
		else
		{
			$Query .= ' and ' . $wk_company_in;
		}
	}
	else
	{
		if ($wk_company_none == "Y")
		{
			$Query .= ' and ( issn.company_id is null)';
		}
	}
	
	if ($wk_wh_in <> "")
	{
		if ($wk_wh_none == "Y")
		{
			$Query .= ' and (' . $wk_wh_in . ' or issn.wh_id is null)';
		}
		else
		{
			$Query .= ' and ' . $wk_wh_in;
		}
	}
	else
	{
		if ($wk_wh_none == "Y")
		{
			$Query .= ' and ( issn.wh_id is null)';
		}
	}


	if ($wk_product_in <> "")
	{
		$Query .= ' and ' . $wk_product_in;
	}
	if ($wk_type_in <> "")
	{
		$Query .= ' and ' . $wk_type_in;
	}
	if ($wk_generic_in <> "")
	{
		$Query .= ' and ' . $wk_generic_in;
	}
	if ($wk_brand_in <> "")
	{
		$Query .= ' and ' . $wk_brand_in;
	}
        $Query .= " AND (POS('" . $wk_import_status . "',ISSN.ISSN_STATUS,0,1) > -1) ";
	
	//echo $Query;
	//echo "query len " . strlen($Query);
	$Query1 = str_replace("'","~",$Query);
	
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
	
	//echo $Query;
	 setBDCScookie($Link, $tran_device, "msaleslineq1", $wk_query1);
	 setBDCScookie($Link, $tran_device, "msaleslineq2", $wk_query2);
	 setBDCScookie($Link, $tran_device, "msaleslineq3", $wk_query3);
	 setBDCScookie($Link, $tran_device, "msaleslineq4", $wk_query7);
	$Query1 = $wk_query1 . $wk_query2 . $wk_query3 . $wk_query7;
	$Query = str_replace("~","'",$Query1);
	
	dopager($Query);
	
} /* end of dodata */
else
{
	if (isset($_GET['SalesLine_next_page']))
	{
 		$wk_query1 = getBDCScookie($Link, $tran_device, "msaleslineq1");
 		$wk_query2 = getBDCScookie($Link, $tran_device, "msaleslineq2");
 		$wk_query3 = getBDCScookie($Link, $tran_device, "msaleslineq3");
 		$wk_query7 = getBDCScookie($Link, $tran_device, "msaleslineq4");
		$Query1 = $wk_query1 . $wk_query2 . $wk_query3 . $wk_query7;
		$Query = str_replace("~","'",$Query1);
		//echo $Query;
		if ($Query <> "")
			dopager($Query);
	}
}

{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./RsvProdLine.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif","N");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
	echo ("<TD>");
echo("<INPUT type=\"hidden\" name=\"salesline\"> ");  
	//echo("<FORM action=\"./Order_Menu.php\" method=\"post\" name=ShowLines>\n");
	echo("<FORM action=\"./ShowLine.php\" method=\"post\" name=Order>\n");
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
