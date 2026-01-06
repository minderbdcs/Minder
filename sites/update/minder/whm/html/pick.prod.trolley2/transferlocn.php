<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<?php
include "viewport.php";
require_once 'DB.php';
require 'db_access.php';
include "logme.php";
include "transaction.php";
?>
<html>
 <head>
  <title>Pick Transfer TO Location Out Page</title>
<?php
//<link rel=stylesheet type="text/css" href="fromlocn.css">
if ($wkMyBW == "IE60")
{
	echo('<link rel=stylesheet type="text/css" href="fromlocn.css">');
} elseif ($wkMyBW == "IE65")
{
	echo('<link rel=stylesheet type="text/css" href="fromlocn-ie7.css">');
} elseif ($wkMyBW == "CHROME")
{
	echo('<link rel=stylesheet type="text/css" href="fromlocn-chrome.css">');
} elseif ($wkMyBW == "SAFARI")
{
	echo('<link rel=stylesheet type="text/css" href="fromlocn-chrome.css">');
} elseif ($wkMyBW == "NETFRONT")
{
	echo('<link rel=stylesheet type="text/css" href="fromlocn-netfront.css">');
}
?>
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
include "2buttons.php";

/*
2 modes here
transfer a temporary pick location to conveyor


what if no pick_items on device
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

/*
<script type="text/javascript">
function processEdit() {
-* # check for valid location *-
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	document.getlocn.message.value = "Not a Location" ;
  	return false;
  }
  else
  {
	return true;
  }
}
</script> */
?>

<?php
echo "<body>";
include "logme.php";

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
	$Query = "select i1.prod_id, p2.pick_order, p2.pick_label_no, p2.prod_id, p2.ssn_id, sum(p1.qty_picked)  
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  and   p1.pick_detail_status = 'PL' 
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id = '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  group by i1.prod_id , p2.pick_label_no, p2.pick_order, p2.prod_id, p2.ssn_id ";
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
	// empty locations
	$Query = "select p3.pick_order, 0, p3.wh_id, p3.locn_id  
                  from pick_location p3 
                  where p3.device_id = '" . $wkDevice . "'
                  and   p3.pick_location_status = 'OP' 
                  group by p3.pick_order, p3.wh_id, p3.locn_id 
                  ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['PICK_ORDER']  = $Row[0];
		$wkIssn['QTY_PICKED']  = $Row[1];
		$wkIssn['ISSN.WH_ID']  = $Row[2];
		$wkIssn['ISSN.LOCN_ID']  = $Row[3];
		$wkResult[]  = $wkIssn;
	}
	//release memory
	ibase_free_result($Result);

	//var_dump($wkResult);
	// locations where picked items have been placed
	$Query = "select p2.pick_order, sum(p1.qty_picked), i1.wh_id, i1.locn_id  
                  from pick_item p2 
                  join pick_item_detail p1 on p2.pick_label_no = p1.pick_label_no
                  join issn i1 on p1.ssn_id = i1.ssn_id
                  join pick_location p4  on p2.pick_order = p4.pick_order and i1.wh_id = p4.wh_id and i1.locn_id = p4.locn_id
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status = 'PL' 
                  and   p1.pick_detail_status = 'PL' 
                  and   p1.device_id = '" . $wkDevice . "'
                  and   i1.locn_id <>  '" . $wkDevice . "'
                  and   i1.current_qty > 0
                  and   p4.pick_location_status = 'OP'
                  and   p4.device_id = '" . $wkDevice . "'
                  group by p2.pick_order, i1.wh_id, i1.locn_id ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkIssn = array();
		$wkIssn['PICK_ORDER']  = $Row[0];
		$wkIssn['QTY_PICKED']  = $Row[1];
		$wkIssn['ISSN.WH_ID']  = $Row[2];
		$wkIssn['ISSN.LOCN_ID']  = $Row[3];
		//$wkResult[]  = $wkIssn;
		// does the order and location already exist in array
		$wk_found = False;
		foreach ($wkResult as $wk_ssn_id => $wk_issn2)
		{
			if (($wk_issn2['PICK_ORDER'] == $wkIssn['PICK_ORDER']) and
			    ($wk_issn2['ISSN.WH_ID'] == $wkIssn['ISSN.WH_ID']) and
			    ($wk_issn2['ISSN.LOCN_ID'] == $wkIssn['ISSN.LOCN_ID']))
			{
				$wk_issn2['QTY_PICKED']  = ($wk_issn2['QTY_PICKED']) + ($wkIssn['QTY_PICKED']) ;
				$wkResult[$wk_ssn_id] = $wk_issn2 ;
				$wk_found = True;
			}
		}
		// else add it
		if (!$wk_found)
		{
			$wkResult[]  = $wkIssn;
		}
	}
	//release memory
	ibase_free_result($Result);
	//var_dump($wkResult);
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
//$wkOnDevice =  getISSNonDevice ($Link, $tran_device );
//$wkMode = count($wkOnDevice) > 0 ? 'Place' : 'Move';
$wkOnTrolley =  getISSNonTrolley ($Link, $tran_device );

