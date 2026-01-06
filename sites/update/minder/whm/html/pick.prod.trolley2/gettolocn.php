<?php
include "../login.inc";
?>
<html>
 <head>
<?php
 include "viewport.php";
?>
  <title>Pick TO Location Page</title>
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
<?php
require_once 'DB.php';
require 'db_access.php';
require_once "logme.php";
include "2buttons.php";
include "checkdatajs.php";

/*
2 modes here
1 ) for products on the device that are on a pick_item_detail record that you own
    want to for each order
    for this product that is marked as picked 'PL' (via issn and pick_item_detail)
    scan a location in the pick_order for this order
    to do the PKIL to
2 ) the old mode where we move product from one location to another
    ie from one location in pick_location for one of the products to another

have to add doing a POAL if the location doesn't belong to the order
the problem is that if more than 1 order which order to choose
use the qty required for the order

*/
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbtran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}

// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function errorHandler(errorMessage,url,line) {
document.write("<p><b>Error message:</b> "+errorMessage+"<br>");
document.write("<b>URL:</b> "+url+"<br>");
document.write("<p><b>Line No:</b> "+line+"</p>");
return true;
}
/* onerror = errorHandler; */
function processEdit() {
/* # check for valid location */
  var mytype;
  var myvalue;
  var mylen;
  var myinstr;
  var i;
  var wkFound;
  var mybuf;
  /* alert(allowedLocations); 
  for (i = 0; i < formDatas.length; i++ ) {
    mybuf = mybuf + formDatas[i]['location'];
  } 
  alert(mybuf); 
  what about pressed a button
  */
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	/* alert("Not a Location"); */
	document.tomessage.message.value = "Not a Location" ;
  	return false;
  }
  else
  {
	if (startposn > 0)
	{
		myvalue = document.getlocn.location.value;
		mylen = myvalue.length - startposn;
		myinstr = myvalue.substr(startposn,mylen);
	}
	else
	{
		myinstr = document.getlocn.location.value;
	}
	if (wkMode == "Move")
	{
		if (wkHaveLabel == false)
		{
			/* scan location to take from */
			wkFound = false;
    	            	for (i = 0; i < allowedLocations.length; i++ ) {
        	        	if (myinstr == allowedLocations[i])
				{
					wkFound = true;
                                        document.getlocn.order.value = formDatas[i]['order'];
                                        document.getlocn.order_no.value = formDatas[i]['order_no'];
                                        document.getlocn.label.value = formDatas[i]['label'];
                                        document.getlocn.qty.value = formDatas[i]['qty'];
				}
        	        }
			if (wkFound == false)
			{
				/* alert("location not in Allowed Values"); */
				/* document.tomessage.message.value = "Not a Trolley Location for this Order and Device" ; */
				/* return false; */
			}
		}
	}
	else
	{
		wkFound = false;
                for (i = 0; i < allowedLocations.length; i++ ) {
                	if (myinstr == allowedLocations[i])
			{
				wkFound = true;
                                document.getlocn.order.value = formDatas[i]['order'];
                                document.getlocn.order_no.value = formDatas[i]['order_no'];
                                document.getlocn.label.value = formDatas[i]['label'];
                                document.getlocn.qty.value = formDatas[i]['qty'];
			}
                }
		if (wkFound == false)
		{
			/* alert("location not in Allowed Values"); */
			/* document.tomessage.message.value = "Not a Trolley Location for this Order and Device" ; */
			/* return false; */
		}
		/* alert(myinstr); */
		/* document.getlocn.location.value = myinstr; */
	}
	return true; 
  }
}
</script>

<?php
echo "<body>";
//require_once "logme.php";

function type_desc($typein)
{
	switch($typein)
	{
		case "M":
			return "All";
			break;
		case "I":
			return "SSN";
			break;
		case "D":
			return "Label";
			break;
		case "P":
			return "Product";
			break;
	}
}

