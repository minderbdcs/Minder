<?php
session_start();

/**
 * getPrinterOpts
 *
 * @param $Link
 */
function getPrinterOpts($Link) {
    $sql = 'SELECT DEVICE_ID FROM SYS_EQUIP WHERE DEVICE_TYPE = \'PR\' ORDER BY DEVICE_ID';
    if (!($result = ibase_query($Link, $sql))) {
        exit('Unable to read printers!');
    }
    $printerOpts = array();
    while (($row = ibase_fetch_row($result))) {
        $printerOpts[$row[0]] = $row[0];
    }
    ibase_free_result($result);

    return $printerOpts;
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
 * getProductInfo
 *
 * @param $Link
 */
function getProductInfo($Link, $product_id) {
    $sql = 'SELECT PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC, UOM.CODE, UOM.DESCRIPTION, PROD_PROFILE.TEMPERATURE_ZONE FROM PROD_PROFILE JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE WHERE PROD_PROFILE.PROD_ID = ?';
    $sql = 'SELECT PROD_PROFILE.PROD_ID,
                   PROD_PROFILE.SHORT_DESC,
                   UOM.CODE,
                   UOM.DESCRIPTION,
                   PROD_PROFILE.TEMPERATURE_ZONE,
                   PROD_PROFILE.PROD_TYPE,
                   PROD_PROFILE.SSN_TYPE
            FROM PROD_PROFILE 
            JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE 
            WHERE PROD_PROFILE.PROD_ID = ?';
    $productInfo = array();
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $product_id);
        if ($r) {
            while (($row = ibase_fetch_row($r))) {
                $productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $productInfo;
}


/**
 * getSSNInfo
 *
 * @param $Link
 * @param string $ssnId
 * @return array or null
 */
function getSSNInfo($Link, $ssnId) {
    $result = array();
    $sql = "SELECT SSN_DESCRIPTION,
            SSN_TYPE AS TYPE_1,
            GENERIC AS TYPE_2, 
            SSN_SUB_TYPE AS TYPE_3,
            BRAND,
            MODEL,
            SERIAL_NUMBER,
            LEGACY_ID,
            SUPPLIER_ID,
            COMPANY_ID,
            MER_DAY(LAST_UPDATE_DATE) || '/' || MER_MONTH(LAST_UPDATE_DATE) || '/' || SUBSTR(CAST(MER_YEAR(LAST_UPDATE_DATE) AS CHAR(4)) , 3,4) AS UPDATE_DATE
            FROM SSN 
            WHERE SSN_ID = ?";
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $ssnId);
        if ($r) {
            //while(( $d = ibase_fetch_row($r))) { 
            while(( $d = ibase_fetch_assoc($r))) { 
                $result = $d;
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}


/**
 * PrintIssnLabel
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function PrintIssnLabel($Link, $printerId, $printerIp, $ssn_id, $qty, $other1, $other2, $product_id, $company_id, $productInfo, $original_ssn) {
/*
need printerIp
     printerId
     owned_by = company_id
     issn ssn_id 
     qty on label
     pallet no 
     login user
     product_id
     productInfo
	tempzone
     	um
*/
global $tran_device;
                    //$cIssn1 = $ssn_id;
                    require_once 'Printer.php';
                    $p = new Printer($printerIp);
                    $p->data['ownerid'] = $company_id;
                    $p->data['ISSN.COMPANY_ID'] = $p->data['ownerid'] ;
                    $p->data['issn'] = $ssn_id;
                    $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                    $p->data['issnlabelprefix'] = substr($ssn_id,0,2);
                    $p->data['issnlabelsuffix'] = substr($ssn_id,2,strlen($ssn_id) - 2);
                    $p->data['qty'] = $qty;
                    $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                    $p->data['palletno'] = $other1;
                    $p->data['ISSN.OTHER1'] = $p->data['palletno'] ;
                    $p->data['parentid'] = $other2;
                    $p->data['ISSN.OTHER2'] = $p->data['parentid'] ;
                    //$loginUser = split('\|', $_COOKIE['LoginUser']);
                    $loginUser = explode("|", $_COOKIE["LoginUser"]);
                    $p->data['userid'] = $loginUser[0];
                    $p->data['ISSN.CREATED_BY'] = $p->data['userid'] ;
                    if (isset($productInfo[$product_id][0])) {
                        $p->data['description'] = $productInfo[$product_id][0];
                    } else {
                        $p->data['description'] = '';
                    }
                    $p->data['PROD_PROFILE.SHORT_DESC'] = $p->data['description'] ;
                    $p->data['product_id'] = $product_id;
                    $p->data['ISSN.PROD_ID'] = $p->data['product_id'] ;
                    $p->data['PROD_PROFILE.PROD_ID'] = $p->data['product_id'] ;
                    if (isset($productInfo[$product_id][2])) {
                        $p->data['um'] = $productInfo[$product_id][2];
                    } else {
                        $p->data['um'] = '';
                    }
                    $p->data['UOM.CODE'] = $p->data['um'] ;
                    $p->data['now'] = date('d/m/y H:i:s');
                    $p->data['ISSN.CREATE_DATE'] = $p->data['now'] ;
                    if (isset($productInfo[$product_id][3])) {
                        $p->data['tempzone'] = $productInfo[$product_id][3];
                    } else {
                        $p->data['tempzone'] = '';
                    }
                    $p->data['PROD_PROFILE.TEMPERATURE_ZONE'] = $p->data['tempzone'] ;
                    $p->data['other3'] = "";
                    $p->data['pickorder'] = "";
                    $p->data['ISSN.ORIGINAL_SSN'] = $original_ssn ;
                    if (isset($productInfo[$product_id][4])) {
                        $p->data['prodtype'] = $productInfo[$product_id][4];
                    } else {
                        $p->data['prodtype'] = '';
                    }
                    $p->data['PROD_PROFILE.PROD_TYPE'] = $p->data['prodtype'] ;
                    $ssnInfo = getSSNInfo($Link, $original_ssn);
                    $p->data['ssn_desc'] = $ssnInfo['SSN_DESCRIPTION'];
                    $p->data['SSN.SSN_DESCRIPTION'] = $p->data['ssn_desc'] ;
                    //$p->data['type_1'] = $ssnInfo['TYPE_1'];
                    if (isset($productInfo[$product_id][5])) {
                        $p->data['type_1'] = $productInfo[$product_id][5];
                    } else {
                        $p->data['type_1'] = '';
                    }
                    $p->data['SSN.TYPE_1'] = $p->data['type_1'] ;
                    $p->data['SSN.SSN_TYPE'] = $p->data['type_1'] ;
                    $p->data['type_2'] = $ssnInfo['TYPE_2'];
                    $p->data['SSN.TYPE_2'] = $p->data['type_2'] ;
                    $p->data['SSN.GENERIC'] = $p->data['type_2'] ;
                    $p->data['type_3'] = $ssnInfo['TYPE_3'];
                    $p->data['SSN.TYPE_3'] = $p->data['type_3'] ;
                    $p->data['SSN.SSN_SUB_TYPE'] = $p->data['type_3'] ;
                    $p->data['model'] = $ssnInfo['MODEL'];
                    $p->data['SSN.MODEL'] = $p->data['model'] ;
                    $p->data['brand'] = $ssnInfo['BRAND'];
                    $p->data['SSN.BRAND'] = $p->data['brand'] ;
                    $p->data['serial'] = $ssnInfo['SERIAL_NUMBER'];
                    $p->data['SSN.SERIAL_NUMBER'] = $p->data['serial'] ;
                    $p->data['title_1'] = "";
                    $p->data['version'] = "";
                    $tpl = file_get_contents('../receive/ISSN-new.prn');
                    $tpl2 = file_get_contents('../receive/ISSN-old.prn');
                    //-- proceed while not printed all labels in 1st set
                    //$save = fopen('/data/asset.rf/' . $printerId .
                    $savefile = '/tmp/' . $p->data['issn'] . '_ISSN.prn';
                    $save = fopen($savefile, 'w');
                    $wk_use_syslabel = "T";
                    $field_type = checkForTypein($ssn_id, 'BARCODE' ); 
                    if ($field_type <> "none")
                    {
                        // is a new barcode
                        //$p->send($tpl, $save);
    			if (!$p->sysLabel($Link, $printerId, "ISSN", 1, "F"))
    			{
                    	    $p->save($tpl, $save);
                            $wk_use_syslabel = "F";
			}
                        //$ssn_id++;
                    }
                    else
                    {
                        // is an old altbarcode
                        //$p->send($tpl2, $save);
    			if (!$p->sysLabel($Link, $printerId, "ISSN", 1, "F"))
    			{
                        	$p->save($tpl2, $save);
                                $wk_use_syslabel = "F";
			}
                        //$ssn_id++;
                    }

                    fclose($save);
                    // now save the split file name for moving to the printer
                    $oldssns = getBDCScookie($Link, $tran_device, "PickSplitSSN");
                    $oldqtys = getBDCScookie($Link, $tran_device, "PickSplitQty");
                    if ($wk_use_syslabel == "F")
                    {
                        $oldssns .= "|" . $savefile;
                    }
                    $oldqtys .= "|" . $qty;
                    setBDCScookie($Link, $tran_device, "PickSplitSSN", $oldssns);
                    setBDCScookie($Link, $tran_device, "PickSplitQty", $oldqtys);
}

if (isset($_COOKIE['LoginUser']))
{
	$tran_type = "PKOL";
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	//include('logme.php');
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$ssn = '';
	$label_no = '';
	$order = '';
	$prod_no = '';
	$description = '';
	$uom = '';
	$order_qty = 0;
	$picked_qty = 0;
	$required_qty = 0;
	$scanned_ssn = '';
	$scanned_printer = '';
	
	$my_object = '';
	if (isset($_COOKIE['BDCSData']))
	{
		//echo "cookie:" . $_COOKIE["BDCSData"];
		list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scanned_ssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"]);
	}
		

	if (isset($_POST['scannedprinter']))
	{
		$scanned_printer = $_POST['scannedprinter'];
	}
	if (isset($_GET['scannedprinter']))
	{
		$scanned_printer = $_GET['scannedprinter'];
	}
	$qty = $picked_qty;
	//echo "picked qty " . $picked_qty;
	//if ($ssn <> "")
	{
		$my_object = $scanned_ssn;
		$my_sublocn = $label_no;
	}
/*
	else
	{
		// a product
		$my_object = $prod_no;
		$my_sublocn = "";
	}
*/

	
	//$location is the device
	$my_ref = '';
	if (isset($_POST['reference']))
	{
		$my_ref = $_POST['reference'];
	}
	if (isset($_GET['reference']))
	{
		$my_ref = $_GET['reference'];
	}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: pick_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	//include("logme.php");
	require_once("logme.php");

	logme($Link, $tran_user, $tran_device, "start printer is " . $scanned_printer);
	$my_source = 'SSBSSKSSS';
	//if ($ssn <> "")
	{
		$tran_tranclass = "B";
	}
/*
	else
	{
		$tran_tranclass = "P";
	}
*/

	$tran_qty = $qty;
	//echo "tran qty " . $tran_qty;

	/* write transaction */
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	{
		$Query .= substr($location,0,2)."','";
		$Query .= substr($location,2,strlen($location) - 2)."','";
	}
	{
		$Query .= $my_object."','";
	}
	$Query .= $tran_type."','";
	$Query .= $tran_tranclass."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	{
		$Query .= $my_ref."',";
	}
	$Query .= $tran_qty.",'F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";

	//echo($Query);
	logme($Link, $tran_user, $tran_device, "start add trn for PKOL");
	//logme($Link, $tran_user, $tran_device, addslashes($Query));
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: pick_Menu.php?message=Unable+to+Add+Transaction!");
		exit();
	}
	/* ibase_free_result($Result); */
	unset($Result); 
	logme($Link, $tran_user, $tran_device, "end add trn for PKOL");
	/* must get the record id just created */
	$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
	{
		$Query .= substr($location,0,2)."' AND LOCN_ID = '";
		$Query .= substr($location,2,strlen($location) - 2)."' AND OBJECT = '";
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
/*
		header("Location: pick_Menu.php?message=Unable+to+Read+Transaction!");
		exit();
*/
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
			header("Location: pick_Menu.php?message=Unable+to+Query+Transaction!");
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
			//print($tran_complete);
			//print($tran_error);
			/*
			if ($tran_complete == "F")
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					header("Location: pick_Menu.php?message=Unable+to+Update+Transaction!");
					exit();
				}
				/* ibase_free_result($Result); *-
				unset($Result);
			}
			*/
		}
		else
		{
			$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
			if (!($Result = ibase_query($Link, $Query)))
			{
				header("Location: pick_Menu.php?message=Unable+to+Update+Transaction!");
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

						header("Location: pick_Menu.php?message=".urlencode($tran_error));
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
		
	// check for issn splits

	$Query = "select p2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id, c3.default_pick_printer, s2.original_ssn from pick_item_detail p2 ";
	$Query .= "join issn s2 on s2.ssn_id = p2.ssn_id ";
	$Query .= "join  control c3 on c3.record_id = 1 ";
	$Query .= " where p2.pick_label_no = '" . $label_no . "'";
	$Query .= " and s2.other2 starting 'Split'";
	$Query .= " and s2.label_date is null";

	$Query = "select p2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id, c3.default_pick_printer, s2.original_ssn, w1.default_pick_printer from pick_item_detail p2 ";
	$Query .= "join issn s2 on s2.ssn_id = p2.ssn_id ";
	$Query .= "join  control c3 on c3.record_id = 1 ";
	$Query .= "join  pick_item p3 on p2.pick_label_no = p3.pick_label_no ";
	$Query .= "join  pick_order p4 on p3.pick_order  =  p4.pick_order ";
	$Query .= "left outer join  warehouse w1 on p4.wh_id =  w1.wh_id  ";
	$Query .= " where p2.pick_label_no = '" . $label_no . "'";
	$Query .= " and s2.other2 starting 'Split'";
	$Query .= " and s2.label_date is null";
	//print($Query);
	
	$wk_split_ssn = "";

	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read SSNs!<BR>\n");
		exit();
	}
	else
	while (($Row = ibase_fetch_row($Result)))
	{
		$wk_split_ssn =  $Row[0];
		$wk_sp_prod =  $Row[1];
		$wk_sp_other1 =  $Row[2];
		$wk_sp_other2 =  $Row[3];
		$wk_sp_qty =  $Row[4];
		$wk_sp_comp =  $Row[5];
		$wk_sp_printer =  $Row[6];
		$wk_sp_original_ssn =  $Row[7];
		$wk_sp_wh_printer  =  $Row[8];
		//include "checkdata.php";
                require_once 'checkdata.php';

		// ok print a label now - probably only one
		if (($wk_sp_wh_printer <> '') and ($wk_sp_wh_printer <> ' '))
		{
			// have a default printer for the orders wh
			$wk_sp_printer = $wk_sp_wh_printer;
		}
		if ($scanned_printer == "")
		{
			$printer_Id = $wk_sp_printer;
		}
		else
		{
			$printer_Id = $scanned_printer;
			$field_type = checkForTypein($printer_Id, 'DEVICE' ); 
			if ($field_type <> "none")
			{
				$printer_Id = substr($printer_Id, $startposn);
			}
		}
		logme($Link, $tran_user, $tran_device, "printer is " . $printer_Id);
		$printer_Id = $wk_sp_printer;
		$printer_Ip = getPrinterIp($Link, $printer_Id);
		/*
		if ($printer_Ip == "")
		{
			// use the default cause no ip for that printer
			$printer_Id = $wk_sp_printer;
			$printer_Ip = getPrinterIp($Link, $printer_Id);
		}
		*/
		logme($Link, $tran_user, $tran_device, "after check ip printer is " . $printer_Id);
		$productInfo = getProductInfo($Link, $wk_sp_prod) ;

		/*
	dont print this label - split any more
		PrintIssnLabel($Link, $printer_Id, $printer_Ip, $wk_split_ssn, $wk_sp_qty, $wk_sp_other1, $wk_sp_other2, $wk_sp_prod, $wk_sp_comp, $productInfo, $wk_sp_original_ssn) ;
		// then update the label printed date
		$Query1 = "update issn set label_date = 'NOW' where ssn_id = '" . $wk_split_ssn . "'";
		if (!($Result1 = ibase_query($Link, $Query1)))
		{
			print("Unable to Update SSN!<BR>\n");
		}
		//else
		//	$Row1 = ibase_fetch_row($Result1);
		*/
		// now reprint the original issn's label
		list($wk_txt_1, $wk_sp_orig_ssn, $wk_txt_2, $wk_sp_order) = explode(" ", $wk_sp_other2 . "    ");

		$Query2 = "select s2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id from issn s2  ";
		$Query2 .= " where s2.ssn_id = '" . $scanned_ssn . "'";
		//print($Query2);
	
		$wk_orig_ssn = "";

		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			print("Unable to Read SSNs!<BR>\n");
			exit();
		}
		else
		while (($Row = ibase_fetch_row($Result2)))
		{
			$wk_orig_ssn =  $Row[0];
			// its other1
			// qty
			$wk_sp_prod =  $Row[1];
			$wk_sp_other1 =  $Row[2];
			$wk_sp_other2 =  $Row[3];
			$wk_sp_qty =  $Row[4];
			$wk_sp_comp =  $Row[5];
			PrintIssnLabel($Link, $printer_Id, $printer_Ip, $wk_orig_ssn, $wk_sp_qty, $wk_sp_other1, $wk_sp_other2, $wk_sp_prod, $wk_sp_comp, $productInfo, $wk_sp_original_ssn) ;
			// then update the label printed date
			$Query1 = "update issn set label_date = 'NOW' where ssn_id = '" . $wk_orig_ssn . "'";
			if (!($Result1 = ibase_query($Link, $Query1)))
			{
				print("Unable to Update SSN!<BR>\n");
			}
			//else
			//	$Row1 = ibase_fetch_row($Result1);
		}

		//release memory
		//ibase_free_result($Result1);
	}
	
	//release memory
	ibase_free_result($Result);
	//====================================================
	$Query = "select pi_legacy_lineno,pick_order_line_no from pick_item ";
	$Query .= " where pick_label_no = '" . $label_no . "'";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$legacyLine = '';
	$pickLine = '';
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Legacy LineNo!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$legacyLine = $Row[0];
		$pickLine = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
	
	$Query = "select 1 from pick_item ";
	$Query .= " where pick_label_no = '" . $label_no . "'";
	$Query .= " and pick_line_status = 'PG'";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$continue_this_line = 0;
	$continue_cnt = 0;

	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$continue_this_line =  $Row[0];
		$continue_cnt = 1;
	}
	
	//release memory
	ibase_free_result($Result);
	logme($Link, $tran_user, $tran_device, "continue:" . $continue_cnt );
	//commit
	ibase_commit($dbTran);
	if ($continue_cnt == 0)
	{
		$Query = "select first 1 1, pick_label_no from pick_item ";
		$Query .= " where pick_line_status in ('AL', 'PG')";
		$Query .= " and device_id = '".$tran_device."'";
		//print($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to Read Pick Item!<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$continue_cnt = 1;
			$continue_label = $Row[1];
		}
	
		//release memory
		ibase_free_result($Result);
	}
	logme($Link, $tran_user, $tran_device, "continue:" . $continue_cnt );

	$Query = "select first 1 1, pick_label_no from pick_item ";
	$Query .= " where pick_line_status in ('PL')";
	$Query .= " and device_id = '".$tran_device."'";
	//print($Query);
	
	$despatch_cnt = 0;
	if (!($Result = ibase_query($Link, $Query)))
	{
		print("Unable to Read Total!<BR>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$despatch_cnt =  1;
		$despatch_label = $Row[1];
	}
	
	//release memory
	ibase_free_result($Result);
	
	logme($Link, $tran_user, $tran_device, "despatch:" . $despatch_cnt );
	// do prod id from pick item

	$end_prod = 0;

	logme($Link, $tran_user, $tran_device, "prod:" . $prod_no );
	if ($prod_no <> '')
	{
		$Query = "select 1 from pick_item ";
		$Query .= " where prod_id = '" . $prod_no . "'";
		$Query .= " and pick_line_status in ('AL','PG')";
		$Query .= " and device_id = '".$tran_device."'";
		if ($legacyLine != '')
		{
			$Query .= " and pi_legacy_lineno = '".$legacyLine."'";
		}
		//print($Query);
		if ($continue_cnt > 0)
		{
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to Read Total!<BR>\n");
				exit();
			}
			else
			if (($Row = ibase_fetch_row($Result)))
			{
				$end_prod =  1;
			}
	
			//release memory
			ibase_free_result($Result);
		}
	logme($Link, $tran_user, $tran_device, "endprod:" . $end_prod  . "after check prod_no");
	}
	else
	{
		// ok an issn or ssn
		// if an issn then end at the prod of the issn
		// if an ssn then end at the ssn
		$Query = "select prod_id from issn ";
		$Query .= " where ssn_id = '" . $scanned_ssn . "'";
		//print($Query);
		if ($continue_cnt > 0)
		{
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to Read ISSN!<BR>\n");
				exit();
			}
			else
			if (($Row = ibase_fetch_row($Result)))
			{
				$ssn_prod =  $Row[0];
			}
	
			//release memory
			ibase_free_result($Result);
		}
	logme($Link, $tran_user, $tran_device, "continue:" . $continue_cnt  . " ssn prod:" . $ssn_prod);
	}
	
	// do prod id from issn
	if ($end_prod == 0)
	{
		if ($continue_cnt > 0)
		{
			if ($prod_no <> '')
			{
				$Query = "select 1 from pick_item ";
				$Query .= " join issn on issn.ssn_id  = pick_item.ssn_id " ;
				$Query .= " where issn.prod_id = '" . $prod_no . "'";
				$Query .= " and pick_item.pick_line_status in ('AL','PG')";
				$Query .= " and pick_item.device_id = '".$tran_device."'";
				if ($legacyLine != '')
				{
					$Query .= " and pick_item.pi_legacy_lineno = '".$legacyLine."'";
				}
			}
			else
			{
				$Query = "select 1 from pick_item ";
				$Query .= " join issn on issn.ssn_id  = pick_item.ssn_id " ;
				$Query .= " where issn.prod_id = '" . $ssn_prod . "'";
				$Query .= " and pick_item.pick_line_status in ('AL','PG')";
				$Query .= " and pick_item.device_id = '".$tran_device."'";
				if ($legacyLine != '')
				{
					$Query .= " and pick_item.pi_legacy_lineno = '".$legacyLine."'";
				}
				$Query .= " union select 1 from pick_item ";
				$Query .= " where pick_item.prod_id = '" . $ssn_prod . "'";
				$Query .= " and pick_item.pick_line_status in ('AL','PG')";
				$Query .= " and pick_item.device_id = '".$tran_device."'";
				if ($legacyLine != '')
				{
					$Query .= " and pick_item.pi_legacy_lineno = '".$legacyLine."'";
				}
				$Query .= " union select 1 from pick_item ";
				$Query .= " join ssn on ssn.ssn_id  = pick_item.ssn_id " ;
				$Query .= " join issn on issn.original_ssn  = ssn.ssn_id " ;
				$Query .= " where issn.prod_id = '" . $ssn_prod . "'";
				$Query .= " and pick_item.pick_line_status in ('AL','PG')";
				$Query .= " and pick_item.device_id = '".$tran_device."'";
				if ($legacyLine != '')
				{
					$Query .= " and pick_item.pi_legacy_lineno = '".$legacyLine."'";
				}
			}
			print($Query);

			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to Read Total!<BR>\n");
				exit();
			}
			else
			if (($Row = ibase_fetch_row($Result)))
			{
				$end_prod =  1;
			}
	
			//release memory
			ibase_free_result($Result);
		}
	}
	
	//commit
	//ibase_commit($dbTran);
	//echo("continue line " . $continue_this_line . " cont " . $continue_cnt . " cont label " . $continue_label . " despatch " . $despatch_cnt . " label " . $despatch_label . "L" . " endProd " . $end_prod . "LegLine " . $legacyLine);
