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

/*
18/7/2011 although allocates many orders at once only pick 1 order at a time
so in  	getfromlocn check that only 1 order on the device in AL PG PL
		other wise update all the AL PG PL to Al Pg Pl
		then update the first order to be AL PG PL from Al Pg Pl
   in  	transactionIL check that finished order 
		if finished update the next order to AL PG PL 
		then continue as normal 
06/09/11 for orders with partial pick allowed
        when have no stock for the line 
	if you have picked any issns for this 
		do an automatic PKOL with zero qty and a fixed reason
	else the status must go to 'AS' and null device
11/08/14 add the use of alternate_companys
06/09/16 shortest route changes for multiple orders at a time
         I am using the next pick item based on the last pick location
	 Also the locations with ISSN's
	 if no pick item is found then am getting a screen with no order or issn's 
	Going back to the pick menu resets the last pick from location - so that they can continue !.
	So if none found
	Already have on the screen for none pick items on device
	So its have a pick item but its back from where you are !!.
*/

$LogFile = '/data/tmp/getfromlocn.log';
		
/**
 * check for Pick Options
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @return string
 */
function getPickOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='PICK'  and code = '" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/data/tmp/getfromlocn.log' , 'a');
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
 * check for More Orders to Pick
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @param string $wkDoLater
 * @return string
 */
function check4MoreOrders ($Link, $wkDevice, $wkDoLater = "T", $wkLastLocnSeq = 0 ) 
{
	//echo(__FILE__);
	// device  = tran_device
	$wkNextOrder = "";
	$wkLastOrder = "";
	$wkOrdersCnt = 0;
	$Query = "select count( *  ) from (  
	          select p2.pick_order   
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('AL','PG','PL') 
                  group by p2.pick_order )  ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkOrdersCnt = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	//echo("OrdersCnt:" . $wkOrdersCnt . "\n");
	if ($wkOrdersCnt > 1)
	{
		$Query = "update pick_item 
                          set pick_line_status = 'Al'
                          where pick_line_status = 'AL' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
		//echo("Updates AL to Al" );
		$Query = "update pick_item 
                          set pick_line_status = 'Pg'
                          where pick_line_status = 'PG' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
		//echo("Updates PG to Pg" );
		$Query = "update pick_item 
                          set pick_line_status = 'Pl'
                          where pick_line_status = 'PL' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();

		}
		//echo("Updates PL to Pl \n" );
	}
	$Query = "select first 1  p2.pick_order   
                  from pick_item p2 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('AL','PG','PL') ";
	//echo ($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkLastOrder = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	//echo("LastOrder:" . $wkLastOrder . "\n");

	if ($wkLastOrder == "")
	{
		$Query = "select first 1  p2.pick_order ,coalesce(l1.locn_seq, 999999 ) as l3  
                  from pick_item p2 
	           left outer join location l1 on l1.wh_id = p2.wh_id and l1.locn_id = p2.pick_location 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('Al','Pg','Pl') ";
		if ($wkDoLater == "F")
		{
			$Query .= " and l1.locn_seq >= '" . $wkLastLocnSeq . "'";
		}
		// have an options record for pick to allow do later or not
		if ($wkDoLater == "T")
		{
	        	  $Query = $Query . " order by  p2.pick_line_priority, l3, p2.pick_location";
		} else {
		          $Query = $Query . " order by  l3, p2.pick_location";
		}
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Items!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkNextOrder = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		//echo("NextOrder:" . $wkNextOrder . "\n");
	}
	if (($wkNextOrder == "") and ($wkLastOrder == ""))
	{
		// no order > current locn seq so go in reverse direction from last locn seq
		$Query = "select first 1  p2.pick_order ,coalesce(l1.locn_seq, 999999 ) as l3  
                  from pick_item p2 
	           left outer join location l1 on l1.wh_id = p2.wh_id and l1.locn_id = p2.pick_location 
                  where p2.device_id = '" . $wkDevice . "'
                  and   p2.pick_line_status in ('Al','Pg','Pl') ";
		// have an options record for pick to allow do later or not
		if ($wkDoLater == "T")
		{
		          $Query = $Query . " order by  p2.pick_line_priority, l3, p2.pick_location";
		} else {
		          $Query = $Query . " order by  l3 desc, p2.pick_location";
		}
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Items!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkNextOrder = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}

	if (($wkNextOrder != "") and ($wkLastOrder == ""))
	{
		$Query = "update pick_item 
                          set pick_line_status = upper(pick_line_status)
                          where pick_order = '" . $wkNextOrder . "'
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
		//echo("Update NextOrder to be This Order"  . "\n");
	}
	return $wkNextOrder;
	//echo("END " . __FILE__);
}

/* use all lines together for do one at a time is false */
/**
 * Update pick items to upshift the status for lines to work on
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkDevice
 * @param string $wkDoLater
 * @return string
 */
function resetAllLinesStatus ($Link, $wkDevice  ) 
{

		$Query = "update pick_item 
                          set pick_line_status = 'AL'
                          where pick_line_status = 'Al' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
		//echo("Updates Al to AL" );
		$Query = "update pick_item 
                          set pick_line_status = 'PG'
                          where pick_line_status = 'Pg' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();
		}
		//echo("Updates Pg to PG" );
		$Query = "update pick_item 
                          set pick_line_status = 'PL'
                          where pick_line_status = 'Pl' 
                          and device_id = '" . $wkDevice . "' ";
		//echo ($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Update Pick Items!<BR>\n");
			exit();

		}
		//echo("Updates Pl to PL \n" );
}


