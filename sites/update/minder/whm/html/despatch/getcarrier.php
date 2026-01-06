<html>
 <head>
  <title>Despatch Consignment</title>
<?php
 include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="consign.css">');
}
else
{
	cho('<link rel=stylesheet type="text/css" href="consign-netfront.css">');
}
?>
 </head>
<script type="text/javascript">
function processEdit() {
  /* document.getcons.message.value="in process edit"; */
  if ( document.getcons.consignment.value=="")
  {
  	document.getcons.message.value="Must Enter the Consignment";
	document.getcons.consignment.focus()
  	return false
  }
  if ( document.getcons.carrier.value=="")
  {
  	document.getcons.message.value="Must Enter the carrier";
	document.getcons.carrier.focus()
  	return false
  }
  else
  {
    if ( document.getcons.message.value == "Must Enter the carrier")
    {
  	document.getcons.message.value="Enter the Pallet Type";
	document.getcons.pallet_type.focus()
  	return false
    }
  }
  if ( document.getcons.pallet_type.value!="NONE")
  {
    if ( document.getcons.pallet_qty.value=="")
    {
  	document.getcons.message.value="Must Enter the Pallet Qty";
	document.getcons.pallet_qty.focus()
  	return false
    }
  }
  if ( document.getcons.carton_qty.value=="")
  {
    if ( document.getcons.satchel_qty.value=="")
    {
  	document.getcons.message.value="Enter #Cartons or #Satchels";
	document.getcons.carton_qty.focus()
  	return false
    }
  }
/*
  else
  {
  -* carton qty or satchel qty must match qty for location or so *-
    if ( document.getcons.carton_qty.value!=document.getcons.consign_qty.value)
    {
  	document.getcons.message.value="Must Match Consignment Qty";
	document.getcons.carton_qty.focus()
  	return false
    }
  }
*/
/*
  if ( document.getcons.satchel_qty.value!="")
  {
  -* carton qty or satchel qty must match qty for location or so *-
    if ( document.getcons.satchel_qty.value!=document.getcons.consign_qty.value)
    {
  	document.getcons.message.value="Must Match Consignment Qty";
	document.getcons.satchel_qty.focus()
  	return false
    }
  }
*/
  if ( document.getcons.weight.value=="")
  {
    if ( document.getcons.volume.value=="")
    {
  	document.getcons.message.value="Must Enter Weight or Volume";
	document.getcons.weight.focus()
  	return false
    }
    else
    {
  	document.getcons.weight.value=250*document.getcons.volume.value 

  	document.getcons.message.value="Enter Payer";
	document.getcons.payer.focus()
  	return false
    }
  }
  else
  {
    if ( document.getcons.message.value=="Must Enter Weight or Volume")
    {
  	document.getcons.message.value="Enter Payer";
	document.getcons.payer.focus()
  	return false
    }
  }
  if ( document.getcons.payer.value=="R")
  {
    if ( document.getcons.account.value=="")
    {
      if ( document.getcons.message.value=="Enter Payer")
      {
  	document.getcons.message.value="Enter Account";
	document.getcons.account.focus()
  	return false
      }
    }
  }
  if ( document.getcons.label_qty.value=="")
  {
  	document.getcons.message.value="Must Enter Label Qty";
	document.getcons.label_qty.focus()
  	return false
  }
  if ( document.getcons.printer.value=="PC")
  {
    if ( document.getcons.message.value=="Must Enter Label Qty")
    {
  	document.getcons.message.value="Enter printer";
	document.getcons.printer.focus()
  	return false
    }
  }
  if ( document.getcons.service.value=="")
  {
  	document.getcons.message.value="Must Enter the carrier Service";
	document.getcons.service.focus()
  	return false
  }
  return true
}
function saveCarrier() {
  	document.getperson.carrier_id.value =  document.getcons.carrier.value;
  	document.getperson.consignment.value=  document.getcons.consignment.value;
  	document.getperson.submit(); 
	return true;
}
</script>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

// function to test whether string is an int
function holds_int($str)
{
	return preg_match("/^-?[0-9]+$/", $str);
}

/* ====================================================================================================== */
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['carrier_id']))
{
	$carrier_id = $_POST['carrier_id'];
}
if (isset($_GET['carrier_id']))
{
	$carrier_id = $_GET['carrier_id'];
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}

