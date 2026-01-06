<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive GRN Client Info</title>
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
//phpinfo();
?>
 </head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
include 'logme.php';

$received_ssn_qty = 0;
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
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$label_qty3 = getBDCScookie($Link, $tran_device, "label_qty3" );
	$ssn_qty3 = getBDCScookie($Link, $tran_device, "ssn_qty3" );
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
if (isset($_POST['vesselname']))
{
	$vesselname = $_POST['vesselname'];
}
if (isset($_GET['vesselname']))
{
	$vesselname = $_GET['vesselname'];
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

if (isset($vesselname))
{
	//echo "have vessel";
	if (trim($vesselname) == "")
	{
		unset($vesselname);
	}
}
if (isset($comment))
{
	//echo "have comment";
	if (trim($comment) == "")
	{
		unset($comment);
	}
}
if (isset($_GET['other_qty1']))
{
	$other_qty1 = $_GET['other_qty1'];
}
if (isset($_POST['other_qty1']))
{
	$other_qty1 = $_POST['other_qty1'];
}
if (isset($_GET['other_qty2']))
{
	$other_qty2 = $_GET['other_qty2'];
}
if (isset($_POST['other_qty2']))
{
	$other_qty2 = $_POST['other_qty2'];
}
if (isset($_GET['other_qty3']))
{
	$other_qty3 = $_GET['other_qty3'];
}
if (isset($_POST['other_qty3']))
{
	$other_qty3 = $_POST['other_qty3'];
}
if (isset($_GET['other_qty4']))
{
	$other_qty4 = $_GET['other_qty4'];
}
if (isset($_POST['other_qty4']))
{
	$other_qty4 = $_POST['other_qty4'];
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
	setBDCScookie($Link, $tran_device, "weight_qty1", $weight_qty1);
	setBDCScookie($Link, $tran_device, "weight_uom", $weight_uom);
	setBDCScookie($Link, $tran_device, "printer", $printer);
	setBDCScookie($Link, $tran_device, "received_ssn_qty", $received_ssn_qty);
	setBDCScookie($Link, $tran_device, "location", $location);
	setBDCScookie($Link, $tran_device, "uom", $uom);
	setBDCScookie($Link, $tran_device, "printed_ssn_qty", $printed_ssn_qty);

	setBDCScookie($Link, $tran_device, "label_qty2", $label_qty2);
	setBDCScookie($Link, $tran_device, "ssn_qty2", $ssn_qty2 );
	if (isset($label_qty3))
		setBDCScookie($Link, $tran_device, "label_qty3", $label_qty3);
	if (isset($ssn_qty3))
		setBDCScookie($Link, $tran_device, "ssn_qty3", $ssn_qty3 );
	setBDCScookie($Link, $tran_device, "owner", $owner );
	if (isset($other_qty1))
		setBDCScookie($Link, $tran_device, "other_qty1", $other_qty1 );
	if (isset($other_qty3))
		setBDCScookie($Link, $tran_device, "other_qty3", $other_qty3 );
	if (isset($other_qty2))
		setBDCScookie($Link, $tran_device, "other_qty2", $other_qty2 );
	if (isset($other_qty4))
		setBDCScookie($Link, $tran_device, "other_qty4", $other_qty4 );
}

if (isset($vesselname) and isset($grn) )
{
	{
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		$tran_ref_x = $vesselname; 
		if (strlen($tran_ref_x) > 78)
		{
			$tran_ref_x = substr($tran_ref_x, 0, 78);
		}
		$tran_ref_x .= "|" . $problem;
		
		$location = substr($tran_ref_x,30,10);
		$my_object = substr($tran_ref_x,0,30);
		$my_sublocn = $grn;
		$my_ref = substr($tran_ref_x,40,40) ;
	
		$my_message = "";
		//$my_message = dotransaction_response("GRNC", "C", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
        $wk_query = ibase_prepare($Link, "update grn set vessel_name = ? where grn = ?");
        if (!$wk_query )
	    {
		    $my_message .= "message=Unable to Prepare GRN Vessel!";
            //echo $my_message;
            $wk_my_error=ibase_errmsg();
            if ($wk_my_error === False)
            {
                $wk_my_error = "";
            }
		    $my_message .= $wk_my_error;
        } else {

	        $Result = ibase_execute($wk_query, $vesselname, $grn);
	        if ($Result === False)
	        {
		        $my_message .= "message=Unable to Update GRN!";
                $wk_my_error=ibase_errmsg();
		        $my_message .= $wk_my_error;
            } else{
		        $my_message .= "message=Processed successfully ";
            }
            //echo $my_message;
        }
        ibase_free_query($wk_query);
	    //commit
	    ibase_commit($dbTran);

		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		} else {
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
	}
}

if (isset($comment) and isset($grn) )
{
	{
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		$tran_ref_x = $comment; 
		if (strlen($tran_ref_x) > 78)
		{
			$tran_ref_x = substr($tran_ref_x, 0, 78);
		}
		$tran_ref_x .= "|" . $problem;
		
		$location = substr($tran_ref_x,30,10);
		$my_object = substr($tran_ref_x,0,30);
		$my_sublocn = $grn;
		$my_ref = substr($tran_ref_x,40,40) ;
	
		$my_message = "";
		//$my_message = dotransaction_response("GRNC", "C", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
        $wk_query = ibase_prepare($Link, "update grn set grn_legacy_memo = ? where grn = ?");
        if (!$wk_query )
	    {
		    $my_message .= "message=Unable to Prepare GRN!";
            //echo $my_message;
            $wk_my_error=ibase_errmsg();
            if ($wk_my_error === False)
            {
                $wk_my_error = "";
            }
		    $my_message .= $wk_my_error;
        } else {

	        $Result = ibase_execute($wk_query, $comment, $grn);
	        if ($Result === False)
	        {
		        $my_message .= "message=Unable to Update GRN!";
                $wk_my_error=ibase_errmsg();
		        $my_message .= $wk_my_error;
            } else{
		        $my_message .= "message=Processed successfully ";
            }
            //echo $my_message;
        }
        ibase_free_query($wk_query);
	    //commit
	    ibase_commit($dbTran);

		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		} else {
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
	}
}
echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form action=\"customerplus.php\" method=\"post\" name=getcomments>");
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
if (isset($other_qty1))
{
	echo("<input type=\"hidden\" name=\"other_qty1\" value=\"$other_qty1\" >");
}
if (isset($other_qty2))
{
	echo("<input type=\"hidden\" name=\"other_qty2\" value=\"$other_qty2\" >");
}
if (isset($other_qty3))
{
	echo("<input type=\"hidden\" name=\"other_qty3\" value=\"$other_qty3\" >");
}
if (isset($other_qty4))
{
	echo("<input type=\"hidden\" name=\"other_qty4\" value=\"$other_qty4\" >");
}

