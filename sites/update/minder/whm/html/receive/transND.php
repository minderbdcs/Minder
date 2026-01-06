<?php
session_start();
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRND";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	require('logme.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (!isset($type))
	{
		$type = "";
	}
	if ($type == "LP")
	{
		$ndtype = "LD";
	}
	else
	{
		$ndtype = $type;
	}
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	if (!isset($order))
	{
		$order = "";
	}
	if (isset($_POST['selorder']))
	{
		$order = $_POST['selorder'];
	}
	if (isset($_GET['selorder']))
	{
		$order = $_GET['selorder'];
	}

	if (isset($_POST['line']))
	{
		$line = $_POST['line'];
	}
	if (isset($_GET['line']))
	{
		$line = $_GET['line'];
	}
	if (!isset($line))
	{
		$line = "";
	}
	if (isset($_POST['selline']))
	{
		$line = $_POST['selline'];
	}
	if (isset($_GET['selline']))
	{
		$line = $_GET['selline'];
	}
	if (isset($_POST['carrier']))
	{
		$carrier = $_POST['carrier'];
	}
	if (isset($_GET['carrier']))
	{
		$carrier = $_GET['carrier'];
	}
	if (!isset($carrier))
	{
		$carrier = "";
	}
	if (isset($_POST['vehicle']))
	{
		$vehicle = $_POST['vehicle'];
	}
	if (isset($_GET['vehicle']))
	{
		$vehicle = $_GET['vehicle'];
	}
	if (!isset($vehicle))
	{
		$vehicle = "";
	}
	if (isset($_POST['container']))
	{
		$container = $_POST['container'];
	}
	if (isset($_GET['container']))
	{
		$container = $_GET['container'];
	}
	if (!isset($container))
	{
		$container = "";
	}
	if (isset($_POST['pallet_type']))
	{
		$pallet_type = $_POST['pallet_type'];
	}
	if (isset($_GET['pallet_type']))
	{
		$pallet_type = $_GET['pallet_type'];
	}
	if (!isset($pallet_type))
	{
		$pallet_type = "";
	}
	if (isset($_POST['pallet_qty']))
	{
		$pallet_qty = $_POST['pallet_qty'];
	}
	if (isset($_GET['pallet_qty']))
	{
		$pallet_qty = $_GET['pallet_qty'];
	}
	if (!isset($pallet_qty))
	{
		$pallet_qty = "";
	}
	if (isset($_POST['received_qty']))
	{
		$received_qty = $_POST['received_qty'];
	}
	if (isset($_GET['received_qty']))
	{
		$received_qty = $_GET['received_qty'];
	}
	if (isset($_POST['sday']))
	{
		$shipped_day = $_POST['sday'];
		//echo("shipped day $shipped_day post");
	}
	if (isset($_GET['sday']))
	{
		$shipped_day = $_GET['sday'];
		//echo("shipped day $shipped_day get");
	}
	if (isset($_POST['smonth']))
	{
		$shipped_mon = $_POST['smonth'];
		//echo("shipped mon $shipped_mon post");
	}
	if (isset($_GET['smonth']))
	{
		$shipped_mon = $_GET['smonth'];
		//echo("shipped mon $shipped_mon get");
	}
	if (isset($_POST['syear']))
	{
		$shipped_year = $_POST['syear'];
		//echo("shipped year $shipped_year post");
	}
	if (isset($_GET['syear']))
	{
		$shipped_year = $_GET['syear'];
		//echo("shipped year $shipped_year post");
	}
	if (isset($shipped_day) and isset($shipped_mon) and isset($shipped_year))
	{
		$shipped_date = sprintf("%04s%02s%02s", $shipped_year, $shipped_mon, $shipped_day);
		//echo("shipped date $shipped_date post");
		// need to convert to a UTC date
		$wk_shipped_date_in = sprintf("%04s-%02s-%02s", $shipped_year, $shipped_mon, $shipped_day);
		$shipped_date = gmdate("Ymd", strtotime($wk_shipped_date_in));
		
		
	}
	if (isset($_POST['class']))
	{
		$class = $_POST['class'];
	}
	if (isset($_GET['class']))
	{
		$class = $_GET['class'];
	}
	if (!isset($class))
	{
		$class = "B";
	}
	if (isset($_POST['problem']))
	{
		$problem = $_POST['problem'];
	}
	if (isset($_GET['problem']))
	{
		$problem = $_GET['problem'];
	}
	if (!isset($problem))
	{
		$problem = "";
	}
	if (isset($_POST['other1']))
	{
		$other1 = $_POST['other1'];
	}
	if (isset($_GET['other1']))
	{
		$other1 = $_GET['other1'];
	}
	if (!isset($other1))
	{
		$other1 = "";
	}
	if (isset($_POST['WH_ID']))
	{
		$wh_id = $_POST['WH_ID'];
	}
	if (isset($_GET['WH_ID']))
	{
		$wh_id = $_GET['WH_ID'];
	}

	$pack_owner = "";
	$pack_type = "";
	$pack_qty = 1;
	$packcontainerno = "";
	$containertype = "";

	$my_object = '';
		
	if (isset($shipped_date))
	{
		$my_object = $consignment . "|" . $shipped_date ;
	}
	else
	{
		$my_object = $consignment ;
	}
	//echo("object $my_object");
	$location = "";
	$location = $carrier;
	$my_sublocn = $vehicle;

	/*
	$passed = "received_qty=$received_qty&type=$type&order=". urlencode($order) . "&line=" . urlencode($line);
	$passed .= "&carrier=" . urlencode($carrier) . "&vehicle=" . urlencode($vehicle) . "&container=". urlencode($container) . "&pallet_type=" . urlencode($pallet_type);
	$passed .= "&pallet_qty=" . urlencode($pallet_qty) . "&consignment=" . urlencode($consignment) ;
	*/
	$passed = "$received_qty|$type|$order|$line";
	$passed .= "|$carrier|$vehicle|$container|$pallet_type";
	$passed .= "|$pallet_qty|$consignment||$problem||" ;
	setcookie("BDCSData","$passed", time()+11186400, "/");

	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getdelivery.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
	if (!isset($wh_id))
	{
		$wh_id = $wk_current_wh_id;
	}
	if ($wh_id == "")
	{
		$wh_id = $wk_current_wh_id;
	}

