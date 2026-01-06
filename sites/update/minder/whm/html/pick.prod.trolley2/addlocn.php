<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
echo("</head>\n");
?>
<?php
/*
go to a screen that has the order no and a location to use for trolley
allow upto 10 orders.
then can allocate and do the poal
*/
?>
<script type="text/javascript" src="../includes/prototype.js"></script>
<script type="text/javascript"  src="../includes/rico.js"></script>
<script type="text/javascript">
function callRICO()
{
   ajaxEngine.registerRequest('myOrdersList', 'checkLocns2.php');
   ajaxEngine.registerAjaxElement('locnsempty');
}
</script>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) {
document.write("<p><b>Error message:</b> "+errorMessage+"<br>");
document.write("<b>URL:</b> "+url+"<br>");
document.write("<p><b>Line No:</b> "+line+"</p>");
return true;
}
onerror = errorHandler;
function displayFormData() {
win2=open("","window2")
win2.document.open("text/plain")
win2.document.writeln("This Document has " + document.forms.length + " forms ");
for(i=0;i<document.forms.length;++i) {
  win2.document.writeln("Form "+i+" has "+document.forms[i].elements.length+" elements ");
  for(j=0;j<document.forms[i].elements.length;++j) {
    win2.document.writeln((j+1)+" A "+document.forms[i].elements[j].type+" element ");
  }
}
win2.document.close();
return false;
}

function processSubmit(canDo) {
  if (canDo == 1 ) {
    document.getdetails.submit(); 
    /* displayFormData(); */
    return true;
  } else {
    return false;
  }
}
function showLocns() {
var	wk_order_parm = "order=myorder" ;
var	wk_location_parm = "location=mylocn" ;
  var field3 = document.getElementById('message2');
	ajaxEngine.sendRequest("myOrdersList",wk_order_parm,wk_location_parm,"method=showempty");
/*	field3.value = "Click OK to Continue"; */
/*	alert(""); */
}
</script>
<?php

require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");
include "logme.php";
include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function checkEdit(thisvalue) {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(thisvalue); 
  if (mytype == "none")
  {
  	return false;
  }
  else
  {
	return true;
  }
}
function processEdit(thiscount) {
  /* document.getdetails.message.value="in process edit"; */
  var label1 = "location" + thiscount;
  var field1 = document.getElementById(label1);
  var label2 = "location" + (parseInt(thiscount) + 1);
  /* alert(label2); */
  var field3 = document.getElementById('message2');
  /* alert(tabfields); */
  if (field1.value == "")
  {
  }
  else
  {
  	if ( checkEdit(field1.value)  )
  	{
		if (tabfields>=(parseInt(thiscount) + 1))
		{
			/* alert("get next locn field "+label2); */
			/* var field2 = document.getElementById(label2); */
			/* field2.value=""; */
			/* field2.focus(); */
			processSubmit('1');
		}
		else
		{
			/* alert("about to submit"); */
			/* submit the form */
			processSubmit('1');
		}
  		field3.value=" ";
	  	return true; 
	  }
	  else
	  {
  		field3.value="Not a Location" ;
		field1.value="";
		field1.focus();
	  	return false;
	  }
  }
}
function checkSubmit() {
	return false;
}
</script>

<?php

/**
 * get Label Fields for Product label
 *
 * @param ibase_link $Link Connection to database
 * @param printer object $p 
 * @return string
 */
