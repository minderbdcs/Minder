<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include("transaction.php");
include "logme.php";
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

/*
	given passed parms must calculate the list of orders
	to allocate (up to max orders and max products)
	so need a table of selected orders
	and a table of selected products
	to ensure don't pass the max's
	(the max's are for just this allocation)
	then for each order involved
	do transaction PKAL class G
	then transaction for PRCL
*/
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

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

/*************************************************************************************************/

$pickmode = "";
if (isset($_POST['pickmode']))
{
	$pickmode = $_POST['pickmode'];
}
if (isset($_GET['pickmode']))
{
	$pickmode = $_GET['pickmode'];
}
$pickordertypes = "";
if (isset($_POST['pickordertypes']))
{
	$pickordertypes = $_POST['pickordertypes'];
}
if (isset($_GET['pickordertypes']))
{
	$pickordertypes = $_GET['pickordertypes'];
}
if ($pickordertypes == "")
{
	$pickordertypes = "GETALL";
}
$pickordermodes = "GETALL";
$pickordernos = "";
if (isset($_POST['pickordernos']))
{
	$pickordernos = $_POST['pickordernos'];
}
if (isset($_GET['pickordernos']))
{
	$pickordernos = $_GET['pickordernos'];
}
if ($pickordernos == "")
{
	$pickordernos = "GETALL";
}
$pickorderstatuses = "GETALL";
$pickorderprioritys = "GETALL";
$pickorderids = "GETALL";

