<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo "<title>Pick From Location</title>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
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
whm2scanvars($Link, 'ssn','BARCODE', 'SSN');
whm2scanvars($Link, 'altssn','ALTBARCODE', 'ALTSSN');
?>

<script type="text/javascript">
var checklocn="T";
function noCheckLocn()
{
	checklocn = "N";
}
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkSsn(document.getlocn.location.value); 
  if (mytype == "SSN")
  {
	return true;
  }
  mytype = checkAltssn(document.getlocn.location.value); 
  if (mytype == "ALTSSN")
  {
	return true;
  }
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	if (document.getlocn.location.value !== "")
	{
		alert("Not a Location");
		document.getlocn.location.value = "";
	}
  	return false;
  }
  {
	return true;
  }
}
</script>

 </head>

<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
// add log function
//include "logme.php";

logme($Link, $tran_user, $tran_device, "start prepare getfromlocn");

$Query = "select max_pick_lines from control";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total from Control!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_system_pick_cnt = $Row[0];
}
//release memory
ibase_free_result($Result);

logme($Link, $tran_user, $tran_device, "start get count lines for device");
$Query = "select count(*) from pick_item";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and (pick_line_status in ('AL','PG')";
$Query .= " or (pick_line_status ='PL' AND picked_qty < pick_order_qty and (reason = '' or reason is null))) " ;
//$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty) " ;
//$Query .= " or (p1.pick_line_status ='PL' AND exists(select p4.pick_detail_id from pick_item_detail p4 where p4.pick_label_no = p1.pick_label_no and p4.despatch_location is NULL))) " ;
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
logme($Link, $tran_user, $tran_device, "end get count lines for device");


$ssn = '';
$label_no = '';
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

// try forcing this to false
// does it effect the speed
/*
if ($wk_pick_cnt < $wk_system_pick_cnt)
{
	logme($Link, $tran_user, $tran_device, "pick cnt " . $wk_pick_cnt . " less than system limit " . $wk_system_pick_cnt);
	logme($Link, $tran_user, $tran_device, "start get 1st line for device");
	$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order "; 
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
	$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
	$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location  ";
	//$Query .= "left outer join location l1 on l1.wh_id = s3.wh_id and l1.locn_id = s3.locn_id ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and ((p1.pick_line_status in ('AL','PG') " ;
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
	//$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty) " ;
	//$Query .= " or (p1.pick_line_status ='PL' AND exists(select p4.pick_detail_id from pick_item_detail p4 where p4.pick_label_no = p1.pick_label_no and p4.despatch_location is NULL))) " ;
	$Query .= ") ";
-*
	$Query .= " and (((s3.wh_id = 'NR') ";
	$Query .= " and   (s3.locn_id starting 'PM')) ";
	$Query .= " or   (s3.ssn_id is null)) ";
*-
	//$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.pick_location";
	//$Query .= " order by  p1.pick_location";
	$Query .= " order by  p1.pick_line_priority, l1.locn_seq, p1.pick_location";
	-* for PM
	must ensure that for the lines on the device
	that we sort by the location to get from
	and that the locations in the zone 'BW' 
	are the first to get from 
	*-
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
	}
	
	//release memory
	ibase_free_result($Result);
	logme($Link, $tran_user, $tran_device, "end 1st line for device");
}
else
*/
{
	logme($Link, $tran_user, $tran_device, "pick cnt " . $wk_pick_cnt . " more than system limit " . $wk_system_pick_cnt);
	logme($Link, $tran_user, $tran_device, "start get 2nd line for device");
	//$Query = "select first 1 p1.pick_label_no, p1.prod_id, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order  ";
	$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_description, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order  ";
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
	$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
	$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
	//$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and (p1.pick_line_status in ('AL','PG') ";
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
	//$Query .= " order by  p1.pick_line_priority, p1.pick_location ";
	//$Query .= " order by  p1.pick_line_priority, l1.locn_seq, p1.pick_location";
	//$Query .= " order by  p1.pick_line_priority ";
	// must have ordering so that all of a product is done serially
	// try having a procedure to return the create date of issn's in wh
	// passing the prod or ssn
	//$Query .= " order by (select wk_seq from pick_item_prod_seq(p3.wh_id,p3.company_id,p1.prod_id,p1.ssn_id)), nonull(p1.prod_id) || nonull(s1.prod_id) ";
	$Query .= " order by (select wk_seq from pick_item_prod_seq(p3.wh_id,p3.company_id,p1.prod_id,p1.ssn_id)) ";

	//echo($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$label_no = $Row[1];
		if ($Row[0] <> '')
		{
			//ssn
			$ssn = $Row[0];
			$description = $Row[3];
			$uom = "EACH";
		}
		if ($Row[2] <> '')
		{
			//product
			$prod_no = $Row[2];
			$description = $Row[4];
			$uom = $Row[5];
		}
		$order_qty = $Row[6];
		$picked_qty = $Row[7];
		if ($picked_qty == "")
		{
			$picked_qty = 0;
		}
		$required_qty = $order_qty - $picked_qty;
		$order = $Row[8];
	}
	
	//release memory
	ibase_free_result($Result);
	logme($Link, $tran_user, $tran_device, "end get 2nd line for device");

}