function getProductLabel($Link, $p )
{
    //echo("start:" . __FUNCTION__);

    $printerId = $p->data['printer_id'] ;
    //echo("printer:" . $printerId );
    $prodId = $p->data['PROD_PROFILE.PROD_ID'];
    $ssnId = $p->data['ISSN.SSN_ID'];
    $pickLabelNo = $p->data['PICK_ITEM.PICK_LABEL_NO'];
    $labelQty = $p->data['labelQty'];
    $sql = "SELECT WK_LABEL_DATA  FROM ADD_PRODUCT_LABEL (?, ?, ?, ?, ?) ";
    $query = ibase_prepare($Link, $sql);
    $output = false;
        
    if (false !== ($result = ibase_execute($query, $printerId, $prodId, $pickLabelNo, $ssnId, $labelQty ))) {
        if (false !== ($row = ibase_fetch_row($result))) {
            $output = $row[0];
        } else {
            $output = false;
        }
        ibase_free_result($result);
    }
    if ($output !== FALSE) {
        $wkParams = explode ("|", $output );
	foreach ($wkParams as $k => $v) {
            $wkParams2 = explode("=", $v,2);
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
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
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
    $sql = 'SELECT WORKING_DIRECTORY FROM SYS_EQUIP WHERE  DEVICE_ID = ?';
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

/****************************************************************************************************************/

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$wk_order = "";
//include "logme.php";
if (isset($_POST['location']))
{
	$wk_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$wk_location = $_GET['location'];
}

if (isset($_POST['pickuser']))
{
	$pickuser = $_POST['pickuser'];
}
if (isset($_GET['pickuser']))
{
	$pickuser = $_GET['pickuser'];
}
if (!isset($pickuser))
{
	$pickuser = $tran_user ;
}
if (isset($_POST['pickdevice']))
{
	$pickdevice = $_POST['pickdevice'];
}
if (isset($_GET['pickdevice']))
{
	$pickdevice = $_GET['pickdevice'];
}
if (isset($_POST['allocatedevice']))
{
	$allocatedevice = $_POST['allocatedevice'];
}
if (isset($_GET['allocatedevice']))
{
	$allocatedevice = $_GET['allocatedevice'];
}
if (isset($_POST['allocateorders']))
{
	$allocateorders = $_POST['allocateorders'];
}
if (isset($_GET['allocateorders']))
{
	$allocateorders = $_GET['allocateorders'];
}
//$allocateorders=10;

if (isset($_POST['from']))
{
	$from = $_POST['from'];
}
if (isset($_GET['from']))
{
	$from = $_GET['from'];
}
$allocateorder = array();
$allocatelocation = array();

if (isset($allocateorders))
{
	for ($wk_loop=1;$wk_loop<=$allocateorders;$wk_loop++)
	{
		if (isset($_POST['order' . $wk_loop]))
		{
			$allocateorder[$wk_loop] = $_POST['order' . $wk_loop];
		}		
		if (isset($_GET['order' . $wk_loop]))
		{
			$allocateorder[$wk_loop] = $_GET['order' . $wk_loop];
		}
		if (isset($_POST['location' . $wk_loop]))
		{
			$allocatelocation[$wk_loop] = $_POST['location' . $wk_loop];
		}	
		if (isset($_GET['location' . $wk_loop]))
		{
			$allocatelocation[$wk_loop] = $_GET['location' . $wk_loop];
		}
	}
	//print_r($allocatelocation, true);
	//print_r($allocateorder, true);
}

$wk_pick_method = "PL2";

//echo "<body>";
echo ("<body onload=\" callRICO();\">\n");
//echo("<h4>Add Locations</h4>\n");
echo("<div>");
echo("<FORM action=\"addlocn.php\" method=\"post\" name=\"getdetails\" onsubmit=\"return processSubmit('0');\">\n");
$got_ssn = 0;
if (!isset($pickuser))
{
	$pickuser = $tran_user;
}
if (!isset($allocatedevice))
{
	$allocatedevice = $tran_device;
}
if (!isset($pickdevice))
{
	//$pickdevice = "";
}
$Query = "select user_id from sys_user order by user_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}

$wk_tot_locns = 0;
$wk_tot_orders = 0;
$Query = "select count( distinct p2.pick_order)  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Orders Count!<BR>\n");
	exit();
}
while (($Row3 = ibase_fetch_row($Result))) {
	$wk_tot_orders = $Row3[0];
}

//release memory
ibase_free_result($Result);
{
	if (!isset($allocateorders))
	{
		$Query = "select count( distinct p1.pick_order)  "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		$Query .= "where p1.pick_line_status in ('AL','PG','PL') ";
		$Query .= "and   p1.device_id = '" . $tran_device . "' ";
		$Query .= "and   p2.pick_status in ('OP','DA') ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Orders Count!<BR>\n");
			exit();
		}
		while (($Row5 = ibase_fetch_row($Result))) {
			$allocateorders = $Row5[0];
		}		

		//release memory
		ibase_free_result($Result);
	}
}