//=====================================================
echo("<div id=\"col3\">\n");
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
	echo("<th>Order</th>\n");
	echo("<th>Qty</th>\n");
	echo ("</tr>\n");
	
	foreach ($wkOnTrolley as $wk_ssn_id => $wk_issn)
	{
		$order_no = $wk_issn['PICK_ORDER'];
		$qtyreqd = $wk_issn['QTY_PICKED'];
		$whId = $wk_issn['ISSN.WH_ID'];
		$locnId = $wk_issn['ISSN.LOCN_ID'];
		echo ("<tr>\n");
		{
			//echo("<td>".$wk_locn['LOCN_ID']."</td>\n");
			echo ("<td>\n");
			echo("<form action=\"transactionPK.php\" method=\"post\" name=\"" . $whId . $locnId . "\" >");
			echo("<INPUT type=\"hidden\" name=\"location\" value=\"" . $whId . $locnId . "\" >");
			echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
			echo("<INPUT type=\"hidden\" name=\"wh_id\" value=\"$whId\" >");
			echo("<INPUT type=\"hidden\" name=\"locn_id\" value=\"$locnId\" >");
			echo("<INPUT type=\"submit\" name=\"locationname\" value=\"".$wk_issn['ISSN.LOCN_ID'] ."\">\n");
			echo("</form >\n");
			echo ("</td>\n");
		}
		echo("<td>".$wk_issn['PICK_ORDER']."</td>\n");
		// must sum the qty picked across prod id and pick_label_no
		// to get the qty to move
		echo("<td>".$wk_issn['QTY_PICKED']."</td>\n");
		echo ("</tr>\n");
	}
	echo ("</table>\n");
} 

echo ("</div>\n");
echo("<div id=\"col5\">\n");
//=====================================================
{
	echo("Transfer Location<BR>");
}
//echo("<form action=\"transactionPK.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
echo("<form action=\"transactionPK.php\" method=\"post\" name=getlocn >");
//if ($wkMode == 'Move')
//{
//	echo("Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\" >");
//} else {
	echo("<INPUT type=\"hidden\" name=\"location\" size=\"10\"  >");
//}
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
echo ("</div>\n");
echo ("<div id=\"message\">\n");
echo("<form action=\"\" method=\"post\" name=\"tomessage\">\n");
echo("<INPUT type=\"text\" name=\"message\" readonly >");
echo("</form>");
echo ("</div>\n");
echo("<div id=\"col6\">\n");
echo ("<table>\n");
echo ("<tr>\n");
//if ($wkMode == 'Move')
{
	//if (isset($wk_have_label))
	//{
	//	echo("<td colspan=\"2\" >Scan Trolley Location to Move To</td>\n");
	//} else {
	//	echo("<td colspan=\"2\" >Click Location to Take From</td>\n");
	//}
}
//else
{
	echo("<td colspan=\"2\" >Click Location to Take From</td>\n");
	//echo("<td colspan=\"2\" >Then Click Locations Button</td>\n");
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
		whm2buttons('Accept', 'getfromlocn.php','N', "Back_50x100.gif","Back","accept.gif" );
	}
	//echo ("</tr>");
	echo ("</table>");
}
echo ("</div>\n");
?>
</body>
</html>
