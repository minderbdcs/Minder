<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
?>
<?php
/*
have an issn scanned
if no order yet then PKAL the 1st order
do the PKOL for the first line that matches
check whether more issns required
if yes must go to some screen to get the next issn (which after its check comes back here)
else go to getordersissn to do the poal and pkil for all the scanned issns on the order
           then then TRPK to the conveyor

*/
?>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) {
document.write("<p><b>Error message:</b> "+errorMessage+"<br>");
document.write("<b>URL:</b> "+url+"<br>");
document.write("<p><b>Line No:</b> "+line+"</p>");
return true;
}
 onerror = errorHandler; 

</script>
</head>
<body >
<?php

require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");
include "logme.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/**
 * get Label Fields for Product label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getProductLabel($Link, $p )
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $prodId = $p->data['PROD_PROFILE.PROD_ID'];
    $ssnId = $p->data['ISSN.SSN_ID'];
    $pickLabelNo = $p->data['PICK_ITEM.PICK_LABEL_NO'];
    $labelQty = $p->data['labelQty'];
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_PRODUCT_LABEL (?, ?, ?, ?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $prodId, $pickLabelNo, $ssnId, $labelQty ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        $wkParams = explode ("|", $output );
	foreach ($wkParams as $k => $v) {
            $wkParams2 = explode("=", $v,2);
            $nameId = $wkParams2[0];
            // need to remove '%' from begin and end of name
            $nameId = substr($nameId,1,strlen($nameId) - 2);
            if (isset($wkParams2[1]) ) {
                $p->data[$nameId] = $wkParams2[1];
            } else {
                $p->data[$nameId] = "";
            } 
	}
    }
    
    //echo("exit " . __FUNCTION__);
    return $p;
}

/**
 * getPrinterIp
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterIp($Link, $printerId) {
    $result = '';
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * getPrinterDir
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterDir($Link, $printerId) {
    $result = '';
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * get default device for the company that the order is for
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder  Order to use to get Company 
 * @return array
 */
