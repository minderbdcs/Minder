<?php
session_start();
if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKIL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	$qty = 1;
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	include("logme.php");
	$my_object = '';
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$type='';
	$order='';
	$order_no='';
	$label='';
	if (isset($_POST['ttype']))
	{
		$type = $_POST['ttype'];
	}
	if (isset($_GET['ttype']))
	{
		$type = $_GET['ttype'];
	}
	$tran_tranclass = $type;
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	$my_object = $order;
	if (isset($_POST['order_no']))
	{
		$order_no = $_POST['order_no'];
	}
	if (isset($_GET['order_no']))
	{
		$order_no = $_GET['order_no'];
	}
	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['label']))
	{
		$label = $_POST['label'];
	}
	if (isset($_GET['label']))
	{
		$label = $_GET['label'];
	}
	if ($type == 'M')
	{
		$my_sublocn = $tran_device;
	}
	else
	{
		$my_sublocn = $label;
	}
	if (isset($_POST['qty']))
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty']))
	{
		$qty = $_GET['qty'];
	}
	$tran_qty = $qty;

	include("transaction.php");
	$transaction_type = $tran_type;

	logme($Link, $tran_user, $tran_device, "start run PKIL");
	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	logme($Link, $tran_user, $tran_device, "end run PKIL");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = " ";
	}
	if (($my_responsemessage == " ") or
	    ($my_responsemessage == ""))
	{
		$my_responsemessage = "Processed successfully ";
	}


	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	//ibase_commit($dbTran);
	
        setBDCScookie($Link, $tran_device, "DespatchLocation", $location);
	// if picked by order for a company
	// with the pick sheet in pick_forms table
	// then print the orders pick sheet to a default printer
	$wk_next_page = "pick_Menu.php";

	$Query = "select 1 "; 
	$Query .= "from pick_order p1 ";
	$Query .= "join options o1 on o1.group_code = 'CMPPKPRTDS' and o1.code = p1.company_id and o1.description = p1.p_country ";
	$Query .= " where p1.pick_order = '" . $order . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		//echo("Unable to Read Company!<BR>\n");
		header("Location: pick_Menu.php?message=Unable+to+Read+Company");
		exit();
	}
	$print_cmp_country_despatch = 0;
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$print_cmp_country_despatch = 1;
	}
	//release memory
	ibase_free_result($Result);
	//echo "print despatch [" . $print_cmp_country_despatch . "]";
	//commit
	ibase_commit($dbTran);
	if ($print_cmp_country_despatch == 1)
	{
		//check whether all lines are picked
		$Query = "select first 1 1 "; 
		$Query .= "from pick_item p1 ";
		$Query .= " where p1.pick_order = '" . $order . "' ";
		//$Query .= " and p1.pick_line_status in ('AL','PG','PL')";
		$Query .= " and p1.pick_line_status in ('AL','PG')";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			//echo("Unable to Read Pick Item!<BR>\n");
			header("Location: pick_Menu.php?message=Unable+to+Read+PickItem");
			exit();
		}
		$order_all_picked = 0;
		// Fetch the results from the database.
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$order_all_picked = 1;
			$wk_next_page = "getfromlocn.php";
		}
		//release memory
		ibase_free_result($Result);
		//echo "order complete [" . $order_all_picked . "]";
		if ($order_all_picked == 0)
		{
			// print orders despatch
			//echo "print orders despatch\n";
			$Query = "select EXPORT_DESPATCH, EXPORT_DESPATCH_PRINTER FROM CONTROL ";
			//echo($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: pick_Menu.php?message=Unable+to+Read+Control");
				exit();
			}
			$wk_export_despatch = "F";
			$wk_export_printer = "PF";
			// Fetch the results from the database.
			while ( ($Row = ibase_fetch_row($Result)) ) {
				$wk_export_despatch = $Row[0];
				$wk_export_printer = $Row[1];
			}
			//release memory
			ibase_free_result($Result);
			//echo "order export [" . $wk_export_despatch . $wk_export_printer . "]";
			if ($wk_export_despatch == "T")
			{
				$Query = "EXECUTE PROCEDURE PC_LABEL_DESPATCH '" . $order . "','" . $wk_export_printer . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
					header("Location: pick_Menu.php?message=Unable+to+Update+Report");
					exit();
				}
			}		
		}
	}
	else
	{
		//check whether all lines are picked
		$Query = "select first 1 1 "; 
		$Query .= "from pick_item p1 ";
		$Query .= " where p1.pick_order = '" . $order . "' ";
		//$Query .= " and p1.pick_line_status in ('AL','PG','PL')";
		$Query .= " and p1.pick_line_status in ('AL','PG')";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			//echo("Unable to Read Pick Item!<BR>\n");
			header("Location: pick_Menu.php?message=Unable+to+Read+PickItem");
			exit();
		}
		$order_all_picked = 0;
		// Fetch the results from the database.
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$order_all_picked = 1;
			$wk_next_page = "getfromlocn.php";
		}
		//release memory
		ibase_free_result($Result);
		//echo "order complete [" . $order_all_picked . "]";
		if ($order_all_picked == 0)
		{
			$wk_next_page = "pick_Menu.php";
		}
	}
	//commit
	//ibase_commit($dbTran);
	//close
	//ibase_close($Link);

	//want to go to despatch screen
	//header("Location: pick_Menu.php");
	header("Location: " . $wk_next_page);
	logme($Link, $tran_user, $tran_device, "end screen PKIL");
	//header("Location: addrprodlabel.php" );
	// no address labels here
}
?>
