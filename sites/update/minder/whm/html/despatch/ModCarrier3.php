<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
include "viewport.php";
?>
<script type="text/javascript">
function saveMe(myid) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	//document.getperson.salesorder.value = myorder; 
  	document.getperson.service_id.value = myid; 
  	document.getperson.submit(); 
	return true;
}
</script>

<style type="text/css">
body {
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<?php
/*
go to a screen that has the order no and enter the trolley location to use 
allow upto the number of orders requested .
then can allocate and do the poal

want to enter part or all of an order
then get the pick item lines for the order show status and label and product
get the pack_sscc for each line
show the record_id pick order label_no product sscc status qty shipped qty_ordered.
	sum the qty_shipped and for the line versus qty_ordered

*/

require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");

echo("</head>\n");
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include("logme.php");

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$wk_order = "";

if (isset($_POST['matchorders']))
{
	$matchorders = $_POST['matchorders'];
	setBDCScookie($Link, $tran_device, "matchorders", $matchorders  );
}
if (isset($_GET['matchorders']))
{
	$matchorders = $_GET['matchorders'];
	setBDCScookie($Link, $tran_device, "matchorders", $matchorders  );
}

if (isset($matchorders))
{
	$wk_dummy = 1;
} else {
	$matchorders  = getBDCScookie($Link, $tran_device, "matchorders"  );
}

if (isset($_POST['matchconnotes']))
{
	$matchconnotes = $_POST['matchconnotes'];
	setBDCScookie($Link, $tran_device, "matchconnotes", $matchconnotes  );
}
if (isset($_GET['matchconnotes']))
{
	$matchconnotes = $_GET['matchconnotes'];
	setBDCScookie($Link, $tran_device, "matchconnotes", $matchconnotes  );
}

if (isset($matchconnotes))
{
	$wk_dummy = 1;
} else {
	$matchconnotes  = getBDCScookie($Link, $tran_device, "matchconnotes"  );
}

/*
if (isset($_POST['salesorder']))
{
	$salesorder = $_POST['salesorder'];
	setBDCScookie($Link, $tran_device, "matchorders", $salesorder  );
}
if (isset($_GET['matchorders']))
{
	$salesorder = $_GET['salesorder'];
	setBDCScookie($Link, $tran_device, "matchorders", $salesorder  );
}
if (isset($salesorder))
{
	$wk_dummy = 1;
} else {
	$salesorder  = getBDCScookie($Link, $tran_device, "salesorder"  );
}
*/

if (isset($_POST['despatch_id']))
{
	$despatch_id = $_POST['despatch_id'];
	setBDCScookie($Link, $tran_device, "despatch_id", $despatch_id  );
}
if (isset($_GET['despatch_id']))
{
	$despatch_id = $_GET['despatch_id'];
	setBDCScookie($Link, $tran_device, "despatch_id", $despatch_id  );
}
if (isset($despatch_id))
{
	$wk_dummy = 1;
} else {
	$despatch_id  = getBDCScookie($Link, $tran_device, "despatch_id"  );
}

if (isset($_POST['carrier_id']))
{
	$carrier_id = $_POST['carrier_id'];
	setBDCScookie($Link, $tran_device, "carrier_id", $carrier_id  );
}
if (isset($_GET['carrier_id']))
{
	$carrier_id = $_GET['carrier_id'];
	setBDCScookie($Link, $tran_device, "carrier_id", $carrier_id  );
}
if (isset($carrier_id))
{
	$wk_dummy = 1;
} else {
	$carrier_id  = getBDCScookie($Link, $tran_device, "carrier_id"  );
}

$current_pickCmp  = getBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY"  );

if (isset($_GET['message']))
{
	$wk_message = $_GET['message'];
}

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

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',false);
	$pager = new ADODB_Pager($conn,$Query,'ISSNs',true);
	$pager->Render(14);

}

echo "<body>";
$wk_max_orders = 0;
$wk_max_products = 0;
$wk_PickRestrictByZone = "F";

//echo("<FORM action=\"transactionUC.php\" method=\"post\" name=getdetails\n>");
echo("<form action=\"ModCarrier2.php\" method=\"post\" name=getdetails\n>");
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\"><br>\n");
$got_ssn = 0;
echo ("<table BORDER=\"0\">\n");
echo ("<tr>\n");

echo ("<td colspan=\"2\">\n");
$rcount = 0;

echo ("<tr><td>\n");
//
$wk_zone_allow_as = "F";

/* =================================================================================== */

$wk_tot_locns = 0;
$wk_tot_orders = 0;
$Query = "select count( distinct p2.pick_order)  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";

	//$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "where  ";
	$Query .= "  p2.pick_status in ('OP','DA') ";
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query .= "and   p1.pick_order like '%" . $matchorders . "%' ";
		}
	}
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Orders Count!<BR>\n");
	exit();
}
while (($Row3 = ibase_fetch_row($Result))) {
	$wk_tot_orders = $Row3[0];
}

//release memory
ibase_free_result($Result);

/* ==================================================================================== */

// check allowed to pick companys
$wk_pick_allowed_company = array();
$Query = "select options.description  from options where options.group_code='PICK' and options.code starting 'COMPANY'  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options <BR>\n");
}
else
{
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pick_allowed_company []  = $Row[0] ;
	}
}
//release memory
ibase_free_result($Result);



