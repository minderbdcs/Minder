<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo "<title>Replenish From Location</title>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
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

<script>
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	/* alert("Not a Location"); */
  	return false;
  }
  else
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
/*
6-11-06
must get transfer request
order by priority
then by locn_seq of FROM location of prods

*/
{
	$Query = "select first 1 p1.trn_line_no, p1.prod_id, p2.short_desc, p2.uom  "; 
	$Query .= "from transfer_request p1 ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
	//$Query .= "left outer join location l1 on l1.wh_id = p1.to_wh_id and l1.locn_id = p1.to_locn_id ";
	$Query .= "left outer join issn i1 on i1.prod_id = p1.prod_id ";
	$Query .= "left outer join location l1 on l1.wh_id = i1.wh_id and l1.locn_id = i1.locn_id ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and p1.trn_status in ('AL','PG') " ;
	$Query .= " and (p1.to_wh_id is not null) " ;
	$Query .= " and (p1.to_locn_id is not null) " ;
	$Query .= " and (i1.wh_id not starting 'X') ";
	$Query .= " and (i1.wh_id <> 'SY') ";
	$Query .= " and i1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . "',i1.issn_status,0,1) > -1";
	$Query .= " order by  p1.trn_priority,l1.locn_seq,p1.to_wh_id,p1.to_locn_id";
	//echo($Query);
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Total!<BR>\n");
		exit();
	}
	
	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$label_no = $Row[0];
		//product
		$prod_no = $Row[1];
		$description = $Row[2];
		$uom = $Row[3];
	}
	
	//release memory
	ibase_free_result($Result);
}

// want all the required qtys for this prod
{
	$picked_qty = getcurrentqty($Link, $prod_no, $wk_device_wh, $tran_device, $allowed_status);
	if ($picked_qty == "")
	{
		$picked_qty = 0;
	}
	//echo ("dev_wh:". $wk_device_wh.":dev:" .$tran_device.":picked:".$picked_qty.":");
	$Query = "select p1.qty, p1.to_wh_id,p1.to_locn_id,l1.max_qty,l1.min_qty,l1.reorder_qty "; 
	$Query .= "from transfer_request p1 ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.to_wh_id and l1.locn_id = p1.to_locn_id ";
	$Query .= " where p1.device_id = '".$tran_device."'";
	$Query .= " and p1.prod_id = '" . $prod_no . "' " ;
	$Query .= " and ((p1.trn_status in ('AL','PG') " ;
	$Query .= " ) " ;
	$Query .= ") ";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Picks!<BR>\n");
		exit();
	}

	// Fetch the results from the database.
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$order_qty1 = $Row[0];
		$wk_2_wh_id = $Row[1];
		$wk_2_locn_id = $Row[2];
		$wk_max_qty1 = $Row[3];
		$wk_min_qty1 = $Row[4];
		$wk_reorder_qty1 = $Row[5];
		if ($order_qty1 == 0)
		{
			//echo("order qty is [". $order_qty1 . "]");
			$wk_current_qty = getcurrentqty($Link, $prod_no, $wk_2_wh_id, $wk_2_locn_id, $allowed_status);
			if ($wk_current_qty <= $wk_reorder_qty1)
			{
				$order_qty1 = $wk_max_qty1 - $wk_current_qty;
			}
			else
			{
				$order_qty1 = 0;
			}
		}
		$required_qty1 = $order_qty1 ;
		$required_qty = $required_qty + $required_qty1;
		$order_qty = $order_qty + $order_qty1;
		//echo("order qty is [". $order_qty . "]");
		//echo("required qty is [". $required_qty . "]");
	}
	//release memory
	ibase_free_result($Result);
}

$required_qty = $required_qty - $picked_qty;
// want ssn label desc
{
	$Query = "select s3.locn_id, sum(s3.current_qty), s3.wh_id "; 
	//$Query = "select s3.locn_id, s3.current_qty, s3.wh_id "; 
	$Query .= "from issn s3  ";
	$Query .= "join location l1 on l1.wh_id = s3.wh_id and l1.locn_id = s3.locn_id ";
	$Query .= " where s3.prod_id = '".$prod_no."'";
	$Query .= " and s3.current_qty > 0 ";
	$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
	$Query .= " and (s3.wh_id <> 'SY') ";
	$Query .= " and (l1.store_type <> 'PS') "; //not a pick location
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
	$Query .= " group by s3.wh_id, s3.locn_id";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}