/**
 * get default device for the company that the order is for
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkOrder  Order to use to get Company 
 * @return array
 */
function getZoneDevice ($Link, $wkOrder )
{
	$wkResult = "";
	$Query = "select z1.default_device_id   
                  from pick_order p1
                  join zone z1 on p1.company_id  = z1.company_id
                  where p1.pick_order = '" . $wkOrder . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Order!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkResult  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wkResult == "") 
	{
		$Query = "select first 1  z1.default_device_id   
                  from zone z1 ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Order!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$wkResult  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	return $wkResult;
}


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
//echo("device:" . $tran_device);
$wk_use_do_later = getPickOption($Link, "DoLater");
if ($wk_use_do_later == "")
{
	$wk_use_do_later = "T";
}
$wk_by_one_order = getPickOption($Link, "OneataTime");
if ($wk_by_one_order == "")
{
	$wk_by_one_order = "T";
}
$wk_allow_restart_sequence = getPickOption($Link, "ResetLocnSeq");

$wk_lastLocn = getBDCScookie($Link, $tran_device, "picklocation" );
$Query = "select locn_seq from location where wh_id = '" ; 
$Query .= substr($wk_lastLocn,0,2)."' and locn_id = '";
$Query .= substr($wk_lastLocn,2,strlen($wk_lastLocn) - 2)."' ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Last Location!<BR>\n");
	exit();
}
$wk_lastLocnSeq = 0;
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == '')
	{
		$wk_lastLocnSeq = 0;
	} else {
		$wk_lastLocnSeq = $Row[0];
	}
}
//release memory
ibase_free_result($Result);
$wk_CurrentPickDir = getBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR" );
if ($wk_CurrentPickDir == "")
{	
	$wk_CurrentPickDir = "A";
}
$wk_PickDirOperator =  ($wk_CurrentPickDir == "A") ? " >= " : " <= ";

// check whether restart locn seq
if (isset($_POST['restartlocnseq']) or isset($_GET['restartlocnseq']))
{
	$wk_lastLocnSeq = 0;
	$wk_CurrentPickDir = "A";
	$wk_PickDirOperator =  ($wk_CurrentPickDir == "A") ? " >= " : " <= ";
	setBDCScookie($Link, $tran_device, "picklocation", "");
	setBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR", "A");
	// now run PKUA to recalc
	$tran_type = "PKUA";
	$my_object = '';
	$my_source = 'SSBSSKSSS';
	$my_ref = '';
	$tran_tranclass = "B";
	$tran_qty = 1;
	$my_location = $tran_device;
	$my_sublocn = '';
	// what is qty
	$my_message = "";
	if (!isset($message))
	{
		$message = "";
	}
	//echo("dotransaction_response($tran_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device)");
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	//echo($my_message);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	//echo($my_responsemessage);
	if ($my_responsemessage <> "Processed successfully ")
	{
		$message .= $my_responsemessage;
	}

	//release memory
	//if (isset($Result))
	//{
	//	ibase_free_result($Result); 
	//}
	
	//commit
	//ibase_commit($dbTran);
	//$dbtran = ibase_trans(IBASE_DEFAULT, $Link);
	
}

if ($wk_by_one_order == "T")
{
	//$wk_NextOrder = check4MoreOrders ($Link, $tran_device  );
	//$wk_NextOrder = check4MoreOrders ($Link, $tran_device, $wk_use_do_later  );
	$wk_NextOrder = check4MoreOrders ($Link, $tran_device, $wk_use_do_later, $wk_lastLocnSeq );
} else {
	resetAllLinesStatus ($Link, $tran_device  );

}
$Query = "select p1.pick_label_no ,p1.pick_line_status, p1.pick_order
          from pick_item  p1  
          where p1.device_id = '" . $tran_device . "' and p1.pick_line_status in ('AL','PG','PL')
          order by  (p1.pick_order_qty - COALESCE (p1.picked_qty,0))  
 ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Pick Location!<BR>\n");
	exit();
}
$wk_picklocns3 = "";
$wk_pickorder3 = array();
// Fetch the results from the database.
while  ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_picklocns3  = $Row[0];
	$wk_pickorder3[]  = $Row[2];
}

//release memory
ibase_free_result($Result);
//echo("picklocn3:" . $wk_picklocns3);
//$Query = "select first 1 p1.pick_label_no  from pick_item  p1  where p1.device_id = '" . $tran_device . "' and p1.pick_line_status in ('AL','PG') ";
$Query = "select first 1 p1.pick_label_no  
          from pick_item  p1  
          where p1.device_id = '" . $tran_device . "' and p1.pick_line_status in ('AL','PG','PL')
          and COALESCE (p1.picked_qty,0) < COALESCE (p1.pick_order_qty,0)
 ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Pick Location!<BR>\n");
	exit();
}
$wk_picklocns = "";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_picklocns  = $Row[0];
}

