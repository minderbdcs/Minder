<?php
if (isset($_COOKIE['LoginUser']))
{
	
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Order_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}

	$query = "update pick_order set pick_status = 'DX' where pick_order = '" . $order . "'";
	$query2 = "update pick_item set pick_line_status = 'DX' where pick_order = '" . $order . "'";
	$wk_message = "";
	if (!($Result1 = ibase_query($Link, $query)))
	{
		$wk_message .= "Unable to Update Order";
	}
	if (!($Result2 = ibase_query($Link, $query2)))
	{
		$wk_message .= " Unable to Update Line";
	}
	if ($wk_message <> "")
	{
		header("Location: Order_Menu.php?message=" . urlencode($wk_message));
		exit();
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	header("Location: getorder.php?order=" . urlencode($order));
}
?>
