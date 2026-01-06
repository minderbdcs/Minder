<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
?>
<?php
/*
allocate and do the pkbs
*/
?>

</head>
<body>
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
/****************************************************************************************************************/

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$wk_order = "";
if (isset($_POST['dopkal']))
/****************************************************************************************************************/

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
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

$current_pickZone = getBDCScookie($Link, $tran_device, "CURRENT_PICK_ZONE"  );
$current_pickCmp  = getBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY"  );

//$allocateorders=10;

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
echo("<FORM action=\"getordersslo.php\" method=\"post\" name=\"getdetails\" >\n");
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
$Query = "select user_id from sys_user order by user_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}

$wk_tot_locns = 0;
$wk_tot_orders = 0;
/*
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
if ($wk_dopkal != "T") {
	if (!isset($allocateorders))
	{
		$Query = "select count( distinct p1.pick_order)  "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		$Query .= "where p1.pick_line_status in ('AL','PG','PL') ";
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

if (!isset($pickdevice))
{
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
}
$wk_max_orders = 0;
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


if ($wk_dopkal == "T") {
	$Query = "select first " . $allocateorders . " distinct p2.pick_order , p2.volume, p2.volume_uom  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	if ($wk_PickRestrictByZone == 'T' )
	{
		$Query .= "left outer join pick_order_sub_type p3 on p2.pick_order_sub_type =  p3.pos_id ";
	}
	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p1.pick_order_qty > 0  ";
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
	//$Query .= "order by p2.pick_priority, p2.pick_due_date, p2.pick_order  ";
	//$Query .= "order by p2.pick_priority, p2.pick_due_date, p1.pick_line_priority, p1.pick_order  ";

	//$Query .= "group by p2.pick_order, p2.pick_priority, p2.pick_due_date, p2.volume, p2.volume_uom  ";
	$Query .= "group by p2.pick_due_date, p2.pick_priority, p2.pick_order, p2.volume, p2.volume_uom  ";
	$Query .= "having count(*) = 1  ";
	//$Query .= "order by p2.pick_priority, p2.pick_due_date,  p2.pick_order  ";
	//$Query .= "order by p2.pick_due_date, p2.pick_priority, p2.pick_order  ";
} else {
	$Query = "select distinct p2.pick_order , p2.volume, p2.volume_uom  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('AL','PG','PL','Al','Pg','Pl') ";
	$Query .= "and   p1.device_id = '" . $tran_device . "' ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
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

// the to device is $tran_device
if (count($allocateorder) > 0 )
{
	$wk_mymessage = "";
	$wk_isok = "T";
	$wk_POAL_isok = "T";
	$wkPOAL = array();
	$wk_printer = "";
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
			// ok now print product labels for orders on device

			// =================================================================================================================

			$Query = "select p3.prod_id, p3.pick_order_qty , c3.default_pick_printer,  w1.default_pick_printer, p3.pick_label_no, p3.picked_qty  ";
			$Query .= "from  pick_order p4  ";
			$Query .= "join  control c3 on c3.record_id = 1 ";
			$Query .= "join  pick_item p3 on p4.pick_order = p3.pick_order ";
			//$Query .= "join  prod_profile p5 on p3.prod_id  = p5.prod_id ";
			$Query .= "join  prod_profile p5 on p3.prod_id  = p5.prod_id and p5.company_id in ('ALL',p4.company_id) ";
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
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=\"continueordrs\" >\n");
	
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