/* ================================================================================================ */
function getVehicleOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = 'OTHER|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			//echo("Unable to Read Options!<BR>\n");
			$log = fopen('/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return array( $wk_data, $wk_data2);
} // end of function
/* ================================================================================================ */
	//logme($Link, $tran_user, $tran_device, "type:" .$ndtype.":");
	if (($ndtype == "LD") and ($order == ""))
	{

		$Query = "SELECT LOAD_ID FROM GET_LOAD_NO ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: getdelivery.php?message=Unable+to+Read+Order!" );
			exit();
		}
		elseif (($Row = ibase_fetch_row($Result)))
		{
			$order =  $Row[0];
			ibase_free_result($Result); 
			unset($Result); 
		}

		//calc the next load
		$line = "1";
	}
	$my_ref = '';
	$my_ref = $ndtype;
	$my_ref .= '|' . $order;
	$my_ref .= '|' . $line;
	$my_ref .= '|' . $container;
	$my_ref .= '|' . $pallet_type;
	$my_ref .= '|' . $pallet_qty;

	//$my_ref .= '|';
	//$my_ref .= '|';
	//$my_ref .= '|';
	$my_ref .= '|' . $pack_owner;
	$my_ref .= '|' . $pack_type;
	$my_ref .= '|' . $pack_qty;
	$my_ref .= '|' . $other1;
	$my_ref .= '|' . $wh_id;
	$my_ref .= '|';

	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = $received_qty;

	/*
	$passed = "received_qty=$received_qty&type=$type&order=". urlencode($order) . "&line=" . urlencode($line);
	$passed .= "&carrier=" . urlencode($carrier) . "&vehicle=" . urlencode($vehicle) . "&container=". urlencode($container) . "&pallet_type=" . urlencode($pallet_type);
	$passed .= "&pallet_qty=" . urlencode($pallet_qty) . "&consignment=" . urlencode($consignment) ;
	*/
	$passed = "$received_qty|$type|$order|$line";
	$passed .= "|$carrier|$vehicle|$container|$pallet_type";
	$passed .= "|$pallet_qty|$consignment||$problem||" ;
	setcookie("BDCSData","$passed", time()+11186400, "/");

	/* write transaction */
	$Query = "SELECT RESPONSE_TEXT FROM ADD_TRAN_RESPONSE('";
	if (isset($location))
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";
	}
	else
	if (isset($order))
	{
		$Query .= substr($order,0,2)."','";
		$Query .= substr($order,2,strlen($order) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $tran_type."','";
	$Query .= $tran_tranclass."','";
	//$tran_trandate = date("Y-M-d H:i:s");
	$tran_trandate = gmdate("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $my_ref."','";
	}
	$Query .= $tran_qty."','F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";

	//echo($Query);
	//logme($Link, $tran_user, $tran_device, "Querylen:" .strlen($Query));
	//$wk_query2 = str_replace('"','Dq',$Query);
	//$wk_query2 = str_replace("'",'Sq',$wk_query2);
	//logme($Link, $tran_user, $tran_device, "Query:" .$wk_query2);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo "Unable to add transaction";
		//die;
		header("Location: getdelivery.php?message=Unable+to+Add+Transaction!" );
		exit();
	}
	elseif (($Row = ibase_fetch_row($Result)))
	{
		$tran_response =  $Row[0];
		list($dummy1, $grn , $dummy2, $tran_load, $dummy3, $tran_message) = explode(":", $tran_response . "::::::");
		//echo("grn $grn load $tran_load message $tran_message\n");
		//$grn =  $Row[0];
		//ibase_free_result($Result); 
		//unset($Result); 
	}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	$passed = "$received_qty|$type|$order|$line";
	$passed .= "|$carrier|$vehicle|$container|$pallet_type";
	$passed .= "|$pallet_qty|$consignment|$grn|$problem||" ;
	setcookie("BDCSData","$passed", time()+11186400, "/");
	setBDCScookie($Link, $tran_device, "received_qty", $received_qty);
	setBDCScookie($Link, $tran_device, "type", $type);
	setBDCScookie($Link, $tran_device, "order", $order);
	setBDCScookie($Link, $tran_device, "line", $line);
	setBDCScookie($Link, $tran_device, "carrier", $carrier);
	setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
	setBDCScookie($Link, $tran_device, "container", $container);
	setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
	setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
	setBDCScookie($Link, $tran_device, "consignment", $consignment);
	setBDCScookie($Link, $tran_device, "grn", $grn);
	setBDCScookie($Link, $tran_device, "problem", $problem);
	setBDCScookie($Link, $tran_device, "other1", $other1);
	if ($problem > "")
	{
		$tran_qty = 0;
		$tran_ref_x = "|" . $problem;
		if (strlen($tran_ref_x) > 80)
		{
			$tran_ref_x = substr($tran_ref_x, 0, 80);
		}
		$my_object = substr($tran_ref_x,0,30);
		$location = substr($tran_ref_x,30,10);
		$my_sublocn = $grn;
		$my_ref = substr($tran_ref_x,40,40) ;
	
		$my_message = "";
		include("transaction.php");
		$my_message = dotransaction_response("GRNC", "C", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			//$message .= $my_responsemessage;
			//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
			header("Location: getdelivery.php?" . $my_message );
			exit();
		}
	}
	//want to go to verify
	switch ($type )
	{
		case "PO":
		case "WO":
		case "TR":
		case "RA":
			if (($order <> "") and ($line <> ""))
			{
				header("Location: verifyPO.php" );
				// include "verifyPO.php";
			}
			else
			{
				//header("Location: getPO.php" );
				header("Location: getPOLine.php" );
				// include "getPO.php";
			}
			break;
		case "LD":
			header("Location: verifyLD.php" );
			// include "verifyLD.php";
			break;
		case "LP":
			$Query = "select default_receive_weights from control";

			//echo($Query);
			$wk_receive_weights =  "F";
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: getdelivery.php?message=Unable+to+Read+Control" );
				exit();
			}
			elseif (($Row = ibase_fetch_row($Result)))
			{
				$wk_receive_weights =  $Row[0];
			}
			//release memory
			if (isset($Result))
			{
				ibase_free_result($Result); 
			}
			// check whether we can use the other grn qty fields  
			//$Query3 = "select description from options where group_code = 'RECEIVE' and code = 'OTHERELIVERY3'  "; 
			$useVehicleDesc1  = getVehicleOption($Link,  "OTHER_QTY1" );
			$useVehicleDesc2  = getVehicleOption($Link,  "OTHER_QTY2" );
			$useVehicleDesc3  = getVehicleOption($Link,  "OTHER_QTY3" );
			$wk_use_delivery3  = "F";
			//echo($Query);
			//if (!($Result3 = ibase_query($Link, $Query3)))
			//{
			//	echo("Unable to Read Options!<BR>\n");
			//}
			//while ( ($Row3 = ibase_fetch_row($Result3)) ) {
			//	$wk_use_delivery3 = $Row3[0];
			//}
			//release memory
			//ibase_free_result($Result3);
		
			//commit
			//ibase_commit($dbTran);
	
                        if ($wk_receive_weights == "F")
			{
                        	//if ($wk_use_delivery3 == "T")
				//{
				//	header("Location: getdelivery3.php" );
				//}
				//else
				if (($useVehicleDesc1[0] == "T" ) and
				    ($useVehicleDesc2[0] == "T" ) and
				    ($useVehicleDesc3[0] == "T" ))
				{
					header("Location: verifyLPOtherQty.php" );
				} else {
					header("Location: verifyLP.php" );
				}
			}
			else
			{
				header("Location: verifyLPweight.php" );
			}
			break;

	}
}
else
{
	header("Location: getdelivery.php?message=User+Not+Logged+In!"  );
}
?>
