<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>SSN Description of Other 6 to 10 </title>
<link rel=stylesheet type="text/css" href="dropssn.css">
 </head>
<script>

function passFields() {
 document.model.currentssn.value = document.getssn.ssn.value;
 document.nextmodel.currentssn.value = document.getssn.ssn.value;
 return true
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
//include "checkdatajs.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
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
if (isset($_POST['other6']))
{
	$other6 = $_POST['other6'];
}
if (isset($_GET['other6']))
{
	$other6 = $_GET['other6'];
}
if (isset($_POST['other7']))
{
	$other7 = $_POST['other7'];
}
if (isset($_GET['other7']))
{
	$other7 = $_GET['other7'];
}
if (isset($_POST['other8']))
{
	$other8 = $_POST['other8'];
}
if (isset($_GET['other8']))
{
	$other8 = $_GET['other8'];
}
if (isset($_POST['other9']))
{
	$other9 = $_POST['other9'];
}
if (isset($_GET['other9']))
{
	$other9 = $_GET['other9'];
}
if (isset($_POST['other10']))
{
	$other10 = $_POST['other10'];
}
if (isset($_GET['other10']))
{
	$other10 = $_GET['other10'];
}
if (isset($_POST['other6change']))
{
	$other6change = $_POST['other6change'];
}
if (isset($_GET['other6change']))
{
	$other6change = $_GET['other6change'];
}
if (isset($_POST['other7change']))
{
	$other7change = $_POST['other7change'];
}
if (isset($_GET['other7change']))
{
	$other7change = $_GET['other7change'];
}
if (isset($_POST['other8change']))
{
	$other8change = $_POST['other8change'];
}
if (isset($_GET['other8change']))
{
	$other8change = $_GET['other8change'];
}
if (isset($_POST['other9change']))
{
	$other9change = $_POST['other9change'];
}
if (isset($_GET['other9change']))
{
	$other9change = $_GET['other9change'];
}
if (isset($_POST['other10change']))
{
	$other10change = $_POST['other10change'];
}
if (isset($_GET['other10change']))
{
	$other10change = $_GET['other10change'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//release memory
//ibase_free_result($Result);

// create js for location check

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
$Query = "select field6, dd_other6, rm_other6, 
field7, dd_other7, rm_other7, 
field8, dd_other8, rm_other8, 
field9, dd_other9, rm_other9, 
field10, dd_other10, rm_other10 
from ssn_group where ssn_group = 'DEFAULT' ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read SSN_GROUP!<BR>\n");
	exit();
}
$wk_other = array();
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_other[6] = array($Row2[0],$Row2[1], $Row2[2])  ;
		$wk_other[7] = array($Row2[3],$Row2[4], $Row2[5])  ;
		$wk_other[8] = array($Row2[6],$Row2[7], $Row2[8])  ;
		$wk_other[9] = array($Row2[9],$Row2[10], $Row2[11])  ;
		$wk_other[10] = array($Row2[12],$Row2[13], $Row2[14])  ;
}
ibase_free_result($Result2);
//print_r($wk_other);
if (isset($ssn))
{
	$Query = "select issn.wh_id, issn.locn_id, ssn.ssn_type FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_type = $Row[2];
	}
	if (isset($other6change) and isset($other7change) )
	{
		include "transaction.php";
		if ($other6change == 1)
		{
			// do other6 change transaction
			$my_message = dotransaction("NIO6", "A", $ssn, $current_location, "", $other6, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[6][0] . " " . $my_responsemessage;
		}
		if ($other7change == 1)
		{
			// do brand change transaction
			$my_message = dotransaction("NIO7", "A", $ssn, $current_location, "", $other7, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[7][0] . " " . $my_responsemessage;
		}
		if ($other8change == 1)
		{
			// do 8 change transaction
			$my_message = dotransaction("NIO8", "A", $ssn, $current_location, "", $other8, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[8][0] . " " . $my_responsemessage;
		}
		if ($other9change == 1)
		{
			// do other9 transaction
			$my_message = dotransaction("NIO9", "A", $ssn, $current_location, "", $other9, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[9][0] . " " . $my_responsemessage;
		}
		if ($other10change == 1)
		{
			// do other10 change transaction
			$my_message = dotransaction("NIOA", "A", $ssn, $current_location, "", $other10, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			//echo($my_message);
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[10][0] . " " . $my_responsemessage;
		}
	}
	$Query = "select issn.wh_id, issn.locn_id, ssn.other6, ssn.other7, ssn.other8, ssn.other9, ssn.other10 FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_other6 = $Row[2] ;
		$current_other7 = $Row[3];
		$current_other8 = $Row[4];
		$current_other9 = $Row[5];
		$current_other10 = $Row[6];
	}
	ibase_free_result($Result);
	unset($Result);
}
$Query = "select field1, field2,  field3, field4, field5
from ssn_type where code = '" . $current_type . "' ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read SSN_TYPE!<BR>\n");
	exit();
}
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_other[6][0] = $Row2[0] ;
		$wk_other[7][0] = $Row2[1] ;
		$wk_other[8][0] = $Row2[2] ;
		$wk_other[9][0] = $Row2[3] ;
		$wk_other[10][0] = $Row2[4] ;
}
echo("<FORM action=\"drop610.php\" method=\"post\" name=getssn >");
echo("<INPUT type=\"hidden\" name=\"other6change\" value=\"0\" >");
echo("<INPUT type=\"hidden\" name=\"other7change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other8change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other9change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other10change\" value=\"0\">");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"100\" class=\"message\"><br>\n");
echo('<table border="0">');
echo("<tr><td>");
echo("Ssn:</td><td><INPUT type=\"text\" name=\"ssn\" readonly=\"Y\" size=\"20\" ");
echo(" value=\"");
if (isset($ssn))
{
	echo $ssn;
}
echo ("\" ></td></tr>\n");
//print_r($wk_other);
if ($wk_other[6][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[6][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM product_description where type_code = '" . $current_type . "' and field_code = '1' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[6][0]);
		//echo(":</td><td><SELECT name=\"other6\" size=\"1\" class=\"sel4\" width=\"20\" onchange=\"document.getssn.other6change.value=1\">\n");
		echo(":</td><td><SELECT name=\"other6\" size=\"1\" class=\"sel4\" onchange=\"document.getssn.other6change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other6))
			{
				if ($current_other6 == $Row[0])
				{
					echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[0]\n");
					$wk_selected = "Y";
				}
				else
				{
					echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
				}
			}
			else
			{
				echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
			}
		}
		if ($wk_selected == "N")
		{
			//if (isset($current_other6))
			{
				if ($current_other6 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other6 . "\" selected>" . $current_other6 . " Not in Product Description Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[6][0]);
		echo(":</td><td><input type=\"text\" name=\"other6\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other6))
		{
			echo $current_other6;
		}
		echo("\" onchange=\"document.getssn.other6change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[6][0]);
	echo(":</td><td><input type=\"text\" name=\"other6\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other6))
	{
		echo $current_other6;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[7][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[7][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM product_description where type_code = '" . $current_type . "' and field_code = '2' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[7][0]);
		//echo(":</td><td><SELECT name=\"other7\" size=\"1\" class=\"sel4\" width=\"20\" onchange=\"document.getssn.other7change.value=1\">\n");
		echo(":</td><td><SELECT name=\"other7\" size=\"1\" class=\"sel4\" onchange=\"document.getssn.other7change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other7))
			{
				if ($current_other7 == $Row[0])
				{
					echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[0]\n");
					$wk_selected = "Y";
				}
				else
				{
					echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
				}
			}
			else
			{
				echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
			}
		}
		if ($wk_selected == "N")
		{
			//if (isset($current_other7))
			{
				if ($current_other7 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other7 . "\" selected>" . $current_other7 . " Not in Product Description Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[7][0]);
		echo(":</td><td><input type=\"text\" name=\"other7\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other7))
		{
			echo $current_other7;
		}
		echo("\" onchange=\"document.getssn.other7change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[7][0]);
	echo(":</td><td><input type=\"text\" name=\"other7\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other7))
	{
		echo $current_other7;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[8][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[8][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM product_description where type_code = '" . $current_type . "' and field_code = '3' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[8][0]);
		//echo(":</td><td><SELECT name=\"other8\" size=\"1\" class=\"sel4\" width=\"20\" onchange=\"document.getssn.other8change.value=1\">\n");
		echo(":</td><td><SELECT name=\"other8\" size=\"1\" class=\"sel4\" onchange=\"document.getssn.other8change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other8))
			{
				if ($current_other8 == $Row[0])
				{
					echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[0]\n");
					$wk_selected = "Y";
				}
				else
				{
					echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
				}
			}
			else
			{
				echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
			}
		}
		if ($wk_selected == "N")
		{
			//if (isset($current_other8))
			{
				if ($current_other8 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other8 . "\" selected>" . $current_other8 . " Not in Product Description Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[8][0]);
		echo(":</td><td><input type=\"text\" name=\"other8\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other8))
		{
			echo $current_other8;
		}
		echo("\" onchange=\"document.getssn.other8change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[8][0]);
	echo(":</td><td><input type=\"text\" name=\"other8\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other8))
	{
		echo $current_other8;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[9][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[9][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM product_description where type_code = '" . $current_type . "' and field_code = '4' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[9][0]);
		//echo(":</td><td><SELECT name=\"other9\" size=\"1\" class=\"sel4\" width=\"20\" onchange=\"document.getssn.other9change.value=1\">\n");
		echo(":</td><td><SELECT name=\"other9\" size=\"1\" class=\"sel4\" onchange=\"document.getssn.other9change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other9))
			{
				if ($current_other9 == $Row[0])
				{
					echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[0]\n");
					$wk_selected = "Y";
				}
				else
				{
					echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
				}
			}
			else
			{
				echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
			}
		}
		if ($wk_selected == "N")
		{
			//if (isset($current_other9))
			{
				if ($current_other9 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other9 . "\" selected>" . $current_other9 . " Not in Product Description Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[9][0]);
		echo(":</td><td><input type=\"text\" name=\"other9\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other9))
		{
			echo $current_other9;
		}
		echo("\" onchange=\"document.getssn.other9change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[9][0]);
	echo(":</td><td><input type=\"text\" name=\"other9\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other9))
	{
		echo $current_other9;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[10][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[10][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM product_description where type_code = '" . $current_type . "' and field_code = '5' ORDER BY description ";
		echo("<td>");
		echo($wk_other[10][0]);
		//echo(":</td><td><SELECT name=\"other10\" size=\"1\" class=\"sel4\" width=\"20\" onchange=\"document.getssn.other10change.value=1\">\n");
		echo(":</td><td><SELECT name=\"other10\" size=\"1\" class=\"sel4\" onchange=\"document.getssn.other10change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other10))
			{
				if ($current_other10 == $Row[0])
				{
					echo( "<OPTION value=\"" . $Row[0] . "\" selected>$Row[0]\n");
					$wk_selected = "Y";
				}
				else
				{
					echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
				}
			}
			else
			{
				echo( "<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
			}
		}
		if ($wk_selected == "N")
		{
			//if (isset($current_other10))
			{
				if ($current_other10 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other10 . "\" selected>" . $current_other10 . " Not in Product Description Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<td>");
		echo($wk_other[10][0]);
		echo(":</td><td><input type=\"text\" name=\"other10\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other10))
		{
			echo $current_other10;
		}
		echo("\" onchange=\"document.getssn.other10change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[10][0]);
	echo(":</td><td><input type=\"text\" name=\"other10\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other10))
	{
		echo $current_other10;
	}
	echo("\" >");
	echo ("</td></tr>");
}
//release memory
ibase_free_result($Result2);
if (isset($Result))
{
	ibase_free_result($Result);
}
//commit
ibase_commit($dbTran);
echo ("<BR>");
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	//whm2buttons('Accept',"./dropssn.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
	echo("<tr><td>\n");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/accept.gif" alt="Accept" >');
	echo("</form>\n");
	echo("</td>\n");
	echo("<td><form action=\"drop1620.php\" method=\"post\" name=model onsubmit=\"return passFields();\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back" >');
	echo("</form>\n");
	echo("</td></tr>\n");
	echo("<tr><td><form action=\"dropcond.php\" method=\"post\" name=nextmodel onsubmit=\"return passFields();\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/more.gif" alt="Next" >');
	echo("</form>\n");
	echo("</td></tr></table>\n");
}
echo("</div>\n");
echo("<script>");
if (isset($message))
{
	echo("document.getssn.message.value=\"" . $message . " Enter Changes" . "\";");
	echo("document.getssn.other6.focus();");
}
else
{
	echo('document.getssn.message.value="Enter Changes";');
	echo("document.getssn.other6.focus();");
}
?>
</script>
</html>