//release memory
ibase_free_result($Result);
//commit
//ibase_commit($dbtran);
//echo("picklocn:" . $wk_picklocns);
if ($wk_picklocns == "")
{
	if ($wk_picklocns3 != "")
	{
		// nothing to pick
		// so update it to PL
		// then either move it to C1 device
		foreach ($wk_pickorder3 as $wk_pickorder3_key => $wk_pickorder3_order)
		{
			$wk_pick_zone_device =  getZoneDevice ($Link, $wk_pickorder3_order);
			$Query = "update pick_item set pick_line_status = 'PL', device_id = '" . $wk_pick_zone_device . "' 
  			        where pick_item.device_id = '" . $tran_device . "' and pick_item.pick_line_status in ('AL','PG','PL')
				and pick_order = '" . $wk_pickorder3_order . "'
                                and ( not exists(select p2.pick_detail_id from pick_item_detail p2 where p2.pick_label_no=pick_item.pick_label_no  and p2.device_id = '" . $tran_device . "' and p2.despatch_location is null and p2.qty_picked>0 and p2.pick_detail_status in ('AL','PG','PL')))
            
 		";
			//echo($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Update Picks !<BR>\n");
			}
		}
		ibase_commit($dbtran);
		$dbtran = ibase_trans(IBASE_DEFAULT, $Link);
		// or go to gettolocn if there are issns on the device
	}
	// have no pick items so go to menu     
	header("Location: pick_Menu.php");
	exit();
}
/*
$Query = "select first 1 p1.pick_label_no  from pick_item  p1 join pick_location p2 on p2.pick_order = p1.pick_order and p2.device_id = p1.device_id   and p2.pick_location_status = 'OP'  where p1.device_id = '" . $tran_device . "' and p1.pick_line_status in ('AL','PG','PL') ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Pick Location!<BR>\n");
	exit();
}
$wk_picklocns2 = "";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_picklocns2  = $Row[0];
}
//release memory
ibase_free_result($Result);
//commit
//ibase_commit($dbtran);
echo("picklocn:" . $wk_picklocns2);
if ($wk_picklocns == "")
{
	// have no pick locns so go to get them
	header("Location: getorders.php?dopkal=" . urlencode("F"));
	exit();
}
*/
?>
<html>
<head>
<title>Pick From Location</title>
<style type="text/css">
body {
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: 0.8em;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
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
 header("Cache-Control:no-cache");
 header("Pragma:no-cache");
include "2buttons.php";
//include "checkdatajs.php";

// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
//whm2scanvars($Link, 'ssn','BARCODE', 'SSN');
//whm2scanvars($Link, 'altssn','ALTBARCODE', 'ALTSSN');
?>

<script type="text/javascript">
function errorHandler(errorMessage,url,line) {
document.write("<p><b>Error message:</b> "+errorMessage+"</p><br>");
document.write("<p><b>URL:</b> "+url+"</p><br>");
document.write("<p><b>Line No:</b> "+line+"</p>");
return true;
}
onerror = errorHandler;
var checklocn="T";
function noCheckLocn()
{
	checklocn = "N";
}
function processEdit() {
/* # check for valid location */
  var mytype;
  /*
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
	document.tomessage.message.value = "Not a Location";
  	return false;
  }
  else
  */
  {
	return true;
  }
}
</script>
</head>
<body>
<?php
	
$Query = "select max_pick_lines, pick_trolley_product_by from control";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total from Control!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_system_pick_cnt = $Row[0];
	$wk_system_product_by = $Row[1];
}
//release memory
ibase_free_result($Result);

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

/* currently is number of lines - should be number of products or number of orders which ever is less */
$Query = "select count(*) from pick_item";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and pick_line_status in ('AL','PG','PL') " ;
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_pick_cnt = $Row[0];
}
//release memory
ibase_free_result($Result);


$ssn = '';
$label_no = '';
$order = '';
$pick_wh_id = '';
$pick_company_id = '';
$pick_alternate_companys = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
if (isset($_POST['locnfound']))
{
	$location_found = $_POST['locnfound'];
}
if (isset($_GET['locnfound']))
{
	$location_found = $_GET['locnfound'];
	//echo("locn found ".$location_found);
}