if (isset($grn))
{
	$Query = "select vessel_name, grn_legacy_memo from grn where grn = '" . $grn . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN!<BR>\n");
		exit();
	}
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$current_vessel = $Row[0];
		$current_comment = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
}
echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
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
echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\">\n");
echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\">\n");
echo("<input type=\"text\" name=\"displaygrn\" maxlength=\"5\" size=\"5\" readonly value=\"$grn\"><BR>\n");
/* */
echo("<input type=\"text\" name=\"vesselname\" maxlength=\"75\" size=\"40\" placeholder=\"Clients Customer\" value=\"");
{
	echo("$current_vessel");
}
echo("\">\n");
echo("<textarea name=\"comment\" rows=\"2\" cols=\"40\" maxlength=\"1024\" ");
    if ($current_comment == "")
    {
        echo("placeholder=\"Processing Notes\"");
    } else {
	    echo(" placeholder=\"$current_comment\"");
    }
	echo(" value=\"$current_comment\">");
echo("</textarea>");
echo("</div>\n");
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\" id=\"col1\">");
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
	if (isset($other_qty1))
	{
		echo("<input type=\"hidden\" name=\"other_qty1\" value=\"$other_qty1\" >");
	}
	if (isset($other_qty2))
	{
		echo("<input type=\"hidden\" name=\"other_qty2\" value=\"$other_qty2\" >");
	}
	if (isset($other_qty3))
	{
		echo("<input type=\"hidden\" name=\"other_qty3\" value=\"$other_qty3\" >");
	}
	if (isset($other_qty4))
	{
		echo("<input type=\"hidden\" name=\"other_qty4\" value=\"$other_qty4\" >");
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
	echo("document.getcomments.message.value=\"Enter Clients Customer\";\n");
	echo("document.getcomments.vesselname.focus();\n");
}
	//release memory
/*
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

