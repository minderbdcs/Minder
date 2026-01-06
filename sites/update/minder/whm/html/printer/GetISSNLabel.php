<?php
include "login.inc";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Reprint ISSN Labels for a Location</title>
<link rel=stylesheet type="text/css" href="GetLocationLabel.css">
</head>
<body>
<script type="text/javascript">
function processEdit() {
  if ( document.printissn.locn.value=="")
  {
  	document.printissn.message.value="Must Enter the Location";
	document.printissn.locn.focus()
  	return false
  }
/*
  if ( document.printissn.labelqty.value=="")
  {
  	document.printissn.message.value="Enter the Qty of Labels";
	document.printissn.labelqty.focus()
  	return false
  }
*/
  return true;
}
function changeLocn() {
/*
  document.printissn.message.value="Enter Qty of Labels";
  document.printissn.labelqty.value="";
  document.printissn.labelqty.focus();
*/
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "transaction.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wk_locn_id = "";
$wk_label_type = "";
$wk_label_qty = 1;
$wk_printer = "";
$wk_image_x = "";
$wk_image_y = "";
if (isset($_POST['locn'])) 
{
	$wk_locn_id = $_POST["locn"];
}
if (isset($_GET['locn'])) 
{
	$wk_locn_id = $_GET["locn"];
}
if (isset($_POST['labelqty'])) 
{
	$wk_label_qty = $_POST["labelqty"];
}
if (isset($_GET['labelqty'])) 
{
	$wk_label_qty = $_GET["labelqty"];
}
if (isset($_POST['printer'])) 
{
	$wk_printer = $_POST["printer"];
}
if (isset($_GET['printer'])) 
{
	$wk_printer = $_GET["printer"];
}
if (isset($_POST['x'])) 
{
	$wk_image_x = $_POST["x"];
}
if (isset($_GET['x'])) 
{
	$wk_image_x = $_GET["x"];
}
if (isset($_POST['y'])) 
{
	$wk_image_y = $_POST["y"];
}
if (isset($_GET['y'])) 
{
	$wk_image_y = $_GET["y"];
}

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
                    $save = fopen($printerDir .
                    	$p->data['issn'] . '_ISSN.prn', 'w');
                    if (!$p->sysLabel($Link, $printerId, "ISSN", 1))
                    {
                    	$p->send($tpl, $save);
                    }

                    fclose($save);
}

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

echo (" <FORM action=\"GetISSNLabel.php\" method=\"post\" name=printissn>");
echo (" <P>");
echo ("<INPUT type=\"text\" name=\"message\" class=\"message\" readonly size=\"40\" ><BR> ");
echo ("Location:<INPUT type=\"text\" name=\"locn\" value = \"".$wk_locn_id."\" size=\"30\" ONCHANGE=\"changelocn();\" ><BR> ");
if (isset($wk_desc))
{
	echo ("<INPUT type=\"text\" name=\"locndesc\" value = \"".$wk_desc."\" size=\"50\" readonly>");
}
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
/*
echo ("<TR><TD>");
echo ("Qty of Labels:");
echo ("</TD><TD>");
echo ("<INPUT type=\"hidden\" name=\"labelqty\" value=\"$wk_label_qty\" size=\"4\" maxlength=\"4\">");
echo ("</TD></TR>");
*/
echo ("<TR><TD>");
echo ("Printer:");
echo ("</TD><TD>");
echo ("<SELECT name=\"printer\" > ");
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
echo ("</SELECT>");
echo ("</TD></TR>");
echo ("</TABLE>");
echo ("<BR><BR><BR><BR><BR><BR><BR>");
echo ("<BR><BR><BR><BR><BR><BR><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
if ($wk_locn_id == "")
{
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.printissn.locn.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.printissn.message.value=\"" . $wk_message . " Enter Location to Print\";\n");
	}
	else
	{
		echo("document.printissn.message.value=\"Enter Location to Print\";\n");
	}
	echo("</script>");
}
else
{
/*
	if ($wk_label_qty == 0)
	{
		whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
		echo("<SCRIPT>");
		echo("document.printissn.labelqty.focus();\n");
		echo("document.printissn.message.value=\"Enter Qty of Labels to Print\";\n");
		echo("</SCRIPT>");
	}
	else
*/
	{
		{
			if ($wk_message <> "")
			{
				echo("<script type=\"text/javascript\">\n");
				echo("document.printissn.message.value=\"" . $wk_message . "\";\n");
				echo("</script>");
			}
			whm2buttons('Print', 'print_Menu.php',"Y","Back_50x100.gif","Back","Print_50x100.gif");
		}
	}
}
//commit
//ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</body>
</html>

