<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
	
function getcurrentqty($Link, $prod_no, $wk_2_wh_id, $wk_2_locn_id, $allowed_status)
{
	$wk_current_qty = 0;
	$Query2 = "select sum(s3.current_qty) "; 
	$Query2 .= "from issn s3  ";
	$Query2 .= " where s3.prod_id = '".$prod_no."'";
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
	$Query = "select tr.prod_id, pp.short_desc, count(*)  "; 
	$Query .= "from transfer_request tr ";
	$Query .= "left outer join prod_profile pp on pp.prod_id=tr.prod_id  ";
	$Query .= " where tr.device_id = '".$tran_device."'";
	$Query .= " and tr.trn_status in ('PL','PG')";
	$Query .= " group by tr.trn_priority, tr.prod_id, pp.short_desc ";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;

echo("<H4>Replenish - Select Place Product</H4>\n");
//echo("<FONT size=\"2\">\n");
echo("<FORM action=\"gettolocn.php\" method=\"post\" name=getso>\n");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Product</TH>\n");
echo("<TH></TH>\n");
echo("<TH>#Lines</TH>\n");
echo("<TH>Qty</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	$prod_no = $Row[0];
	$picked_qty = getcurrentqty($Link, $prod_no, $wk_device_wh, $tran_device, $allowed_status);
	if ($picked_qty == "")
	{
		$picked_qty = 0;
	}
	if ($picked_qty == 0)
	{
		//nothing to place just cancel it
		$Query3 = "update transfer_request set trn_status='CN',device_id=NULL ";
		$Query3 .= " where  prod_id = '".$prod_no."'";
		$Query3 .= " and device_id = '".$tran_device."'";
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Update Lines!<BR>\n");
			exit();
		}
	}
	else
	{
		//echo("<TD>".$Row[0]."</TD>\n");
		echo("<TD>");
		echo("<INPUT type=\"submit\" name=\"product\" value=\"" . $Row[0] . "\" >");
		echo("<INPUT type=\"hidden\" name=\"desc\" value=\"" . $Row[1] . "\" >");
		echo("</TD>\n");
		echo("<TD>".$Row[1]."</TD>\n");
		echo("<TD>".substr($Row[2],0,30)."</TD>\n");
		echo("<TD>".$picked_qty."</TD>\n");
		echo ("</TR>\n");
	}
}

//release memory
ibase_free_result($Result);

echo ("</TABLE>\n");
echo("<BR>");

echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Select Product</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

{
	$Query = "select first 1 1 "; 
	$Query .= "from transfer_request ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and trn_status in ('AL', 'PG') ";
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
	$back_screen = "getfromlocn.php";
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
<SCRIPT>
<?php
{
	//echo("document.getso.product.focus();\n");
}
?>
</SCRIPT>
</HTML>