echo ("<tr><td>\n");
//echo("Orders Matching:</td><td><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
echo("Connote Matching:</td><td colspan=\"3\"><INPUT name=\"matchconnotes\" type=\"text\" value=\"") ;
if (isset($matchconnotes))
{
	echo($matchconnotes);
}
echo( "\"  size=\"15\" maxlength=\"25\"  >\n");
echo ("</td>\n");
echo ("<td>\n");
echo("Order Matching:</td><td colspan=\"3\"><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
echo( "\"  size=\"15\" maxlength=\"25\"  >\n");
echo ("</td></tr>\n");
echo ("<tr><td>\n");
echo("Carrier:</td><td colspan=\"3\"><INPUT name=\"carrier_id\" type=\"text\" readonly value=\"") ;
if (isset($carrier_id))
{
	echo($carrier_id);
}
echo( "\"  size=\"10\" maxlength=\"10\"  >\n");
echo ("</td></tr>\n");
echo ("<tr><td>\n");
echo("Choose Service:</td>") ;
echo ("</tr>\n");
echo ("</table>\n");
/*
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\">\n");
*/
$Query = "";
//$Query = "select distinct p2.pick_order as PO, p1.pick_label_no as label, p1.pick_line_status, p1.prod_id as prod , p1.picked_qty as picked_qty, p1.pick_order_qty as order_qty "; 
$Query = "select distinct p3.record_id, p2.pick_order as PO, p1.pick_label_no as label, p3.ps_sscc_status, p3.ps_product_gtin as prod , p3.ps_qty_shipped as shipped_qty, p3.ps_qty_ordered as order_qty  "; 
$Query = "select distinct p3.record_id, p2.pick_order as PO, p1.pick_label_no as label, p3.ps_sscc_status as status, p3.ps_product_gtin as prod , p3.ps_qty_shipped as shipped_qty, p3.ps_qty_ordered as order_qty ,p3.ps_sscc as in_sscc, p3.ps_out_sscc as out_sscc, p3.ps_awb_consignment_no as in_connote "; 
$Query = "select distinct p3.pick_detail_id, p2.pick_order as PO, p1.pick_label_no as label, p1.prod_id as prod, p3.ssn_id as ssn, p3.qty_picked as qty , i1.serial_number as serial "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "join pick_item_detail p3 on p1.pick_label_no = p3.pick_label_no ";
	$Query .= "join issn i1 on p3.ssn_id = i1.ssn_id ";
	$Query .= "where  ";
	$Query .= "  p2.pick_status in ('OP','DA','DX') ";
	//$Query .= "  p2.pick_status in ('OP','DA') ";
	$Query .= "  and (p3.qty_picked is not null)  ";
/*
	if (isset($salesorder))
	{
		if ($salesorder <> "")
		{
			$Query .= "and   p1.pick_order like '%" . $salesorder . "%' ";
		}
	}
*/
	if (isset($pick_label_no))
	{
		if ($pick_label_no <> "")
		{
			$Query .= "and   p1.pick_label_no =  '" . $pick_label_no . "' ";
		}
	}
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query .= "and   p1.pick_order like '%" . $matchorders . "%' ";
		}
	}

	$Query .= "order by p1.pick_order, p1.pick_label_no, p3.pick_detail_id ";
	//$Query .= "order by p2.pick_order, p1.pick_label_no ";

$Query = "select  c1.carrier_id as Id, p1.first_name as ca, p2.pickd_pick_order1 as PO, p2.pickd_carrier_id as current_ca "; 
	$Query .= "from carrier c1 ";
	$Query .= "join person p1 on c1.carrier_id = p1.person_id ";
	$Query .= "join pick_despatch p2 on p2.despatch_id = " . $despatch_id ;
$Query = "select  c2.record_id as Id, c2.service_type as cs, c1.carrier_id as ca, p2.pickd_pick_order1 as PO, p2.pickd_carrier_id as current_ca , p2.pickd_service_type as current_cs,p2.pickd_service_record_id as current_cs_record "; 
	$Query .= "from carrier_service c2 ";
	$Query .= "join carrier c1 on c2.carrier_id = c1.carrier_id ";
	$Query .= "join pick_despatch p2 on p2.despatch_id = " . $despatch_id ;
	$Query .= " where  ";
	if (isset($carrier_id))
	{
		if ($carrier_id <> "")
		{
			$Query .= " c2.carrier_id  =  '" . $carrier_id  . "' ";
		}
	}
//echo($Query);

dopager($Query);

echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
{
	whm2buttons('GetService', 'ModCarrier.php', "Y","Back_50x100.gif","Back","nextorder.gif");
}

echo("<form action=\"transactionUC.php\" method=\"post\" name=getperson\n>");
//echo("<FORM action=\"$_SERVER['PHP_SELF']\" method=\"post\" name=getperson\n>");
//echo("<INPUT type=\"hidden\" name=\"salesorder\"> ");  
echo("<INPUT type=\"hidden\" name=\"service_id\"> ");  
echo("<INPUT type=\"hidden\" name=\"carrier_id\" value=\"");  
if (isset($carrier_id))
{
	echo($carrier_id);
}
echo( "\"  >\n");
echo("<INPUT type=\"hidden\" name=\"despatch_id\" value=\"");
if (isset($despatch_id))
{
	echo($despatch_id);
}
echo( "\"  >\n");
echo("<INPUT type=\"hidden\" name=\"matchorders\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
echo( "\"  >\n");
echo("<INPUT type=\"hidden\" name=\"matchconnotes\" value=\"") ;
if (isset($matchconnotes))
{
	echo($matchconnotes);
}
echo( "\"  >\n");
echo("</form>");
?>
<script type="text/javascript">
<?php
{
}
?>
</script>
</body>
</html>
