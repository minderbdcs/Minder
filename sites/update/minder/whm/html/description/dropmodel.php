<?php
include "../login.inc";
?>
<html>
 <head>
  <title>SSN Description of Model .. Legacy via Drop Downs</title>
<?php
include "viewport.php";
{
	echo('<link rel=stylesheet type="text/css" href="dropssn.css">');
}
?>
 </head>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "checkdatajs.php";

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
$message = "";
/*
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if ($message == "Scan SSN")
{
	$message = "";
}
*/
if (isset($_POST['snmodel']))
{
	$snmodel = $_POST['snmodel'];
}
if (isset($_GET['snmodel']))
{
	$snmodel = $_GET['snmodel'];
}
if (isset($snmodel))
{
	// upshift it
	$snmodel = strtoupper($snmodel);
}
if (isset($_POST['currentssn']))
{
	$ssn = $_POST['currentssn'];
}
if (isset($_GET['currentssn']))
{
	$ssn = $_GET['currentssn'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['brand']))
{
	$brand = $_POST['brand'];
}
if (isset($_GET['brand']))
{
	$brand = $_GET['brand'];
}
if (isset($_POST['cc']))
{
	$cc = $_POST['cc'];
}
if (isset($_GET['cc']))
{
	$cc = $_GET['cc'];
}
if (isset($_POST['serial']))
{
	$serial = $_POST['serial'];
}
if (isset($_GET['serial']))
{
	$serial = $_GET['serial'];
}
if (isset($serial))
{
	// upshift it
	$serial = strtoupper($serial);
}
if (isset($_POST['legacy']))
{
	$legacy = $_POST['legacy'];
}
if (isset($_GET['legacy']))
{
	$legacy = $_GET['legacy'];
}
if (isset($_POST['modelchange']))
{
	$modelchange = $_POST['modelchange'];
}
if (isset($_GET['modelchange']))
{
	$modelchange = $_GET['modelchange'];
}
if (isset($_POST['brandchange']))
{
	$brandchange = $_POST['brandchange'];
}
if (isset($_GET['brandchange']))
{
	$brandchange = $_GET['brandchange'];
}
if (isset($_POST['ccchange']))
{
	$ccchange = $_POST['ccchange'];
}
if (isset($_GET['ccchange']))
{
	$ccchange = $_GET['ccchange'];
}
if (isset($_POST['serialchange']))
{
	$serialchange = $_POST['serialchange'];
}
if (isset($_GET['serialchange']))
{
	$serialchange = $_GET['serialchange'];
}
if (isset($_POST['legacychange']))
{
	$legacychange = $_POST['legacychange'];
}
if (isset($_GET['legacychange']))
{
	$legacychange = $_GET['legacychange'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//release memory
//ibase_free_result($Result);

// create js for check pass values to notes
echo "<script>\n";
echo "function passFields(passType) {\n";
echo " document.model.currentssn.value = document.getssn.ssn.value;\n";
echo " document.notes.currentssn2.value = document.getssn.ssn.value;\n";
echo " if (passType == \"1\")\n";
echo " {\n";
$Query = "select allow_handheld_ssn_notes from control ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
$wk_allow_notes = "T";
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_allow_notes = $Row[0] ;
}
if ($wk_allow_notes == 'T')
{
	echo " return true;\n";
}
else
{
	echo " return false;\n";
}
echo " }\n";
echo " else\n";
echo " {\n";
echo "  return true;\n";
echo " }\n";
echo "}\n";
echo "</script>\n";

// create js for location check

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
if (isset($ssn))
{
	$Query = "select issn.wh_id, issn.locn_id, ssn.model, ssn.brand, ssn.cost_center, ssn.serial_number, ssn.legacy_id FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_model = $Row[2] ;
		$current_brand = $Row[3];
		$current_cc = $Row[4];
		$current_serial = $Row[5];
		$current_legacy = $Row[6];
	}
	if (isset($modelchange) and isset($brandchange) )
	{
		include "transaction.php";
		if ($modelchange == 1)
		{
			// do type change transaction
			$my_message = dotransaction("NIMO", "A", $ssn, $current_location, "", $snmodel, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= "Model " . $my_responsemessage;
		}
		if ($brandchange == 1)
		{
			// do brand change transaction
			$my_message = dotransaction("NIBC", "A", $ssn, $current_location, "", $brand, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= " Brand " . $my_responsemessage;
		}
		if ($ccchange == 1)
		{
			// do cc change transaction
			$my_message = dotransaction("NICC", "A", $ssn, $current_location, "", $cc, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= " CC " . $my_responsemessage;
		}
		if ($serialchange == 1)
		{
			// do serial change transaction
			$my_message = dotransaction("NISN", "A", $ssn, $current_location, "", $serial, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= " Serial " . $my_responsemessage;
		}
		if ($legacychange == 1)
		{
			// do legacy change transaction
			$my_message = dotransaction("NILG", "A", $ssn, $current_location, "", $legacy, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= " Legacy " . $my_responsemessage;
		}
		$Query = "select issn.wh_id, issn.locn_id, ssn.model, ssn.brand, ssn.cost_center, ssn.serial_number, ssn.legacy_id FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read ISSN!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$current_location = $Row[0] . $Row[1];
			$current_model = $Row[2] ;
			$current_brand = $Row[3];
			$current_cc = $Row[4];
			$current_serial = $Row[5];
			$current_legacy = $Row[6];
		}
	}
}
echo("<FORM action=\"dropmodel.php\" method=\"post\" name=getssn >");
echo("<INPUT type=\"hidden\" name=\"modelchange\" value=\"0\" >");
echo("<INPUT type=\"hidden\" name=\"brandchange\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"ccchange\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"serialchange\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"legacychange\" value=\"0\">");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"50\" class=\"message\"><br>\n");
echo('<table border="0">');
echo("<tr><td>");
echo("Ssn:</td><td><INPUT type=\"text\" name=\"ssn\" readonly=\"Y\" size=\"20\" ");
echo(" value=\"");
if (isset($ssn))
{
	echo $ssn;
}
echo ("\" ></td></tr>\n");
echo("<tr><td>");
echo("Model:</td><td><input type=\"text\" name=\"snmodel\" size=\"20\" maxlength=\"40\" value=\"");
if (isset($current_model))
{
	echo $current_model ;
}
echo("\" onchange=\"document.getssn.modelchange.value=1\">");
echo ("</td></tr>");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<tr><td>");
echo("Brand:</td><td><SELECT name=\"brand\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.brandchange.value=1\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($current_brand))
	{
		if ($current_brand == $Row[0])
		{
			echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[1]\n");
			$wk_selected = "Y";
		}
		else
		{
			echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
		}
	}
	else
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_brand))
	{
		if ($current_brand == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
		else
		{
			echo( "<OPTION value=\"" . $current_brand . "\" selected>" . $current_brand . " Not in Brands Master List\n");
		}
	}
}
echo ("</SELECT></td></tr>");
echo("<tr><td>");
$Query = "SELECT code, description FROM cost_centre ORDER BY description ";
echo("Cost Center:</td><td><SELECT name=\"cc\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.ccchange.value=1\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Cost Centre!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($current_cc))
	{
		if ($current_cc == $Row[0])
		{
			echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[1]\n");
			$wk_selected = "Y";
		}
		else
		{
			echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
		}
	}
	else
	{
		echo( "<OPTION value=\"" . $Row[0] .  "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_cc))
	{
		if ($current_cc == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
		else
		{
			echo( "<OPTION value=\"" . $current_cc . "\" selected>" . $current_cc . " Not in Cost Center Master List\n");
		}
	}
}
echo ("</SELECT></td></tr>");
echo("<tr><td>");
echo("Serial:</td><td><input type=\"text\" name=\"serial\" size=\"20\" maxlength=\"30\" value=\"");
if (isset($current_serial))
{
	echo $current_serial ;
}
echo("\" onchange=\"document.getssn.serialchange.value=1\">");
echo ("</td></tr>");
echo("<tr><td>");
echo("Legacy:</td><td><input type=\"text\" name=\"legacy\" size=\"20\" maxlength=\"20\" value=\"");
if (isset($current_legacy))
{
	echo $current_legacy ;
}
echo("\" onchange=\"document.getssn.legacychange.value=1\">");
echo ("</td></tr>");
/*
$Query = "SELECT legacy_id, legacy_description FROM legacy ORDER BY legacy_description ";
echo("Legacy:</td><td><SELECT name=\"legacy\" size=\"1\" class=\"sel3\" onchange=\"document.getssn.legacychange.value=1\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($current_legacy))
	{
		if ($current_legacy == $Row[0])
		{
			echo( "<OPTION value=\"" . $Row[0] . "\" selected>" . $Row[0] . " " . $Row[1] . "\n");
			$wk_selected = "Y";
		}
		else
		{
			echo( "<OPTION value=\"" . $Row[0] . "\">" . $Row[0] . " " . $Row[1] . "\n");
		}
	}
	else
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">" . $Row[0] . " " . $Row[1] . "\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_legacy))
	{
		if ($current_legacy == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
		else
		{
			echo( "<OPTION value=\"" . $current_legacy . "\" selected>" . $current_legacy . " Not in Legacys Master List\n");
		}
	}
}
echo ("</SELECT></td></tr>");
*/
//release memory
ibase_free_result($Result);
//commit
ibase_commit($dbTran);
echo ("<BR>");
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./dropssn.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
	echo("<tr><td><form action=\"drop1115.php\" method=\"post\" name=model onsubmit=\"return passFields(0);\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/more.gif" alt="Model .. Status" >');
	echo("</form>\n");
	echo("</td>\n");
	echo("<td><form action=\"notes.php\" method=\"post\" name=notes onsubmit=\"return passFields(1);\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn2\" > ");  
	echo("<input type=\"hidden\" name=\"from\" value=\"dropmodel.php\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/comment.gif" alt="Notes" >');
	echo("</form>\n");
	echo("</td></tr></table>\n");
}
echo("</div>\n");
echo("<script>");
if (isset($message))
{
	echo("document.getssn.message.value=\"" . $message . " Enter Changes" . "\";");
	echo('document.getssn.snmodel.focus();');
}
else
{
	echo('document.getssn.message.value="Enter Changes";');
	echo('document.getssn.snmodel.focus();');
}
?>
</script>
</html>