function getZoneDevice ($Link, $wkOrder )
{
	$wkResult = "";
	$Query = "select z1.default_device_id   
                  from pick_order p1
                  join zone z1 on p1.company_id  = z1.company_id
                  where p1.pick_order = '" . $wkOrder . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Order!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult == "") 
	{
		$Query = "select first 1  z1.default_device_id   
                  from zone z1 ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Order!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkResult  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	return $wkResult;
}


/**
 * get ISSNs on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function doTRPK ($Link, $wkOrder )
{
	// transfer order from this device to conveyor device
	// from my device to device for company of order
	// object = order
	// location = from device
	// sub locn = to device
	// ref = comment
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$my_location = $tran_device . "        ";
	$wk_to_device = getZoneDevice($Link, $wkOrder);
	$wk_sublocn = ($wk_to_device == "") ?  $tran_device : $wk_to_device;
	$my_source = 'SSBSSKSSS';
	$my_message = "";
	$my_message = dotransaction("TRPK", "o", $wkOrder, $my_location, $wk_sublocn, "transfer to Conveyor:Finished Pick", 0, $my_source, $tran_user, $tran_device, "N");
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage == "")
	{
			$my_responsemessage = "Processed successfully ";
	}
	//echo($my_responsemessage);
	if ($my_responsemessage <> "Processed successfully ")
	{
		//$message .= $my_responsemessage;
		header("Location: pick_Menu.php?" . $my_message);
		exit();
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
}

/**
 * getOrderDetails
 *
 * @param $Link
 * @param string $orderNo
 * @return array or null
 */
function getOrderDetails($Link, $orderNo) {
    $result = array();
    $sql = 'SELECT COMPANY_ID, PICK_ORDER_TYPE, PICK_ORDER_SUB_TYPE FROM PICK_ORDER WHERE  PICK_ORDER = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $orderNo);
        if ($r) {
            //$d = ibase_fetch_row($r);
            $d = ibase_fetch_assoc($r);
            if ($d) {
                $result = $d;
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/****************************************************************************************************************/

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
{
	$wk_cookie = getBDCScookie($Link, $tran_device, "BDCSData" );
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $wk_cookie . "||||||||||");
}
		
$wk_order = "";
if (isset($_POST['dopkal']))
{
	$wk_dopkal  = $_POST['dopkal'];
}
if (isset($_GET['dopkal']))
{
	$wk_dopkal = $_GET['dopkal'];
}
if (isset($_POST['salesorder']))
{
	$wk_salesorder  = $_POST['salesorder'];
	$matchorders  = $_POST['salesorder'];
}
if (isset($_GET['salesorder']))
{
	$wk_salesorder = $_GET['salesorder'];
	$matchorders = $_GET['salesorder'];
}
//include "logme.php";
if (isset($wk_dopkal))
{
	setBDCScookie($Link, $tran_device, "DOPKAL", $wk_dopkal);
} else {
	$wk_dopkal = getBDCScookie($Link, $tran_device, "DOPKAL" );
}
if (isset($_POST['location']))
{
	$wk_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$wk_location = $_GET['location'];
}

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
//$allocateorders=10;
// if dont have the vars passed then default them
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
	$Query = "select first 1 device_id from sys_equip where device_type = 'TR' order by device_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Devices!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	while (($Row2 = ibase_fetch_row($Result))) {
		$pickdevice = $Row2[0];
	}
}
if (!isset($allocateorders))
{
	$allocateorders = 1;
}

$wk_allow_single_line = 'T' ; /* only allow orders with a single line waiting to complete them */

$allocateorder = array();
$allocatelocation = array();

if (isset($allocateorders))
{
	for ($wk_loop=1;$wk_loop<=$allocateorders;$wk_loop++)
	{
		if (isset($_POST['order' . $wk_loop]))
		{
			$allocateorder[$wk_loop] = $_POST['order' . $wk_loop];
		}		
		if (isset($_GET['order' . $wk_loop]))
		{
			$allocateorder[$wk_loop] = $_GET['order' . $wk_loop];
		}
		if (isset($_POST['location' . $wk_loop]))
		{
			$allocatelocation[$wk_loop] = $_POST['location' . $wk_loop];
		}	
		if (isset($_GET['location' . $wk_loop]))
		{
			$allocatelocation[$wk_loop] = $_GET['location' . $wk_loop];
		}
	}
	//print_r($allocatelocation, true);
	//print_r($allocateorder, true);
}

$wk_pick_method = "PL2";

//echo "<body>";
//echo("<h4>Allocate - Get Qtys</h4>\n");
echo("<div id=\"col3\">");
echo("<FORM action=\"getordersissn2.php\" method=\"post\" name=\"getdetails\" >\n");
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
	//$pickdevice = "";
}

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

if ($wk_dopkal != "T") {
	if (!isset($allocateorders))
	{
		$Query = "select count( distinct p1.pick_order)  "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		$Query .= "where p1.pick_line_status in ('AL','PG','PL','Al','Pg','Pl') ";
		$Query .= "and   p1.device_id = '" . $tran_device . "' ";
		$Query .= "and   p2.pick_status in ('OP','DA') ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Orders Count!<BR>\n");
			exit();
		}
		while (($Row5 = ibase_fetch_row($Result))) {
			$allocateorders = $Row5[0];
		}		

		//release memory
		ibase_free_result($Result);
	}
}

if (!isset($allocateorders))
{
	$allocateorders = 1;
}
echo("<INPUT name=\"pickuser\" type=\"hidden\" value=\"" . $pickuser . "\">\n");
echo("<INPUT name=\"allocatedevice\" type=\"hidden\" value=\"" . $allocatedevice . "\">\n");
echo("<INPUT name=\"allocateorders\" type=\"hidden\" value=\"" . $allocateorders . "\">\n");
if (isset($wk_saleorder))
{
	echo("<INPUT name=\"salesorder\" type=\"hidden\" value=\"" . $wk_salesorder . "\">\n");
}

if (isset($pickdevice))
{
	echo("<INPUT name=\"pickdevice\" type=\"hidden\" value=\"" . $pickdevice . "\">\n");
} else {
	$pickdevice = "";
	$Query = "select first 1 device_id from sys_equip where device_type = 'TR' order by device_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Users!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	$rcount = 0;
	while (($Row2 = ibase_fetch_row($Result))) {
		$pickdevice = $Row2[0];
	}
	
	//release memory
	ibase_free_result($Result);

	echo("<INPUT name=\"pickdevice\" type=\"hidden\" value=\"" . $pickdevice . "\">\n");
}
$wk_max_orders = 0;