// no consolidation
/*
if ($ssn == '')
{
	// a product - so get the order qtys picked qtys for all
	// order lines allocated to me
	logme($Link, $tran_user, $tran_device, "start get picked qty");
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
	logme($Link, $tran_user, $tran_device, "end get picked qty");
}
*/

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

// want ssn label desc
// get the restriction on company and wh available
$wk_wh = "";
$wk_company = "";
// get wh and company from the order
$Query = "select p2.wh_id, p2.company_id "; 
$Query .= "from pick_order p2 ";
$Query .= "where p2.pick_order = '" . $order . "' ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Order!<BR>\n");
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_wh = $Row[0];
		$wk_company = $Row[1];
	}
}
//release memory
ibase_free_result($Result);

// get user flags
$wk_sysuser = "";
$wk_usercomp = "";
$wk_inventoryop = "";
$Query = "SELECT sys_admin, company_id, inventory_operator from sys_user";
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
	$wk_usercomp = $Row[1];
	$wk_inventoryop = $Row[2];
}
//release memory
ibase_free_result($Result);

if ($wk_company == "")
{
	if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
	{
		/* if im sysadmin then can access  all companys */
		$Query1 = "select company_id from company ";
	}
	else
	{
		/* if im not sysadmin then can only access companys in access company */
		$Query1 = "select company_id from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') ";
	}
	//echo($Query);
}
else
{
	$Query1 = "'" . $wk_company. "'";
}

if ($wk_wh == "")
{
	if ($wk_sysuser == "T") 
	{
		/* if im sysadmin then can access  all warehouses */
		$Query2 = "select wh_id from warehouse where wh_id < 'X' or wh_id >'X~'  ";
	}
	else
	{
		/* if im not sysadmin then can only access companys in access user */
		$Query2 = "select wh_id from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') ";
	}
}
else
{
	$Query2 = "'" . $wk_wh. "'";
}

