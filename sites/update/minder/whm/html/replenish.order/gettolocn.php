<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Replenish get the TO location</title>
<link rel=stylesheet type="text/css" href="product.css">
<?php
include "viewport.php";
?>
 </head>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";
//include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
/*
  mytype = checkLocn(document.gettolocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
*/
  {
	return true;
  }
}
</script>

<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
$current_despatchZone = getBDCScookie($Link, $tran_device, "CURRENT_DESPATCH_ZONE"  );
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$company_id = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
	
if (isset($_POST['product']))
{
	$prod_no = $_POST['product'];
}
if (isset($_GET['product']))
{
	$prod_no = $_GET['product'];
}
if (isset($_POST['company']))
{
	$company_id = $_POST['company'];
}
if (isset($_GET['company']))
{
	$company_id = $_GET['company'];
}
if (isset($_POST['desc']))
{
	$desc = $_POST['desc'];
}
if (isset($_GET['desc']))
{
	$desc = $_GET['desc'];
}
if (isset($_POST['description']))
{
	$desc = $_POST['description'];
}
if (isset($_GET['description']))
{
	$desc = $_GET['description'];
}
if (isset($_POST['locnfound']))
{
	$location_found = $_POST['locnfound'];
}
if (isset($_GET['locnfound']))
{
	$location_found = $_GET['locnfound'];
	//echo("locn found ".$location_found);
}
function getcurrentqty($Link, $prod_no, $company_id, $wk_2_wh_id, $wk_2_locn_id, $allowed_status)
{
	$wk_current_qty = 0;
	$Query2 = "select sum(s3.current_qty) "; 
	$Query2 .= "from issn s3  ";
	$Query2 .= " where s3.prod_id = '".$prod_no."'";
	$Query2 .= " and s3.company_id  = '".$company_id."'";
	$Query2 .= " and s3.current_qty > 0 ";
	$Query2 .= " and (s3.wh_id = '" . $wk_2_wh_id."' and s3.locn_id = '" . $wk_2_locn_id ."') ";
	$Query2 .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query2 .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
	//echo($Query2);

	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read ISSNs!<BR>\n");
		exit();
	}
	else
	{
		if ( ($Row2 = ibase_fetch_row($Result2)) ) 
		{
			$wk_current_qty = $Row2[0];
		}
	}
	//release memory
	ibase_free_result($Result2);

	return $wk_current_qty;
} /* end function get current qty */

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
$wh_device_wh = "";
$Query = "select first 1 wh_id from location "; 
$Query .= "where locn_id = '" . $tran_device . "' "; 
$Query .= " and (wh_id not starting 'X') ";
$Query .= " order by wh_id  ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Location<BR>\n");
	$wk_device_wh = "";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_device_wh = $Row[0];
	}
}
//release memory
ibase_free_result($Result);