$Query = "select max_pick_orders, max_pick_products from control ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_max_orders = $Row2[0];
	$wk_max_products = $Row2[1];
}
if (!isset($wk_max_orders2))
{
	$wk_max_orders2 = $wk_max_orders;
}
if (!isset($wk_max_products2))
{
	$wk_max_products2 = $wk_max_products;
}

// now must use the wh and company of the issn to restirct the orders listed
$wk_allow_wh  = "" ;
$wk_allow_locn  = "" ;
$wk_allow_prod  = "" ;
$wk_allow_company  = "" ;
$wk_allow_qty  = 0 ;
$Query = "select wh_id, locn_id, prod_id,company_id, current_qty from issn where ssn_id = '" . $scanned_ssn . "'"; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read ISSN <BR>\n");
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_allow_wh  = $Row[0] ;
		$wk_allow_locn  = $Row[1] ;
		$wk_allow_prod  = $Row[2] ;
		$wk_allow_company  = $Row[3] ;
		$wk_allow_qty  = $Row[4] ;
	}
}
//release memory
ibase_free_result($Result);

$Query = "select distinct p2.pick_order as PO, case when (p1.prod_id is null) then p1.ssn_id else p1.prod_id end as Part "; 

if ($wk_dopkal == "T") {
	$Query = "select first " . $allocateorders . " distinct p2.pick_order , p2.volume, p2.volume_uom  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
	$Query .= "and  ( p1.ssn_id = '" . $scanned_ssn . "' ";
	$Query .= "or     p1.prod_id = '" . $wk_allow_prod . "'  ) ";
	$Query .= "and   p2.wh_id = '" . $wk_allow_wh . "' ";
	$Query .= "and   p2.company_id = '" . $wk_allow_company . "' ";

	if (count($wk_pick_allowed_company) > 0)
	{
		$Query .= " and p2.company_id in ('";
		$Query .= implode("','",$wk_pick_allowed_company);
		$Query .= "') ";
	}

	$Query .= "and   coalesce(p1.picked_qty,0) < p1.pick_order_qty ";
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query .= "and   p1.pick_order like '%" . $matchorders . "%' ";
		}
	}
	if ($wk_allow_as != 'T' )
	{
		$Query .= "and   ( not exists (select p4.pick_label_no from pick_item p4 where p4.pick_order=p1.pick_order and p4.pick_line_status = 'AS' ) ) ";
	}
	// want only orders requiring this issn to complete them
	if ($wk_allow_single_line == 'T' )
	{
		$Query .= " and  ( not exists (select p5.pick_label_no from pick_item p5 where p5.pick_order=p1.pick_order and p5.pick_line_status in ( 'OP','UP' ) and p5.pick_label_no <> p1.pick_label_no ) ) ";
	}
	//
	//$Query .= "order by p2.pick_priority, p2.pick_due_date, p2.pick_order  ";
	$Query .= "order by p2.pick_priority, p2.pick_due_date, p1.pick_line_priority, p1.pick_order  ";
} else {
	$Query = "select distinct p2.pick_order , p2.volume, p2.volume_uom  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('AL','PG','PL','Al','Pg','Pl') ";
	$Query .= "and   p1.device_id = '" . $tran_device . "' ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
	$Query .= "and  ( p1.ssn_id = '" . $scanned_ssn . "' ";
	$Query .= "or     p1.prod_id = '" . $wk_allow_prod . "'  ) ";
	$Query .= "and   p2.wh_id = '" . $wk_allow_wh . "' ";
	$Query .= "and   p2.company_id = '" . $wk_allow_company . "' ";
	$Query .= "and   coalesce(p1.picked_qty,0) < p1.pick_order_qty ";
	$Query .= "order by p2.pick_order  ";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Orders!<BR>\n");
	exit();
}
$wk_count = 0;
$wk_div = 0;

while (($Row4 = ibase_fetch_row($Result))) {
	$wk_count = $wk_count + 1;
// 1 - 5 -> div 1
// 6 - 10 -> div 2
	$allocateorder[$wk_count] =  $Row4[0] ;
}

//release memory
ibase_free_result($Result);

echo("</div>");

