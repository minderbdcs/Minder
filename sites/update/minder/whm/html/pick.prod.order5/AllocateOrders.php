<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
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

$pickmode = "";
if (isset($_POST['pickmode']))
{
	$pickmode = $_POST['pickmode'];
}
if (isset($_GET['pickmode']))
{
	$pickmode = $_GET['pickmode'];
}
//print("pickmode:" . $pickmode);
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
//print("pickordernos:" . $pickordernos);

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
// for orders
// whether this pick mode allows orders entered
$Query4 = "SELECT pm.pick_param_ordno, pm.pick_order_type, pm.procedure_name,control.max_pick_lines  FROM control join pick_mode pm on pm.pick_mode_no = '" . $pickmode . "'";
if (!($Result4 = ibase_query($Link, $Query4)))
{
	echo("Unable to Read Pick Mode!<BR>\n");
	exit();
}
$wk_pickmode_byorder = "F";
if ( ($Row5 = ibase_fetch_row($Result4)) ) 
{
	$wk_pickmode_byorder = $Row5[0];
	$pick_order_type = $Row5[1];
	$pick_procedure = $Row5[2];
	$wk_max_lines = $Row5[3];
}
//release memory
ibase_free_result($Result4);
if ( $wk_pickmode_byorder == "T")  
{
	// pick by order
	$wk_max_products = 10000;
	if ( $pickordernos == "GETALL")  
	{
		$wk_max_orders = 1;
	}
	else
	{
		$wk_max_orders = 20; // $wk_max_orders2;
	}
	$wk_tran_class = "G";
}
else
{
	// pick by product
	$wk_max_orders = 1; 
	$wk_max_products = 1 ; //$wk_max_products2; // 1
	$wk_tran_class = "H";
}
include "transaction.php";
$wk_tot_orders = 0;
$wk_tot_products = 0;
{
	//$pick_order_type = $Row[0];
	//$pick_procedure = $Row[1];
	
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
		if ($wk_tot_orders > $wk_max_orders)
		{
			//echo("past limit of orders");
			break;
		}
		$Query3 = "SELECT DISTINCT prod_id FROM  ";
		$Query3 .= "pick_item WHERE pick_order = '";
		$Query3 .= $wk_order_no . "'";
		$Query3 .= " AND pick_line_status in ('OP','UP')";
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
		if ($wk_tot_products > $wk_max_products)
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
			$location = $allocatedevice;
			$my_sublocn = $pickdevice;
			$my_ref = $pickuser;
			$my_source = 'SSBSSKSSS';
			if ($wk_tran_class == "H")
			{
				$my_object = $wk_products[0];
				$tran_qty = $wk_max_lines;
			}
			else
			{
				$my_object = $wk_order_no;
				$tran_qty = 0;
			}

			$my_message = "";
			$my_message = dotransaction("PKAL", $wk_tran_class, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, 'N');
			if ($my_message > "")
			{
				//$wk_header = "Location: ViewAllocate.php?pickmode=". urlencode($pickmode) ;
				$wk_header = "Location: ViewOrders.php?pickmode=". urlencode($pickmode) ;
				$wk_header .= "&pickordertypes=". urlencode($pickordertypes) ;
				$wk_header .= "&pickordernos=". urlencode($pickordernos) ;
/*
				$wk_header .= "&pickuser=". urlencode($pickuser) ;
				$wk_header .= "&allocatedevice=". urlencode($allocatedevice) ;
				$wk_header .= "&pickdevice=". urlencode($pickdevice) ;
				$wk_header .= "&maxorders=". urlencode($wk_max_orders2) ;
				$wk_header .= "&maxproducts=". urlencode($wk_max_products2) ;
*/
				$wk_header .= "&" . $my_message ;
				header($wk_header);
				break;
			}
/*
			else
			{
				// was allocated ok
				//release memory
				//ibase_free_result($Result4);

			}
*/
		}

	}
	//release memory
	ibase_free_result($Result2);
}
//release memory
//ibase_free_result($Result);

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