{
	$Query = "select  prod_id, company_id, to_wh_id, to_locn_id, qty  "; 
	$Query .= "from transfer_request  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and trn_status in ('PL','PG')";
	$Query .= " and prod_id = '" . $prod_no . "'";
	$Query .= " and company_id = '" . $company_id . "'";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;

echo("<body bgcolor=\"#FFFFF0\">\n");
//echo("<h2>Replenish - Place Product Location</h2>\n");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TH>");
echo "Replenish";
echo("</TH><TH>");
echo "-";
echo("</TH><TH>");
echo "Place";
echo("</TH><TH>");
echo "Product";
echo("</TH><TH>");
echo "Location\n";
echo("</TH></TR>\n");
echo("</TABLE>");
echo("<BR>");
echo("<BR>");
//echo("<FONT size=\"2\">\n");
//echo("<FORM action=\"gettoqty.php\" method=\"post\" name=gettolocn>\n");
echo("<FORM action=\"checktolocn.php\" method=\"get\" name=gettolocn>\n");
echo("<INPUT type=\"hidden\" name=\"product\" value=\"" . $prod_no . "\" >");
echo("<INPUT type=\"hidden\" name=\"company\" value=\"" . $company_id . "\" >");
echo("<INPUT type=\"hidden\" name=\"description\" value=\"" . $desc . "\" >");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Product</TH>\n");
echo("<TH>Company</TH>\n");
echo("<TH>Device Qty</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Reqd Qty</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	$prod_no = $Row[0];
	$company_id = $Row[1];
	$picked_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_device_wh, $tran_device, $allowed_status);
	if ($picked_qty == "")
	{
		$picked_qty = 0;
	}
	{
		echo("<TD>".$Row[0]."</TD>\n");
		echo("<TD>".$Row[1]."</TD>\n");
		echo("<TD>".$picked_qty."</TD>\n");
		echo("<TD>");
		//echo("<INPUT type=\"submit\" name=\"location\" value=\"" . $Row[1] . $Row[2] . "\" >");
		echo($Row[2] . $Row[3]);
		echo("</TD>\n");
		$rcount = $rcount + 1;
		$wk_order_qty = $Row[4];
		if ($wk_order_qty == "")
		{
			$wk_to_wh = $Row[2];
			$wk_to_locn = $Row[3];
			$wk_current_qty = getcurrentqty($Link, $prod_no, $company_id, $wk_to_wh, $wk_to_locn, $allowed_status);
/*
			$Query4 = "select l1.max_qty,l1.min_qty,l1.reorder_qty "; 
			$Query4 .= "from location l1 ";
			$Query4 .= " where l1.wh_id = '".$wk_to_wh."' and ";
			$Query4 .= " l1.locn_id = '".$wk_to_locn."'";
			//echo($Query4);

			if (!($Result4 = ibase_query($Link, $Query4)))
			{
				echo("Unable to Read Locations!<BR>\n");
				exit();
			}

			// Fetch the results from the database.
			$wk_max_qty = 0;
			$wk_min_qty = 0;
			$wk_reorder_qty = 0;
			if ( ($Row4 = ibase_fetch_row($Result4)) ) {
				$wk_max_qty = $Row4[0];
				$wk_min_qty = $Row4[1];
				$wk_reorder_qty = $Row4[2];
			}
			if ($wk_current_qty <= $wk_reorder_qty)
			{
				$order_qty1 = $wk_max_qty - $wk_current_qty;
			}
			else
			{
				$order_qty1 = 0;
			}
*/
			$order_qty1 = $wk_current_qty;
			echo("<TD>".$order_qty1."</TD>\n");
		}
		else
		{
			// a nonimated qty
			echo("<TD>".$wk_order_qty."</TD>\n");
		}
		echo ("</TR>\n");
	}
}

//release memory
ibase_free_result($Result);

echo ("</TABLE>\n");
echo("<BR>");

echo("<INPUT type=\"hidden\" name=\"nolocations\" value=\"" . $rcount . "\" >");
echo ("<TABLE>\n");
echo ("<TR><TD>\n");
echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\"");
//echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
echo(" >");
//echo(" onchange=\"document.getlocn.submit\">");
echo ("</TD></TR>\n");
echo ("<TR>\n");
echo("<TD colspan=\"2\">Scan the Location in Zone " .$current_despatchZone . " </TD>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

{
	$Query = "select first 1 1 "; 
	$Query .= "from transfer_request ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and trn_status in ('PL','PG') ";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_back_items = 0;
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$got_back_items++;
	//$got_back_items++;
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

if ($got_back_items == 0)
{
	$back_screen = "replenish_Menu.php";
}
else
{
	$back_screen = "gettoso.php";
}
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', $back_screen,"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
{
	//echo("document.getso.product.focus();\n");
	if (isset($location_found))
	{
		if ($location_found == 0)
		{
			echo("alert(\"Wrong Location\");\n");
		}
/*
		else
		{
			echo("alert(\"Location Found\");\n");
		}
*/
	}
	echo("document.gettolocn.location.focus();\n");
}
?>
</script>
</body>
</html>