echo("<INPUT name=\"pickuser\" type=\"hidden\" value=\"" . $pickuser . "\">\n");
echo("<INPUT name=\"allocatedevice\" type=\"hidden\" value=\"" . $allocatedevice . "\">\n");
echo("<INPUT name=\"allocateorders\" type=\"hidden\" value=\"" . $allocateorders . "\">\n");

if (isset($pickdevice))
{
	echo("<INPUT name=\"pickdevice\" type=\"hidden\" value=\"" . $pickdevice . "\">\n");
} else {
	//$pickdevice = "";
	echo ("<tr><td>\n");
	$Query = "select device_id from sys_equip where device_type = 'TR' order by device_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Users!<BR>\n");
		exit();
	}
	echo("Pick to Device:</td><td><SELECT name=\"pickdevice\">\n");
	// Fetch the results from the database.
	$rcount = 0;
	if (!isset($pick_device))
	{
		$pick_device = "";
	}
	while (($Row2 = ibase_fetch_row($Result))) {

		if ($pickdevice == $Row2[0])
		{
			echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
		}
		else
		{
			if ($pickdevice == "" and $rcount == 0)
			{
				$pickdevice = $Row2[0];
				echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
			}
			else
			{
				echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
			}
		}
		$rcount++;
	}
	echo("</SELECT></td></tr>\n");
	
	//release memory
	ibase_free_result($Result);
}
$wk_max_orders = 0;

$Query = "select max_pick_orders, max_pick_products from control ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_max_orders = $Row2[0];
	$wk_max_products = $Row2[1];
}
if (!isset($wk_max_orders2))
{
	$wk_max_orders2 = $wk_max_orders;
}
if (!isset($wk_max_products2))
{
	$wk_max_products2 = $wk_max_products;
}


{
	$Query = "select distinct p2.pick_order , p2.volume, p2.volume_uom  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query .= "where p1.pick_line_status in ('AL','PG','PL') ";
	$Query .= "and   p1.device_id = '" . $tran_device . "' ";
	$Query .= "and   p2.pick_status in ('OP','DA') ";
	$Query .= "order by p2.pick_order  ";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Orders!<BR>\n");
	exit();
}
$wk_count = 0;
$wk_div = 0;
echo("<div id=\"TAB" . $wk_div . "\">");
echo("<table BORDER=\"1\" ALIGN=\"LEFT\">");
echo("<caption>Scan Trolley Locations</caption>");
echo("<tr>");
echo("<th>Location</th>");
echo("<th>Order</th>");
echo("<th>Volume</th>");
echo("<th></th>");
echo("</tr>");
echo("<tbody>");
while (($Row4 = ibase_fetch_row($Result))) {
	$wk_count = $wk_count + 1;
// 1 - 5 -> div 1
// 6 - 10 -> div 2
	echo("<tr>");
	echo("<td>" . "<input name=\"location" . $wk_count . "\" type=\"text\" size=\"10\" id=\"location" . $wk_count . "\" onchange=\"return processEdit('". $wk_count . "');\" >" . "</td>");
	echo("<td>" . "<input name=\"order" . $wk_count . "\" type=\"text\" readonly size=\"10\" id=\"order" . $wk_count . "\" value=\"" . $Row4[0] . "\">" . "</td>");
	echo("<td>" . $Row4[1] . "</td>");
	//echo("<td>" . $Row4[2] . "</td>");
	echo("<td>" . "<input name=\"scancount" . $wk_count . "\" type=\"text\" readonly size=\"2\" value=\"" . $wk_count . "\">" . "</td>");
	echo("</tr>");
	if ( $wk_count < $allocateorders and fmod($wk_count, 5) == 0) {
		// end of div
		echo("</tbody>");
		echo ("</table>\n");
		echo("</div>");
		$wk_div = $wk_div + 1;
		echo("<div id=\"TAB" . $wk_div . "\">");
		echo("<table BORDER=\"1\" ALIGN=\"LEFT\">");
		echo("<caption>Scan Trolley Locations</caption>");
		echo("<tr>");
		echo("<th>Location</th>");
		echo("<th>Order</th>");
		echo("<th>Volume</th>");
		echo("<th></th>");
		echo("</tr>");
		echo("<tbody>");
	}
}
echo("</tbody>");
echo ("</table>\n");
echo("</div>");

