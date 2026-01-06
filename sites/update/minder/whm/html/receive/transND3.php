<?php
session_start();
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRND";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	require('logme.php');
//phpinfo();
	
/* need
grn			yes passed
owner			yes from grn_order
retfrom			tes from grn_order
label_qty		yes passed
type			yes passed
order			yes from grn_order
packcontainerno		yes from grn
containertype		yes from grn
printer			yes passed
*/
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
	if (isset($_POST['label_qty']))
	{
		$label_qty = $_POST['label_qty'];
	}
	if (isset($_GET['label_qty']))
	{
		$label_qty = $_GET['label_qty'];
	}
	if (isset($_POST['owner']))
	{
		$owner = $_POST['owner'];
	}
	if (isset($_GET['owner']))
	{
		$owner = $_GET['owner'];
	}
	if (isset($_POST['retfrom']))
	{
		$retfrom = $_POST['retfrom'];
	}
	if (isset($_GET['retfrom']))
	{
		$retfrom = $_GET['retfrom'];
	}
	if (isset($_POST['printer']))
	{
		$printer = $_POST['printer'];
	}
	if (isset($_GET['printer']))
	{
		$printer = $_GET['printer'];
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
	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}
	if (!isset($grn))
	{
		$grn = "";
	}


	$pack_owner = "";
	$pack_type = "";
	$pack_qty = 1;
	$packcontainerno = "";
	$containertype = "";

	$my_object = '';
		
	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getgrn.php?message=Can+t+connect+to+DATABASE&type=" . $type  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	if (($ndtype == "LD") and ($grn == ""))
	{
		// need a GRN
		header("Location: getgrn.php?message=No+GRN&type=" . $type  );
		exit();
	}

	$Query = "SELECT GRN, ORDER_NO,GRN_LABEL_NO, COMPANY_ID, SUPPLIER_ID FROM GRN_ORDER WHERE GRN='" . $grn . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Devices!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if (is_null($Row[0])) {
			$grn = "";
		}
		$order = $Row[1];
		$owner = $Row[3];
		$retfrom = $Row[4]; 
	}
	//release memory
	ibase_free_result($Result);
	$Query = "SELECT CONTAINER_NO, SHIP_CONTAINER_TYPE FROM GRN WHERE GRN='" . $grn . "'";
       
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Devices!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$packcontainerno = $Row[0];
		$containertype = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
	if (($ndtype == "LD") and ($grn == ""))
	{
		// need a GRN
		header("Location: getgrn.php?message=No+GRN&type=" . $type  );
		exit();
	}


	/* do class L transaction for labels */
	$my_object = '';
		
	$my_object = $grn ;
	$location = "";
	$location = $owner;
	$wk_test_owner = trim($owner);
	//if (empty(trim($owner)))
	if (empty($wk_test_owner))
	{
		$log = fopen('/data/tmp/transnd3.log' , 'a');
		fwrite($log,"owner is empty - go to getdelivery2\n");
		fclose($log);
		header("Location: getdelivery2.php?message=".urlencode('Must enter an Owner') . "&type=" . $type  );
		exit();
	}
	if (strlen($location) > 10)
	{
		$location = "TOOBIG";
	}
	$my_sublocn = $retfrom;
	$tran_tranclass = "L";
	$tran_qty = $label_qty;
	if ($tran_qty == "")
	{
		$tran_qty = 0;
	}

	$my_ref = '';
	$my_ref = $ndtype;
	$my_ref .= '|' . $order;
	//$my_ref .= '|' . $line;
	$my_ref .= '|' . $packcontainerno;
	$my_ref .= '|' . $containertype;
	$my_ref .= '|' . $printer;
	$my_ref .= '|' . $owner;
	$my_ref .= '|'  ;
	/* write transaction */
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
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
	$log = fopen('/data/tmp/transnd3.log' , 'a');
	fwrite($log, $Query);
	fwrite($log,"\n");
	fclose($log);
	if (!($Result = ibase_query($Link, $Query)))
	{
		$log = fopen('/data/tmp/transnd3.log' , 'a');
		fwrite($log,"Unable to add transaction\n");
		fclose($log);
		header("Location: getgrn.php?message=Unable+to+Add+Transaction&type=" . $type );
		exit();
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	/* must get the record id just created */
	$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	if (isset($location))
	{
		$Query .= substr($location,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location,2,strlen($location) - 2)."' AND OBJECT = '";
	}
	else
	if (isset($order))
	{
		$Query .= substr($order,0,2)."' AND LOCN_ID = '";
		$Query .= substr($order,2,strlen($order) - 2)."' AND OBJECT = '";
	}
	{
		$Query .= $my_object."' AND TRN_DATE = '";
	}
	$Query .= $tran_trandate."' AND TRN_CODE = '";
	$Query .= $tran_tranclass."' AND DEVICE_ID = '";
	$Query .= $tran_device."' AND COMPLETE = 'F'";
	//print($Query); 
	$tran_recordid = NULL;
	if (!($Result = ibase_query($Link, $Query)))
	{
		// processed ok
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$tran_recordid =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}

	/* print("got record id ".$tran_recordid); */
	/* process procedure */
	if (isset($tran_recordid))
	{
		/* must get the record id just updated */
		$Query = "SELECT ERROR_TEXT,COMPLETE,RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
		/* print($Query); */
		if (!($Result = ibase_query($Link, $Query)))
		{
			header("Location: getgrn.php?message=Unable+to+Query+Transaction&type=" . $type );
			exit();
		}
		$tran_error = NULL;
		$tran_complete = NULL;
		if (($Row = ibase_fetch_row($Result)))
		{
			$tran_error =  $Row[0];
			$tran_complete =  $Row[1];
		}
		//release memory
		ibase_free_result($Result); 
		unset($Result); 
		if (isset($tran_complete))
		{
		}
		else
		{
			$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: getgrn.php?message=Unable+to+Update+Transaction&type=" . $type );
				exit();
			}
			/* ibase_free_result($Result); */
			unset($Result);
		}
		if (isset($tran_complete))
		{
			if ($tran_complete == "F")
			{
				if (isset($tran_error))
				{
					if ($tran_error != "")
					{

						header("Location: getgrn.php?message=".urlencode($tran_error) . "&type=" . $type  );
						exit();
					}
				}
			}
		}
		/* ibase_free_result($Result); */
		unset($Result); 
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
	
	//want to go to next grn
	//header("Location: getgrn.php" );
	header("Location: receive_menu.php" );

}
?>
