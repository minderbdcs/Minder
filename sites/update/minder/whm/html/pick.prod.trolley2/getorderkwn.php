<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
include "viewport.php";
?>
<script type="text/javascript">
function processEdit(maxorder ) {
  if ( document.getdetails.allocateorders.value>maxorder)
  {
  	document.getdetails.message.value="Must Be less than the Maximum " + maxorder;
	document.getdetails.allocateorders.focus();
  	return false;
  }
  document.getdetails.message.value=" ";
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

29/09/13 for the user
         show orders OP or DA
	 that have a pick_order_sub_type 
	 for a zone that the user can pick from
	 use the current zone - set in the pick menu
	 if the pick_order_sub_type is null or doesnt exist in pick_order_sub_type table
	 then the pick_order_sub_type.pos_restrict_by_zone = 'F'
	 so no restriction by zone

	 so only restrict by zone if control.pick_restrict_by_zone = 'T'
	 and pick_order_sub_type.pos_restrict_by_zone = 'T'
	 then the current zone  must = pick_order.zone_c
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

if (isset($_POST['pickuser']))
{
	$pickuser = $_POST['pickuser'];
}
if (isset($_GET['pickuser']))
{
	$pickuser = $_GET['pickuser'];
}
if (isset($_POST['pickdevice']))
{
	$pickdevice = $_POST['pickdevice'];
}
if (isset($_GET['pickdevice']))
{
	$pickdevice = $_GET['pickdevice'];
}
if (isset($_POST['allocatedevice']))
{
	$allocatedevice = $_POST['allocatedevice'];
}
if (isset($_GET['allocatedevice']))
{
	$allocatedevice = $_GET['allocatedevice'];
}
if (isset($_POST['allocateorders']))
{
	$allocateorders = $_POST['allocateorders'];
}
if (isset($_GET['allocateorders']))
{
	$allocateorders = $_GET['allocateorders'];
}
if (isset($_POST['matchorders']))
{
	$matchorders = $_POST['matchorders'];
}
if (isset($_GET['matchorders']))
{
	$matchorders = $_GET['matchorders'];
}

$current_pickZone = getBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE"  );
$current_pickCmp  = getBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY"  );

function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	require 'adodb/adodb-pager.inc.php';
	//require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',false);
	$pager->Render(3);

}

$wk_pick_method = "PL2";


echo "<body>";
$wk_max_orders = 0;
$wk_max_products = 0;
$wk_PickRestrictByZone = "F";

$Query = "select max_pick_orders, max_pick_products, pick_restrict_by_zone from control ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_max_orders = $Row2[0];
	$wk_max_products = $Row2[1];
	$wk_PickRestrictByZone = $Row2[2];
}
if (!isset($wk_max_orders2))
{
	$wk_max_orders2 = $wk_max_orders;
}
if (!isset($wk_max_products2))
{
	$wk_max_products2 = $wk_max_products;
}
//release memory
ibase_free_result($Result);

if ($wk_PickRestrictByZone == 'T' )
{
	echo("Zone:" . $current_pickZone);
}
//echo("<h4>Allocate - Get Qtys</h4>\n");
echo("<FORM action=\"getorderskwn.php\" method=\"post\" name=\"getdetails\">\n");
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\"><br>\n");
$got_ssn = 0;
if (!isset($pickuser))
{
	$pickuser = $tran_user;
}
if (!isset($allocatedevice))
{
	$allocatedevice = $tran_device;
}
if (!isset($pickdevice))
{
	$pickdevice = "";
}
$Query = "select user_id from sys_user order by user_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}
echo ("<table BORDER=\"0\">\n");
echo ("<tr><td>\n");
//echo("Allocate to User:</td><td><SELECT name=\"pickuser\">\n");
echo("User:</td><td colspan=\"3\"><select name=\"pickuser\">\n");
// Fetch the results from the database.
while (($Row2 = ibase_fetch_row($Result))) {
	if ($Row2[0] == $pickuser)
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
	}
}
echo("</select></td></tr>\n");
//release memory
ibase_free_result($Result);
$Query = "select device_id from sys_equip where device_type in ('HH', 'PC') order by device_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<BR>\n");
	exit();
}
echo ("<tr><td>\n");
//echo("Allocate to Device:</td><td><SELECT name=\"allocatedevice\" >\n");
echo("Device:</td><td><select name=\"allocatedevice\" >\n");
// Fetch the results from the database.
while (($Row2 = ibase_fetch_row($Result))) {
	if ($Row2[0] == $allocatedevice)
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
	}
}
//echo("</SELECT></td></tr>\n");
echo("</select></td>\n");

