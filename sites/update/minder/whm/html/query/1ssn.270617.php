<?php
include "../login.inc";
?>
<html>
<head>
<title>ISSN Query</title>
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
 <body BGCOLOR="#FFFFFF">
<?php
require_once 'DB.php';
require 'db_access.php';
if (isset($_POST['ssn_id'])) 
{
	$ssn_id = $_POST["ssn_id"];
}
if (isset($_GET['ssn_id'])) 
{
	$ssn_id = $_GET["ssn_id"];
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
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
//echo($dsn);
//$Link = DB::connect($dsn,true);
//if (DB::isError($Link))
//{
//	echo("Unable to Connect!<BR>\n");
//	echo($Link->getMessage());
//	exit();
//}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/* get my offset from utc */
$wk_my_tz = $_SESSION['TZ'];
$wk_db_tz = $_SESSION['DBTZ'];
if ($wk_db_tz == "")
{
	$wk_db_tz = "UTC";
	$_SESSION['DBTZ'] = $wk_db_tz;
}
if ($wk_my_tz == "")
{
	$mdrTimeZone = $mdr['timezone'];
	$wk_my_tz = $mdrTimeZone;
	$_SESSION['TZ'] = $wk_my_tz;
}
$wkdateTimeZoneMy = new DateTimeZone($wk_my_tz);
$wkdateTimeZoneDB = new DateTimeZone($wk_db_tz);
$wkdateTimeDB = new DateTime("now", $wkdateTimeZoneDB);
//$wktimeOffset = $wkdateTimeZoneMy->getOffset($wkdateTimeDB);
//var_dump($wktimeOffset);
//$_SESSION['TZOFFSET'] = $wktimeOffset ;
 $wktimeOffset = $_SESSION['TZOFFSET']  ;
//var_dump($wktimeOffset);

// Set the variables for the database access:
// for printing what is the location to use 

/* functions */
/**
 * check for Query Options
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return string
 */
function getQueryOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='QUERY'  and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/data/tmp/1ssn.log' , 'a');
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
	return $wk_data ;
} // end of function

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
                    $tpl = '';
                    $tpl2 = '';
                    //-- proceed while not printed all labels in 1st set
                    //$save = fopen($printerDir .
                    //	$p->data['issn'] . '_ISSN.prn', 'w');
                    if (!$p->sysLabel($Link, $printerId, "ISSN", 1))
                    {
                    	//$p->send($tpl, $save);
                    }

                    //fclose($save);
}

// ****************************************************************************************************88
$wk_use_ssn_adjust = "";
$wk_use_ssn_adjust =  getQueryOption($Link, "SSNAdjust");
if ($wk_use_ssn_adjust == "")
{
	$wk_use_ssn_adjust = "F";
}
// if printer is not null print the ISSN
if (!is_null($wk_printer))
{

	$wk_message = "";
	$wk_ssn_id = $ssn_id;	
	if (($wk_ssn_id <>"") and ($wk_printer <> "") )
	{
		$printer_Id = $wk_printer;
		$printer_Ip = getPrinterIp($Link, $printer_Id);
		// now reprint the original issn's label
	
		$Query2 = "select s2.ssn_id, s2.prod_id, s2.other1, s2.other2, s2.current_qty, s2.company_id, s2.other4 from issn s2  ";
		$Query2 .= " where s2.ssn_id = '" . $wk_ssn_id. "' ";
		//$Query2 .= " order by s2.wh_id, s2.locn_id, s2.original_ssn, s2.ssn_id ";
		//echo($Query2);
	
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
//  ====================================================================
function convertDate($value) {

    //$formats = ['M d, Y', 'Y-m-d', 'Y-m-d H:i:s'];
    $formats = array('M d, Y', 'Y-m-d', 'd.m.Y', 'Y-m-d H:i:s');
    foreach($formats as $f) {
        $d = DateTime::createFromFormat($f, $value);
        $is_date = $d && $d->format($f) === $value;

        if ( true == $is_date ) break;
    }

    return $is_date;

}
//  ====================================================================

$rcount = 0;
echo("Query ISSN ".$ssn_id);
echo("<BR>\n");

$Query = "select sys_admin, editable from sys_user where user_id = '" . $tran_user . "'"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read User!<BR>\n");
	//exit();
}
$sysadmin = "";
$editable = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$sysadmin = $Row[0];
	}
	if ($Row[1] > "")
	{
		$editable = $Row[1];
	}
}
//release memory
ibase_free_result($Result);

