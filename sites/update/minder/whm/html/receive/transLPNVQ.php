<?php
session_start();

/*
if (isset($_POST) && !empty($_POST))
{
    if (isset($_SESSION['posttimer']))
    {
        if ( (time() - $_SESSION['posttimer']) <= 2)
        {
            // less then 2 seconds since last post
            // treat this as notok
            $wkTransPONVProcess = "NOTOK";
            header("Location: verifyLPweight.php?message=Already+Being+Processed" );
            exit();
        }   
        else
        {
            // more than 2 seconds since last post
            // treat this as ok
            $wkTransPONVProcess = "OK";
        }   
    }   
    $_SESSION['posttimer'] = time();
}   
*/
if (isset($_SESSION['token'])) {
    if (isset($_POST['token'])) {
        if ($_POST['token'] != $_SESSION['token']) {
            $wkTransPONVProcess = "NOTOK";
            header("Location: verifyLPweight.php?message=Already+Being+Processed" );
            exit();
        } else {
            // process form
            // reset session token
            $_SESSION['token'] = md5( $_POST['token'] . time() );
            $wkTransPONVProcess = "OK";
        }
    } else {
        //echo 'post token not set';
        $_SESSION['token'] = md5( 'transLPNVQ.php'  . time() );
        $wkTransPONVProcess = "OK";
    }
}

