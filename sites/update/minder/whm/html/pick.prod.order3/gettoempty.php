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
$order_no = '';
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
$scannedssn = '';
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
$original_type = $type;
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}

/*
	1st
	have product to find in trolley 
	use despatch_location for pick_item 
	where prod_id matches and in correct trolley
	and despatch_location not null
	and pick_item status 'PL' 
*/
	$wk_trolley = "";
	$wk_trolley_name = "";
	$Query = "select first 1 pick_item.despatch_location_group, "; 
	$Query .= " sys_equip.equipment_description_code "; 
	$Query .= "from pick_item  ";
	$Query .= "join sys_equip on sys_equip.device_id = pick_item.despatch_location_group  ";
	$Query .= " where pick_item.prod_id  = '".$order."'";
	$Query .= " and pick_item.pick_line_status = 'PL'";
	$Query .= " and pick_item.device_id = '" . $tran_device . "'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Picks!1<BR>\n");
		exit();
	}

	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_trolley = $Row[0];
		$wk_trolley_name = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
	echo("<H4>$wk_trolley_name</H4>");

	$Query = "select despatch_location "; 
	$Query .= "from pick_item  ";
	$Query .= " where prod_id  = '".$order."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and despatch_location starting '" . $wk_trolley . "'";
	$Query .= " group by despatch_location";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Picks!2<BR>\n");
		exit();
	}

	$got_prod = 0;
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($got_prod == 0)
		{
			$got_prod = 1;
			// echo headers
			echo ("<TABLE BORDER=\"1\">\n");
			echo ("<TR>\n");
			echo("<TH>Product</TH>\n");
			echo("<TH>Location Used</TH>\n");
			echo ("</TR>\n");
		}
		echo("<TR><TD>$order</TD><TD>$Row[0]</TD></TR>");
	}
	//release memory
	ibase_free_result($Result);

	if ($got_prod == 1)
	{
		echo ("</TABLE>\n");
	}
/*
	2nd
	have to find empty locations trolley 
	for location in trolley for this pick_label
	use despatch_location for pick_item 
	where in correct trolley
	and pick_item status 'PL' 
	
*/
	$Query = "select location.wh_id, location.locn_id, location.locn_name, count(pick_item.despatch_location) "; 
	$Query .= "from location  ";
	$Query .= "left outer join pick_item on  pick_item.despatch_location =  location.locn_id  "; 
	 $Query .= "and pick_item.pick_line_status = 'PL' ";
	$Query .= " where location.locn_id starting '".$wk_trolley."'";
	$Query .= " and location.locn_stat = 'OK'";
	$Query .= " group by location.wh_id, location.locn_id, location.locn_name";

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Locations!3<BR>\n");
		exit();
	}

	$got_prod = 0;
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[3] == 0 )
		{
			if ($got_prod == 0)
			{
				$got_prod = 1;
				// echo headers
				echo ("<TABLE BORDER=\"1\">\n");
				echo ("<TR>\n");
				echo("<TH>WH</TH>\n");
				echo("<TH>Location Unused</TH>\n");
				echo("<TH>Name</TH>\n");
				echo ("</TR>\n");
			}
			echo("<TR><TD>$Row[0]</TD>");
			echo("<TD>$Row[1]</TD>");
			echo("<TD>$Row[2]</TD></TR>");
		}
	}
	//release memory
	ibase_free_result($Result);

	if ($got_prod == 1)
	{
		echo ("</TABLE>\n");
	}
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	{
		$alt = "Back";
		echo ("<TR>");
		echo ("<TD>");
		echo("<FORM action=\"gettolocn.php\" method=\"post\" name=goback>\n");
		echo("<INPUT type=\"hidden\" name=\"type\" value=\"" . $type . "\" >");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/Back_50x100.gif" alt="' . $alt . '"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		echo ("</TR>");
	}
	echo ("</TABLE>");
}
?>
</HTML>
