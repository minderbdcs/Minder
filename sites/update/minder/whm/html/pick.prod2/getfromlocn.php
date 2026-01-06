<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo "<title>Pick From Location</title>";
include "viewport.php";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
echo("</head>\n");
echo "<body>";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
include "checkdatajs.php";
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
  /*
  mytype = checkLocn(document.getlocn.location.value); 
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

if ($wk_pick_cnt < $wk_system_pick_cnt)
{
	$Query = "select first 1 p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order "; 
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
	$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
	$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
	$Query .= "left outer join location l1 on l1.wh_id = p1.wh_id and l1.locn_id = p1.pick_location ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and ((p1.pick_line_status in ('AL','PG') " ;
	$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty and (p1.reason = '' or p1.reason is null))) " ;
	//$Query .= " or (p1.pick_line_status ='PL' AND p1.picked_qty < p1.pick_order_qty) " ;
	//$Query .= " or (p1.pick_line_status ='PL' AND exists(select p4.pick_detail_id from pick_item_detail p4 where p4.pick_label_no = p1.pick_label_no and p4.despatch_location is NULL))) " ;
	$Query .= ") ";
	//$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.pick_location";
	//$Query .= " order by  p1.pick_location";
	$Query .= " order by  l1.locn_seq, p1.pick_location";
	/* for PM
	must ensure that for the lines on the device
	that we sort by the location to get from
	and that the locations in the zone 'BW' 
	are the first to get from 
	*/
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
}
else
{
	$Query = "select first 1 p1.pick_label_no, p1.prod_id, p2.short_desc, p2.uom, p1.pick_order_qty, p1.picked_qty, p1.pick_order  ";
	$Query .= "from pick_item p1 ";
	$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
	$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
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
	}
	
	//release memory
	ibase_free_result($Result);

}

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
if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1  ";
	$Query .= " where s1.ssn_id = '".$ssn."'";
}
else
{
	$Query = "select s3.locn_id, sum(s3.current_qty), s3.wh_id "; 
	//$Query = "select s3.locn_id, s3.current_qty, s3.wh_id "; 
	$Query .= "from issn s3  ";
	$Query .= " where s3.prod_id = '".$prod_no."'";
	$Query .= " and s3.current_qty > 0 ";
	$Query .= " and (s3.wh_id < 'X' or s3.wh_id > 'X~') ";
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
echo("<FONT size=\"2\">\n");
echo ("<DIV>\n");
//echo("<FORM action=\"checkfromlocn.php\" method=\"post\" name=getlocn>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Pick</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></TD><TD>");
echo("SO</TD><TD><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></TD></TR></TABLE><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
if ($ssn <> '')
{
	echo("SSN</TD><TD><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("</TD></TR></TABLE><BR><BR>");
}
else
{
	echo("Part</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("</TD></TR></TABLE><BR><BR>");
}
// echo headers
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

echo ("</TABLE><BR><BR>\n");
echo ("</DIV>\n");
for($tcnt = 0; $tcnt <= ($rcount * 1.5); $tcnt++)
{
	echo("<BR>");
}
echo ("<DIV ID=\"col1\">\n");
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo ("<TR><TD>\n");
echo("Qty Reqd</TD><TD><INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\"></TD></TR></TABLE><BR><BR>");
//release memory
ibase_free_result($Result);

echo ("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
echo ("<TR><TD>\n");
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
echo("Location:</TD><TD><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\"");
echo(" >");
//echo(" onchange=\"document.getlocn.submit\">");
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
echo ("<TABLE ALIGN=\"BOTTOM\">\n");
echo ("<TR>\n");
echo("<TH>Scan Location</TH>\n");
echo ("</TR>\n");
echo ("</TABLE><BR><BR>\n");
//echo total
//echo("</FORM>\n");
// if and status 'PL' items for this device then allow
// despatch button
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

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'cancel.php', "N","Back_50x100.gif","Back","accept.gif");
	$wk_buttons = 0;
	//if (($got_ssn == 0) or ($prod_no == "NOPROD"))
	{
		// no locations for ssn or product
		$wk_buttons++;
		$alt = "No Stock Reason";
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
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
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
	}
	if ($have_despatch == "Y")
	{
		$alt = "Despatch";
		$wk_buttons++;
		if ($wk_buttons == 1)
		{
			echo ("<TR>");
		}
		echo ("<TD>");
		//echo("<FORM action=\"gettomethod.php\" method=\"post\" name=method>\n");
		echo("<FORM action=\"confirmto.php\" method=\"post\" name=todespatch>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">");
		echo("<INPUT type=\"IMAGE\" ");  
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/pick.despatch.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		if ($wk_buttons == 2)
		{
			echo ("</TR>");
			$wk_buttons = 0;
		}
	}
	echo ("</TABLE>");
echo ("</DIV>\n");
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