// now get the query for displayed issns
if ($ssn <> '')
{
	$Query = "select ssn_id  "; 
	$Query .= "from ssn s1  ";
	$Query .= " where s1.ssn_id = '".$ssn."'";
	$wk_ssn_found = "";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query ssn!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_ssn_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_ssn_found == "")
	{
		/* is ssn an issn */
		$Query = "select s1.ssn_id, s1.locn_id, s1.current_qty, s1.wh_id, s1.prod_id "; 
		$Query .= "from issn s1  ";
		$Query .= " where s1.ssn_id = '".$ssn."'";
	}
	else
	{
		/* is ssn an ssn */
		$Query = "select first 4 s3.ssn_id, s3.locn_id, s3.current_qty, s3.wh_id, s3.prod_id "; 
		$Query .= "from issn s3  ";
		$Query .= " left outer join ssn s4 on s3.original_ssn = s4.ssn_id  ";
		$Query .= " where s3.original_ssn = '".$ssn."'";
		// wh must match wh of order
		$Query .= " and s3.wh_id in (" . $Query2 . ") ";
		// company must match company of order
		$Query .= " and s3.company_id in (" . $Query1 . ") ";
		$Query .= " and s3.current_qty > 0 ";
		$Query .= " and pos('" . $allowed_status . ",RS,AL," . "',s3.issn_status,0,1) > -1";
		//$Query .= " group by s3.wh_id, s3.locn_id";
		//$Query .= " order by s3.create_date desc";
		//$Query .= " order by s4.create_date desc";
		$Query .= " order by s4.po_receive_date ";
	}
}
else
{
/*
	$wk_wh = "";
	$wk_company = "";
	// get wh and company from the order
	$Query = "select p2.wh_id, p2.company_id "; 
	$Query .= "from pick_order p2 ";
	$Query .= "where p2.pick_order = '" . $order . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Order!<BR>\n");
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_wh = $Row[0];
			$wk_company = $Row[1];
		}
	}
	//release memory
	ibase_free_result($Result);

	// get user flags
	$wk_sysuser = "";
	$wk_usercomp = "";
	$wk_inventoryop = "";
	$Query = "SELECT sys_admin, company_id, inventory_operator from sys_user";
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
		$wk_usercomp = $Row[1];
		$wk_inventoryop = $Row[2];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_company == "")
	{
		if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
		{
			-* if im sysadmin then can access  all companys *-
			$Query1 = "select company_id from company ";
		}
		else
		{
			-* if im not sysadmin then can only access companys in access company *-
			$Query1 = "select company_id from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') ";
		}
		//echo($Query);
	}
	else
	{
		$Query1 = "'" . $wk_company. "'";
	}

	if ($wk_wh == "")
	{
		if ($wk_sysuser == "T") 
		{
			-* if im sysadmin then can access  all warehouses *-
			$Query2 = "select wh_id from warehouse where wh_id < 'X' or wh_id >'X~'  ";
		}
		else
		{
			-* if im not sysadmin then can only access companys in access user *-
			$Query2 = "select wh_id from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') ";
		}
	}
	else
	{
		$Query2 = "'" . $wk_wh. "'";
	}
*/

	//$Query = "select first 5 s3.ssn_id, s3.locn_id, s3.current_qty, s3.wh_id, s3.prod_id "; 
	$Query = "select first 4 s3.ssn_id, s3.locn_id, s3.current_qty, s3.wh_id, s3.prod_id "; 
	$Query .= "from issn s3  ";
	$Query .= " left outer join ssn s4 on s3.original_ssn = s4.ssn_id  ";
	$Query .= " where s3.prod_id = '".$prod_no."'";
	// wh must match wh of order
	$Query .= " and s3.wh_id in (" . $Query2 . ") ";
	// company must match company of order
	$Query .= " and s3.company_id in (" . $Query1 . ") ";
	$Query .= " and s3.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	//$Query .= " group by s3.wh_id, s3.locn_id";
	//$Query .= " order by s3.create_date desc";
	//$Query .= " order by s4.create_date desc";
	$Query .= " order by s4.po_receive_date ";
}
//echo($Query);
$rcount = 0;