if (isset($_POST['reason']))
{
	$reason = $_POST['reason'];
}
if (isset($_GET['reason']))
{
	$reason = $_GET['reason'];
}
//if ($wk_pick_cnt < $wk_system_pick_cnt)
if (($wk_pick_cnt < $wk_system_pick_cnt) or  ($wk_use_do_later == "F"))
{
	$order = "";
	//$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order, p3.wh_id, p3.company_id , CASE  WHEN l1.locn_seq IS NULL THEN 999999 ELSE l1.locn_seq END AS  l3 "; 
	$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order, p3.wh_id, p3.company_id , coalesce(l1.locn_seq, 999999 ) as l3 ,coalesce(p1.prod_id, p1.ssn_id, '') as line_id ,p3.alternate_companys   "; 
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
	$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
	$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id ";
	//$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and p2.company_id in ('ALL',p3.company_id) ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and  (p2.company_id in ('ALL',p3.company_id) ";
	$Query .= " or (V4POS(coalesce(p3.alternate_companys,p3.company_id),p2.company_id,0,1) > -1)) ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and ((p1.pick_line_status in ('AL','PG') " ;
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
	$Query .= ") ";
	// if many at a time must 
	if ($wk_by_one_order == "F")
	{
		// use pick dir and current locn seq to choose lines
		// if locn direction is A then >=
		// else <=
		$Query .= " and coalesce(l1.locn_seq, 999999) " . $wk_PickDirOperator . " '".$wk_lastLocnSeq."'";
	}

	//$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.pick_location";
	//$Query .= " order by  p1.pick_location";
	//$Query .= " order by  l1.locn_seq, p1.pick_location";
	if ($wk_use_do_later == "T")
	{
		$Query .= " order by  p1.pick_line_priority, l3  ";
	} else {
		$Query .= " order by  l3  ";
	} 
	if ($wk_CurrentPickDir == "D")
	{
        	$Query .= " desc ";
	}
	$Query .= ", p1.pick_location , line_id";
	//echo($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	
	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] <> '')
		{
			//ssn
			$ssn = $Row[0];
			$description = $Row[3];
			$uom = "EA";
		}
		$label_no = $Row[1];
		if ($Row[2] <> '')
		{
			//product
			$prod_no = $Row[2];
			$description = $Row[4];
			$uom = $Row[5];
		}
		$order_qty = $Row[6];
		$picked_qty = $Row[7];
		$required_qty = $order_qty - $picked_qty;
		$order = $Row[8];
		$pick_wh_id = $Row[9];
		$pick_company_id = $Row[10];
		$pick_alternate_companys = $Row[13];
		if (is_null($pick_alternate_companys))
		{
			$pick_alternate_companys = $pick_company_id;
		}
	}
	
	//release memory
	ibase_free_result($Result);
	/* 06/09/16 if required qty is null or 0 - try to reset the current location and reget the next */
	if ($wk_by_one_order == "F")
	{
		{
			file_put_contents($LogFile, "device " . $tran_device . "|" . $order . "|PICK_DIR" . $wk_CurrentPickDir .  "|PICK_LOCATION" . $wk_lastLocn  . "|Locn Seq" . $wk_lastLocnSeq . "|PICK DIR Operator " . $wk_PickDirOperator  ."|" .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
			file_put_contents($LogFile, "after 1st read for Pick Item :device " . $tran_device . "|" . $order . "|" . $label_no . "|" . $ssn . "|" .  $prod_no . "|" . $required_qty . "|" .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
		}
		if ($order == "" )
		{
			// no order found so try reversing the pick direction
			//$wk_lastLocnSeq = 0;
			$wk_CurrentPickDir =  ($wk_CurrentPickDir == "A") ? "D" : "A";
			$wk_PickDirOperator =  ($wk_CurrentPickDir == "A") ? " >= " : " <= ";
			//setBDCScookie($Link, $tran_device, "picklocation", "");
			setBDCScookie($Link, $tran_device, "CURRENT_PICK_DIR", $wk_CurrentPickDir);
			file_put_contents($LogFile, "reset direction :device " . $tran_device . "|" . $order . "|PICK_DIR" . $wk_CurrentPickDir .  "|PICK_LOCATION" . $wk_lastLocn  . "|Locn Seq" . $wk_lastLocnSeq . "|PICK DIR Operator " . $wk_PickDirOperator  ."|" .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
			// so redo query

			$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order, p3.wh_id, p3.company_id , coalesce(l1.locn_seq, 999999 ) as l3 ,coalesce(p1.prod_id, p1.ssn_id, '') as line_id ,p3.alternate_companys   "; 
			$Query .= "from pick_item p1 ";
			$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
			$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
			$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
			$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id ";
			//$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and p2.company_id in ('ALL',p3.company_id) ";
			$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and  (p2.company_id in ('ALL',p3.company_id) ";
			$Query .= " or (V4POS(coalesce(p3.alternate_companys,p3.company_id),p2.company_id,0,1) > -1)) ";
			$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location ";
			$Query .= " where device_id = '".$tran_device."'";
			$Query .= " and ((p1.pick_line_status in ('AL','PG') " ;
			$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
			$Query .= ") ";
			// if many at a time must 
			if ($wk_by_one_order == "F")
			{
				// use pick dir and current locn seq to choose lines
				// if locn direction is A then >=
				// else <=
				$Query .= " and coalesce(l1.locn_seq, 999999) " . $wk_PickDirOperator . " '".$wk_lastLocnSeq."'";
			}
		
			//$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.pick_location";
			//$Query .= " order by  p1.pick_location";
			//$Query .= " order by  l1.locn_seq, p1.pick_location";
			if ($wk_use_do_later == "T")
			{
				$Query .= " order by  p1.pick_line_priority, l3  ";
			} else {
				$Query .= " order by  l3  ";
			} 
			if ($wk_CurrentPickDir == "D")
			{
		        	$Query .= " desc ";
			}
			$Query .= ", p1.pick_location , line_id";
			//echo($Query);
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read Total!<BR>\n");
				exit();
			}
			
			// Fetch the results from the database.
			if ( ($Row = ibase_fetch_row($Result)) ) {
				if ($Row[0] <> '')
				{
					//ssn
					$ssn = $Row[0];
					$description = $Row[3];
					$uom = "EA";
				}
				$label_no = $Row[1];
				if ($Row[2] <> '')
				{
					//product
					$prod_no = $Row[2];
					$description = $Row[4];
					$uom = $Row[5];
				}
				$order_qty = $Row[6];
				$picked_qty = $Row[7];
				$required_qty = $order_qty - $picked_qty;
				$order = $Row[8];
				$pick_wh_id = $Row[9];
				$pick_company_id = $Row[10];
				$pick_alternate_companys = $Row[13];
				if (is_null($pick_alternate_companys))
				{
					$pick_alternate_companys = $pick_company_id;
				}
			}
			//release memory
			ibase_free_result($Result);
		}
	}
}
else
{
	$Query = "select first 1 p1.pick_label_no, p1.prod_id, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order , p3.wh_id, p3.company_id , p3.alternate_companys ";
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	//$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
	//$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and p2.company_id in ('ALL',p3.company_id) ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id and  (p2.company_id in ('ALL',p3.company_id) ";
	$Query .= " or (V4POS(coalesce(p3.alternate_companys,p3.company_id),p2.company_id,0,1) > -1)) ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and (p1.pick_line_status in ('AL','PG') ";
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
	$Query .= " order by  p1.pick_line_priority, p1.pick_location ";
	//echo($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$label_no = $Row[0];
		{
			//product
			$prod_no = $Row[1];
			$description = $Row[2];
			$uom = $Row[3];
		}
		$order_qty = $Row[4];
		$picked_qty = $Row[5];
		$required_qty = $order_qty - $picked_qty;
		$order = $Row[6];
		$pick_wh_id  = $Row[7];
		$pick_company_id = $Row[8];
		$pick_alternate_companys = $Row[9];
		if (is_null($pick_alternate_companys))
		{
			$pick_alternate_companys = $pick_company_id;
		}
	}
	
	//release memory
	ibase_free_result($Result);

}

 // 09-07-2014
 // encode the description
$description = htmlspecialchars($description);

if ($ssn == '')
{
	// a product - so get the order qtys picked qtys for all
	// order lines allocated to me
	$Query = "select sum(pick_order_qty), sum(picked_qty)  "; 
	$Query .= "from pick_item ";
	$Query .= " where  device_id = '".$tran_device."'";
	$Query .= " and prod_id = '".$prod_no."'";
	$Query .= " and pick_line_status in ('AL','PG','PL') " ;
	//$Query .= " or (pick_line_status ='PL' AND picked_qty < pick_order_qty)) " ;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Item<BR>\n");
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$order_qty = $Row[0];
			$picked_qty = $Row[1];
			$required_qty = $order_qty - $picked_qty;
		}
	}
	//release memory
	ibase_free_result($Result);
}