if (isset ($carrier_id))
{
	// have carrier but this field is carrier_id | isso
	//$carrier_id = explode ("|", $carrier_id ) [0];
	$carrier_fields = explode ("|", $carrier_id ) ;
	$carrier_id = $carrier_fields[0];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include("logme.php");

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($order))
{ 
	//var_dump($order);
	if (!empty($order))
	{
		setBDCScookie($Link, $tran_device, "order", $order );
		setBDCScookie($Link, $tran_device, "location", "" );
	}
}
if (isset($location))
{ 
	//var_dump($location);
	if (!empty($location))
	{
		setBDCScookie($Link, $tran_device, "location", $location );
		setBDCScookie($Link, $tran_device, "order", "" );
	}
}
//var_dump ($_SESSION);

if (!isset($location))
{
	$location = getBDCScookie($Link, $tran_device, "location" );
	if ($location == "")
	{
		unset ($location);
	}
}
if (!isset($order))
{
	$order = getBDCScookie($Link, $tran_device, "order" );
	if ($order == "")
	{
		unset ($order);
	}
}
$Query = "select default_carrier_id, default_despatch_printer, default_connote_weight, default_connote_qty_labels, default_connote_pack, default_connote_pack_qty from control";
$Query = "select default_carrier_id, default_despatch_printer, default_connote_weight, default_connote_qty_labels, default_connote_pack, default_connote_pack_qty,despatch_label_printer from control";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<br>\n");
	exit();
}

while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] <> "")
	{
		$default_carrier = $Row[0];
	}
	if ($Row[1] <> "")
	{
		// default despatch printer
		$default_printer = $Row[1];
	}
	if ($Row[2] <> "")
	{
		$default_weight = $Row[2];
	}
	if ($Row[3] <> "")
	{
		$default_label_qty = $Row[3];
	}
	else
	{
		$default_label_qty = 0;
	}
	if ($Row[4] <> "")
	{
		$default_pack_type = $Row[4];
	}

	if ($Row[5] <> "")
	{
		$default_pack_type_qty = $Row[5];
	}
	if ($Row[6] <> "")
	{
		// default despatch label printer
		$default_printer = $Row[6];
	}
}
//release memory
ibase_free_result($Result);
if (isset($carrier_id))
{
	//echo "                         have carrier id " . $carrier_id . " set to default ";
	$default_carrier = $carrier_id ; 
} else {
	$carrier_id = $default_carrier ; 

}


$Query = "select sum(issn.current_qty)  "; 
$Query .= "from issn  ";
$Query .= "join pick_item_detail on pick_item_detail.ssn_id = issn.ssn_id  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where issn.issn_status = 'DS' " ;
if (isset($location))
{
	$Query .= "and issn.wh_id = '" . substr($location,0,2) . "' ";
	$Query .= "and issn.locn_id = '" . substr($location,2,strlen($location) - 2) . "'";
}
else
{
	if (isset($order))
	{
		$Query .= "and pick_item.pick_order = '" . $order . "' ";
	}
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<br>\n");
	exit();
}

while ( ($Row = ibase_fetch_row($Result)) ) {
	//if ($Row[0] <> $last_wh)
	{
		$consign_qty = $Row[0];
	}
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);
echo("<body>\n");
echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form action=\"transactionOT.php\" method=\"post\" name=getcons ONSUBMIT=\"return processEdit();\">");
if (isset($location))
{
	echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
	echo("Location: <input type=\"text\" readonly name=\"wk_location\" size=\"10\" value=\"". substr($location,2,strlen($location) - 2) . "\" >");
}
else
{
	if (isset($order))
	{
		echo("S.O. No:<input type=\"text\" readonly name=\"order\" size=\"10\" value=\"$order\" >");
	}
}