logme($Link, $tran_user, $tran_device, "start get issn ");
if (!($Result5 = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}


$got_ssn = 0;
echo "<body>";
//echo("<FONT size=\"2\">\n");
echo ("<DIV id=\"locationform\">\n");
//echo ("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
//echo ("<TR><TD>\n");
//echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
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
//echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\"");
if ($ssn == "")
{
	//echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
	//echo("ISSN:</TD><TD COLSPAN=\"2\"><INPUT type=\"text\" name=\"location\" size=\"20\" ONBLUR=\"return processEdit();\"");
	echo("<p><label for=\"location\">ISSN:</label><input type=\"text\"  id=\"location\" name=\"location\" size=\"18\" onblur=\"return processEdit();\" class=\"locationform\"");
}
else
{
	//echo("ISSN:</TD><TD COLSPAN=\"2\"><INPUT type=\"text\" name=\"location\" size=\"20\" ONBLUR=\"return processEdit();\"");
	echo("<p><label for=\"location\">ISSN:</label><input type=\"text\"  id=\"location\" name=\"location\" size=\"18\" onblur=\"return processEdit();\" class=\"locationform\"");
}
echo(" >");
//echo(" onchange=\"document.getlocn.submit\">");
//echo ("</TD></TR>\n");
//echo("</p>");
//echo("<p>");
echo("<label for=\"required_qty2\">Qty</label><input type=\"text\" readonly id=\"required_qty2\" name=\"required_qty2\" size=\"3\" value=\"$required_qty\" class=\"locationform\">\n");
echo("</p>");
echo ("</DIV>\n"); // end of locationform div
//echo("<TR><TD>Picked:</TD><TD><INPUT type=\"text\" name=\"qtypicked\" size=\"6\" ONBLUR=\"return processEdit();\"");
//echo ("</TD></TR>\n");
//echo ("</TABLE>\n");
/*
echo ("<TABLE ALIGN=\"BOTTOM\">\n");
echo ("<TR>\n");
echo("<TH><input type=\"text\" readonly name=\"message\"></TH>\n");
echo ("</TR>\n");
echo ("</TABLE><BR><BR>\n");
*/
//echo total
//echo("</FORM>\n");
// if and status 'PL' items for this device then allow
// despatch button
logme($Link, $tran_user, $tran_device, "start whether despatch any ");
$Query = "select first 1 pick_label_no "; 
$Query .= "from pick_item ";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and pick_line_status = 'PL'";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$have_despatch = "N";
if ( ($Row = ibase_fetch_row($Result)) ) {
	$have_despatch = "Y";
}

// if and status 'DS' items for this device then allow
// despatch label button
logme($Link, $tran_user, $tran_device, "start whether despatch label any ");
$Query = "select first 1 pick_label_no "; 
$Query .= "from pick_item ";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and pick_line_status = 'DS'";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$have_despatch_label = "N";
/*
no address or despatch labels here in picking
if ( ($Row = ibase_fetch_row($Result)) ) {
	$have_despatch_label = "Y";
}
*/

echo ("<DIV id=\"col2\">\n");
// echo headers
echo ("<TABLE BORDER=\"1\" ALIGN=\"LEFT\">\n");
echo ("<TR>\n");
echo("<TH>SSN</TH>\n");
echo("<TH>WH</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Qty</TH>\n");
echo("<TH>Prod</TH>\n");
echo("<TH>Uom</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result5)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[3]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	echo("<TD>".$Row[4]."</TD>\n");
	echo("<TD>".$uom."</TD>\n");
	echo ("</TR>\n");
	$rcount++;
}
echo ("</TABLE>");
echo ("</DIV>\n"); // end of col2

logme($Link, $tran_user, $tran_device, "end get issn ");


echo ("<DIV id=\"btns\">\n");
	//echo ("<TR>");
	//echo ("<TD>");
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	whm2buttons('Accept', 'cancel.php', "N","Back_50x100.gif","Back","accept.gif","N");
	$wk_buttons = 0;
	//if (($got_ssn == 0) or ($prod_no == "NOPROD"))
	{
		// no locations for ssn or product
		$wk_buttons++;
		$alt = "No Stock Reason";
/*
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
*/
		echo ("<TD>");
		//echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<FORM action=\"getOLreason.php\" method=\"post\" name=nostock>\n");
		echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label_no\">");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		if ($ssn <> '')
		{
			echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
		}
		else
		{
			echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
		}
		echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\">");
		echo("<INPUT type=\"hidden\" name=\"desc\" value=\"$description\">");
		echo("<INPUT type=\"hidden\" name=\"required_qty\" value=\"$required_qty\">");
		echo("<INPUT type=\"hidden\" name=\"location\" value=\"\">");
		echo("<INPUT type=\"hidden\" name=\"scannedssn\" value=\"\">");
		echo("<INPUT type=\"hidden\" name=\"picked_qty\" value=\"0\">");
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
/*
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
*/
	}
	if ($have_despatch == "Y")
	{
		$alt = "Despatch";
		$wk_buttons++;
/*
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
*/
		echo ("<TD>");
		//echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<FORM action=\"confirmto.php\" method=\"post\" name=todespatch>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
/*
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
*/
	}
	if ($have_despatch_label == "Y")
	{
		$alt = "Despatch Label";
		$wk_buttons++;
/*
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
*/
		echo ("<TD>");
		echo("<FORM action=\"addrprodlabel.php\" method=\"post\" name=todespatch>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
/*
		echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '"></INPUT>');
*/
		echo("</FORM>");
		echo ("</TD>");
/*
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
*/
	}
/*
	{
		$alt = "Do Later";
		$wk_buttons++;
-*
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
*-
		echo ("<TD>");
		echo("<FORM action=\"transactionUP.php\" method=\"post\" name=tolater>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		if ($ssn <> '')
		{
			echo("<INPUT type=\"hidden\" name=\"ssn\" value=\"$ssn\">");
		}
		else
		{
			echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
		}
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '" onclick="noCheckLocn();">');
		//echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
-*
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
*-
	}
*/
	echo("<FORM action=\"pick_Menu.php\" method=\"get\" name=noorder>\n");
	echo("<input type=\"text\" readonly name=\"message\">\n");
	echo("</FORM>");
	//echo ("</TABLE>");
	//echo ("</TD></TR>");
echo ("</TR>");
echo ("</TABLE>");
echo ("</DIV>\n"); // end of btns div

echo("<br> </br>\n");
echo("<br> </br>\n");
echo("<br> </br>\n");
echo ("<DIV id=\"order\">\n");
//echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
//echo("<TR><TD>");
//echo("Pick</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></TD><TD>");
	//echo("<p><label for=\"label\">Pick:</label><input type=\"text\" readonly id=\"label\" name=\"label\" size=\"7\" value=\"$label_no\" class=\"order\">\n");
//echo("SO</TD><TD><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></TD></TR></TABLE><BR><BR>");
//echo("SO</TD><TD COLSPAN=\"2\"><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></TD></TR>");
	//echo("<label for=\"order\">SO</label><input type=\"text\" readonly id=\"order\" name=\"order\" size=\"15\" value=\"$order\" class=\"order\">\n");
//echo("</p>");
/*
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
*/
if ($order == "")
{
	$description = "No Lines Left to Pick - No Stock";
}
if ($ssn <> '')
{
/*
	echo("SSN</TD><TD><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE>");
*/
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	//echo("<TR><TD>");
	//echo("<TR><TD COLSPAN=\"5\">");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"50\" value=\"$description\">");
	//echo("</TD></TR></TABLE>");
	//echo("</TD></TR>");
	echo("<p><input type=\"text\" readonly id=\"desc\" name=\"desc\" size=\"50\" value=\"$description\" class=\"order\">\n");
	echo("</p>");
}
else
{
/*
	echo("Part</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"15\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"pickstodo\" size=\"3\" value=\"$wk_pick_cnt\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE>");
*/
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	//echo("<TR><TD COLSPAN=\"5\">");
	//echo("<INPUT type=\"text\" name=\"desc\" size=\"50\" value=\"$description\" >");
	//echo("</TD></TR></TABLE>");
	//echo("</TD></TR>");
	echo("<p><input type=\"text\" readonly id=\"desc\" name=\"desc\" size=\"50\" value=\"$description\" class=\"order\">\n");
	echo("</p>");
}

//echo ("</TABLE>\n");
//echo ("</DIV>\n"); // end of order div
/*
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<BR>");
}
*/
//echo ("<DIV ID=\"col1\">\n");
//echo ("<DIV id=\"reqdform\">\n");
//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
//echo ("<TR><TD>\n");
//echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></TD></TR></TABLE>");
//echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></TD></TR>");
//echo("<p><label for=\"required_qty\">Qty Reqd</label><input type=\"text\" readonly id=\"required_qty\" name=\"required_qty\" size=\"4\" value=\"$required_qty\" class=\"order\">\n");
//echo("<p><label for=\"required_qty\">Qty</label><input type=\"text\" readonly id=\"required_qty\" name=\"required_qty\" size=\"4\" value=\"$required_qty\" class=\"order\">\n");
//echo("</p>");
//release memory
//ibase_free_result($Result);

echo ("</DIV>\n"); // end of order div
//release memory
ibase_free_result($Result);
ibase_free_result($Result5);

logme($Link, $tran_user, $tran_device, "end whether despatch any and end prepare screen ");

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>
<SCRIPT>
<?php
{
/*
	if ($ssn == "")
	{
		echo("document.getlocn.message.value = \"Scan SSN\";\n");
	}
	else
	{
		echo("document.getlocn.message.value = \"Scan SSN\";\n");
	}
*/
	if ($order == "")
	{
		echo('document.noorder.message.value = "No Items Left with Stock to Pick";');
		echo("document.noorder.submit();\n");
	}
	elseif ($prod_no == "NOPROD")
	{
		echo("document.nostock.submit();\n");
	}
	else
	{
		if (isset($location_found))
		{
			if ($location_found == 0)
			{
				echo("alert(\"Wrong SSN\");\n");
			}
			else
			{
				echo("alert(\"SSN Found\");\n");
			}
		}
		echo("document.getlocn.location.focus();\n");
	}
}
?>
</SCRIPT>
</body>
</html>