$Query2 = "select description, label from query_layout where code = '1ssn' order by sequence";
#$Query = "select ssn.ssn_id, issn.locn_id, issn.wh_id, ssn.ssn_type, ssn.generic, ssn.brand, ssn.model,ssn.serial_number, issn.ssn_id, issn.issn_status, issn.prod_id , issn.current_qty, ssn.ssn_description";
#$Query .= " from issn join ssn on issn.original_ssn = ssn.ssn_id";
#$Query .= " where issn.ssn_id = '".$ssn_id."'";

//$Result3 = $Link->query($Query2);
//if (DB::isError($Result3))
//{
//	echo("Unable to query layout!<BR>\n");
//	exit();
//}
if (!($Result3 = ibase_query($Link, $Query2)))
{
	echo("Unable to query layout!<BR>\n");
	exit();
}

$Query = "select ";
// Fetch the results from the database.
//while ( ($Row = $Result3->fetchRow())  ) {
//	$fields[$rcount] = $Row[1];
//	$Query .= $Row[0] . ",";
//	$rcount++;
//}
while (($Row = ibase_fetch_row($Result3))) {
	$fields[$rcount] = $Row[1];
 	$Query .= $Row[0] . ",";
	$rcount++;
}

//release memory
//$Result3->free();
ibase_free_result($Result3);

//echo("[$Query]\n");
$Query = substr($Query,0,strlen($Query) - 1);
$Query .= " from issn join ssn on issn.original_ssn = ssn.ssn_id";
$Query .= " left outer join prod_profile on issn.prod_id = prod_profile.prod_id";
$Query .= " and issn.company_id = prod_profile.company_id";
$Query .= " where issn.ssn_id = '".$ssn_id."'";

//echo $Query;
//echo("[$Query]\n");
// Create a table.
echo ("<table BORDER=\"1\">\n");
//echo ("<TABLE >\n");
//$Result2 = $Link->query($Query);
//if (DB::isError($Result2))
//{
//	echo("Unable to query issn!<BR>\n");
//	exit();
//}

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
//echo ("<tr>\n");
//$info = $Result2->tableInfo();
//release memory
//$Result2->free();
//echo($info[0]['name']);
//for ($i=0; $i<$Result->numCols(); $i++)
//{
	//$info = ibase_field_info($Result, $i);
//	echo("<TH>{$info[$i]["name"]}</TH>\n");
//}
//echo ("</tr>\n");