// set the cookie info so far

{
	// save original fields
	$cookiedata = "";
	{
		$cookiedata .= $label_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $order;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $ssn;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $prod_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $uom;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $description;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $required_qty;
	}
	$cookiedata .= '|';
	{
		// the location - not entered yet
	}
	$cookiedata .= '|';
		// the scanned ssn - not entered yet
	$cookiedata .= '|';
		// the qty taken  - not entered yet
	$cookiedata .= '|';
	//setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata);
	{
		//$LogFile = '/data/tmp/getfromlocn.log';
		file_put_contents($LogFile, "device " . $tran_device . "|" . $order . "|" . $label_no . "|" . $ssn . "|" .  $prod_no . "|" . $required_qty . "|" .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
	}
}

$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);

$wk_other1 = "";
$wk_Order_Zone = "";
$wk_Order_SubType = "";
$wk_restrict_zone = "F";
//$Query3 = "select other1 from pick_order where pick_order = '" . $order . "'"; 
$Query3 = "select other1, zone_c, pick_order_sub_type from pick_order where pick_order = '" . $order . "'"; 
if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Read Order!<BR>\n");
}
while ( ($Row3 = ibase_fetch_row($Result3)) ) {
	$wk_other1 = $Row3[0];
	$wk_Order_Zone = $Row3[1];
	$wk_Order_SubType = $Row3[2];
}
//release memory
ibase_free_result($Result3);
if ($wk_Order_Zone == "AZ" )
{
	$wk_Order_Zone = "" ;
}

if ($wk_Order_SubType <> "" )
{
	$Query3 = "select pos_restrict_by_zone from pick_order_sub_type where pos_id = '" . $wk_Order_SubType . "'"; 
	if (!($Result3 = ibase_query($Link, $Query3)))
	{
		echo("Unable to Read Order Sub Type!<BR>\n");
	}
	while ( ($Row3 = ibase_fetch_row($Result3)) ) {
		$wk_restrict_zone = $Row3[0];
	}
	//release memory
	ibase_free_result($Result3);
}

$got_ssn = 0;
//echo "<body>";
//echo("<FONT size=\"2\">\n");
echo ("<div>\n");
//echo("<form action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<tr><td>");
echo("Pick</td><td><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></td><td>");
//	echo("<p><label for=\"location\">ISSN:</label><input type=\"text\"  id=\"location\" name=\"location\" size=\"18\" onblur=\"return processEdit();\" class=\"locationform\"");
echo("SO</td><td><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></td></tr>");
//echo("</table>\n");
//echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<tr><td>");
if ($ssn <> '')
{
	echo("SSN</td><td colspan=\"3\"><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></td><td>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></td></tr>");
	//echo("</table>");
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<tr><td colspan=\"4\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"40\" value=\"$description\">");
	echo("</td></tr></table>");
}
else
{
	echo("Part</td><td colspan=\"3\"><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></td><td>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></td></tr>");
	//echo("</table>");
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<tr><td colspan=\"4\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"35\" value=\"$description\" >");
	echo("</td><td>");
	echo("<INPUT type=\"text\" readonly name=\"other1\" size=\"3\" value=\"$wk_other1\">");
	echo("</td></tr></table>");
}
echo ("</div>\n");
//for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
//{
//	echo("<BR>");
//}
//echo ("<div ID=\"col1\">\n");
echo ("<div id=\"col5\">\n");
echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<tr><td>");
//echo("Qty Reqd</td><td><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></td></tr></table>");
echo("Qty Reqd</td><td><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></td>");
//release memory

