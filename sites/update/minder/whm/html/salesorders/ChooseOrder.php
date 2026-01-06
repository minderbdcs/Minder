<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

// 18/09/07 must be able to see orders that don't have a company or wh_id !!!

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
	//echo "Host:" . $Host;
	//echo "User:" . $User;
	//echo "Password:" . $Password;
	//echo "DB:" . $mydb;

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	$pager->Render(7);

}

?>

<script type="text/javascript">
function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getperson.salesorder.value = myorder; 
  	document.getperson.submit(); 
	return true;
}
</script>

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

$wk_person = "";
$wk_company = "";
$wk_wh = "";

//phpinfo();
if (isset($_POST['person'])) 
{
	$wk_person = $_POST["person"];
}
if (isset($_GET['person'])) 
{
	$wk_person = $_GET["person"];
}
//echo "person:";
//print_r($wk_person);
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

$wk_order_type = getBDCScookie($Link, $tran_device, "OrderType");
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

if ($wk_person == "")
{
	$wk_person = array("");
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


echo(" <body BGCOLOR=\"#0080FF\">\n");
echo("<h2>Choose Shipping Orders</h2>\n");
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"ChooseOrder.php\" method=\"post\" name=getorder\n>");

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
echo("<SELECT multiple name=\"company[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");
echo( "</OPTION>\n");

while ( ($Row = ibase_fetch_row($Result)) ) {
	//if ($wk_company == $Row[0])
	if (in_array($Row[0],$wk_company))
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
echo("<SELECT multiple name=\"wh[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");
echo( "</OPTION>\n");

while ( ($Row2 = ibase_fetch_row($Result2)) ) {
	//if ($wk_wh == $Row2[0])
	if (in_array($Row2[0],$wk_wh))
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
echo("<SELECT multiple name=\"person[]\" size=\"5\" >\n");
echo( "<OPTION value=\"\">None\n");
echo( "</OPTION>\n");

while ( ($Row3 = ibase_fetch_row($Result3)) ) {
	$wk_select = $Row3[0] . " " . $Row3[1] . " " . $Row3[2];
	$wk_select = $Row3[1] . " " . $Row3[2];
	if (strlen($wk_select) > 45)
	{
		$wk_select = substr($wk_select,0,45);
	}
	//if ($wk_person == $Row3[0])
	if (in_array($Row3[0],$wk_person))
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

$Query2 = "select description, label from query_layout where code = 'msales' order by sequence";

if (!($Result3 = ibase_query($Link, $Query2)))
{
	echo("Unable to query layout!<BR>\n");
	exit();
}

$rcount = 0;
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
$Query .= " from pick_order left outer join person on pick_order.person_id = person.person_id";
//$Query .= " where issn.ssn_id = '".$ssn_id."'";

// the where  

$wk_where = " where ";
$wk_company_in = " pick_order.company_id in (";
$wk_wh_in = " pick_order.wh_id in (";
$wk_person_in = " pick_order.person_id in (";

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
$wk_have_person = "N";
foreach ($wk_person as $Key_person => $Value_person)
{
	//echo $Value_person;
	if ($Value_person <> "")
	{
		$wk_person_in .=  "'" . $Value_person . "',";
		$wk_have_person = "Y";
	}
}
$wk_person_in .=  "'None')";
/*
$wk_have_prod = "N";
foreach ($wk_product as $Key_product => $Value_product)
{
	//echo $Value_product;
	$wk_product_in = $wk_product_in . "'" . $Value_product . "',";
	$wk_have_prod = "Y";
}
$wk_product_in = $wk_product_in . "'None')";
*/
if ($wk_have_person == "N")
{
	$wk_person_in = "";
}
/*
if ($wk_have_prod == "N")
{
	$wk_product_in = "";
}
else
{
	$wk_product_in .= " and ";
}

$wk_product_in .= " issn.current_qty > 0 ";
*/
$wk_where  .= " pick_order.pick_order_type = '" . $wk_order_type . "' ";
//$Query .= $wk_where . $wk_wh_in . ' and ' . $wk_person_in . ' and ' . $wk_product_in;
//$Query .= $wk_where .  $wk_company_in . " and " . $wk_wh_in . " and " .  $wk_person_in  ;
$Query .= $wk_where ;  
if ($wk_company_in <> "")
{
	if ($wk_wh_none == "Y")
	{
		$Query .= ' and (' . $wk_company_in . ' or pick_order.company_id is null)';
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
		$Query .= ' and ( pick_order.company_id is null)';
	}
}

if ($wk_wh_in <> "")
{
	if ($wk_wh_none == "Y")
	{
		$Query .= ' and (' . $wk_wh_in . ' or pick_order.wh_id is null)';
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
		$Query .= ' and ( pick_order.wh_id is null)';
	}
}


if ($wk_person_in <> "")
{
	$Query .= ' and ' . $wk_person_in;
}
$Query .= " order by pick_order.create_date desc ";

$Query1 = str_replace("'","~",$Query);

//echo $Query;

if (strlen($Query1) > 250)
{
	$wk_query1 = substr($Query1,0,250);
	$wk_query2 = substr($Query1,250);

}
else
{
	$wk_query1 = $Query1;
	$wk_query2 = "";
}

 setBDCScookie($Link, $tran_device, "msalesquery1", $wk_query1);
 setBDCScookie($Link, $tran_device, "msalesquery2", $wk_query2);
//echo $Query;

dopager($Query);

} /* end of dodata */
else
{
	if (isset($_GET['SalesOrder_next_page']))
	{
 		$wk_query1 = getBDCScookie($Link, $tran_device, "msalesquery1");
 		$wk_query2 = getBDCScookie($Link, $tran_device, "msalesquery2");
		$Query1 = $wk_query1 . $wk_query2;
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
echo("<FORM action=\"GetOrdCust.php\" method=\"post\" name=getperson\n>");
echo("<INPUT type=\"hidden\" name=\"salesorder\"> ");  

echo("</FORM>");
}
?>
</body>
</html>
