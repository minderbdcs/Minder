<?php
include "../login.inc";

require_once 'DB.php';
require 'db_access.php';
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:

	include 'logme.php';
	
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	
function logtime2( $Link,  $message)
{
	$Query = "";
	$log = fopen('/data/tmp/querySSN.log' , 'a');
		$wk_current_time = "";
		$Query = "select cast(cast('NOW' as timestamp) as char(24)) from control ";
		$Query = "select cast('NOW' as timestamp) from control ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table control<BR>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$wk_current_time =  $Row[0];
		}
		else
		{
			$wk_current_time = "";
		}
		
		//release memory
		//$Result->free();
		//ibase_free_result($Result);
		$wk_current_micro = microtime();
		$wk_current_time .= " " . $wk_current_micro;
		$wk_message = str_replace("'", "`", $message);
		
		// 1st try ip address of handheld
	if (isset($_COOKIE['LoginUser']))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
		$userline = sprintf("%s  by %s on %s", $wk_current_time, $tran_user, $tran_device);
	} else {
		$userline = sprintf("%s  ", $wk_current_time );
	}

	fwrite($log, $userline);
	fwrite($log, $message);
	fwrite($log,"  ");
	fwrite($log,"  \n");
	fclose($log);
}

if (isset($_POST['location']))
{
	$location = $_POST['location'];
	setBDCScookie($Link, $tran_device, "location", $location);
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
	setBDCScookie($Link, $tran_device, "location", $location);
}
if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}
if (isset($_POST['device']))
{
	$wk_device = $_POST['device'];
}
if (isset($_GET['device']))
{
	$wk_device = $_GET['device'];
}
$message = "";
if (!isset($location))
{
	$location = getBDCScookie($Link, $tran_device, "location" );
}
if (isset($location))
{
	logtime2( $Link,  $location);
	$wk_len = strlen($location);
	logtime2( $Link,  $wk_len);
        // trim it
	$location = trim($location);
	logtime2( $Link,  $location);
	$wk_len = strlen($location);
	logtime2( $Link,  $wk_len);

/*
	//if ( (strlen($location) == 8) and (isint($location)) )
	if ( (strlen($location) == 8) )
	{
		//header ("Location: 1ssn.php?ssn_id=".$location);
		header ("Location: 1ssn.php?ssn_id=".urlencode($location));
	}
	if ( (strlen($location) == 6) or (strlen($location) == 13) or (strlen($location) == 14) or (strlen($location) == 15) or (strlen($location) == 18) )
	{
		//header ("Location: product.php?product=".$location);
		header ("Location: product.php?product=".urlencode($location));
	}
*/
	include "checkdata.php";

	$field_type = checkForTypein($location, 'BARCODE','SSN' ); 
	if ($field_type == "none")
	{
		logtime2( $Link,  "not a BARCODE");
		// not an ssn - perhaps an altbarcode
		$field_type = checkForTypein($location, 'ALTBARCODE' ); 
		if ($field_type == "none")
		{
			logtime2( $Link,  "not a ALTBARCODE");
			// not an ssn - perhaps a product
			$field_type = checkForTypein($location, 'PROD_13' ); 
			if ($field_type == "none")
			{
				logtime2( $Link,  "not a PROD_13");
				// not a prod 13 - perhaps a location
				$field_type = checkForTypein($location, 'LOCATION' ); 
				if ($field_type != "none")
				{
					logtime2( $Link,  "a LOCATION");
					// a location
					if ($startposn > 0)
					{
						$wk_realdata = substr($location,$startposn);
						$location = $wk_realdata;
					}
				}
				else
				{
					// not a location - perhaps a device 
					$field_type = checkForTypein($location, 'DEVICE' ); 
					if ($field_type == "none")
					{
						logtime2( $Link,  "not a DEVICE");
						// not a device try prod internal 
						$field_type = checkForTypein($location, 'PROD_INTERNAL' ); 
						if ($field_type == "none")
						{
							logtime2( $Link,  "not a PROD_INTERNAL");
							// not a product internal try alt prod internal
							$field_type = checkForTypein($location, 'ALT_PROD_INTERNAL' ); 
							if ($field_type == "none")
							{
								logtime2( $Link,  "not a ALT_PROD_INTERNAL");
								// not any of the allowed types
								//header ("Location: getlocn.php?message=" . urlencode( "Not an SSN, Location, Device or Product"));
								header ("Location: getlocn.php?message=" . urlencode( "Not an SSN, Location, Device or Product: [$location] Len $wk_len"));
								exit();
							}
							else
							{
								logtime2( $Link,  " a ALT_PROD_INTERNAL");
								// an alt prod internal 
								if ($startposn > 0)
								{
									$wk_realdata = substr($location,$startposn);
									$location = $wk_realdata;
								}
								//header ("Location: product.php?product=".$location);
								header ("Location: product.php?product=".urlencode($location));
								exit();
							}
						}
						else
						{
							logtime2( $Link,  " a PROD_INTERNAL");
							// a prod internal 
							if ($startposn > 0)
							{
								$wk_realdata = substr($location,$startposn);
								$location = $wk_realdata;
							}
							//header ("Location: product.php?product=".$location);
							header ("Location: product.php?product=".urlencode($location));
							exit();
						}
					}
					else
					{
						logtime2( $Link,  " a DEVICE");
						// a device
						$wk_device = "T";
						if ($startposn > 0)
						{
							$wk_realdata = substr($location,$startposn);
							$location = $wk_realdata;
						}
					}
				}
			}
			else
			{
				logtime2( $Link,  " a PROD_13");
				// a prod_13
				if ($startposn > 0)
				{
					$wk_realdata = substr($location,$startposn);
					$location = $wk_realdata;
				}
				//header ("Location: product.php?product=".$location);
				header ("Location: product.php?product=".urlencode($location));
				exit();
			}
		}
		else
		{
			logtime2( $Link,  " a ALTBARCODE");
			// an alt barcode
			if ($startposn > 0)
			{
				$wk_realdata = substr($location,$startposn);
				$location = $wk_realdata;
			}
			//header ("Location: 1ssn.php?ssn_id=".$location);
			header ("Location: 1ssn.php?ssn_id=".urlencode($location));
			exit();
		}
	}
	else
	{
		logtime2( $Link,  " a BARCODE");
		// a barcode
		if ($startposn > 0)
		{
			$wk_realdata = substr($location,$startposn);
			$location = $wk_realdata;
		}
		//header ("Location: 1ssn.php?ssn_id=".$location);
		header ("Location: 1ssn.php?ssn_id=".urlencode($location));
		exit();
	}
	$wk_locn_id = $location;
}
?>
<html>
<head>
<title>Retrieving SSNs</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
<link rel=stylesheet type="text/css" href="fromlocn.css">
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
</head>
<body>
<?php
include "2buttons.php";
if (isset($_POST['ssn_id'])) 
{
	$lastssn = $_POST["ssn_id"];
	setBDCScookie($Link, $tran_device, "ssn_id", $lastssn);
}
else
if (isset($_GET['ssn_id'])) 
{
	$lastssn = $_GET["ssn_id"];
	setBDCScookie($Link, $tran_device, "ssn_id", $lastssn);
}
else
{
	$lastssn = "";
	$lastssn = getBDCScookie($Link, $tran_device, "ssn_id" );
}
if (isset($_POST['printer'])) 
{
	$wk_printer = $_POST["printer"];
}
else
if (isset($_GET['printer'])) 
{
	$wk_printer = $_GET["printer"];
}
else
{
	$wk_printer = "";
}
//$Link = DB::connect($dsn,true);
//if (DB::isError($Link))
//{
//	echo("Unable to Connect!<BR>\n");
//	echo($Link->getMessage());
//	exit();
//}
/*
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:
*/


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