/**
 * get ISSNs on the Device
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getISSNonDevice ($Link, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked), p3.other1  
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  join pick_order p3 on p2.pick_order = p3.pick_order
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
        /*        and   p1.pick_detail_status = 'PL'  */
                  and   p1.pick_detail_status IN ( 'PL', 'PG') 
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id = '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id, p3.other1 ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['ISSN.PROD_ID']  = $Row[0];
		$wkIssn['PICK_ORDER']  = $Row[1];
		$wkIssn['PICK_LABEL_NO']  = $Row[2];
		$wkIssn['PROD_ID']  = $Row[3];
		$wkIssn['SSN_ID']  = $Row[4];
		$wkIssn['QTY_PICKED']  = $Row[5];
		$wkIssn['OTHER1']  = $Row[6];
		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}

/**
 * get Allowed Location to Place Into for this Order
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder
 * @param string $wkDevice - limit work to this device only
 * @return array
 */
function getEmptyLocation ($Link, $wkOrder, $wkDevice )
{
	//echo("get locns for :" . $wkOrder);
	$wkResult = array();
   	if ($wkDevice <> "All")
	{
		$Query = "select p1.wh_id, p1.locn_id 
                	  from pick_location p1 
			  where p1.pick_order  = '" . $wkOrder . "'
        	          and   p1.pick_location_status = 'OP' 
                	  and   p1.device_id  = '" . $wkDevice . "' 
	                   ";
	} else {
		$Query = "select p1.wh_id, p1.locn_id 
        	          from pick_location p1 
                	  where p1.pick_order  = '" . $wkOrder . "'
	                  and   p1.pick_location_status = 'OP' 
                	   ";
	} 
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkLocn = array();
		$wkLocn['WH_ID']  = $Row[0];
		$wkLocn['LOCN_ID']  = $Row[1];
		$wkResult[]  = $wkLocn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('pick_location:' . print_r($wkResult,true));
	return $wkResult;
}

/**
 * get ISSNs on the Trolley
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return array
 */
function getISSNonTrolley ($Link, $wkDevice )
{
	// device  = tran_device
	$wkResult = array();
	$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked), i1.wh_id, i1.locn_id  
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  and   p1.pick_detail_status = 'PL' 
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id <>  '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id, i1.wh_id, i1.locn_id ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['ISSN.PROD_ID']  = $Row[0];
		$wkIssn['PICK_ORDER']  = $Row[1];
		$wkIssn['PICK_LABEL_NO']  = $Row[2];
		$wkIssn['PROD_ID']  = $Row[3];
		$wkIssn['SSN_ID']  = $Row[4];
		$wkIssn['QTY_PICKED']  = $Row[5];
		$wkIssn['ISSN.WH_ID']  = $Row[6];
		$wkIssn['ISSN.LOCN_ID']  = $Row[7];
		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);
	//echo('issns:' . print_r($wkResult,true));
	return $wkResult;
}

$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';

$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
	
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
$original_type = $type;
//echo("original type " . $original_type);
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['order_no']))
{
	$order_no = $_POST['order_no'];
}
if (isset($_GET['order_no']))
{
	$order_no = $_GET['order_no'];
}
if (isset($_POST['label']))
{
	$label = $_POST['label'];
	$wk_have_label = "T";
}
if (isset($_GET['label']))
{
	$label = $_GET['label'];
	$wk_have_label = "T";
}
if (isset($_POST['qty']))
{
	$qtyreqd  = $_POST['qty'];
}
if (isset($_GET['qty']))
{
	$qtyreqd = $_GET['qty'];
}
if (isset($_POST['wh_id']))
{
	$whId  = $_POST['wh_id'];
}
if (isset($_GET['wh_id']))
{
	$whId = $_GET['wh_id'];
}
if (isset($_POST['locn_id']))
{
	$locnId  = $_POST['locn_id'];
}
if (isset($_GET['locn_id']))
{
	$locnId = $_GET['locn_id'];
}
$wkOnDevice =  getISSNonDevice ($Link, $tran_device );
$wkMode = count($wkOnDevice) > 0 ? 'Place' : 'Move';
$wkOnTrolley =  getISSNonTrolley ($Link, $tran_device );
$wkAllowedLocations = array();
$wkFormDatas = array();
echo ("<div id=\"col3\">\n");