// the to device is $tran_device
$wk_next_label_total = "";
if (count($allocateorder) > 0 )
{
	// do pkal - yes
	// do poal - yes
	// do pkbs - yes

	// do pkol - yes
	// do pkil - yes
	// if end of order then 
	// 	do trpk
	//	go back to menu
	// else
	//	continue picking of lines via getfromlocn

	$wk_mymessage = "";
	$wk_isok = "T";
	$wk_POAL_isok = "T";
	$wkPOAL = array();
	$wk_printer = "";
	$wk_next_label = "";
	$wk_next_label_total = "";
	for($wk_loop = 1; $wk_loop <= count($allocateorder); $wk_loop++)
	{
		if ($wk_dopkal == "T") {
			if ($wk_isok == "T") {
				$transaction_type = "PKAL";
				$my_object = $allocateorder[$wk_loop];
				$my_source = 'SSSSSSSSS';
				$tran_tranclass = "I";
				$tran_qty = 0;
				$my_sublocn = $tran_device;
				$location = $allocatedevice  . 'T|' . $wk_printer;
				$my_ref = $pickuser ;
				$my_order = $allocateorder[$wk_loop];
		                $orderData = getOrderDetails($Link, $my_order);
				$my_company = $orderData['COMPANY_ID'];
				$my_order_type = $orderData['PICK_ORDER_TYPE'];
				$my_order_subtype = $orderData['PICK_ORDER_SUB_TYPE'];
	
				$my_message = "";
				//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
				//$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
				$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N","",$my_company, $my_order, $my_order_type, $my_order_subtype);
				if ($my_message > "") {
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				} else {
					$my_responsemessage = " ";
				}
				if (($my_responsemessage == " ") or
			            ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
				if ($my_responsemessage == "Processed successfully ")
				{
					$my_responsemessage = "OK";	
				} else {
					$wk_isok = "F";
				}
				$wk_mymessage .= "PKAL:" . $my_responsemessage;
			}
		}
		// now check have at least 1 line 'AL' or 'PG'  - ie have stock
		$wk_have_stock = "";
		$Query2 = "select first 1 pick_label_no from pick_item where pick_order = '" . $allocateorder[$wk_loop] . "' "; 
		$Query2 .= " and device_id = '" . $tran_device . "' "; 
		$Query2 .= " and pick_line_status in ('AL','PG','PL') "; 
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read Stock!<BR>\n");
		}
		while (($Row6 = ibase_fetch_row($Result2))) {
			$wk_have_stock = $Row6[0];
		}
		//release memory
		ibase_free_result($Result2);
		
		if ($wk_have_stock == "")
		{
			$wk_mymessage .= "No Stock:" ;
		}
		if ($wk_isok == "T") {
			$transaction_type = "PKBS";
			$my_object = "";
			$my_source = 'SSSSSSSSS';
			$tran_tranclass = "D";
			$tran_qty = 0;
			$my_sublocn = "";
			$location  = ""; 
			$my_ref =  $pickuser . '|' . $allocatedevice  . '|assign priority' ;

			$my_message = "";
			//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if (($my_responsemessage == " ") or
	        	    ($my_responsemessage == "")) {
				$my_responsemessage = "Processed successfully ";
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
					$wk_isok = "F";
			}
			$wk_mymessage .= "PKBS:" . $my_responsemessage;
		}
		if ($wk_isok == "T") {
			$transaction_type = "PKOL";
			$my_object = $scanned_ssn;
			$my_source = 'SSSSSSSSS';
			$tran_tranclass = "B";
			$location  = $wk_allow_wh . $wk_allow_locn ;  /* location of issn */
			$Query3 = "select first 1 p1.pick_label_no, p1.ssn_id, p1.pick_order_qty, p1.picked_qty ";
			$Query3 .= "from pick_item p1 ";
			$Query3 .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
			$Query3 .= "where p1.pick_line_status in ('AL','PG','PL') ";
			$Query3 .= "and   p1.device_id = '" . $tran_device . "' ";
			$Query3 .= "and   p2.pick_status in ('OP','DA') ";
			$Query3 .= "and  ( p1.ssn_id = '" . $scanned_ssn . "' ";
			$Query3 .= "or     p1.prod_id = '" . $wk_allow_prod . "'  ) ";
			$Query3 .= "and   p2.wh_id = '" . $wk_allow_wh . "' ";
			$Query3 .= "and   p2.company_id = '" . $wk_allow_company . "' ";
			$Query3 .= "and   coalesce(p1.picked_qty,0) < p1.pick_order_qty ";
			if (!($Result3 = ibase_query($Link, $Query3)))
			{
				echo("Unable to Read Pick Item!<BR>\n");
			}
			while (($Row7 = ibase_fetch_row($Result3))) {
				$label_no = $Row7[0];
				$ssn = $Row7[1];
				$wk_pick_order_qty = $Row7[2];
				$wk_picked_qty = $Row7[3];
				$required_qty = intval($wk_pick_order_qty) - intval($wk_picked_qty);
			}
			//release memory
			ibase_free_result($Result3);
			$order = $allocateorder[$wk_loop];
			$prod_no = $wk_allow_prod;
			$picked_qty = min(intval($wk_allow_qty), $required_qty);
			// save the cookie
			//$wk_cookie = implode ("|", list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) ) ;
			$wk_cookie = implode ("|", array($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) ) ;
			setBDCScookie($Link, $tran_device, "BDCSData", $wk_cookie);

			$tran_qty = $picked_qty; /* qty to take  */
			$my_sublocn = $label_no; /* pick label no */
		
			$my_ref =  "" ;

			$my_message = "";
			//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if (($my_responsemessage == " ") or
	        	    ($my_responsemessage == "")) {
				$my_responsemessage = "Processed successfully ";
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
					$wk_isok = "F";
			}
			$wk_mymessage .= "PKOL:" . $my_responsemessage;
			// save the issn in the transactions word table
		}
		if ($wk_isok == "dontdothisone") {
			// dont do this one
			$transaction_type = "PKIL";
			//$my_object = $scanned_ssn;
			$my_object = $label_no;
			$my_source = 'SSSSSSSSS';
			$tran_tranclass = "D";
			//$location  = $wk_allow_wh . $wk_allow_locn ;  /* location of issn */
			$location  = $allocatelocation[$wk_loop];
			// need a location  fro this order

			$tran_qty = $picked_qty; /* qty to take  */
			$my_sublocn = $label_no; /* pick label no */
		
			$my_ref =  "" ;

			$my_message = "";
			//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
			$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "") {
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			} else {
				$my_responsemessage = " ";
			}
			if (($my_responsemessage == " ") or
	        	    ($my_responsemessage == "")) {
				$my_responsemessage = "Processed successfully ";
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
					$wk_isok = "F";
			}
			$wk_mymessage .= "PKIL:" . $my_responsemessage;
		}
		if ($wk_isok == "T") {
			// check for  at end of order
			//
			$wk_next_label = "";
			$Query4 = "select first 1 p1.pick_label_no ";
			$Query4 .= "from pick_item p1 ";
			$Query4 .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
			$Query4 .= "where p1.pick_line_status in ('AL','PG','PL','Al','Pg','Pl') ";
			$Query4 .= "and   p1.device_id = '" . $tran_device . "' ";
			$Query4 .= "and   p2.pick_status in ('OP','DA') ";
			$Query4 .= "and   (coalesce(p1.picked_qty,0) < p1.pick_order_qty)   ";
			$Query4 .= "and   ( alltrim(p1.reason) = '' or p1.reason is null) ";
			if (!($Result4 = ibase_query($Link, $Query4)))
			{
				echo("Unable to Read Pick Item!<BR>\n");
			}
			while (($Row7 = ibase_fetch_row($Result4))) {
				$wk_next_label = $Row7[0];
			}
			//release memory
			ibase_free_result($Result4);
			if ($wk_next_label <> "") {
				$wk_next_label_total = "T";
			}
		}
		if ($wk_isok == "T") {
			// ok now print product labels for orders on device

			// =================================================================================================================

			$Query = "select p3.prod_id, p3.pick_order_qty , c3.default_pick_printer,  w1.default_pick_printer, p3.pick_label_no, p3.picked_qty  ";
			$Query .= "from  pick_order p4  ";
			$Query .= "join  control c3 on c3.record_id = 1 ";
			$Query .= "join  pick_item p3 on p4.pick_order = p3.pick_order ";
			$Query .= "join  prod_profile p5 on p3.prod_id  = p5.prod_id ";
			$Query .= "left outer join  warehouse w1 on p4.wh_id =  w1.wh_id  ";
			$Query .= " where p4.pick_order  = '" .  $allocateorder[$wk_loop] . "'";
			$Query .= " and   p3.device_id = '" . $tran_device . "'";
			$Query .= " and p5.issue_per_inner_unit > p3.pick_order_qty " ;
			//print($Query);
		
			$wk_split_ssn = "";
	
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read SSNs!<BR>\n");
				//exit();
			}
			else
			while (($Row = ibase_fetch_row($Result)))
			{
				$wk_sp_prod =  $Row[0];
				$wk_sp_qty =  $Row[1];
				$wk_sp_printer =  $Row[2];
				$wk_sp_wh_printer  =  $Row[3];
				$wk_sp_label_no  =  $Row[4];
				$wk_sp_qty_start  =  $Row[5];
				if ($wk_sp_qty_start = null) 
				{
					$wk_sp_qty_start = 0;
				}
				$wk_sp_qty = $wk_sp_qty - $wk_sp_qty_start;
	
				// ok print a label now - probably only one
				if (($wk_sp_wh_printer <> '') and ($wk_sp_wh_printer <> ' '))
				{
					// have a default printer for the orders wh
					$wk_sp_printer = $wk_sp_wh_printer;
				}
				// need printers IP
				// printer directory
		                $printerIp = getPrinterIp($Link, $wk_sp_printer);
		                $printerDir = getPrinterDir($Link, $wk_sp_printer);
				if ($wk_sp_prod <> "")
				{
	                		require_once 'Printer.php';
					$p = new Printer($printerIp);
		                	$p->data['printer_id'] = $wk_sp_printer;
	        	        	$p->data['PROD_PROFILE.PROD_ID'] = $wk_sp_prod;
	                		$p->data['PICK_ITEM.PICK_LABEL_NO'] = $wk_sp_label_no ;
	                		$p->data['ISSN.SSN_ID'] = '';
		                	//$p->data['labelQty'] = $wk_sp_qty;
		                	$p->data['labelQty'] = 1;
	        	        	$p->data['PACK_QTY'] = 1;
					// qty on label = wk_sp_qty
	                		//$p->data['PACK_QTY'] = $wk_sp_qty;
					getProductLabel($Link, $p, $wk_sp_prod, $wk_sp_label_no ) ;
		        	        $save = fopen(	$printerDir .
	        	        			$wk_sp_prod . '_PROD_' . $wk_sp_qty . '.prn', 'w');
	                	    	$tpl = "";
					//if (!$p->sysLabel($Link, $wk_sp_printer, "PRODUCT", $wk_sp_qty))
					if (!$p->sysLabel($Link, $wk_sp_printer, "PRODUCT", $p->data['labelQty']))
		        	        {
	        	        		$p->send($tpl, $save);
		        	        }
	        	        	fclose($save);
				}
			}
		}
		// ok done the labels so now can transfer if end of order
		if ($wk_isok == "T" and $wk_next_label == "") {
			$my_order  = $allocateorder[$wk_loop];
			//doTRPK ($Link, $my_order );
		}

		// =================================================================================================================
	} /* end of for loop */

}
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

echo("<div id=\"message\">");
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly id=\"message2\" value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
if (isset($wk_mymessage))
{
	echo($wk_mymessage);
}
echo("\">\n");
echo("</div>\n");
echo("<div id=\"col4\">");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
if (count($allocateorder) > 0 )
{
	echo("</FORM>\n");
	// if end of order then go to pick menu
	// else go to getfromlocn
	if ($wk_next_label_total == "") {
		echo("<FORM action=\"pick_Menu.php\" method=\"post\" name=\"continueordrs\" >\n");
	} else {
		echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=\"continueordrs\" >\n");
	}
	
	whm2buttons('ContinuePick', 'pick_Menu.php', "Y","Back_50x100.gif","Back","continue_picks.gif");
	$wk_dofocus = "T";
	if (isset($wk_isok))
	{
		if ($wk_isok == "T")
		{
			echo("<script type=\"text/javascript\">");
			echo("document.continueordrs.submit();");
			echo("</script>");
			$wk_dofocus = "F";
		}
	}
}
else
{
	whm2buttons('GetOrder', 'pick_Menu.php', "Y","Back_50x100.gif","Back","nextorder.gif","Y");
}
echo("</div>");
?>
</body>
</html>