/**
 * getProductInfo
 *
 * @param $Link
 */
function getProductInfo($Link, $product_id) {
    $sql = 'SELECT PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC, UOM.CODE, UOM.DESCRIPTION, PROD_PROFILE.TEMPERATURE_ZONE FROM PROD_PROFILE JOIN UOM ON PROD_PROFILE.UOM = UOM.CODE WHERE PROD_PROFILE.PROD_ID = ?';
    $productInfo = array();
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $product_id);
        if ($r) {
            while (($row = ibase_fetch_row($r))) {
                $productInfo[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $productInfo;
}


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
 * PrintIssnLabel
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function PrintIssnLabel($Link, $printerId, $printerIp, $ssn_id, $qty, $other1, $other2, $product_id, $company_id, $productInfo, $other3) {
/*
need printerIp
     printerId
     owned_by
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
	            //include "checkdata.php";
                    require_once 'checkdata.php';
                    $p = new Printer($printerIp);
                    $p->data['printer_id'] = $printerId;
                    $p->data['ownerid'] = $company_id;
                    $p->data['issn'] = $ssn_id;
                    $p->data['issnlabelprefix'] = substr($ssn_id,0,2);
                    $p->data['issnlabelsuffix'] = substr($ssn_id,2,strlen($ssn_id) - 2);
                    $p->data['qty'] = $qty;

                    $p->data['palletno'] = $other1;
                    $p->data['parentid'] = $other2;
                    //$loginUser = split('\|', $_COOKIE['LoginUser']);
                    $loginUser = explode("|", $_COOKIE["LoginUser"]);

                    $p->data['userid'] = $loginUser[0];
                    if (isset($productInfo[$product_id][0])) {
                        $p->data['description'] = $productInfo[$product_id][0];
                    } else {
                        $p->data['description'] = '';
                    }
                    $p->data['product_id'] = $product_id;
                    if (isset($productInfo[$product_id][2])) {
                        $p->data['um'] = $productInfo[$product_id][2];
                    } else {
                        $p->data['um'] = '';
                    }
                    $p->data['now'] = date('d/m/y H:i:s');
                    if (isset($productInfo[$product_id][3])) {
                        $p->data['tempzone'] = $productInfo[$product_id][3];
                    } else {
                        $p->data['tempzone'] = '';
                    }
                    //$p->data['other3'] = "";
                    $p->data['other3'] = $other3;
                    $p->data['pickorder'] = "";
                    $p->data['ISSN.SSN_ID'] = $p->data['issn'];
                    $p->data['ISSN.CURRENT_QTY'] = $p->data['qty'] ;
                    $p->data['ISSN.CREATED_BY'] = $p->data['userid'] ;
                    $p->data['ISSN.PROD_ID'] = $p->data['product_id'] ;
                    $p->data['PROD_PROFILE.SHORT_DESC'] = $p->data['description'] ;
                    $p->data['PROD_PROFILE.PROD_ID'] = $p->data['product_id'] ;
                    $p->data['UOM.CODE'] = $p->data['um'] ;
                    $p->data['ISSN.CREATE_DATE'] = $p->data['now'] ;
                    $p->data['PROD_PROFILE.TEMPERATURE_ZONE'] = $p->data['tempzone'] ;
                    $p->data['title_1'] = "";
                    $p->data['version'] = "";
                    $q = getISSNLabel($Link, $p );
                    $printerDir = getPrinterDir($Link, $printerId);
                    //$tpl = file_get_contents('../receive/ISSN-new.prn');
                    $tpl = '';
                    //$tpl2 = file_get_contents('../receive/ISSN-old.prn');
                    $tpl2 = '';
                    //-- proceed while not printed all labels in 1st set
                    //$save = fopen('/data/asset.rf/' . $printerId .
                    //$savefile = '/tmp/' . $p->data['issn'] . '_ISSN.prn';
                    //$savefile = '/data/asset.rf/' . $printerId . "/" . $tran_device .  '_ISSN.prn';
                    //$savefile = '/data/asset.rf/' . $printerId . "/" . $tran_device . $p->data['issn'] .   '_ISSN.prn';
                    //$save = fopen($savefile, 'w');
                    //$field_type = checkForTypein($ssn_id, 'BARCODE' ); 
                    //if ($field_type <> "none")
                    //{
                    //    // is a new barcode
                    //    $p->send($tpl, $save);
                    //    //$p->save($tpl, $save);
                    //    //$ssn_id++;
                    //}
                    //else
                    //{
                    //    // is an old altbarcode
                    //    $p->send($tpl2, $save);
                    //    //$p->save($tpl2, $save);
                    //    //$ssn_id++;
                    //}
                    //$save = fopen($printerDir .
                    //	$p->data['issn'] . '_ISSN.prn', 'w');
                    if (!$p->sysLabel($Link, $printerId, "ISSN", 1))
                    {
                    	//$p->send($tpl, $save);
                    }

                    //fclose($save);
}

// ****************************************************************************************************88
// if printer is not null print the location
if (!is_null($wk_printer))
{


	$wk_message = "";
	
	$wk_locn_exists = "";
	//$wk_desc = "";
	if (isset($wk_locn_id))
	{
		// check that the locn exists and the labeltype list
		$Query4 = "SELECT first 1 locn_name FROM location where wh_id = '" . substr($wk_locn_id,0,2) . "' and locn_id like '";
		$Query4 .= substr($wk_locn_id,2) . "'";
		if (!($Result4 = ibase_query($Link, $Query4)))
		{
			echo("Unable to Read Location!<BR>\n");
			exit();
		}
		if ( ($Row5 = ibase_fetch_row($Result4)) ) 
		{
			$wk_locn_exists = "Y";
			$wk_desc = $Row5[0];
		}
		//release memory
		ibase_free_result($Result4);
	}
	if (isset($wk_locn_id))
	{
		if ($wk_locn_exists == "")
		{
			$wk_locn_id = "";
	  		$wk_message = "Location Dosn't Exist ";
		}
	}
	//if (($wk_locn_id <>"") and ($wk_copys <> "") and ($wk_label_qty <> "") and ($wk_per_label_qty <> "") and ($wk_printer <> "") and ($wk_image_x > 0) and ($wk_image_y > 0))
	if (($wk_locn_id <>"") and ($wk_printer <> "") )
	{
		$printer_Id = $wk_printer;
		$printer_Ip = getPrinterIp($Link, $printer_Id);
		// now reprint the original issn's label
	
		$Query2 = "select s2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id, s2.other4 from issn s2  ";
		$Query2 .= " where s2.wh_id = '" . substr($wk_locn_id,0,2) . "' and locn_id like '";
		$Query2 .= substr($wk_locn_id,2) . "'";
		$Query2 .= " and s2.issn_status in ('PA','ST','QR')";
		$Query2 .= " order by s2.wh_id, s2.locn_id, s2.original_ssn, s2.ssn_id ";
		//print($Query2);
	
		$wk_orig_ssn = "";
	
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read SSNs!<BR>\n");
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
			$productInfo = getProductInfo($Link, $wk_sp_prod) ;
			$other3 = $Row[6];
			//for ($wk_printed_cnt = 0; $wk_printed_cnt < $wk_label_qty; $wk_printed_cnt++)
			{
				PrintIssnLabel($Link, $printer_Id, $printer_Ip, $wk_orig_ssn, $wk_sp_qty, $wk_sp_other1, $wk_sp_other2, $wk_sp_prod, $wk_sp_comp, $productInfo, $other3) ;
			}
			// then update the label printed date
			$Query1 = "update issn set label_date = 'NOW' where ssn_id = '" . $wk_orig_ssn . "'";
			if (!($Result1 = ibase_query($Link, $Query1)))
			{
				echo("Unable to Update SSN!<BR>\n");
			}
		}
	
			//release memory
			//ibase_free_result($Result1);
	}
	//release memory
	//ibase_free_result($Result);

}
$TableName = "issn";
$rcount = 0;
echo("<table>");
echo ("<tr>\n");
echo("<th>");
//echo("Objects in ".$_POST["location"]);
echo($_POST["location"]);
//$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wk_moveable = "None";
$wk_locnType = "None";
if (isset($wk_device))
{
}
else
{
	$Query4 = "SELECT moveable_locn, locn_type from location where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" ;
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to query Location Type!<BR>\n");
		exit();
	}
	else
	if (($Row4 = ibase_fetch_row($Result4)))
	{
		$wk_moveable = $Row4[0];
		$wk_locnType = $Row4[1];
	}
	//release memory
	ibase_free_result($Result4);
}

if (isset($wk_device))
{
	$Query = "SELECT count(*) from ".$TableName." where locn_id = '".$location."'" ;
	$Query = "SELECT count(*), sum(current_qty) from ".$TableName." where locn_id = '".$location."'" ;
}
else
{
	$Query = "SELECT count(*) from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" ;
	$Query = "SELECT count(*), sum(current_qty) from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" ;
}
$Query .= " and current_qty <> 0";
if (isset($product))
{
	$Query .= " and prod_id='" . $product . "'";
}
$qty_total = 0;
$sum_qty_total = 0;
$prod_total = 0;

if (!($Result3 = ibase_query($Link, $Query)))
{
	echo("Unable to query icount of issn!<BR>\n");
	exit();
}
else
if (($Row = ibase_fetch_row($Result3)))
{
	$qty_total = $Row[0];
	$sum_qty_total = $Row[1];
}
//release memory
ibase_free_result($Result3);
if (isset($wk_device))
{
	$Query = "SELECT count(distinct prod_id) from ".$TableName." where locn_id = '".$location."'"  ;
}
else
{
	$Query = "SELECT count(distinct prod_id) from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" ;
}
$Query .= " and current_qty <> 0";
if (isset($product))
{
	$Query .= " and prod_id='" . $product . "'";
}
if (!($Result3 = ibase_query($Link, $Query)))
{
	echo("Unable to query icount of issn!<BR>\n");
	exit();
}
else
if (($Row = ibase_fetch_row($Result3)))
{
	$prod_total = $Row[0];
}
//release memory
ibase_free_result($Result3);
echo(" # ".$qty_total);
echo(" Qty ".$sum_qty_total);
echo(" Prods ".$prod_total);
echo("</th>");
echo ("</tr>\n");
echo("</table>");

/* $Query = "SELECT * from ".$TableName." where locn_id = '".$_POST["location"]."'"; */

if (isset($product))
{
	if (isset($wk_device))
	{
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id ,company_id from ".$TableName." where locn_id = '".$location .  "' AND ssn_id > '".$lastssn."' and prod_id='" . $product . "'  ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, company_id from ".$TableName." where locn_id = '".$location .  "' AND ssn_id > '".$lastssn."' and prod_id='" . $product . "'  ";
	}
	else
	{
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id, company_id  from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" .  "AND ssn_id > '".$lastssn."' and prod_id='" . $product . "'  ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, company_id  from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" .  "AND ssn_id > '".$lastssn."' and prod_id='" . $product . "'  ";
	}
	$Query .= " and current_qty <> 0";
	$Query .= " order by ssn_id ";
}
else
{
	if (isset($wk_device))
	{
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id ,company_id from ".$TableName." where locn_id = '".$location."'" .  "AND ssn_id > '".$lastssn."' order by ssn_id ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id ,company_id from ".$TableName." where locn_id = '".$location."'" .  "AND ssn_id > '".$lastssn."'  ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id ,company_id from ".$TableName." where locn_id = '".$location."'" .  " AND ssn_id > '".$lastssn."'  ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, company_id from ".$TableName." where locn_id = '".$location."'" .  " AND ssn_id > '".$lastssn."'  ";
	}
	else
	{
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id , company_id from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" .  "AND ssn_id > '".$lastssn."' order by ssn_id ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, prev_prev_wh_id, prev_prev_locn_id , company_id from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" .  "AND ssn_id > '".$lastssn."'  ";
		$Query = "SELECT ssn_id, prod_id, current_qty, issn_status, prev_wh_id, prev_locn_id, company_id from ".$TableName." where wh_id = '".substr($location,0,2)."' AND locn_id = '".substr($location,2,strlen($location)-2)."'" .  "AND ssn_id > '".$lastssn."'  ";
	}
	$Query .= " and current_qty <> 0";
	$Query .= " order by ssn_id ";
}
//echo($Query);
// Create a table.
//echo("<BR>\n");
echo ("<table BORDER=\"1\">\n");

//$Result = $Link->query($Query);
//if (DB::isError($Result))
//{
//	echo("Unable to query issn!<BR>\n");
//	exit();
//}
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query issn!<BR>\n");
	exit();
}
// echo headers
echo ("<tr>\n");
{
	echo("<th>SSN</th>\n");
	echo("<th>Prod</th>\n");
	echo("<th>Qty</th>\n");
	echo("<th>St</th>\n");
	echo("<th>P WH</th>\n");
	echo("<th>P Locn</th>\n");
	//echo("<th>PP WH</th>\n");
	//echo("<th>PP Locn</th>\n");
	echo("<th>Company</th>\n");
}
echo ("</tr>\n");

// Fetch the results from the database.
//while ( ($Row = $Result->fetchRow())  and ($rcount < 5) ) {
// 	echo ("<TR>\n");
//	//for ($i=0; $i<ibase_num_fields($Result); $i++)
//	for ($i=0; $i<$Result->numCols(); $i++)
while ( ($Row = ibase_fetch_row($Result)) and ($rcount < 5) ) 
{
 	echo ("<tr>\n");
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($i == 0)
		{

			echo("<td>");
			echo("<form action=\"1ssn.php\" method=\"post\" name=getssn>\n");
			echo("<input type=\"submit\" name=\"ssn_id\" value=\"$Row[$i]\">\n");
			echo("</form>\n");
			echo("</td>");
			$lastssn = $Row[$i];
		}
		else
		{
 			echo ("<td>$Row[$i]</td>\n");
		}
	}
 	echo ("</tr>\n");
	$rcount++;
}
echo ("</table>\n");

//release memory
//$Result->free();
ibase_free_result($Result);

//commit
//$Link->commit();
//ibase_commit($dbTran);

//close
//$Link->disconnect();
//ibase_close($Link);

 echo("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo (" <form action=\"ssn.php\" method=\"post\" name=showssn>");
echo (" <P>");
echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$lastssn."\"> ");
echo ("<input type=\"hidden\" name=\"location\" value=\"".$location."\"> ");
if (isset($product))
{
echo ("<input type=\"hidden\" name=\"product\" value=\"".$product."\"> ");
}
if (isset($wk_device))
{
echo ("<input type=\"hidden\" name=\"device\" value=\"".$wk_device."\"> ");
}
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<input type=\"submit\" name=\"more\" value=\"More!\">\n");
	echo("</form>\n");
	echo("<form action=\"getlocn.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('More', 'getlocn.php');
	//whm2buttons('More',"getlocn.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"more.gif");
	//whm2buttons('More',"getlocn.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"more.gif","N");
	whm2buttons('More',"getlocn.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"more.gif","N","Y");
	// show orders for locations  
	if ($wk_moveable == "T" and ($wk_locnType == "PL" or $wk_locnType == ""))
	{
		$alt = "Show Shipping Orders";
		echo ("<td>");
		echo("<form action=\"getorder.php\" method=\"post\" name=\"showorder\">\n");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\">");
		echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$lastssn."\"> ");
		echo ("<input type=\"hidden\" name=\"location\" value=\"".$location."\"> ");
		if (isset($product))
		{
			echo ("<input type=\"hidden\" name=\"product\" value=\"".$product."\"> ");
		}
		if (isset($wk_device))
		{
			echo ("<input type=\"hidden\" name=\"device\" value=\"".$wk_device."\"> ");
		}
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("<tr>");
	{
		// printer to use
		echo ("<td>");
		echo("<form action=\"ssn.php\" method=\"post\" name=\"printlocn\">\n");
		echo ("<select name=\"printer\" > ");
		$Query = "SELECT device_id FROM sys_equip WHERE device_type = 'PR' ORDER BY device_id ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read sys_equip!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			echo ("<OPTION value=\"" . $Row[0] . "\"");
			if ($wk_printer == "")
			{
				$wk_printer = $Row[0];
			}
			if ($wk_printer == $Row[0])
			{
				echo(" selected ");
			}
			echo(">" . $Row[0] . "</OPTION>");
		}
		//release memory
		ibase_free_result($Result);
		echo ("</select>");
		echo ("</td>");
		// add button to print locations issn labels
		$alt = "Print ISSNs Labels";
		echo ("<td>");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\">");
		echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$lastssn."\"> ");
		echo ("<input type=\"hidden\" name=\"location\" value=\"".$location."\"> ");
		if (isset($product))
		{
			echo ("<input type=\"hidden\" name=\"product\" value=\"".$product."\"> ");
		}
		if (isset($wk_device))
		{
			echo ("<input type=\"hidden\" name=\"device\" value=\"".$wk_device."\"> ");
		}
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("</table>");
/*
	echo("<BUTTON name=\"more\" value=\"More!\" type=\"submit\">\n");
	echo("More<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</form>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
//release memory
//$Result->free();
ibase_free_result($Result);

//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
ibase_close($Link);

?>
<script type="text/javascript">
document.showssn.more.focus();
</script>
</body>
</html>