$got_ssn = 0;
echo "</head>\n";
echo "<body bgcolor=\"#FFFFF0\">\n";
echo "<h2>Replenish From Location</h2>\n";
//echo("<FONT size=\"2\">\n");
echo ("<DIV id=\"col1\">\n");
//echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Line</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"4\" value=\"$label_no\"></TD>");
//echo("</TR></TABLE><BR><BR>");
//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
//echo("<TR><TD>");
echo("<TD>");
if ($ssn == '')
{
	echo("Prod</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR>");
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD colspan=\"5\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"60\" value=\"$description\" >");
	//echo("</TD></TR></TABLE><BR><BR><BR><BR><BR><BR><BR><BR>");
	echo("</TD></TR>");
}
// echo headers
echo ("<TR><TD colspan=\"5\">\n");
echo ("<TABLE BORDER=\"1\" ALIGN=\"LEFT\">\n");
echo ("<TR>\n");
echo("<TH>WH</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Qty Available</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo ("</TR>\n");
	$rcount++;
}

//echo ("</TABLE><BR><BR>\n");
//echo ("</TABLE></TD></TR></TABLE>\n");
echo ("</TABLE></TD></TR>\n");
//echo ("</DIV>\n");
/*
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<BR>");
}
*/
//release memory
ibase_free_result($Result);
// now if sum required qty is zero then nothing to do
if ($required_qty <= 0)
{
	/*
	if ($picked_qty > 0)
	{
		// go to next product
		// adjust status to PL and priority lower
		// present on screen the move locations button
	}
	else
	{
		// order qty is 0
		// adjust status to PL and priority lower
	}
	*/
	{
		$Query = "update transfer_request set trn_status='PL' ";
		$Query .= " where  prod_id = '".$prod_no."'";
		$Query .= " and device_id = '".$tran_device."'";
	}
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Lines!<BR>\n");
		exit();
	}
	echo("<script>");
	echo("document.getlocn.submit();\n");
	echo("</script>");
}
if ($got_ssn == 0)
{
	echo "no stock";
	// cannot do this since no more stock
	// if any prod on device then adjust status to PL
	// present on screen the move locations button
	// else
	// so cancel it
}
//echo ("<DIV ID=\"col1\">\n");
echo ("<TR><TD colspan=\"6\">\n");
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo ("<TR><TD>\n");
echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"" .$required_qty . "\"></TD><TD>");
echo("On Device</TD><TD><INPUT type=\"text\" readonly name=\"picked_qty\" size=\"4\" value=\"" . $picked_qty . "\"></TD><TD>");
//echo("Total</TD><TD><INPUT type=\"text\" readonly name=\"replenish_qty\" size=\"4\" value=\"" . $order_qty . "\"></TD></TR></TABLE><BR><BR>");
echo("Total</TD><TD><INPUT type=\"text\" readonly name=\"replenish_qty\" size=\"4\" value=\"" . $order_qty . "\"></TD></TR></TABLE>");
echo ("</TD></TR>\n");
echo ("<TR><TD colspan=\"5\">\n");

echo ("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
echo ("<TR><TD>\n");
echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label_no\">");
if ($ssn == '')
{
	echo("<INPUT type=\"hidden\" name=\"prod\" value=\"$prod_no\">");
	echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\">");
	echo("<INPUT type=\"hidden\" name=\"desc\" value=\"$description\" >");
}
echo("<INPUT type=\"hidden\" name=\"required_qty\" value=\"$required_qty\">");
//echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\"");
echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
echo(" >");
//echo(" onchange=\"document.getlocn.submit\">");
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE ALIGN=\"BOTTOM\">\n");
echo ("<TR>\n");
echo("<TH>Scan Location</TH>\n");
echo ("</TR>\n");
//echo ("</TABLE><BR><BR>\n");
echo ("</TABLE></TD></TR>\n");
//echo total
//echo("</FORM>\n");
// if and status 'PL' items for this device then allow
// despatch button
$Query = "select first 1 trn_line_no "; 
$Query .= "from transfer_request ";
$Query .= " where device_id = '".$tran_device."'";
$Query .= " and trn_status in ('PL','PG')";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$have_despatch = "N";
if ( ($Row = ibase_fetch_row($Result)) ) {
	$have_despatch = "Y";
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo ("<TR><TD colspan=\"5\">\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'cancel.php', "N","Back_50x100.gif","Back","accept.gif","N");
	$wk_buttons = 1;
	//if (($got_ssn == 0) or ($prod_no == "NOPROD"))
	{
		// no locations for ssn or product
		$wk_buttons++;
		$alt = "No Stock Reason";
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
		if ($wk_buttons == 4)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
	}
	if ($have_despatch == "Y")
	{
		$alt = "Despatch";
		$wk_buttons++;
		if ($wk_buttons == 4)
		{
			echo ("<TR>");
		}
		echo ("<TD>");
		//echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<FORM action=\"gettoso.php\" method=\"post\" name=todespatch>\n");
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		//echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '"></INPUT>');
		echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
	}
	echo ("</TABLE>");
	echo ("</TD></TR></TABLE>");
echo ("</DIV>\n");
?>
<script>
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
				echo("alert(\"Wrong Location\");\n");
			}
			else
			{
				echo("alert(\"Location Found\");\n");
			}
		}
		echo("document.getlocn.location.focus();\n");
	}
}
?>
</script>
</body>
</html>