//release memory
ibase_free_result($Result);
//echo ("<tr><td>\n");
echo ("<td colspan=\"2\">\n");
$Query = "select device_id from sys_equip where device_type = 'TR' order by device_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}
//echo("Pick to Device:</td><td><SELECT name=\"pickdevice\">\n");
echo("Pick Device:</td><td><select name=\"pickdevice\">\n");
// Fetch the results from the database.
$rcount = 0;
while (($Row2 = ibase_fetch_row($Result))) {
	if ($pickdevice == $Row2[0])
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		if ($pickdevice == "" and $rcount == 0)
		{
			$pickdevice = $Row2[0];
			echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
		}
		else
		{
			echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
		}
	}
	$rcount++;
}
echo("</select></td></tr>\n");

//release memory
ibase_free_result($Result);
/*
$wk_tot_locns = 0;
$wk_tot_orders = 0;
$Query = "select count( distinct p2.pick_order)  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
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
*/
echo ("<tr><td>\n");
/*
$wk_max_orders = 0;
$wk_max_products = 0;
$wk_PickRestrictByZone = "F";

$Query = "select max_pick_orders, max_pick_products, pick_restrict_by_zone from control ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_max_orders = $Row2[0];
	$wk_max_products = $Row2[1];
	$wk_PickRestrictByZone = $Row2[2];
}
if (!isset($wk_max_orders2))
{
	$wk_max_orders2 = $wk_max_orders;
}
if (!isset($wk_max_products2))
{
	$wk_max_products2 = $wk_max_products;
}
//release memory
ibase_free_result($Result);
*/
//
$wk_zone_allow_as = "F";
$Query = "select allow_as_lines from zone where code='" . $current_pickZone . "' ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Zone!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_zone_allow_as = $Row2[0];
}
//echo $Query;
//release memory
ibase_free_result($Result);

$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);

$Query = "select options.description  from sys_user join options on options.group_code='PICK_SEQ' and options.code = sys_user.pick_sequence  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Sequence<BR>\n");
	$wk_lastLocnSeq  = 0 ;
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_lastLocnSeq  = $Row[0] ;
	}
	else
	{
		$wk_lastLocnSeq  = 0 ;
	}
}
//release memory
ibase_free_result($Result);

$Query = "select options.description  from options where options.group_code='PICK' and options.code = 'AS'  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options <BR>\n");
	$wk_allow_as = "F" ;
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_allow_as  = $Row[0] ;
	}
	else
	{
		$wk_allow_as  = "F" ;
	}
}
//release memory
ibase_free_result($Result);

/* =================================================================================== */