/*
$fields[0] = "sSSN";
$fields[1] = "LOCN";
$fields[2] = "WH";
$fields[3] = "Type";
$fields[4] = "Gen";
$fields[5] = "Brand";
$fields[6] = "Model";
$fields[7] = "Serial";
$fields[8] = "iSSN";
$fields[9] = "Status";
$fields[10] = "Prod";
$fields[11] = "Qty";
$fields[12] = "Description";
*/
// Fetch the results from the database.
//while ( ($Row = $Result->fetchRow()) ) {
while (($Row = ibase_fetch_row($Result))) {
	//for ($i=0; $i<$Result->numCols(); $i++)
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
 		echo ("<tr>\n");
		//echo("<TH>{$info[$i]["name"]}</TH>\n");
		echo("<th>{$fields[$i]}</th>\n");
 		//echo ("<td>$Row[$i]</td>\n");
 		echo ("<td>");
                $is_datetime =  convertDate($Row[$i]) ;

		if ($is_datetime) {
			//echo "DateTime";
			// get the UTC time
			$wk_utc_date = DateTime::createFromFormat(
		            'Y-m-d G:i:s',
		            $Row[$i],
		            $wkdateTimeZoneDB
		        );
			//$wktimeOffset = $wkdateTimeZoneMy->getOffset($wk_utc_date);
			$wkMyInterval=DateInterval::createFromDateString((string)$wktimeOffset . 'seconds');
			$wk_utc_date->add($wkMyInterval);
			echo( $wk_utc_date->format('Y-m-d H:i:s'));
        	} else {
 			echo ("$Row[$i]");
		}
 		echo ("</td>\n");
 		echo ("</tr>\n");
	}
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

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
$backto = 'getlocn.php';
$backto = 'ssn.php';
$endtable = "Y";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	if (($sysadmin == "T"))
	//if (($editable == "T") or ($sysadmin == "T"))
	{
		echo ("<td>");
		echo("<FORM action=\"../util/dbquery.csv\" method=\"post\" name=history>\n");
		echo("<INPUT type=\"hidden\" name=\"Query\" ");  
		//$wk_query = "select SH.WH_ID, SH.LOCN_ID, SH.SSN_ID, LPAD(MER_HOUR(SH.TRN_DATE),'0',2) || ':' || LPAD(MER_MINUTE(SH.TRN_DATE),'0',2) || ':' || LPAD(SEC(SH.TRN_DATE),'0',2) || ' ' || LPAD(MER_DAY(SH.TRN_DATE),'0',2) || '/' || LPAD(MER_MONTH(SH.TRN_DATE),'0',2) || '/' || MER_YEAR(SH.TRN_DATE) AS TDATE, SH.TRN_TYPE, SH.TRN_CODE, SH.REFERENCE, SH.QTY, SH.SUB_LOCN_ID, SH.DEVICE_ID, SH.PERSON_ID FROM SSN_HIST SH LEFT OUTER JOIN ISSN ON ISSN.SSN_ID = SH.SSN_ID JOIN CONTROL ON CONTROL.RECORD_ID = 1 WHERE (SH.SSN_ID = '$ssn_id' OR ISSN.PROD_ID = '$ssn_id') AND (POS(CONTROL.MOVEMENT_TRANS,SH.TRN_TYPE,0,1) > -1) ORDER BY SH.TRN_DATE";
		$wk_query = "select SH.WH_ID, SH.LOCN_ID, SH.SSN_ID, LPAD(MER_HOUR(SH.TRN_DATE),'0',2) || ':' || LPAD(MER_MINUTE(SH.TRN_DATE),'0',2) || ':' || LPAD(SEC(SH.TRN_DATE),'0',2) || ' ' || LPAD(MER_DAY(SH.TRN_DATE),'0',2) || '/' || LPAD(MER_MONTH(SH.TRN_DATE),'0',2) || '/' || MER_YEAR(SH.TRN_DATE) AS TDATE, SH.TRN_TYPE, SH.TRN_CODE, SH.REFERENCE, SH.QTY, SH.SUB_LOCN_ID, SH.DEVICE_ID, SH.PERSON_ID,SH.TRN_DATE FROM SSN_HIST SH  WHERE (SH.SSN_ID = '$ssn_id') ";
		$wk_query .= " ORDER BY SH.TRN_DATE";
		echo("value=\"" . urlencode($wk_query) . "\"> ");  
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=SSN+History&fromimage=');
		echo('Blank_Button_50x100.gif" alt="SSNHistory">');
		echo("<INPUT type=\"hidden\" name=\"ExportAs\" value=\"CSV\"> ");  
		echo("</FORM>");
		echo ("</td>");
	}
	if (($sysadmin == "T"))
	//if (($editable == "T") or ($sysadmin == "T"))
	{
		echo ("<td>");
		echo("<FORM action=\"../util/dbquery.csv\" method=\"post\" name=history2>\n");
		echo("<INPUT type=\"hidden\" name=\"Query\" ");  
		$wk_query3 = "select prod_id from issn where ssn_id = '$ssn_id'";
		if (!($Result = ibase_query($Link, $wk_query3)))
		{
			echo("Unable to query issn!<BR>\n");
			exit();
		}
		$prod_id = '';
		while (($Row = ibase_fetch_row($Result))) 
		{
	 		$prod_id = $Row[0];
		}
		//$wk_query2 = "select SH.WH_ID, SH.LOCN_ID, SH.SSN_ID, LPAD(MER_HOUR(SH.TRN_DATE),'0',2) || ':' || LPAD(MER_MINUTE(SH.TRN_DATE),'0',2) || ':' || LPAD(SEC(SH.TRN_DATE),'0',2) || ' ' || LPAD(MER_DAY(SH.TRN_DATE),'0',2) || '/' || LPAD(MER_MONTH(SH.TRN_DATE),'0',2) || '/' || MER_YEAR(SH.TRN_DATE) AS TDATE, SH.TRN_TYPE, SH.TRN_CODE, SH.REFERENCE, SH.QTY, SH.SUB_LOCN_ID, SH.DEVICE_ID, SH.PERSON_ID FROM SSN_HIST SH LEFT OUTER JOIN ISSN ON ISSN.SSN_ID = SH.SSN_ID JOIN CONTROL ON CONTROL.RECORD_ID = 1 WHERE (SH.SSN_ID = '$prod_id' OR SH.SSN_ID = '$ssn_id' OR ISSN.PROD_ID='$prod_id') AND (POS(CONTROL.MOVEMENT_TRANS,SH.TRN_TYPE,0,1) > -1) ORDER BY SH.TRN_DATE";
		$wk_query2 = "select SH.WH_ID, SH.LOCN_ID, SH.SSN_ID, LPAD(MER_HOUR(SH.TRN_DATE),'0',2) || ':' || LPAD(MER_MINUTE(SH.TRN_DATE),'0',2) || ':' || LPAD(SEC(SH.TRN_DATE),'0',2) || ' ' || LPAD(MER_DAY(SH.TRN_DATE),'0',2) || '/' || LPAD(MER_MONTH(SH.TRN_DATE),'0',2) || '/' || MER_YEAR(SH.TRN_DATE) AS TDATE, SH.TRN_TYPE, SH.TRN_CODE, SH.REFERENCE, SH.QTY, SH.SUB_LOCN_ID, SH.DEVICE_ID, SH.PERSON_ID,SH.TRN_DATE FROM ISSN JOIN SSN_HIST SH ON ISSN.SSN_ID = SH.SSN_ID WHERE (ISSN.PROD_ID = '$prod_id') ";
		echo("value=\"" . urlencode($wk_query2) . "\"> ");  
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=Prod+History&fromimage=');
		echo('Blank_Button_50x100.gif" alt="ProdHistory">');
		echo("<INPUT type=\"hidden\" name=\"ExportAs\" value=\"CSV\"> ");  
		echo("</FORM>");
		echo ("</td>");
	} 
	echo ("<td>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("</FORM>");
	echo ("</td>");
	echo ("</tr>");
	echo ("<tr>");
	{
		// printer to use
		echo ("<td>");
		echo("<form action=\"1ssn.php\" method=\"post\" name=\"printlocn\">\n");
		echo ("<select name=\"printer\" > ");
		$Query = "SELECT device_id FROM sys_equip WHERE device_type = 'PR' ORDER BY device_id ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read sys_equip!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			echo ("<option value=\"" . $Row[0] . "\"");
			if ($wk_printer == "")
			{
				$wk_printer = $Row[0];
			}
			if ($wk_printer == $Row[0])
			{
				echo(" selected ");
			}
			echo(">" . $Row[0] . "</option>");
		}
		//release memory
		ibase_free_result($Result);
		echo ("</select>");
		echo ("</td>");
		// add button to print locations issn labels
		//$alt = "Print ISSN Label";
		$alt = "Print ISSN";
		echo ("<td>");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\">");
		echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$ssn_id."\"> ");
		//echo ("<input type=\"hidden\" name=\"location\" value=\"".$location."\"> ");
		echo("</form>");
		echo ("</td>");
		// add button to split issn qty
		$alt = "Split ISSN";
		echo ("<td>");
		echo("<form action=\"split1ssn.php\" method=\"post\" name=\"splitIssn\">\n");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\">");
		echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$ssn_id."\"> ");
		echo("</form>");
		echo ("</td>");
		// if something allow the adjust
		if ($wk_use_ssn_adjust == "T")
		{
			echo ("</tr>");
			echo ("<tr>");
			// add button to adjust issn qty 
			$alt = "Adjust ISSN";
			echo ("<td>");
			echo("<form action=\"GetSSNAdj.php\" method=\"post\" name=\"adjustissn\">\n");
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
			//echo("<input type=\"hidden\" name=\"from\" value=\"getlocn.php\">");
			echo("<input type=\"hidden\" name=\"from\" value=\"ssn.php\">");
			echo ("<input type=\"hidden\" name=\"ssn_id\" value=\"".$ssn_id."\"> ");
			echo ("<input type=\"hidden\" name=\"ssnfrom\" value=\"".$ssn_id."\"> ");
			echo("</form>");
			echo ("</td>");
		}
	}
	echo ("</tr>");
	if ($endtable == "Y")
	{
		echo ("</table>");
	}
//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
//ibase_close($Link);

?>
</body>
</html>