$wk_allow_transfer = "F";
$Query = "select description from options where group_code='PKIL' and code = 'TRANSFER'";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_allow_transfer = $Row[0];
}
//release memory
ibase_free_result($Result);

if ($wkMode == 'Move')
{
	// Move mode
	//echo("type2 is " . $type);
	
	//echo($Query);
	$rcount = 0;
	
	$got_items = 0;
	
	//echo("<FONT size=\"2\">\n");
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	// echo headers
	echo ("<table BORDER=\"1\">\n");
	echo ("<tr>\n");
	echo("<th>Location</th>\n");
	echo("<th>Product</th>\n");
	echo("<th>Order</th>\n");
	echo("<th>Qty</th>\n");
	echo("<th>Label</th>\n");
	echo ("</tr>\n");
	
	foreach ($wkOnTrolley as $wk_ssn_id => $wk_issn)
	{
		$order_no = $wk_issn['PICK_ORDER'];
		$order = $wk_issn['PICK_LABEL_NO'];
		$label = $wk_issn['PICK_LABEL_NO'];
		$qtyreqd = $wk_issn['QTY_PICKED'];
		$prod = $wk_issn['PROD_ID'];
		$ssn_id = $wk_issn['SSN_ID'];
		$whId = $wk_issn['ISSN.WH_ID'];
		$locnId = $wk_issn['ISSN.LOCN_ID'];
		$type = "D";
		echo ("<tr>\n");
		{
			//echo("<td>".$wk_locn['LOCN_ID']."</td>\n");
			echo ("<td>\n");
			echo("<form action=\"gettolocn.php\" method=\"post\" name=\"" . $locnId  . "\" >");
			echo("<INPUT type=\"hidden\" name=\"location\" value=\"" . $whId . $locnId  . "\" >");
			$wkAllowedLocations [] =  $whId . $locnId ;
			$wkFormData = array();
			$wkFormData ['location'] = $whId . $locnId ;
			$wkFormData ['order'] = $order;
			$wkFormData ['order_no'] = $order_no;
			$wkFormData ['label'] = $label ;
			$wkFormData ['qty'] = $qtyreqd ;
			$wkFormDatas [] = $wkFormData;
			echo('<INPUT type="hidden" name="ttype" value="' . $type . '" >');
			echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
			echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
			echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label\" >");
			echo("<INPUT type=\"hidden\" name=\"qty\" value=\"$qtyreqd\" >");
			echo("<INPUT type=\"hidden\" name=\"wh_id\" value=\"$whId\" >");
			echo("<INPUT type=\"hidden\" name=\"locn_id\" value=\"$locnId\" >");
			echo("<INPUT type=\"submit\" name=\"locationname\" value=\"".$wk_issn['ISSN.LOCN_ID'] ."\">\n");
			echo("</form >\n");
			echo ("</td>\n");
		}
		if ($wk_issn['ISSN.PROD_ID'] == "")
		{
			echo("<td>".$wk_issn['SSN_ID']."</td>\n");
		}
		else
		{
			echo("<td>".$wk_issn['ISSN.PROD_ID']."</td>\n");
		}
		echo("<td>".$wk_issn['PICK_ORDER']."</td>\n");
		// must sum the qty picked across prod id and pick_label_no
		// to get the qty to move
		echo("<td>".$wk_issn['QTY_PICKED']."</td>\n");
		echo("<td>".$wk_issn['PICK_LABEL_NO']."</td>\n");
		echo ("</tr>\n");
	}
	echo ("</table>\n");
} else {
	// place mode
	//echo("<FONT size=\"2\">\n");
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	// echo headers
	echo ("<table BORDER=\"1\">\n");
	echo ("<tr>\n");
	echo("<th>Product</th>\n");
	echo("<th>Order</th>\n");
	echo("<th>Qty</th>\n");
	echo("<th>Pick</th>\n");
	echo ("</tr>\n");
	$wk_sysuser = "";
	{
		$Query = "SELECT sys_admin from sys_user";
		$Query .= " where user_id = '" . $tran_user . "'";
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query table!<BR>\n");
			exit();
		}
	
		// echo headers
		// Fetch the results from the database.
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_sysuser = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	foreach ($wkOnDevice as $wk_ssn_id => $wk_issn)
	{
		$order_no = $wk_issn['PICK_ORDER'];
		$order = $wk_issn['PICK_LABEL_NO'];
		$label = $wk_issn['PICK_LABEL_NO'];
		$qtyreqd = $wk_issn['QTY_PICKED'];
		$prod = $wk_issn['PROD_ID'];
		$ssn_id = $wk_issn['SSN_ID'];
		$type = "D";
		if ($wk_sysuser == "T")
		{
			$wkAllowedLocns = getEmptyLocation ($Link, $order_no ,"All" );
		} else {
			$wkAllowedLocns = getEmptyLocation ($Link, $order_no ,$tran_device );
		}
		echo ("<tr>\n");
		if ($wk_issn['ISSN.PROD_ID'] == "")
		{
			echo("<td>".$wk_issn['SSN_ID']."</td>\n");
		}
		else
		{
			echo("<td>".$wk_issn['ISSN.PROD_ID']."</td>\n");
		}
		echo("<td>".$wk_issn['PICK_ORDER']."</td>\n");
		// must sum the qty picked across prod id and pick_label_no
		// to get the qty to move
		echo("<td>".$wk_issn['QTY_PICKED']."</td>\n");
		echo("<td>".$wk_issn['PICK_LABEL_NO']."</td>\n");
		echo ("</tr>\n");
		echo ("<tr>\n");
		//echo ("<td>\n");
		//echo ("</td>\n");
		echo ("<td>Locations\n");
		echo ("</td>\n");
		foreach ($wkAllowedLocns as $wk_locn_id => $wk_locn)
		{
			//echo("<td>".$wk_locn['LOCN_ID']."</td>\n");
			echo ("<td>\n");
			echo("<form action=\"transactionIL.php\" method=\"post\" name=\"" . $wk_locn['LOCN_ID'] . "\" >");
			echo("<INPUT type=\"hidden\" name=\"location\" value=\"" . $wk_locn['WH_ID'] . $wk_locn['LOCN_ID'] . "\" >");
			$wkAllowedLocations [] =  $wk_locn['WH_ID'] . $wk_locn['LOCN_ID'] ;
			$wkFormData = array();
			$wkFormData ['location'] = $wk_locn['WH_ID'] . $wk_locn['LOCN_ID']  ;
			$wkFormData ['order'] = $order;
			$wkFormData ['order_no'] = $order_no;
			$wkFormData ['label'] = $label ;
			$wkFormData ['qty'] = $qtyreqd ;
			$wkFormDatas [] = $wkFormData;
			echo('<INPUT type="hidden" name="ttype" value="' . $type . '" >');
			echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
			echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
			echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label\" >");
			echo("<INPUT type=\"hidden\" name=\"qty\" value=\"$qtyreqd\" >");
			echo("<INPUT type=\"submit\" name=\"locationname\" value=\"".$wk_locn['LOCN_ID'] ."\">\n");
			echo("</form >\n");
			echo ("</td>\n");
		}
		// if something
		echo("<td>".$wk_issn['OTHER1']."</td>\n");
		echo ("</tr>\n");
	}
	echo ("</table>\n");
}
if ($wkMode == 'Move')
{
	echo("Despatch transfer<BR>");
}
else
{
	//echo("Place Qty into Trolley Location<BR>"); 
	echo("Despatch transfer<BR>");
}
if ($wkMode == 'Move')
{
	if (isset($wk_have_label))
	{
		echo("<form action=\"transactionIL.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
	} else {
		echo("<form action=\"gettolocn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
	} 
} else {
	echo("<form action=\"transactionIL.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
}
if ($wkMode == 'Move')
{
	if (isset($wk_have_label))
	{
		//echo("Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\"  ONBLUR=\"return processEdit();\" >");
		echo("Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\" >");
	} else {
		//echo("From Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\" ONBLUR=\"return processEdit();\" >");
		echo("From Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\" >");
	}
} else {
	//echo("<INPUT type=\"hidden\" name=\"location\" size=\"10\"  >");
	//echo("Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\" ONBLUR=\"return processEdit();\" >");
	echo("Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"12\" >");
}
echo('<INPUT type="hidden" name="ttype" value="' . $type . '" >');
//echo("type3 is " . $type);

echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label\" >");
if (isset($qtyreqd))
{
	echo("<INPUT type=\"hidden\" name=\"qty\" value=\"$qtyreqd\" >");
} else {
	echo("<INPUT type=\"hidden\" name=\"qty\" value=\"0\" >");
}
echo ("<table>\n");
echo ("<tr>\n");
if ($wkMode == 'Move')
{
	if (isset($wk_have_label))
	{
		echo("<td colspan=\"2\" >Scan Location to Move To</td>\n");
	} else {
		//echo("<td colspan=\"2\" >Click Location to Take From</td>\n");
		echo("<td colspan=\"2\" >Scan Location to Take From</td>\n");
	}
}
else
{
	//echo("<td colspan=\"2\" >Then Click Locations Button</td>\n");
	echo("<td colspan=\"2\" >Scan Location to Move To</td>\n");
}
echo ("</tr>\n");
//echo ("</table>\n");
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbtran);

//close
//ibase_close($Link);

//echo("</form>\n");
{
	// html 4.0 browser
 	//echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	{
		whm2buttons('Accept', 'getfromlocn.php','N', "Back_50x100.gif","Back","accept.gif", 'N');
	}
	// transfer a location to conveyor
	if ($wk_allow_transfer == "T")
	{
		$alt = "Transfer Location to Conveyor";
		echo("<td colspan=\"2\" >\n");
		echo("<form action=\"transferlocn.php\" method=\"post\" name=transferOut>\n");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo("</form>");
		echo ("</td>");
	}
	// add a location to order 
	if ($wk_allow_transfer == "T")
	{
		$alt = "Add Trolley Location";
		echo ("<td>");
		echo("<form action=\"addlocn.php\" method=\"post\" name=\"addLocation\">\n");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<INPUT type=\"hidden\" name=\"from\" value=\"gettolocn.php\">");
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("</table>");
}
if ($wkMode == 'Move')
{
	echo("<script type=\"text/javascript\">\n");
	echo("var allowedLocations = " . json_encode($wkAllowedLocations) . ";\n");
	echo("var formDatas = " . json_encode($wkFormDatas) . ";\n");
	echo("var wkMode = \"" . $wkMode  . "\";\n");
	if (isset($wk_have_label))
	{
		echo("var wkHaveLabel = true;\n");
	} else {
		echo("var wkHaveLabel = false;\n");
	}
	echo("document.getlocn.location.focus();\n");
	echo("</script>\n");
} else {
	echo("<script type=\"text/javascript\">\n");
	echo("var allowedLocations = " . json_encode($wkAllowedLocations) . ";\n");
	echo("var formDatas = " . json_encode($wkFormDatas) . ";\n");
	echo("var wkMode = \"" . $wkMode  . "\";\n");
	echo("var wkHaveLabel = false;\n");
	echo("document.getlocn.location.focus();\n");
	echo("</script>\n");
} 
echo ("</div>\n");
echo ("<div id=\"message\">\n");
echo("<form action=\"\" method=\"post\" name=\"tomessage\">\n");
echo("<INPUT type=\"text\" name=\"message\" readonly >");
echo("</form>");
echo ("</div>\n");
?>
</body>
</html>
