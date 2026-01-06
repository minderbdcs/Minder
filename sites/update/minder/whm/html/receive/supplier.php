<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Add System Wide Suppliers</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD-netfront.css">');
}
?>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
onerror = errorHandler
 </script>
 </head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
include 'logme.php';

/* 
here add/update the person record for the supplier
$addperson 
$addpersonname 
$addperson_type 
$addpersonfirstname 
$addpersonlastname 
then put into prod_supplier
supplers product id
supplier no = $addperson
*/
$received_ssn_qty = 0;
$wk_goback = "F";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

{
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );

	$retfrom = getBDCScookie($Link, $tran_device, "retfrom" );
	$product = getBDCScookie($Link, $tran_device, "product" );
	$label_qty1 = getBDCScookie($Link, $tran_device, "label_qty1" );
	$ssn_qty1 = getBDCScookie($Link, $tran_device, "ssn_qty1" );
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$label_qty3 = getBDCScookie($Link, $tran_device, "label_qty3" );
	$ssn_qty3 = getBDCScookie($Link, $tran_device, "ssn_qty3" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
}

if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['line']))
{
	$line = $_POST['line'];
}
if (isset($_GET['line']))
{
	$line = $_GET['line'];
}
if (isset($_POST['carrier']))
{
	$carrier = $_POST['carrier'];
}
if (isset($_GET['carrier']))
{
	$carrier = $_GET['carrier'];
}
if (isset($_POST['vehicle']))
{
	$vehicle = $_POST['vehicle'];
}
if (isset($_GET['vehicle']))
{
	$vehicle = $_GET['vehicle'];
}
if (isset($_POST['container']))
{
	$container = $_POST['container'];
}
if (isset($_GET['container']))
{
	$container = $_GET['container'];
}
if (isset($_POST['pallet_type']))
{
	$pallet_type = $_POST['pallet_type'];
}
if (isset($_GET['pallet_type']))
{
	$pallet_type = $_GET['pallet_type'];
}
if (isset($_POST['pallet_qty']))
{
	$pallet_qty = $_POST['pallet_qty'];
}
if (isset($_GET['pallet_qty']))
{
	$pallet_qty = $_GET['pallet_qty'];
}
if (isset($_POST['received_qty']))
{
	$received_qty = $_POST['received_qty'];
}
if (isset($_GET['received_qty']))
{
	$received_qty = $_GET['received_qty'];
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}
if (isset($_POST['sday']))
{
	$sday = $_POST['sday'];
}
if (isset($_GET['sday']))
{
	$sday = $_GET['sday'];
}
if (isset($_POST['smonth']))
{
	$smonth = $_POST['smonth'];
}
if (isset($_GET['smonth']))
{
	$smonth = $_GET['smonth'];
}
if (isset($_POST['syear']))
{
	$syear = $_POST['syear'];
}
if (isset($_GET['syear']))
{
	$syear  = $_GET['syear'];
}
if (isset($_POST['addperson']))
{
	$addperson = $_POST['addperson'];
}
if (isset($_GET['addperson']))
{
	$addperson  = $_GET['addperson'];
}
if (isset($_POST['addpersonname']))
{
	$addpersonname = $_POST['addpersonname'];
}
if (isset($_GET['addpersonname']))
{
	$addpersonname  = $_GET['addpersonname'];
}
if (isset($_POST['addperson_type']))
{
	$addperson_type = $_POST['addperson_type'];
}
if (isset($_GET['addperson_type']))
{
	$addperson_type  = $_GET['addperson_type'];
}
if (isset($_POST['addpersonfirstname']))
{
	$addpersonfirstname = $_POST['addpersonfirstname'];
}
if (isset($_GET['addpersonfirstname']))
{
	$addpersonfirstname  = $_GET['addpersonfirstname'];
}
if (isset($_POST['addpersonlastname']))
{
	$addpersonlastname = $_POST['addpersonlastname'];
}
if (isset($_GET['addpersonlastname']))
{
	$addpersonlastname  = $_GET['addpersonlastname'];
}
if (isset($_POST['addsupplierprod']))
{
	$addsupplierprod = $_POST['addsupplierprod'];
}
if (isset($_GET['addsupplierprod']))
{
	$addsupplierprod  = $_GET['addsupplierprod'];
}
if (isset($_POST['grn']))
{
	$grn = $_POST['grn'];
}
if (isset($_GET['grn']))
{
	$grn = $_GET['grn'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (!isset($printer))
{
	$printer = "PA";
}
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
}
if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($_POST['received_ssn_qty']))
{
	$received_ssn_qty = $_POST['received_ssn_qty'];
}
if (isset($_GET['received_ssn_qty']))
{
	$received_ssn_qty = $_GET['received_ssn_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['from']))
{
	$from = $_POST['from'];
}
if (isset($_GET['from']))
{
	$from = $_GET['from'];
}
if (isset($_POST['comment']))
{
	$comment = $_POST['comment'];
}
if (isset($_GET['comment']))
{
	$comment = $_GET['comment'];
}
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}
if (isset($_POST['label_qty1']))
{
	$label_qty1 = $_POST['label_qty1'];
}
if (isset($_GET['label_qty1']))
{
	$label_qty1 = $_GET['label_qty1'];
}
if (isset($_POST['label_qty2']))
{
	$label_qty2 = $_POST['label_qty2'];
}
if (isset($_GET['label_qty2']))
{
	$label_qty2 = $_GET['label_qty2'];
}
if (isset($_POST['label_qty3']))
{
	$label_qty3 = $_POST['label_qty3'];
}
if (isset($_GET['label_qty3']))
{
	$label_qty3 = $_GET['label_qty3'];
}
if (isset($_POST['ssn_qty1']))
{
	$ssn_qty1 = $_POST['ssn_qty1'];
}
if (isset($_GET['ssn_qty1']))
{
	$ssn_qty1 = $_GET['ssn_qty1'];
}
if (isset($_POST['ssn_qty2']))
{
	$ssn_qty2 = $_POST['ssn_qty2'];
}
if (isset($_GET['ssn_qty2']))
{
	$ssn_qty2 = $_GET['ssn_qty2'];
}
if (isset($_POST['ssn_qty3']))
{
	$ssn_qty3 = $_POST['ssn_qty3'];
}
if (isset($_GET['ssn_qty3']))
{
	$ssn_qty3 = $_GET['ssn_qty3'];
}
if (isset($_POST['printed_ssn_qty']))
{
	$printed_ssn_qty = $_POST['printed_ssn_qty'];
}
if (isset($_GET['printed_ssn_qty']))
{
	$printed_ssn_qty = $_GET['printed_ssn_qty'];
}
if (isset($_POST['weight_qty1']))
{
	$weight_qty1 = $_POST['weight_qty1'];
}
if (isset($_GET['weight_qty1']))
{
	$weight_qty1 = $_GET['weight_qty1'];
}
if (isset($_POST['weight_uom']))
{
	$weight_uom = $_POST['weight_uom'];
}
if (isset($_GET['weight_uom']))
{
	$weight_uom = $_GET['weight_uom'];
}
if (isset($_POST['other_qty1']))
{
	$other_qty1 = $_POST['other_qty1'];
}
if (isset($_GET['other_qty1']))
{
	$other_qty1 = $_GET['other_qty1'];
}
if (isset($_POST['other_qty2']))
{
	$other_qty2 = $_POST['other_qty2'];
}
if (isset($_GET['other_qty2']))
{
	$other_qty2 = $_GET['other_qty2'];
}
if (isset($_POST['other_qty3']))
{
	$other_qty3 = $_POST['other_qty3'];
}
if (isset($_GET['other_qty3']))
{
	$other_qty3 = $_GET['other_qty3'];
}
if (isset($_POST['other_qty4']))
{
	$other_qty4 = $_POST['other_qty4'];
}
if (isset($_GET['other_qty4']))
{
	$other_qty4 = $_GET['other_qty4'];
}
if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_GET['x']))
{
	$image_x = $_GET['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (isset($_GET['y']))
{
	$image_y = $_GET['y'];
}

if (isset($comment))
{
	//echo "have comment";
	if (trim($comment) == "")
	{
		unset($comment);
	}
}
{
/*
	setBDCScookie($Link, $tran_device, "grn", $grn);
	setBDCScookie($Link, $tran_device, "type", $type);
	setBDCScookie($Link, $tran_device, "order", $order);
	setBDCScookie($Link, $tran_device, "line", $line);
*/
	setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
	setBDCScookie($Link, $tran_device, "product", $product);
	setBDCScookie($Link, $tran_device, "label_qty1", $label_qty1);
	setBDCScookie($Link, $tran_device, "ssn_qty1", $ssn_qty1);
	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
	setBDCScookie($Link, $tran_device, "ssn_qty2", $ssn_qty2);
	setBDCScookie($Link, $tran_device, "label_qty3", $label_qty3);
	setBDCScookie($Link, $tran_device, "ssn_qty3", $ssn_qty3);
	setBDCScookie($Link, $tran_device, "weight_qty1", $weight_qty1);
	setBDCScookie($Link, $tran_device, "weight_uom", $weight_uom);
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);
}
//if (isset($addperson) and isset($addperson_type)  )
if (isset($addpersonname)  )
{
	//if (strlen($addperson) > 0)
	if (strlen($addpersonname) > 0)
	{
		$wk_goback = "";
		/* want person record changes */
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		
		$my_location = $addperson;
		if (strlen($my_location) > 10)
		{
			$my_location = 'Id2Big' ;
			//$my_location = substr($my_location,0,10) ;
		}
		$my_object = $addpersonname;
		$my_sublocn = $addperson_type ;
		// have to strip out | from first and last name
		//$my_ref = $addpersonfirstname . "|" . $addpersonlastname ;
		//$my_ref = $addpersonfirstname . "|" . $addpersonlastname . "|" . $addperson ;
		$my_ref = trim($addpersonfirstname, "|") . "|" . trim($addpersonlastname, "|") . "|" . trim($addperson,"|") ;
/*
		if (strlen($my_ref) > 255)
		{
			$my_ref = substr($my_ref,0,255) ;
		}
*/
		if (strlen($my_ref) > 1024)
		{
			$my_ref = substr($my_ref,0,1024) ;
		}
	
		$my_message = "";
		$my_message = dotransaction_response("MDPE", "S", $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		$my_mess_field2 = "";
		$my_mess_label2 = "";
		list($my_mess_field2, $my_mess_label2) = explode("|", $my_responsemessage ."|");
		if ($my_mess_label2 <> "Processed successfully ")
		{
			$message .= $my_mess_label2;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else
		{
			$addperson = $my_mess_field2;
			$wk_goback = "t";
			if ($wk_goback == "t")
			{
				$retfrom = $addperson;
				setBDCScookie($Link, $tran_device, "retfrom", $retfrom);
				$wk_goback = "T";
			}
		}
	}
}
echo("<FONT size=\"2\">\n");
//echo("<div id=\"col3\">");
echo("<div id=\"col16\">");
echo("<form action=\"supplier.php\" method=\"post\" name=getsuppliers>");
if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
}
if (isset($grn))
{
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
}
if (isset($retfrom))
{
	echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
}
if (isset($owner))
{
	echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\" >");
}
if (isset($received_ssn_qty))
{
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
}
if (isset($printed_ssn_qty))
{
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
}
if (isset($location))
{
	echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
}
if (isset($printer))
{
	echo("<input type=\"hidden\" name=\"printer\" value=\"$printer\" >");
}
if (isset($problem))
{
	echo("<input type=\"hidden\" name=\"problem\" value=\"$problem\" >");
}
if (isset($from))
{
	echo("<input type=\"hidden\" name=\"from\" value=\"$from\" >");
}
if (isset($uom))
{
	echo("<input type=\"hidden\" name=\"uom\" value=\"$uom\" >");
}
if (isset($product))
{
	echo("<input type=\"hidden\" name=\"product\" value=\"$product\" >");
}
if (isset($label_qty1))
{
	echo("<input type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
}
if (isset($label_qty2))
{
	echo("<input type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
}
if (isset($label_qty3))
{
	echo("<input type=\"hidden\" name=\"label_qty3\" value=\"$label_qty3\" >");
}
if (isset($ssn_qty1))
{
	echo("<input type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
}
if (isset($ssn_qty2))
{
	echo("<input type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
}
if (isset($ssn_qty3))
{
	echo("<input type=\"hidden\" name=\"ssn_qty3\" value=\"$ssn_qty3\" >");
}
if (isset($weight_qty1))
{
	echo("<input type=\"hidden\" name=\"weight_qty1\" value=\"$weight_qty1\" >");
}
if (isset($weight_uom))
{
	echo("<input type=\"hidden\" name=\"weight_uom\" value=\"$weight_uom\" >");
}

/*
if (isset($grn))
{
	$Query = "select comments from grn where grn = '" . $grn . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN!<BR>\n");
		exit();
	}
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$current_comment = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
*/
echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
/*
switch ($type )
{
	case "PO":
		echo($type . ".No.:");
		break;
	case "RA":
		echo("Return No.:");
		break;
	case "TR":
		echo("TR No.:");
		break;
	case "WO":
		echo($type . ".No.:");
		break;
	case "LD":
		//echo("Load No.:");
		echo("Non-Product:");
		break;
	case "LP":
		echo("Product Load No.:");
		break;
}
*/
//echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\">\n");
echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
//echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
//echo("<input type=\"text\" name=\"displaygrn\" maxlength=\"4\" size=\"4\" readonly value=\"$grn\"><BR>\n");
/* */
//echo("<div id=\"col5\">");
if (isset($addperson))
{
   //echo("<label for=\"addpersonid\">Supplier Id:<input type=\"text\" name=\"addperson\" value=\"$addperson\" id=\"addperson\" maxlength=\"10\" size=\"10\" class=\"col17\"></label><br>");
   echo("<label for=\"addpersonid\">Supplier Id:<input type=\"text\" name=\"addperson\" value=\"$addperson\" id=\"addperson\" maxlength=\"20\" size=\"20\" class=\"col17\"></label><br>");
} else {
   //echo("<label for=\"addpersonid\">Supplier Id:<input type=\"text\" name=\"addperson\" value=\"\" id=\"addperson\" maxlength=\"10\" size=\"10\" class=\"col17\"></label><br>");
   echo("<label for=\"addpersonid\">Supplier Id:<input type=\"text\" name=\"addperson\" value=\"\" id=\"addperson\" maxlength=\"20\" size=\"20\" class=\"col17\"></label><br>");
}
if (isset($addpersonname))
{
   echo("<label for=\"addpersonname\">Name<input type=\"text\" name=\"addpersonname\" value=\"$addpersonname\" id=\"addpersonname\" maxlength=\"30\" size=\"20\" class=\"col17\"></label><br>");
} else {
   echo("<label for=\"addpersonname\">Name<input type=\"text\" name=\"addpersonname\" value=\"\" id=\"addpersonname\" maxlength=\"30\" size=\"20\" class=\"col17\"></label><br>");
}
echo("Person Type:<select name=\"addperson_type\" size=\"1\" ");
echo(" >");
{
	$Query = "select code, description from person_type where ";
        $Query .= " ( code in ('RP','CO','CS','CV','CR','IS') )  "; 
	$Query .= " order by code desc ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Person Types!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		echo( "<OPTION value=\"" . $Row[0] . "\">" . $Row[1] . "\n");
	}
	//release memory
	ibase_free_result($Result);
}
echo("</select><br>\n");
if (isset($addpersonfirstname))
{
   echo("<label for=\"addpersonfirstname\">Contact Name<br><input type=\"text\" name=\"addpersonfirstname\" value=\"$addpersonfirstname\" id=\"addpersonfirstname\" maxlength=\"25\" size=\"20\" class=\"col17\"></label>");
} else {
   echo("<label for=\"addpersonfirstname\">Contact Name<br><input type=\"text\" name=\"addpersonfirstname\" value=\"\" id=\"addpersonfirstname\" maxlength=\"125\" size=\"20\" class=\"col17\"></label>");
} 
if (isset($addpersonlastname))
{
   echo("<br><label for=\"addpersonlastname\"><input type=\"text\" name=\"addpersonlastname\" value=\"$addpersonlastname\" id=\"addpersonlastname\" maxlength=\"125\" size=\"20\"></label><br>");
} else {
   echo("<br><label for=\"addpersonlastname\"><input type=\"text\" name=\"addpersonlastname\" value=\"\" id=\"addpersonlastname\" maxlength=\"25\" size=\"20\"></label><br>");
} 
/*
if (isset($addsupplierprod))
{
   echo("<label for=\"addsupplierprod\">Suppliers Prod<input type=\"text\" name=\"addsupplierprod\" value=\"$addsupplierprod\" id=\"addsupplierprod\" maxlength=\"20\" size=\"20\"></label><br>");
} else {
   echo("<label for=\"addsupplierprod\">Suppliers Prod<input type=\"text\" name=\"addsupplierprod\" value=\"\" id=\"addsupplierprod\" maxlength=\"20\" size=\"20\"></label><br>");
} 
*/
//echo("</div>\n");
echo("</div>\n");
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table border=\"0\" align=\"LEFT\" id=\"col1\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<input type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"" . $from . "\"  method=\"post\"  name=getssnback>\n");
	echo("<input type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	if (isset($carrier))
	{
		echo("<input type=\"hidden\" name=\"carrier\" value=\"$carrier\" >");
	}
	if (isset($vehicle))
	{
		echo("<input type=\"hidden\" name=\"vehicle\" value=\"$vehicle\" >");
	}
	if (isset($container))
	{
		echo("<input type=\"hidden\" name=\"container\" value=\"$container\" >");
	}
	if (isset($pallet_type))
	{
		echo("<input type=\"hidden\" name=\"pallet_type\" value=\"$pallet_type\" >");
	}
	if (isset($pallet_qty ))
	{
		echo("<input type=\"hidden\" name=\"pallet_qty\" value=\"$pallet_qty\" >");
	}
	if (isset($received_qty ))
	{
		echo("<input type=\"hidden\" name=\"received_qty\" value=\"$received_qty\" >");
	}
	if (isset($consignment ))
	{
		echo("<input type=\"hidden\" name=\"consignment\" value=\"$consignment\" >");
	}
	if (isset($sday ))
	{
		echo("<input type=\"hidden\" name=\"sday\" value=\"$sday\" >");
	}
	if (isset($smonth ))
	{
		echo("<input type=\"hidden\" name=\"smonth\" value=\"$smonth\" >");
	}
	if (isset($syear ))
	{
		echo("<input type=\"hidden\" name=\"syear\" value=\"$syear\" >");
	}
	if (isset($retfrom))
	{
		echo("<input type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
	}
	if (isset($owner))
	{
		echo("<input type=\"hidden\" name=\"owner\" value=\"$owner\" >");
	}
	if (isset($received_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
	}
	if (isset($printed_ssn_qty))
	{
		echo("<input type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
	}
	if (isset($location))
	{
		echo("<input type=\"hidden\" name=\"location\" value=\"$location\" >");
	}
	if (isset($printer))
	{
		echo("<input type=\"hidden\" name=\"printer\" value=\"$printer\" >");
	}
	if (isset($problem))
	{
		echo("<input type=\"hidden\" name=\"problem\" value=\"$problem\" >");
	}
	if (isset($uom))
	{
		echo("<input type=\"hidden\" name=\"uom\" value=\"$uom\" >");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\" >");
	}
	if (isset($label_qty1))
	{
		echo("<input type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
	}
	if (isset($label_qty2))
	{
		echo("<input type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
	}
	if (isset($label_qty3))
	{
		echo("<input type=\"hidden\" name=\"label_qty3\" value=\"$label_qty3\" >");
	}
	if (isset($ssn_qty1))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
	}
	if (isset($ssn_qty2))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
	}
	if (isset($ssn_qty3))
	{
		echo("<input type=\"hidden\" name=\"ssn_qty3\" value=\"$ssn_qty3\" >");
	}
	if (isset($weight_qty1))
	{
		echo("<input type=\"hidden\" name=\"weight_qty1\" value=\"$weight_qty1\" >");
	}
	if (isset($weight_uom))
	{
		echo("<input type=\"hidden\" name=\"weight_uom\" value=\"$weight_uom\" >");
	}
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");

}
echo("</div>\n");
?>
<script type="text/javascript">
<?php
{
	if ($wk_goback == "T")
	{
		echo("document.getssnback.submit();\n");
	}
	else
	{
		echo("document.getsuppliers.message.value=\"Enter Supplier\";\n");
		echo("document.getsuppliers.addperson.focus();\n");
	}
}
/*
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
*/
		
	//commit
	ibase_commit($dbTran);
?>
</script>
</body>
</html>