//logme($Link, $tran_user, $tran_device, "continue line " . $continue_this_line . " cont " . $continue_cnt . " cont label " . $continue_label . " despatch " . $despatch_cnt . " label " . $despatch_label . "L" . " endProd " . $end_prod . "LegLine " . $legacyLine);
	
	//close
	//ibase_close($Link);

	//want to go to pick screen
	//header("Location: transactionUA.php");
	//want to go to pick screen
	// if this current line is still pg then go to ssn
	//
	// if end of product go to addrprodlabel
	$wk_next_screen = "";
	if ($continue_cnt > 0)
	{
		//header("Location: getfromlocn.php");
		$wk_next_screen = "getfromlocn.php";
		if ($end_prod == 0)
		{
			// what about the pkil for this product
                    	setBDCScookie($Link, $tran_device, "PickNextScreen", $wk_next_screen);
			//header("Location: addrprodlabel.php" );
			header("Location: confirmto.php?order=" . urlencode($order));
		}       
		else
		{
			header("Location: " . $wk_next_screen );
		}
	}
	else
	{
		if ($despatch_cnt > 0)
		{
			// go to confirm pick despatch
			// else cancel
			// if confirm pick despatch
			// must pick despatch all to default locn
			// then go to despatch menu ...
			$wk_next_screen = "pick_Menu.php";
             		setBDCScookie($Link, $tran_device, "PickNextScreen", $wk_next_screen);
			header("Location: confirmto.php?order=" . urlencode($order));
		}
		else
		{
			//header("Location: pick_Menu.php");
			$wk_next_screen = "pick_Menu.php";
             		setBDCScookie($Link, $tran_device, "PickNextScreen", $wk_next_screen);
			header("Location: addrprodlabel.php" );
		}
		// must be end of product
	}
	logme($Link, $tran_user, $tran_device, "end of screen PKOL");
}
?>