echo("<input type=\"text\" name=\"consign_qty\" readonly size=\"4\" value=\"$consign_qty\"><br>\n");
echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
//echo("Consignment:<input type=\"text\" name=\"consignment\" maxlength=\"20\" size=\"10\"><br>\n");
echo("Consignment:<input type=\"text\" name=\"consignment\" maxlength=\"20\" size=\"10\" ");
if (isset($consignment))
{
	echo ("value=\"" . $consignment . "\"");
}
echo ("><br>\n");
//echo("Carrier:<input type=\"text\" name=\"carrier\" size=\"10\" maxlength=\"10\"><br>");
echo("Carrier:<select name=\"carrier\" size=\"1\" class=\"sel3\" onchange=\"return saveCarrier();\">\n");
$Query = "select carrier_id, default_connote_isso from carrier order by carrier_id desc"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Carrier!<br>\n");
	exit();
}
$wk_carrier_line = 0;
while ( ($Row = ibase_fetch_row($Result)) ) {
 	$wk_carrier_line ++;
	//if ($wk_carrier_line == 1)
	if (isset($default_carrier))
	{
		if ($default_carrier == $Row[0])
		{
			echo( "<option value=\"" . $Row[0] . "|" . $Row[1] . "\" selected>$Row[0]\n");
			$default_connote_isso = $Row[1];
		}
		else
		{
			echo( "<option value=\"" . $Row[0] . "|" . $Row[1] . "\">$Row[0]\n");
		}
	}
	else
	{
		echo( "<option value=\"" . $Row[0] . "|" . $Row[1] . "\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
echo("Pallets:<select name=\"pallet_type\" size=\"1\" class=\"sel1\">\n");
echo( "<option value=\"NONE\">NONE\n");
echo( "<option value=\"UNKNOWN\">UNKNOWN\n");
echo( "<option value=\"CHEP\">CHEP\n");
echo( "<option value=\"OWN\">OWN\n");
echo("</select>\n");
if (isset($default_pack_type))
{
	if ($default_pack_type == 'P')
	{
		echo("Qty:<input type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\" value=\"". $default_pack_type_qty . "\"><br>\n");
	}
	else
	{
		echo("Qty:<input type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\"><br>\n");
	}
	if ($default_pack_type == 'C')
	{
		echo("Cartons:<input type=\"text\" name=\"carton_qty\" size=\"4\" maxlength=\"4\" value=\"" . $default_pack_type_qty . "\">\n");
	}
	else
	{
		echo("Cartons:<input type=\"text\" name=\"carton_qty\" size=\"4\" maxlength=\"4\">\n");
	}
	if ($default_pack_type == 'S')
	{
		echo("Satchels:<input type=\"text\" name=\"satchel_qty\" size=\"4\" maxlength=\"4\" value=\"" . $default_pack_type_qty . "\"><br>\n");
	}
	else
	{
		echo("Satchels:<input type=\"text\" name=\"satchel_qty\" size=\"4\" maxlength=\"4\"><br>\n");
	}
}
else
{
	echo("Qty:<input type=\"text\" name=\"pallet_qty\" size=\"4\" maxlength=\"4\"><br>\n");
	echo("Cartons:<input type=\"text\" name=\"carton_qty\" size=\"4\" maxlength=\"4\">\n");
	echo("Satchels:<input type=\"text\" name=\"satchel_qty\" size=\"4\" maxlength=\"4\"><br>\n");
}
if (isset($default_weight))
{
	echo("Wt(Kgs):<input type=\"text\" name=\"weight\" size=\"4\" maxlength=\"5\" value=\"" . $default_weight. "\">\n");
}
else
{
	echo("Wt(Kgs):<input type=\"text\" name=\"weight\" size=\"4\" maxlength=\"5\">\n");
}
echo("Vol(M");
echo("<SUP>3</SUP>");
echo("):<input type=\"text\" name=\"volume\" size=\"4\" maxlength=\"5\"><br>\n");
echo("Payer:<select name=\"payer\" size=\"1\" class=\"sel2\">\n");
echo( "<option value=\"S\">SENDER\n");
echo( "<option value=\"R\">RECEIVER\n");
echo("</select>\n");
echo("<input type=\"text\" name=\"account\" size=\"10\" maxlength=\"10\"><br>\n");
if (isset($default_label_qty))
{
	echo("Qty Labels:<input type=\"text\" name=\"label_qty\" size=\"4\" maxlength=\"4\" value=\"" . $default_label_qty . "\">\n");
}
else
{
	echo("Qty Labels:<input type=\"text\" name=\"label_qty\" size=\"4\" maxlength=\"4\">\n");
}
/*
if (isset($default_printer))
{
	echo("Ptr:<input type=\"text\" name=\"printer\" size=\"2\" maxlength=\"2\" value=\"" . $default_printer . "\"><br>\n");
}
else
{
	echo("Ptr:<input type=\"text\" name=\"printer\" size=\"2\" maxlength=\"2\" value=\"PC\"><br>\n");
}
*/
echo ("Ptr:<select name=\"printer\" > ");
$Query = "SELECT device_id FROM sys_equip WHERE device_type = 'PR' ORDER BY device_id ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read sys_equip!<br>\n");
	exit();
}
if (!isset($default_printer))
{
	$default_printer = "PC";
}
while ( ($Row = ibase_fetch_row($Result)) ) 
{
	echo ("<option value=\"" . $Row[0] . "\"");
	if ($default_printer == $Row[0])
	{
		echo(" selected ");
	}
	echo(">" . $Row[0] . "</option>");
}
//release memory
ibase_free_result($Result);
echo ("</select>");
echo ("<br>");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<input type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\" id=\"col1\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
echo("<br><br>\n");
echo("<select name=\"service\" size=\"1\" id=\"col4\" class=\"col4\" >\n");
/*
echo( "<option value=\"EXP\" SELECTED >Service\n");
echo( "<option value=\"GEN\" >General\n");
*/
if (isset($carrier_id))
{
//echo "have carrier " . $carrier_id;
	$wk_default_service_type = "";
	$Query = "select default_service_type from carrier where carrier_id = '" . $carrier_id . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read carrier 2!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) 
	{
		$wk_default_service_type =  $Row[0] ;
	}
	//release memory
	ibase_free_result($Result);
//echo "have carrier service default " . $wk_default_service_type;
	// the default service type is either numeric and a record id or alpha and a code
	if (holds_int($wk_default_service_type))
	{
//echo "holds an int";
		$wk_default_service_id = $wk_default_service_type;
	} else {
//echo "holds not int";
		$wk_default_service = $wk_default_service_type;
	}
	//
	$Query = "SELECT service_type, description, record_id FROM carrier_service  WHERE carrier_id = '" . $carrier_id . "'  ";
//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read carrier service!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) 
	{
		echo ("<option value=\"" . $Row[0] . "\"");
		if (isset($wk_default_service))
		{
			if ($wk_default_service == $Row[0])
			{
				echo(" selected ");
			}
		}
		if (isset($wk_default_service_id))
		{
			if ($wk_default_service_id == $Row[2])
			{
				echo(" selected ");
			}
		}
		echo(">" . $Row[1] . "</option>");
	}
	//release memory
	ibase_free_result($Result);
}
echo("</select>\n");
echo("<br><br>\n");
/*
//echo("</form>\n");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo("</div>\n");
// put other resed to getcarrier here
echo("<form action=\"getcarrier.php\" method=\"post\" name=getperson\n>");
//echo("<form action=\"$_SERVER['PHP_SELF']\" method=\"post\" name=getperson\n>");
echo("<input type=\"hidden\" name=\"carrier_id\">\n");  
echo("<input type=\"hidden\" name=\"consignment\">\n");  
if (isset($order))
{
	echo("<input type=\"hidden\" name=\"order\" value=\"") ;
	echo($order);
	echo( "\"  >\n");
}
if (isset($location))
{
	echo("<input type=\"hidden\" name=\"location\" value=\"") ;
	echo($location);
	echo( "\"  >\n");
}
echo("</form>");
echo("<script type=\"text/javascript\">\n");
if (isset($default_connote_isso))
{
	if ($default_connote_isso == 'T')
	{
		if (isset($location))
		{
			echo('document.getcons.consignment.value="' . $location . '"');
		}
		else
		{
			if (isset($order))
			{
				echo('document.getcons.consignment.value="' . $order . '"');
			}
		}
	}
	else
	{
		echo('document.getcons.message.value="Scan Consignment No";');
		echo('document.getcons.consignment.focus();');
	}
}
else
{
	echo('document.getcons.message.value="Scan Consignment No";');
	echo('document.getcons.consignment.focus();');
}
?>
</script>
</body>
</html>
