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
	
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );

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
		// needs to be UTC
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
	if (isset($_POST['owner']))
	{
		$owner = $_POST['owner'];
	}
	if (isset($_GET['owner']))
	{
		$owner = $_GET['owner'];
	}
	if (isset($_POST['other_qty1']))
	{
		$other_qty1 = $_POST['other_qty1'];
	}
	if (isset($_GET['other_qty1']))
	{
		$other_qty1 = $_GET['other_qty1'];
	}
	if (isset($_POST['other_qty2']))
	{
		$other_qty2 = $_POST['other_qty2'];
	}
	if (isset($_GET['other_qty2']))
	{
		$other_qty2 = $_GET['other_qty2'];
	}
	if (isset($_POST['other_qty3']))
	{
		$other_qty3 = $_POST['other_qty3'];
	}
	if (isset($_GET['other_qty3']))
	{
		$other_qty3 = $_GET['other_qty3'];
	}
	if (isset($_POST['other_qty4']))
	{
		$other_qty4 = $_POST['other_qty4'];
	}
	if (isset($_GET['other_qty4']))
	{
		$other_qty4 = $_GET['other_qty4'];
	}
	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}

	$pack_owner = "";
	$pack_type = "";
	$pack_qty = 1;
	$packcontainerno = "";
	$containertype = "";

	$my_object = '';
		
	{
		$my_object = "" ;
	}
	//echo("object $my_object");
	$location = "";

	/*
	$passed = "received_qty=$received_qty&type=$type&order=". urlencode($order) . "&line=" . urlencode($line);
	$passed .= "&carrier=" . urlencode($carrier) . "&vehicle=" . urlencode($vehicle) . "&container=". urlencode($container) . "&pallet_type=" . urlencode($pallet_type);
	$passed .= "&pallet_qty=" . urlencode($pallet_qty) . "&consignment=" . urlencode($consignment) ;
	*/

	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: getdelivery3.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	//logme($Link, $tran_user, $tran_device, "type:" .$ndtype.":");
	$my_ref = '';
	include("transaction.php");
	if (isset($owner))
	{
		if ($owner <> "")
		{
			$my_ref = $owner;
			$my_object = $grn ;
			$my_sublocn = $grn;

			$tran_type = 'UGON';
			$tran_tranclass = "G";
			$tran_qty = 0;
			$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
				if ($my_message <> "")
				{
					//$message .= $my_responsemessage;
					//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
					header("Location: getdelivery3.php?" . $my_message );
					exit();
				}
			}
		}
	}

	/*
	$passed = "received_qty=$received_qty&type=$type&order=". urlencode($order) . "&line=" . urlencode($line);
	$passed .= "&carrier=" . urlencode($carrier) . "&vehicle=" . urlencode($vehicle) . "&container=". urlencode($container) . "&pallet_type=" . urlencode($pallet_type);
	$passed .= "&pallet_qty=" . urlencode($pallet_qty) . "&consignment=" . urlencode($consignment) ;
	*/

	//echo($Query);
	//logme($Link, $tran_user, $tran_device, "Querylen:" .strlen($Query));
	//$wk_query2 = str_replace('"','Dq',$Query);
	//$wk_query2 = str_replace("'",'Sq',$wk_query2);
	//logme($Link, $tran_user, $tran_device, "Query:" .$wk_query2);
	
	setBDCScookie($Link, $tran_device, "owner", $owner);
	setBDCScookie($Link, $tran_device, "other_qty1", $other_qty1);
	setBDCScookie($Link, $tran_device, "other_qty2", $other_qty2);
	setBDCScookie($Link, $tran_device, "other_qty3", $other_qty3);
	setBDCScookie($Link, $tran_device, "other_qty4", $other_qty4);
	{
		$tran_qty = $other_qty1;
		$my_ref = "update grns other_qty1";
		$my_object = $grn ;
		$my_sublocn = $grn;
	
		$my_message = "";
		//include("transaction.php");
		$my_message = dotransaction_response("UGO1", "G", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
			header("Location: getdelivery3.php?" . $my_message );
			exit();
		}
	}
	{
		$tran_qty = $other_qty2;
		$my_ref = "update grns other_qty2";
		$my_object = $grn ;
		$my_sublocn = $grn;
	
		$my_message = "";
		//include("transaction.php");
		$my_message = dotransaction_response("UGO2", "G", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
			header("Location: getdelivery3.php?" . $my_message );
			exit();
		}
	}
	{
		$tran_qty = $other_qty3;
		$my_ref = "update grns other_qty3";
		$my_object = $grn ;
		$my_sublocn = $grn;
	
		$my_message = "";
		//include("transaction.php");
		$my_message = dotransaction_response("UGO3", "G", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
			header("Location: getdelivery3.php?" . $my_message );
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
				header("Location: getdelivery3.php?message=Unable+to+Read+Control" );
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
		
			//commit
			//ibase_commit($dbTran);
	
                        if ($wk_receive_weights == "F")
			{
				header("Location: verifyLP.php" );
			}
			else
			{
				header("Location: verifyLPweight.php" );
			}
			// include "verifyLP.php";
			break;

	}
	//echo("got here");
}
else
{
	header("Location: getdelivery3.php?message=User+Not+Logged+In!"  );
}
?>