// ========================  Functions  ===========================================================================
/**
 * get Label Fields for ISSN label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getIssnLabel($Link, $p)
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $issnId = $p->data['ISSN.SSN_ID'];
    //echo("ISSN:" . $issnId );
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_ISSN_LABEL (?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $issnId ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        //echo("\nlabel data:" );
        //echo($output );
        //echo("end of label data\n" );
        $wkParams = explode ("|", $output );
        //echo("Params:");
        //var_dump($wkParams);
	foreach ($wkParams as $k => $v) {
            //echo("\nk:" . $k);
            //echo("\nv:" . $v);
            $wkParams2 = explode("=", $v,2);
            //echo("\nParam:");
            //var_dump($wkParams2 );
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
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' AND DEVICE_ID = ?';
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
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' AND DEVICE_ID = ?';
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

//  ===============================================================================================================

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "GRNV";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include 'logme.php';
	include("transaction.php");
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
/*
15/02/2012 want to have labels to be able to go to bdcsprint
*/
	
	if (isset($_COOKIE['BDCSData']))
	{
		list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
	}
{
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: verifyLPweight.php?message=Can+t+connect+to+DATABASE!"  );
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

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
	$printed_ssn_qty  = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
}

	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (isset($_POST['order']))
	{
		$order = $_POST['order'];
	}
	if (isset($_GET['order']))
	{
		$order = $_GET['order'];
	}
	if (isset($_POST['line']))
	{
		$line = $_POST['line'];
	}
	if (isset($_GET['line']))
	{
		$line = $_GET['line'];
	}
	if (isset($_POST['carrier']))
	{
		$carrier = $_POST['carrier'];
	}
	if (isset($_GET['carrier']))
	{
		$carrier = $_GET['carrier'];
	}
	if (isset($_POST['vehicle']))
	{
		$vehicle = $_POST['vehicle'];
	}
	if (isset($_GET['vehicle']))
	{
		$vehicle = $_GET['vehicle'];
	}
	if (isset($_POST['container']))
	{
		$container = $_POST['container'];
	}
	if (isset($_GET['container']))
	{
		$container = $_GET['container'];
	}
	if (isset($_POST['pallet_type']))
	{
		$pallet_type = $_POST['pallet_type'];
	}
	if (isset($_GET['pallet_type']))
	{
		$pallet_type = $_GET['pallet_type'];
	}
	if (isset($_POST['pallet_qty']))
	{
		$pallet_qty = $_POST['pallet_qty'];
	}
	if (isset($_GET['pallet_qty']))
	{
		$pallet_qty = $_GET['pallet_qty'];
	}
	if (isset($_POST['received_qty']))
	{
		$received_qty = $_POST['received_qty'];
	}
	if (isset($_GET['received_qty']))
	{
		$received_qty = $_GET['received_qty'];
	}
	if (isset($_POST['consignment']))
	{
		$consignment = $_POST['consignment'];
	}
	if (isset($_GET['consignment']))
	{
		$consignment = $_GET['consignment'];
	}
	if (isset($_POST['grn']))
	{
		$grn = $_POST['grn'];
	}
	if (isset($_GET['grn']))
	{
		$grn = $_GET['grn'];
	}
	if (isset($_POST['printer']))
	{
		$printer = $_POST['printer'];
	}
	if (isset($_GET['printer']))
	{
		$printer = $_GET['printer'];
	}
	if (!isset($printer))
	{
		$printer = "PA";
	}

	if (isset($_POST['location']))
	{
		$location = $_POST['location'];
	}
	if (isset($_GET['location']))
	{
		$location = $_GET['location'];
	}
	if (isset($_POST['received_ssn_qty']))
	{
		$received_ssn_qty = $_POST['received_ssn_qty'];
	}
	if (isset($_GET['received_ssn_qty']))
	{
		$received_ssn_qty = $_GET['received_ssn_qty'];
	}
	if (!isset($received_ssn_qty))
	{
		$received_ssn_qty = 1;
	}
	if (isset($_POST['printed_ssn_qty']))
	{
		$printed_ssn_qty = $_POST['printed_ssn_qty'];
	}
	if (isset($_GET['printed_ssn_qty']))
	{
		$printed_ssn_qty = $_GET['printed_ssn_qty'];
	}
	if (!isset($printed_ssn_qty))
	{
		$printed_ssn_qty = 0;
	}
	if (isset($_POST['uom']))
	{
		$uom = $_POST['uom'];
	}
	if (isset($_GET['uom']))
	{
		$uom = $_GET['uom'];
	}
	if (!isset($uom))
	{
		$uom = "EA";
	}
	if (isset($_POST['product']))
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product']))
	{
		$product = $_GET['product'];
	}
	if (isset($_POST['retfrom']))
	{
		$retfrom = $_POST['retfrom'];
	}
	if (isset($_GET['retfrom']))
	{
		$retfrom = $_GET['retfrom'];
	}
	if (!isset($retfrom))
	{
		$retfrom = "";
	}
	if (isset($_POST['complete']))
	{
		$complete = $_POST['complete'];
	}
	if (isset($_GET['complete']))
	{
		$complete = $_GET['complete'];
	}
	if (!isset($complete))
	{
		$complete = "";
	}
	if (isset($_POST['completeno']))
	{
		$complete = "N";
	}
	if (isset($_GET['completeno']))
	{
		$complete = "N";
	}
	if (isset($_POST['completeyes']))
	{
		$complete = "Y";
	}
	if (isset($_GET['completeyes']))
	{
		$complete = "Y";
	}
	if (isset($_POST['label_qty1']))
	{
		$label_qty1 = $_POST['label_qty1'];
	}
	if (isset($_GET['label_qty1']))
	{
		$label_qty1 = $_GET['label_qty1'];
	}
	if (isset($_POST['weight_qty1']))
	{
		$weight_qty1 = $_POST['weight_qty1'];
	}
	if (isset($_GET['weight_qty1']))
	{
		$weight_qty1 = $_GET['weight_qty1'];
	}
	if (isset($_POST['ssn_qty1']))
	{
		$ssn_qty1 = $_POST['ssn_qty1'];
	}
	if (isset($_GET['ssn_qty1']))
	{
		$ssn_qty1 = $_GET['ssn_qty1'];
	}
	if (isset($_POST['weight_uom']))
	{
		$weight_uom = $_POST['weight_uom'];
	}
	if (isset($_GET['weight_uom']))
	{
		$weight_uom = $_GET['weight_uom'];
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
		$class = "Q";
	}
	if (isset($_POST['problem']))
	{
		$problem = $_POST['problem'];
	}
	if (isset($_GET['problem']))
	{
		$problem = $_GET['problem'];
	}

	$my_object = '';
		
	// $my_object = sprintf("%-10.10s%-10.10s", $retfrom, $owner) ;
	$my_object = $product ;
/*
	$location = "";
*/
	$my_sublocn = $grn;

	$my_ref = '';
	$my_ref = $type;
	// $my_ref .= '|' . $order;
	$my_ref .= '|' . $retfrom;
	$my_ref .= '|' . $line;
	$my_ref .= '|' . $label_qty1;
	$my_ref .= '|' . $ssn_qty1;
	$my_ref .= '|' . $weight_qty1;
	$my_ref .= '|' . $weight_uom;
	$my_ref .= '|' . substr($printer,1,1);
	$my_source = 'SSBSSKSSS';
	$tran_tranclass = $class;
	$tran_qty = $received_ssn_qty;