//echo ("<table BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
//echo ("<tr><td>\n");
echo ("<td>");
//echo("<form action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<form action=\"checkfromlocn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label_no\">");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
if ($ssn <> '')
{
	echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
	echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\">");
	echo("<INPUT type=\"hidden\" name=\"desc\" value=\"$description\">");
}
else
{
	echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
	echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\">");
	echo("<INPUT type=\"hidden\" name=\"desc\" value=\"$description\" >");
}
echo("<INPUT type=\"hidden\" name=\"required_qty\" value=\"$required_qty\">");
//echo("Location:</td><td><INPUT type=\"text\" name=\"location\" size=\"10\"");
//echo("Location:</td><td><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
if ($ssn <> "")
{
	echo("ISSN:</td><td><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
} elseif ( $wk_system_product_by == 'ISSN') {
	echo("ISSN:</td><td><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
} else {
	echo("Location:</td><td><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
}
//	echo("<p><label for=\"location\">ISSN:</label><input type=\"text\"  id=\"location\" name=\"location\" size=\"18\" onblur=\"return processEdit();\" class=\"locationform\"");
echo(" >");
//echo(" onchange=\"document.getlocn.submit\">");
echo ("</td></tr>");
echo ("</table>\n");
/*
echo ("<table ALIGN=\"BOTTOM\">\n");
echo ("<tr>\n");
if ($ssn <> '')
{
	echo("<th>Scan ISSN</th>\n");
} elseif ( $wk_system_product_by == 'ISSN') {
	echo("<th>Scan ISSN</th>\n");
} else {
	echo("<th>Scan Location</th>\n");
}
echo ("</tr>\n");
echo ("</table>\n");
*/
//echo total
//echo("</form>\n");
// if and status 'PL' items for this device then allow
// despatch button
$Query = "select first 1 pick_label_no "; 
$Query .= "from pick_item ";
$Query .= " where device_id = '".$tran_device."' ";
$Query .= " and pick_line_status = 'PL' ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!1<BR>\n");
	exit();
}

$have_despatch = "N";
if ( ($Row = ibase_fetch_row($Result)) ) {
	$have_despatch = "Y";
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbtran);
$dbtran = ibase_trans(IBASE_DEFAULT, $Link);

//close
//ibase_close($Link);

/*
$rcount = 5;
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<br>");
}
*/
echo ("</div>\n");
// ===============================================================================
//echo ("<div id=\"locns\">\n");
echo ("<div id=\"message3\">\n");
// want ssn label desc
if ($ssn <> '')
{
	$Query = "select first 3  s1.locn_id, s1.current_qty, s1.wh_id, s1.ssn_id "; 
	$Query .= "from issn s1  ";
	$Query .= "join location l1  on s1.wh_id = l1.wh_id and s1.locn_id = l1.locn_id ";
	$Query .= " where (s1.ssn_id = '".$ssn."'";
	$Query .= "  or s1.original_ssn = '".$ssn."')";
	$Query .= " and   s1.wh_id = '".$pick_wh_id."'";
	$Query .= " and   (s1.company_id = '".$pick_company_id."'";
	$Query .= " or (V4POS('".$pick_alternate_companys."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	//if ($wk_use_do_later == "F")
	{
		//$Query .= " and l1.locn_seq >= '" . $wk_lastLocnSeq . "'";
	}
        $Query .= " and s1.locn_id not in (select device_id from sys_equip) ";
}
else
{
	// a product
	// either scan a location ala weldmaster
	// or scan an issn ala fpg
	if ( $wk_system_product_by == 'ISSN')
	{
		$Query = "select first 3 s3.locn_id, s3.current_qty, s3.wh_id, s3.ssn_id, (coalesce(l3.locn_seq,999999) - $wk_lastLocnSeq) as path_seq "; 
		$Query .= "from issn s3  ";
		$Query .= "join location l3  on s3.wh_id = l3.wh_id and s3.locn_id = l3.locn_id ";
		$Query .= " where s3.prod_id = '".$prod_no."'";
		$Query .= " and s3.current_qty > 0 ";
		$Query .= " and   s3.wh_id = '".$pick_wh_id."'";
		//$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
		$Query .= " and  (s3.company_id = '".$pick_company_id."'";
		$Query .= " or (V4POS('".$pick_alternate_companys."',s3.company_id,0,1) > -1)) ";
		$Query .= " and pos('" . $allowed_status . ",AL," .  "',s3.issn_status,0,1) > -1";
		//if ($wk_use_do_later == "F")
		{
			//$Query .= " and l3.locn_seq >= '" . $wk_lastLocnSeq . "'";
		}
	        $Query .= " and s3.locn_id not in (select device_id from sys_equip) ";
		if ($wk_restrict_zone == "T")
		{
			if ($wk_Order_Zone != "")
			{
	        		$Query .= " and l3.zone_c = '" . $wk_Order_Zone . "'"; 
			}
		}
		if ($wk_by_one_order == "F")
		{
			// if locn direction is A then >=
			// else <=
			$Query .= " and coalesce(l3.locn_seq, 999999) " . $wk_PickDirOperator . " '".$wk_lastLocnSeq."'";
		}
	        $Query .= " order by path_seq ";
		if ($wk_CurrentPickDir == "D")
		{
	        	$Query .= " desc ";
		}
	} else {
		$Query = "select first 3 locn_id, qtysum, wh_id from (select s3.locn_id, sum(s3.current_qty) as qtysum, s3.wh_id , (coalesce(l3.locn_seq,999999) - $wk_lastLocnSeq) as path_seq "; 
		//$Query = "select s3.locn_id, s3.current_qty, s3.wh_id "; 
		$Query .= "from issn s3  ";
		$Query .= "join location l3  on s3.wh_id = l3.wh_id and s3.locn_id = l3.locn_id ";
		$Query .= " where s3.prod_id = '".$prod_no."'";
		$Query .= " and s3.current_qty > 0 ";
		$Query .= " and   s3.wh_id = '".$pick_wh_id."'";
		//$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
		$Query .= " and  (s3.company_id = '".$pick_company_id."'";
		$Query .= " or (V4POS('".$pick_alternate_companys."',s3.company_id,0,1) > -1)) ";
		$Query .= " and pos('" . $allowed_status . ",AL," .  "',s3.issn_status,0,1) > -1";
		//if ($wk_use_do_later == "F")
		{
			//$Query .= " and l3.locn_seq >= '" . $wk_lastLocnSeq . "'";
		}
	        $Query .= " and s3.locn_id not in (select device_id from sys_equip) ";
	        $Query .= " and l3.moveable_locn not in ('T') ";
		if ($wk_restrict_zone == "T")
		{
			if ($wk_Order_Zone != "")
			{
	        		$Query .= " and l3.zone_c = '" . $wk_Order_Zone . "'"; 
			}
		}
		if ($wk_by_one_order == "F")
		{
			// if locn direction is A then >=
			// else <=
			$Query .= " and coalesce(l3.locn_seq, 999999) " . $wk_PickDirOperator . " '".$wk_lastLocnSeq."'";
		}
		$Query .= " group by s3.wh_id, path_seq, s3.locn_id)";
		if ($wk_CurrentPickDir == "D")
		{
	        	$Query .= " order by path_seq  desc ";
		}
	}
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}


// echo headers
echo ("<table BORDER=\"1\" ALIGN=\"LEFT\">\n");
echo ("<thead>\n");
echo ("<tr>\n");
echo("<th></th>\n");
echo("<th></th>\n");
if ($ssn <> "")
{
	echo("<th>ISSN</th>\n");
} elseif ( $wk_system_product_by == 'ISSN') {
	echo("<th>ISSN</th>\n");
}
//echo("<th>Available</th>\n");

echo ("</tr>\n");

echo ("</thead>\n");
echo ("<tbody>\n");
$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<tr>\n");
	echo("<td>".$Row[2]."</td>\n");
	echo("<td>".$Row[0]."</td>\n");
	if ($ssn <> "")
	{
		echo("<td>".$Row[3]."</td>\n");
	} elseif ( $wk_system_product_by == 'ISSN') {
		echo("<td>".$Row[3]."</td>\n");
	} 
	echo("<td>".$Row[1]."</td>\n");
	echo ("</tr>\n");
	$rcount++;
}

