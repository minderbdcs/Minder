<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Add Carriers</title>
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
 </head>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
include 'logme.php';

$wk_goback = "F";
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
	$other1 = getBDCScookie($Link, $tran_device, "other1" );
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
if (isset($_POST['addpersonservice']))
{
	$addpersonservice = $_POST['addpersonservice'];
}
if (isset($_GET['addpersonservice']))
{
	$addpersonservice  = $_GET['addpersonservice'];
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
if (isset($_POST['printed_ssn_qty']))
{
	$printed_ssn_qty = $_POST['printed_ssn_qty'];
}
if (isset($_GET['printed_ssn_qty']))
{
	$printed_ssn_qty = $_GET['printed_ssn_qty'];
}
if (isset($_POST['other1']))
{
	$other1 = $_POST['other1'];
}
if (isset($_GET['other1']))
{
	$other1 = $_GET['other1'];
}
{
	setBDCScookie($Link, $tran_device, "received_qty", $received_qty);
	setBDCScookie($Link, $tran_device, "type", $type);
	setBDCScookie($Link, $tran_device, "order", $order);
	setBDCScookie($Link, $tran_device, "line", $line);
	setBDCScookie($Link, $tran_device, "carrier", $carrier);
	setBDCScookie($Link, $tran_device, "vehicle", $vehicle);
	setBDCScookie($Link, $tran_device, "container", $container);
	setBDCScookie($Link, $tran_device, "pallet_type", $pallet_type);
	setBDCScookie($Link, $tran_device, "pallet_qty", $pallet_qty);
	setBDCScookie($Link, $tran_device, "consignment", $consignment);
	setBDCScookie($Link, $tran_device, "grn", $grn);
	setBDCScookie($Link, $tran_device, "problem", $problem);
	setBDCScookie($Link, $tran_device, "other1", $other1);
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
//if (isset($addperson) and isset($addperson_type) and isset($addpersonservice) )
if (isset($addpersonname)  )
{
	//if (strlen($addperson) > 0)
	if (strlen($addpersonname) > 0)
	{
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		
		$my_location = $addperson;
		$my_object = $addpersonname;
		$my_sublocn = $addperson_type . "|" . $addpersonservice;
		$my_ref = $addpersonfirstname . "|" . $addpersonlastname ;
		if (strlen($my_ref) > 40)
		{
			$my_ref = substr($my_ref,0,40) ;
		}
	
		$my_message = "";
		$my_message = dotransaction_response("MDAC", "C", $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
                        $carrier = $addperson;
			setBDCScookie($Link, $tran_device, "carrier", $carrier);
			$wk_goback = "T";
		}
/*
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else 
		{
                        $carrier = $addperson;
			setBDCScookie($Link, $tran_device, "carrier", $carrier);
			$wk_goback = "T";
		}
*/
	}
}
echo("<FONT size=\"2\">\n");
//echo("<div id=\"col3\">");
echo("<div id=\"col16\">");
echo("<form action=\"carrier.php\" method=\"post\" name=getcarriers>");
if (isset($type))
{
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
}
if (isset($grn))
{
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
}
if (isset($retfrom))
{
	echo("<INPUT type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
}
if (isset($owner))
{
	echo("<INPUT type=\"hidden\" name=\"owner\" value=\"$owner\" >");
}
if (isset($received_ssn_qty))
{
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
}
if (isset($printed_ssn_qty))
{
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
}
if (isset($location))
{
	echo("<INPUT type=\"hidden\" name=\"location\" value=\"$location\" >");
}
if (isset($printer))
{
	echo("<INPUT type=\"hidden\" name=\"printer\" value=\"$printer\" >");
}
if (isset($problem))
{
	echo("<INPUT type=\"hidden\" name=\"problem\" value=\"$problem\" >");
}
if (isset($from))
{
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"$from\" >");
}
if (isset($uom))
{
	echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\" >");
}
if (isset($product))
{
	echo("<INPUT type=\"hidden\" name=\"product\" value=\"$product\" >");
}
if (isset($label_qty1))
{
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
}
if (isset($label_qty2))
{
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
}
if (isset($ssn_qty1))
{
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
}
if (isset($ssn_qty2))
{
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
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
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
//echo("<div id=\"col5\">");
if (isset($addperson))
{
   echo("<label for=\"addpersonid\">Carrier Id:<INPUT type=\"text\" name=\"addperson\" value=\"$addperson\" id=\"addperson\" maxlength=\"10\" size=\"10\" class=\"col17\"></label><br>");
} else {
   echo("<label for=\"addpersonid\">Carrier Id:<INPUT type=\"text\" name=\"addperson\" value=\"\" id=\"addperson\" maxlength=\"10\" size=\"10\" class=\"col17\"></label><br>");
}
if (isset($addpersonname))
{
   echo("<label for=\"addpersonname\">Name<INPUT type=\"text\" name=\"addpersonname\" value=\"$addpersonname\" id=\"addpersonname\" maxlength=\"30\" size=\"20\" class=\"col17\"></label><br>");
} else {
   echo("<label for=\"addpersonname\">Name<INPUT type=\"text\" name=\"addpersonname\" value=\"\" id=\"addpersonname\" maxlength=\"30\" size=\"20\" class=\"col17\"></label><br>");
}
echo("Carrier Type:<select name=\"addperson_type\" size=\"1\" ");
echo(" >");
echo( "<OPTION value=\"DSOT\">DSOT\n");
echo( "<OPTION value=\"DSST\">DSST\n");
echo("</select><br>\n");
if (isset($addpersonfirstname))
{
   echo("<label for=\"addpersonfirstname\">Contact Name<br><INPUT type=\"text\" name=\"addpersonfirstname\" value=\"$addpersonfirstname\" id=\"addpersonfirstname\" maxlength=\"25\" size=\"20\" class=\"col17\"></label>");
} else {
   echo("<label for=\"addpersonfirstname\">Contact Name<br><INPUT type=\"text\" name=\"addpersonfirstname\" value=\"\" id=\"addpersonfirstname\" maxlength=\"25\" size=\"20\" class=\"col17\"></label>");
}
if (isset($addpersonlastname))
{
   echo("<br><label for=\"addpersonlastname\"><INPUT type=\"text\" name=\"addpersonlastname\" value=\"$addpersonlastname\" id=\"addpersonlastname\" maxlength=\"25\" size=\"20\" class=\"col17\"></label><br>");
} else {
   echo("<br><label for=\"addpersonlastname\"><INPUT type=\"text\" name=\"addpersonlastname\" value=\"\" id=\"addpersonlastname\" maxlength=\"25\" size=\"20\" class=\"col17\"></label><br>");
}
if (isset($addpersonservice))
{
   echo("<label for=\"addpersonservice\">Carrier Service:<INPUT type=\"text\" name=\"addpersonservice\" value=\"$addpersonservice\" id=\"addpersonservice\" maxlength=\"3\" size=\"3\"></label>");
} else {
   echo("<label for=\"addpersonservice\">Carrier Service:<INPUT type=\"text\" name=\"addpersonservice\" value=\"\" id=\"addpersonservice\" maxlength=\"3\" size=\"3\"></label>");
}
//echo("</div>\n");
echo("</div>\n");
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table border=\"0\" align=\"LEFT\" id=\"col1\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	//echo("<form action=\"" . $from . "\"  method=\"get\"  name=getssnback>\n");
	echo("<form action=\"" . $from . "\"  method=\"post\"  name=getssnback>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	if (isset($carrier))
	{
		echo("<INPUT type=\"hidden\" name=\"carrier\" value=\"$carrier\" >");
	}
	if (isset($vehicle))
	{
		echo("<INPUT type=\"hidden\" name=\"vehicle\" value=\"$vehicle\" >");
	}
	if (isset($other1))
	{
		echo("<INPUT type=\"hidden\" name=\"other1\" value=\"$other1\" >");
	}
	if (isset($container))
	{
		echo("<INPUT type=\"hidden\" name=\"container\" value=\"$container\" >");
	}
	if (isset($pallet_type))
	{
		echo("<INPUT type=\"hidden\" name=\"pallet_type\" value=\"$pallet_type\" >");
	}
	if (isset($pallet_qty ))
	{
		echo("<INPUT type=\"hidden\" name=\"pallet_qty\" value=\"$pallet_qty\" >");
	}
	if (isset($received_qty ))
	{
		echo("<INPUT type=\"hidden\" name=\"received_qty\" value=\"$received_qty\" >");
	}
	if (isset($consignment ))
	{
		echo("<INPUT type=\"hidden\" name=\"consignment\" value=\"$consignment\" >");
	}
	if (isset($sday ))
	{
		echo("<INPUT type=\"hidden\" name=\"sday\" value=\"$sday\" >");
	}
	if (isset($smonth ))
	{
		echo("<INPUT type=\"hidden\" name=\"smonth\" value=\"$smonth\" >");
	}
	if (isset($syear ))
	{
		echo("<INPUT type=\"hidden\" name=\"syear\" value=\"$syear\" >");
	}
	if (isset($retfrom))
	{
		echo("<INPUT type=\"hidden\" name=\"retfrom\" value=\"$retfrom\" >");
	}
	if (isset($owner))
	{
		echo("<INPUT type=\"hidden\" name=\"owner\" value=\"$owner\" >");
	}
	if (isset($received_ssn_qty))
	{
		echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" value=\"$received_ssn_qty\" >");
	}
	if (isset($printed_ssn_qty))
	{
		echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" value=\"$printed_ssn_qty\" >");
	}
	if (isset($location))
	{
		echo("<INPUT type=\"hidden\" name=\"location\" value=\"$location\" >");
	}
	if (isset($printer))
	{
		echo("<INPUT type=\"hidden\" name=\"printer\" value=\"$printer\" >");
	}
	if (isset($problem))
	{
		echo("<INPUT type=\"hidden\" name=\"problem\" value=\"$problem\" >");
	}
	if (isset($uom))
	{
		echo("<INPUT type=\"hidden\" name=\"uom\" value=\"$uom\" >");
	}
	if (isset($product))
	{
		echo("<INPUT type=\"hidden\" name=\"product\" value=\"$product\" >");
	}
	if (isset($label_qty1))
	{
		echo("<INPUT type=\"hidden\" name=\"label_qty1\" value=\"$label_qty1\" >");
	}
	if (isset($label_qty2))
	{
		echo("<INPUT type=\"hidden\" name=\"label_qty2\" value=\"$label_qty2\" >");
	}
	if (isset($ssn_qty1))
	{
		echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" value=\"$ssn_qty1\" >");
	}
	if (isset($ssn_qty2))
	{
		echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" value=\"$ssn_qty2\" >");
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
		echo("document.getcarriers.message.value=\"Enter Carrier\";\n");
		echo("document.getcarriers.addperson.focus();\n");
	}
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
</html>