$pickuser = $tran_user;
if (isset($_POST['pickuser']))
{
	$pickuser = $_POST['pickuser'];
}
if (isset($_GET['pickuser']))
{
	$pickuser = $_GET['pickuser'];
}
$allocatedevice = $tran_device;
if (isset($_POST['allocatedevice']))
{
	$allocatedevice = $_POST['allocatedevice'];
}
if (isset($_GET['allocatedevice']))
{
	$allocatedevice = $_GET['allocatedevice'];
}
$pickdevice = "";
if (isset($_POST['pickdevice']))
{
	$pickdevice = $_POST['pickdevice'];
}
if (isset($_GET['pickdevice']))
{
	$pickdevice = $_GET['pickdevice'];
}
//echo("pickdevice " . $pickdevice);
if (isset($_POST['maxorders']))
{
	$wk_max_orders2 = $_POST['maxorders'];
}
if (isset($_GET['maxorders']))
{
	$wk_max_orders2 = $_GET['maxorders'];
}
if (isset($_POST['maxproducts']))
{
	$wk_max_products2 = $_POST['maxproducts'];
}
if (isset($_GET['maxproducts']))
{
	$wk_max_products2 = $_GET['maxproducts'];
}
// need the default printer code
$Query4 = "SELECT sys_equip.computer_name, control.default_pick_printer, control.default_pick_priority FROM control LEFT OUTER JOIN sys_equip ON control.default_pick_printer = sys_equip.device_id ";
if (!($Result4 = ibase_query($Link, $Query4)))
{
	echo("Unable to Read Printer!<BR>\n");
	exit();
}
$wk_printer_name = "";
if ( ($Row5 = ibase_fetch_row($Result4)) ) 
{
	$wk_printer_name = $Row5[0];
	$wk_printer_id = $Row5[1];
	$wk_default_pick_priority = $Row5[2];
	if ($wk_printer_name == "")
	{
		$wk_printer_name = $wk_printer_id;
	}
}
//release memory
ibase_free_result($Result4);
$Query = "select pick_order_type, procedure_name  from pick_mode ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}
$wk_tot_orders = 0;
$wk_tot_products = 0;
if ( ($Row = ibase_fetch_row($Result)) ) {
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	
	$Query2 = "SELECT wk_order FROM  ";
	$Query2 .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$Query2 .= $pickordermodes . "','" ;
	$Query2 .= $pickordernos . "','" ;
	$Query2 .= $pickorderstatuses . "','" ;
	$Query2 .= $pickorderprioritys . "','" ;
	$Query2 .= $pickorderids . "')" ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Orders!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		$wk_order_no = $Row3[0];
		// add 1 to order count
		$wk_tot_orders++;
		// if past order limit then stop
		if ($wk_tot_orders > $wk_max_orders2)
		{
			//echo("past limit of orders");
			break;
		}
		$Query3 = "SELECT DISTINCT prod_id FROM  ";
		$Query3 .= "pick_item WHERE pick_order = '";
		$Query3 .= $wk_order_no . "'";
		$Query3 .= " AND (NOT prod_id IS NULL)";

		//echo($Query3);
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read No Products!<BR>\n");
			exit();
		}
		while ( ($Row4 = ibase_fetch_row($Result3)) ) 
		{
			$wk_product_id = $Row4[0];
			// add to products table
			// work out product count
			if (!isset($wk_products))
			{
				// create array
				$wk_products = array($wk_product_id);
				$wk_tot_products = 1;
			}
			else
			{
				if (!in_array($wk_product_id, $wk_products))
				{
					// add to array
					$wk_products [] = $wk_product_id;	
					$wk_tot_products++;
				}
			}
		}
		//release memory
		ibase_free_result($Result3);
		// if past product count then stop
		if ($wk_tot_products > $wk_max_products2)
		{
			//echo("past limit of products");
			break;
		}
		else
		{
			// else do the processing for this order
			//echo("ok to do order:" . $wk_order_no);
			//echo("for products:");
			//print_r( $wk_products);
			$my_object = $wk_order_no;
			$location = $allocatedevice;
			$my_sublocn = $pickdevice;
			$my_ref = $pickuser;
			$my_source = 'SSBSSKSSS';
			$tran_qty = 0;
			$my_order = $wk_order_no;
		        $orderData = getOrderDetails($Link, $my_order);
			$my_company = $orderData['COMPANY_ID'];
			$my_order_type = $orderData['PICK_ORDER_TYPE'];
			$my_order_subtype = $orderData['PICK_ORDER_SUB_TYPE'];

			$my_message = "";
			//$my_message = dotransaction("PKAL", "G", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'N');
			$my_message = dotransaction("PKAL", "G", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N","",$my_company, $my_order, $my_order_type, $my_order_subtype);
			if ($my_message > "")
			{
				$wk_header = "Location: ViewAllocate.php?pickmode=". urlencode($pickmode) ;
				$wk_header .= "&pickordertypes=". urlencode($pickordertypes) ;
				$wk_header .= "&pickordernos=". urlencode($pickordernos) ;
				$wk_header .= "&pickuser=". urlencode($pickuser) ;
				$wk_header .= "&allocatedevice=". urlencode($allocatedevice) ;
				$wk_header .= "&pickdevice=". urlencode($pickdevice) ;
				$wk_header .= "&maxorders=". urlencode($wk_max_orders2) ;
				$wk_header .= "&maxproducts=". urlencode($wk_max_products2) ;
				$wk_header .= "&" . $my_message ;
				header($wk_header);
				break;
			}
			else
			{
				// was allocated ok
				// do PRCL
				// 1st get message id
				$Query4 = "SELECT message_id FROM GET_NEXT_MESSAGE ";
				if (!($Result4 = ibase_query($Link, $Query4)))
				{
					echo("Unable to Get Next Message!<BR>\n");
					exit();
				}
				if ( ($Row5 = ibase_fetch_row($Result4)) ) 
				{
					$wk_message_id = $Row5[0];
				}
				//release memory
				ibase_free_result($Result4);
				// need the supplier list flag
				$Query4 = "SELECT supplier_list FROM PICK_ORDER WHERE PICK_ORDER = '" . $wk_order_no . "' ";
				if (!($Result4 = ibase_query($Link, $Query4)))
				{
					echo("Unable to Read Order!<BR>\n");
					exit();
				}
				$wk_supplier_list = "F";
				if ( ($Row5 = ibase_fetch_row($Result4)) ) 
				{
					$wk_supplier_list = $Row5[0];
				}
				if ($wk_supplier_list == "T")
				{
					$wk_supplier_list = "true";
				}
				else
				{
					$wk_supplier_list = "false";
				}
				//release memory
				ibase_free_result($Result4);
				// add transactionv4
				$tran_type = "PRCL";
				$tran_tranclass = "S";
				$tran_delim = "|";
				$tran_message = $wk_message_id;
				$tran_data = $tran_user . $tran_delim . $tran_device . $tran_delim . $tran_message . $tran_delim;
				$tran_data .= "<ContactInstanceID>" . $wk_order_no . $tran_delim;
				$tran_data .= "<CoverLetterFlag>true" . $tran_delim;
				$tran_data .= "<SupplierListFlag>" . $wk_supplier_list . $tran_delim;
				$tran_data .= "<Printername>" . $wk_printer_name . $tran_delim;
				$my_source = "KSSSSSS";

				$Query4 = "SELECT REC_ID FROM ADD_TRAN_V4('V4','";
				$Query4 .= $tran_type."','";
				$Query4 .= $tran_tranclass."','";
				$tran_trandate = date("Y-M-d H:i:s");
				$Query4 .= $tran_trandate."','";
				$Query4 .= $tran_delim."','";
				$Query4 .= $tran_user."','";
				$Query4 .= $tran_device."','";
				$Query4 .= $tran_message."','";
				$Query4 .= $tran_data."','";
				$Query4 .= "F','','MASTER',0,'";
				$Query4 .= $my_source."')";
			
				//echo($Query4);
				if (!($Result4 = ibase_query($Link, $Query4)))
				{
					echo("Unable to Add Transaction4!<BR>\n");
					exit();
				}
				else
				if ( ($Row5 = ibase_fetch_row($Result4)) ) 
				{
					$wk_record_id = $Row5[0];
				}
				//release memory
				ibase_free_result($Result4);

				$tran_type = "PRCL";
				$tran_tranclass = "S";
				$tran_delim = "|";
				$tran_message = $wk_message_id;
				$tran_priority = $wk_default_pick_priority;

				$Query4 = "EXECUTE PROCEDURE ADD_MESSAGE_V4('";
				$Query4 .= $tran_message."','";
				$Query4 .= "V4','";
				$Query4 .= $tran_type."','";
				$Query4 .= $tran_tranclass."','";
				$Query4 .= $tran_trandate."','";
				$Query4 .= $tran_user."','";
				$Query4 .= $tran_device."','";
				$Query4 .= $tran_priority."','";
				$Query4 .= "WS','',NULL)";
			
				//echo($Query4);
				if (!($Result4 = ibase_query($Link, $Query4)))
				{
					echo("Unable to Add Transaction4!<BR>\n");
					exit();
				}
				//release memory
				//ibase_free_result($Result4);
				// add web request

			}
		}

	}
	//release memory
	ibase_free_result($Result2);
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

if (isset($wk_header))
{
	exit;
}
else
{
	$wk_header = "Location: getfromlocn.php" ;
	header($wk_header);
	exit;
}
?>