echo ("</tbody>\n");
echo ("</table>\n");
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<br>");
}
/*
	echo("<form action=\".\" method=\"post\" name=tomessage id=tomessage>\n");
	echo("<input type=\"text\" name=\"message\" readonly size=\"40\" >");
	//echo("<INPUT type=\"text\" name=\"message\" hidden  >");
	echo("</form>");
*/
echo ("</div>\n");
ibase_free_result($Result);

// =================================================================================

//echo ("<div id=\"buttons3\">\n");
echo ("<div >\n");
$rcount = 12;
$rcount = 8;
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<br>");
}
	echo("<input type=\"text\" name=\"message\" readonly size=\"40\" ><br>");
	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'cancel.php', "N","Back_50x100.gif","Back","accept.gif","N");
	$wk_buttons = 0;
	echo ("</tr>");
	echo ("<tr>");
	//if (($got_ssn == 0) or ($prod_no == "NOPROD"))
	{
		// no locations for ssn or product
		global $wk_menu_output;
		$wk_use_output_type = isset($wk_menu_output) ? $wk_menu_output : "BUTTON";
		$wk_buttons++;
		$alt = "No Stock Reason";
/*
		if ($wk_buttons == 4)
		{
			echo ("<tr>");
		}
*/
		echo ("<td>");
		//echo("<form action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<form action=\"getOLreason.php\" method=\"post\" name=nostock>\n");
		echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label_no\">");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		//if ($ssn <> '')
		{
			echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
		}
		//else
		{
			echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
		}
		echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\">");
		echo("<INPUT type=\"hidden\" name=\"desc\" value=\"$description\">");
		echo("<INPUT type=\"hidden\" name=\"required_qty\" value=\"$required_qty\">");
		echo("<INPUT type=\"hidden\" name=\"location\" value=\"\">");
		echo("<INPUT type=\"hidden\" name=\"scannedssn\" value=\"\">");
		echo("<INPUT type=\"hidden\" name=\"picked_qty\" value=\"0\">");
/*
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
		}
		echo("</form>");
		echo ("</td>");
/*
		if ($wk_buttons == 5)
		{
			echo ("</tr>");
			$wk_buttons = 0;
		}
*/
	}
	//if ($have_despatch == "no not allow this")
	if ($have_despatch == "T")
	{
		$alt = "Despatch";
		$wk_buttons++;
/*
		if ($wk_buttons == 4)
		{
			echo ("<tr>");
		}
*/
		echo ("<td>");
		//echo("<form action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<form action=\"gettolocn.php\" method=\"post\" name=todespatch>\n");
		echo("<INPUT type=\"hidden\" name=\"order2\" value=\"$order\">");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$prod_no\">");
		//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
		//echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '">');

		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			//echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			//echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
			echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '">');
		}

		echo("</form>");
		echo ("</td>");
