<?php
session_start();
{
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include "logme.php";
	$message = "";
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		$message = "Can't connect to DATABASE!";
		header("Location: ../mainmenu.php?message=" . urlencode($message));
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// check putaway id
	$wk_putaway = "";
	$wk_putaway_exists = 0;
/*
	$Query = "select 1, description 
		from session 
		where device_id = '" . $tran_device . "'
		and code = 'PUTAWAY_ID'";
	//echo($Query); 
	if (!($Result = ibase_query($Link, $Query)))
	{
		$message = "Can't read SESSION!";
		header("Location: ../mainmenu.php?message=" . urlencode($message));
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_putaway_exists = $Row[0];
		$wk_putaway = $Row[1];
	}
	ibase_free_result($Result); 
	unset($Result);
*/
	$wk_putaway = getBDCScookie($Link, $tran_device, "PUTAWAY_ID" );

	if ($wk_putaway > "") 
	{
		// have putaway
		$Query = "execute procedure export_putaway('" . $wk_putaway . "')";
		//echo($Query); 
		if (!($Result = ibase_query($Link, $Query)))
		{
			$message = "Can't export Putaway ID!";
			header("Location: ../mainmenu.php?message=" . urlencode($message));
			exit();
		}
/*
		$Query = "update session set description = '' ";
		$Query .= " where device_id = '" . $tran_device . "' and code = 'PUTAWAY_ID'";
		//echo($Query); 
		if (!($Result = ibase_query($Link, $Query)))
		{
			$message = "Can't update Session!";
			header("Location: ../mainmenu.php?message=" . urlencode($message));
			exit();
		}
*/
		setBDCScookie($Link, $tran_device, "PUTAWAY_ID", "" );

		unset($Result);
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);

	// ok done transfer to mainmenu
	header("Location: ../mainmenu.php");
}
?>