$wk_tot_locns = 0;
$wk_tot_orders = 0;
$Query = "select count( distinct p2.pick_order)  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	if ($wk_PickRestrictByZone == 'T' )
	{
		$Query .= "left outer join pick_order_sub_type p3 on p2.pick_order_sub_type =  p3.pos_id ";
	}

	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
	//if ($wk_allow_as != 'T' )
	if (($wk_allow_as != 'T' ) and ($wk_zone_allow_as != "T"))
	{
		$Query .= "and   ( not exists (select p4.pick_label_no from pick_item p4 where p4.pick_order=p1.pick_order and p4.pick_line_status = 'AS' ) ) ";
	}
	if ($wk_PickRestrictByZone == 'T' )
	{
		$Query .= "and ( p2.zone_c = '" . $current_pickZone . "'   ";
		$Query .= "or    p3.pos_restrict_by_zone = 'F' ";
		$Query .= "or    p3.pos_id is null   ";
		$Query .= "or    p2.zone_c is null ) ";
		$Query .= "and ( p2.company_id = '" . $current_pickCmp . "' ) ";
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


//echo("Allocate Orders:</td><td><INPUT name=\"allocateorders\" type=\"text\" value=\"" . $wk_max_orders2 . "\"  size=\"3\" maxlength=\"3\" onchange=\"return processEdit('". $wk_max_orders . "','" . $wk_max_products . "');\" >\n");
//echo("Allocate Orders:</td><td><INPUT name=\"allocateorders\" type=\"text\" value=\"") ;
echo("Allocate:</td><td><INPUT name=\"allocateorders\" type=\"text\" value=\"") ;
if (isset($allocateorders))
{
	echo($allocateorders);
} else {
	echo(min($wk_max_orders2, $wk_tot_orders));
}
echo( "\"  size=\"3\" maxlength=\"3\" onchange=\"return processEdit('". $wk_max_orders . "');\" >\n");
echo ("</td><td>\n");

echo("of</td><td><INPUT name=\"waitingorders\" type=\"text\" value=\"" . $wk_tot_orders . "\" readonly size=\"3\" >\n");
echo ("</td><td>Orders\n");
echo ("</td></tr>\n");
echo ("<tr><td>\n");
//echo("Orders Matching:</td><td><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
echo("Matching:</td><td colspan=\"3\"><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
//echo( "\"  size=\"10\" maxlength=\"10\" onchange=\"return processEdit('". $wk_max_orders . "');\" >\n");
echo( "\"  size=\"15\" maxlength=\"15\" onchange=\"return processEdit('". $wk_max_orders . "');\" >\n");
echo ("</td></tr>\n");
echo ("</table>\n");
/*
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\">\n");
*/
echo("<INPUT name=\"dopkal\" type=\"hidden\"  value=\"T\">\n" );

$Query = "select distinct p2.pick_order as PO, case when (p1.prod_id is null) then p1.ssn_id else p1.prod_id end as Part "; 
$Query = "select distinct p2.pick_order as PO, p2.volume, p2.volume_uom as UOM "; 
$Query = "select distinct p2.pick_order as PO, p2.volume, p2.net_weight  as Weight "; 
$Query = "select distinct p2.pick_order as PO, p2.volume as vol, p2.net_weight  as Wt, p2.pick_priority as pri "; 
/*
$Query .= ", 
(case when p1.ssn_id is null  or p1.ssn_id = '' then
          (select first 1  coalesce(l3.locn_seq, 9999999.000 )  
		from issn s3  
		join location l3  on s3.wh_id = l3.wh_id and s3.locn_id = l3.locn_id 
		 where s3.prod_id = p1.prod_id 
		 and s3.current_qty > 0 
		 and   s3.wh_id = p2.wh_id 
		 and (s3.wh_id < 'X' or s3.wh_id > 'X~') 
		 and   s3.company_id = p2.company_id 
		 and pos('" . $allowed_status . ",AL," .  "',s3.issn_status,0,1) > -1
		 and l3.locn_seq >= " . $wk_lastLocnSeq . "
	         and s3.locn_id not in (select device_id from sys_equip) )
     else
          (select first 1  coalesce(l1.locn_seq, 9999999.000 )  
	   from issn s1  
	   join location l1  on s1.wh_id = l1.wh_id and s1.locn_id = l1.locn_id 
	   where (s1.ssn_id = p1.ssn_id or s1.original_ssn = p1.ssn_id )
	   and   s1.wh_id = p2.wh_id 
 	   and   s1.company_id = p2.company_id 
  	   and s1.current_qty > 0 
	   and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1
	   and l1.locn_seq >= " . $wk_lastLocnSeq . "
           and s1.locn_id not in (select device_id from sys_equip) )
 end
) as seq

";
*/
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "join control c1 on c1.record_id = 1 ";
	if ($wk_PickRestrictByZone == 'T' )
	{
		$Query .= "left outer join pick_order_sub_type p3 on p2.pick_order_sub_type =  p3.pos_id ";
	}
	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
	if (count($wk_pick_allowed_company) > 0)
	{
		$Query .= " and p2.company_id in ('";
		$Query .= implode("','",$wk_pick_allowed_company);
		$Query .= "') ";
	}
	//if ($wk_allow_as != 'T' )
	if (($wk_allow_as != 'T' ) and ($wk_zone_allow_as != "T"))
	{
		$Query .= "and   ( not exists (select p4.pick_label_no from pick_item p4 where p4.pick_order=p1.pick_order and p4.pick_line_status = 'AS' ) ) ";
	}
	if ($wk_PickRestrictByZone == 'T' )
	{
		$Query .= "and ( p2.zone_c = '" . $current_pickZone . "'   ";
		$Query .= "or    p3.pos_restrict_by_zone = 'F' ";
		$Query .= "or    p3.pos_id is null   ";
		$Query .= "or    p2.zone_c is null ) ";
		$Query .= "and ( p2.company_id = '" . $current_pickCmp . "' ) ";
	}
	//$Query .= "order by p2.pick_priority, p2.pick_due_date, p1.pick_line_priority, p1.pick_order  ";
	//$Query .= "order by p2.pick_priority, p2.pick_due_date, p2.pick_order  ";
	$Query .= "order by p2.pick_due_date, p2.pick_priority, p2.pick_order  ";
//echo($Query);

dopager($Query);

echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
{
	whm2buttons('GetOrder', 'pick_Menu.php', "Y","Back_50x100.gif","Back","nextorder.gif");
}
?>
</body>
</html>