/*
		if ($wk_buttons == 5)
		{
			echo ("</tr>");
			$wk_buttons = 0;
		}
*/
	}
	//echo ("</tr>");
	//echo ("<tr>");
	if ($wk_use_do_later == "T")
	{
		$alt = "Do Later";
		$wk_buttons++;
/*
		if ($wk_buttons == 1)
		{
			echo ("<tr>");
		}
*/
		echo ("<td>");
		echo("<form action=\"transactionUP.php\" method=\"post\" name=tolater>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		//if ($ssn <> '')
		{
			echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
		}
		//else
		{
			echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
		}
		//echo("<INPUT type=\"IMAGE\" ");  
		//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		//echo('Blank_Button_50x100.gif" alt="' . $alt . '" onclick="noCheckLocn();">');
		//echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '">');
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" " .  ' onclick="noCheckLocn();"/>'  );
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt .  '" onclick="noCheckLocn();">' );
		}
		echo("</form>");
		echo ("</td>");
/*
		if ($wk_buttons == 2)
		{
			echo ("</tr>");
			$wk_buttons = 0;
		}
*/
	}
	{
		$alt = "UnAllocate";
		$wk_buttons++;
/*
		if ($wk_buttons == 1)
		{
			echo ("<tr>");
		}
*/
		echo ("<td>");
		echo("<form action=\"transactionUL.php\" method=\"post\" name=tostop>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label_no\">");
		//if ($ssn <> '')
		{
			echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
		}
		//else
		{
			echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
		}
		//echo("<INPUT type=\"IMAGE\" ");  
		//echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		//echo('Blank_Button_50x100.gif" alt="' . $alt . '" onclick="noCheckLocn();">');
		// //echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '">');
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" " .  ' onclick="noCheckLocn();"/>'  );
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt .  '" onclick="noCheckLocn();">' );
		}
		echo("</form>");
		echo ("</td>");
/*
		if ($wk_buttons == 2)
		{
			echo ("</tr>");
			$wk_buttons = 0;
		}
*/

	}

	// transfer a location to conveyor
	if ($wk_allow_transfer == "T")
	{
		$alt = "Transfer Location to Conveyor";
		echo ("<td>");
		echo("<form action=\"transferlocn.php\" method=\"post\" name=\"transferOut\">\n");
		//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" " .  ' />' . "\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			//echo('Blank_Button_50x100.gif" alt="' . $alt .  '" >' );
			echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		}
		echo("</form>");
		echo ("</td>");
	}
	// add a location to my group
	if ($wk_allow_transfer == "T")
	{
		$alt = "Add Trolley Location";
		echo ("<td>");
		echo("<form action=\"addlocn.php\" method=\"post\" name=\"addLocation\">\n");
		//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		// //echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		//echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		//echo('Blank_Button_50x100.gif" alt="' . $alt . '" >');
		echo("<INPUT type=\"hidden\" name=\"from\" value=\"getfromlocn.php\">");
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" " .  ' />' . "\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt .  '" >' );
		}
		echo("</form>");
		echo ("</td>");
	}
	// restart pick sequence 
	if ($wk_allow_restart_sequence == "T")
	{
		$alt = "ReStart Location Sequence";
		echo ("<td>");
		echo("<form action=\"getfromlocn.php\" method=\"post\" name=\"restartlocnseq\">\n");
		echo("<INPUT type=\"hidden\" name=\"from\" value=\"getfromlocn.php\">");
		echo("<INPUT type=\"hidden\" name=\"restartlocnseq\" value=\"T\">");
		$menuName = $alt;
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" " .  ' />' . "\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt .  '" >' );
		}
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("</table>");
echo ("</div>\n");
/*
echo ("<div id=\"message3\">\n");
	echo("<form action=\"\" method=\"post\" name=\"tomessage\">\n");
	//echo("<INPUT type=\"text\" name=\"message\" readonly size=\"40\" >");
	echo("<INPUT type=\"text\" name=\"message\" hidden  >");
	echo("</form>");
echo ("</div>\n");
*/
?>
<script type="text/javascript">
<?php
{
	if ($prod_no == "NOPROD")
	{
		echo("document.nostock.submit();\n");
	}
	else
	{
		if (isset($location_found))
		{
			if ($location_found == 0)
			{
				//echo("alert(\"Wrong Location\");\n");
				if ($ssn <> "")
				{
					//echo("document.tomessage.message.value = \"Wrong ISSN\";");
					//echo("document.tomessage.message.value = \"" . $reason . "\";");
					echo("document.getlocn.message.value = \"" . $reason . "\";");
				} elseif ( $wk_system_product_by == 'ISSN') {
					//echo("document.tomessage.message.value = \"Wrong ISSN\";");
					//echo("document.tomessage.message.value = \"" . $reason . "\";");
					echo("document.getlocn.message.value = \"" . $reason . "\";");
				} else {
					//echo("document.tomessage.message.value = \"Wrong Location\";");
					//echo("document.tomessage.message.value = \"" . $reason . "\";");
					echo("document.getlocn.message.value = \"" . $reason . "\";");
				}
			}
			else
			{
				//echo("alert(\"Location Found\");\n");
				//echo("document.tomessage.message.value = \"Location Found\";");
				echo("document.getlocn.message.value = \"Location Found\";");
			}
		}
		else
		{
			if ($ssn <> "")
			{
				//echo("document.tomessage.message.value = \"Scan ISSN for SSN\";");
				echo("document.getlocn.message.value = \"Scan ISSN for SSN\";");
			} elseif ( $wk_system_product_by == 'ISSN') {
				//echo("document.tomessage.message.value = \"Scan ISSN for Product\";");
				echo("document.getlocn.message.value = \"Scan ISSN for Product\";");
			} else {
				//echo("document.tomessage.message.value = \"Scan From Location\";");
				echo("document.getlocn.message.value = \"Scan From Location\";");
			}
		}
		echo("document.getlocn.location.focus();\n");
	}
}
?>
</script>
</body>
</html>