/*
	setBDCScookie($Link, $tran_device, "grn", $grn);
	setBDCScookie($Link, $tran_device, "type", $type);
	setBDCScookie($Link, $tran_device, "order", $order);
	setBDCScookie($Link, $tran_device, "line", $line);
*/

	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
	setBDCScookie($Link, $tran_device, "product", $product);
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
	setBDCScookie($Link, $tran_device, "weight_qty1", $weight_qty1);
	setBDCScookie($Link, $tran_device, "weight_uom", $weight_uom);
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);

	/* write transaction */
		
	{
		$my_message = "";
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
		//if control.generate_label_text = 'F' error text is 10001011|1|5|PA|Processed successfully  
		//if control.generate_label_text = 'T' error text is Processed successfully  
		$my_result = explode('|', $my_responsemessage);
		//if ($my_responsemessage <> "Processed successfully ")
		if (sizeof($my_result) > 0 ) {
			if ($my_result[sizeof($my_result) - 1] <> "Processed successfully ")
			{
				//$message .= $my_responsemessage;
				//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
				header("Location: verifyLPweight.php?" . $my_message );
				exit();
			}
		} else {
			//$message .= $my_responsemessage;
			//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
			header("Location: verifyLPweight.php?" . $my_message );
			exit();
		}
	}
		
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
			header("Location: verifyLPweight.php?" . $my_message );
			exit();
		}
	}


	// if the control.generate_label_text is 'F' then must create the labels
	// for issn's with no label date
	// for this grn
	$errorText = '';
	if (sizeof($my_result) > 1)
	{
		$printerIp = getPrinterIp($Link, $printer);
            	$printerDir = getPrinterDir($Link, $printer);
		$result = $my_result;
		/* result 0 is start ssn_id
                          1 is qty of labels
		          2 is qty on label
		          3 is printer
		          4 is message status */
                require_once 'Printer.php';

		// need printer ip and printer id and working directory
                $p = new Printer($printerIp);

                $p->data['printer_id'] = $printer;
                $p->data['title_1'] = "";
                $p->data['version'] = "";
		// need  a start issn
                $p->data['issn'] = $result[0];
                $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                $p->data['issnlabelprefix'] = substr($result[0],0,2);
                $p->data['issnlabelsuffix'] = substr($result[0],2,strlen($result[0]) - 2);
                $p->data['qty'] = $result[2];
                $p->data['userid'] = $tran_user;
                $p->data['now'] = date('d/m/y H:i:s');
                $q = getISSNLabel($Link, $p );
                $tpl = "";
		if ($result[1] != '')
		{
                        for ($i = 0; $i < $result[1]; $i++) {
                                    $save = fopen($printerDir .
                                                  $p->data['issn'] . '_ISSN.prn', 'w');
                                    //if (!$p->sysLabel($Link, $printer, "ISSN", 1))
                                    if (!$p->sysLabel($Link, $printer, "ISSN_PRODUCT", 1))
                                    {
					
                                        $p->send($tpl, $save);
                                    }
                                    $wk_suffix = "1" . $p->data['issnlabelsuffix'];
                                    $wk_suffix++;
                                    $p->data['issnlabelsuffix'] = substr($wk_suffix,1);
                                    $p->data['issn'] = $p->data['issnlabelprefix'] .  $p->data['issnlabelsuffix']; 
                                    $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                                    fclose($save);
                        }
		}
		$errorText = getBDCScookie($Link, $tran_device, "LabelErrorText" );
		echo $errorText;
	}

	//commit
	ibase_commit($dbTran);
	
	//want to go to 
	if ($complete == "Y" )
	{
		header("Location: receive_menu.php" );
		// include "receive_menu.php" ;
	}
	elseif ($complete == "N" )
	{
		$printed_ssn_qty += ($label_qty1 * $ssn_qty1);
		setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);
		//header("Location: getdelivery.php" );
		header("Location: verifyLPweight.php" );
		// include "getdelivery.php" ;
	}
	else
	{
		$printed_ssn_qty += ($label_qty1 * $ssn_qty1);
		setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);
		$wk_header = "?retfrom=". urlencode($retfrom);
		if (isset($problem))
		{
			$wk_header .= "&problem=". urlencode($problem);
		}
		$wk_header .= "&uom=". urlencode($uom);
		$wk_header .= "&product=". urlencode($product);
		$wk_header .= "&received_ssn_qty=". urlencode($received_ssn_qty);
		$wk_header .= "&printed_ssn_qty=". urlencode($printed_ssn_qty);
		$wk_header .= "&printer=". urlencode($printer);
		$wk_header .= "&location=". urlencode($location);
		header("Location: verifyLPweight.php" . $wk_header );
		// include "getdelivery.php" ;
	}
}
?>