//release memory
ibase_free_result($Result);

echo("</div>");
echo("<script type=\"text/javascript\">");
echo("var tabfields=" . $wk_count . ";");
echo("</script>");
// the to device is $tran_device
if (count($allocateorder) > 0 )
{
	$wk_mymessage = "";
	$wk_isok = "T";
	$wk_printer = "";
	for($wk_loop = 1; $wk_loop <= count($allocateorder); $wk_loop++)
	{
		$transaction_type = "POAL";
		// need order
		// need location
		// need trolley
		$my_object = $allocateorder[$wk_loop];
		$my_source = 'SSSSSSSSS';
		$tran_tranclass = "O";
		$tran_qty = 0;
		$my_sublocn = $pickdevice;
		$location  = $allocatelocation[$wk_loop];
		$my_ref =  $pickuser . '|' . $allocatedevice  . '|assign location to order' ;

		if ($location <> "") 
		{
			$my_message = "";
			$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = " ";
			}
			if (($my_responsemessage == " ") or
	        	    ($my_responsemessage == ""))
			{
				$my_responsemessage = "Processed successfully ";
			}
			if ($my_responsemessage == "Processed successfully ")
			{
				$my_responsemessage = "OK";	
			} else {
				$wk_isok = "F";
			}
			$wk_mymessage .= "POAL:" . $my_responsemessage;
		}
	}

}
//commit
ibase_commit($dbTran);
	
//close
//ibase_close($Link);

echo("<div id=\"col4\">");
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly id=\"message2\" value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
if (isset($wk_mymessage))
{
	echo($wk_mymessage);
}
echo("\"><br>\n");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
if (count($allocateorder) > 0 )
{
	echo("</FORM>\n");
	if (isset($from)) {
		echo("<FORM action=\"$from\" method=\"post\" name=\"continueordrs\" >\n");
		whm2buttons('ContinuePick', $from, "Y","Back_50x100.gif","Back","continue_picks.gif");
	} else {
		echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=\"continueordrs\" >\n");
		whm2buttons('ContinuePick', 'getfromlocn.php', "Y","Back_50x100.gif","Back","continue_picks.gif");
	}
	$wk_dofocus = "T";
	if (isset($wk_isok))
	{
		if ($wk_isok == "T")
		{
			echo("<script type=\"text/javascript\">");
			echo("document.continueordrs.submit();");
			echo("</script>");
			$wk_dofocus = "F";
		}
	}
	if ($wk_dofocus == "T")
	{
		echo("<script type=\"text/javascript\">");
		echo("document.getdetails.location1.focus();");
		echo("</script>");
	}
}
else
{
	whm2buttons('GetOrder', 'pick_Menu.php', "Y","Back_50x100.gif","Back","nextorder.gif","N");
	// show empty locations 
	{
		$alt = "Show Empty Locations";
		echo ("<td>");
		echo("<form action=\"\" method=\"post\" name=\"showempty\" onsubmit=\"return false;\">\n");
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		//echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '">');
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" onclick="showLocns();" >');
		echo("<INPUT type=\"hidden\" name=\"from\" value=\"$from\">");
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("</table>");
	echo("<script type=\"text/javascript\">");
	echo("document.getdetails.location1.focus();");
	echo("</script>");
}
echo("</div>");
echo("<div  id=\"locnsempty\"></div>\n");
?>
</body>
</html>
