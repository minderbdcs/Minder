<?php
include "../login.inc";
?>
<html>
 <head>
  <title>SSN Description of Other 11 to 20 </title>
<link rel=stylesheet type="text/css" href="dropssn.css">
 </head>
<script language="JavaScript" src="ts_picker.js">
</script>
<script>

function passFields() {
 document.model.currentssn.value = document.getssn.ssn.value;
 document.nextmodel.currentssn.value = document.getssn.ssn.value;
 return true
}
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processEditNum(myLabel1,myLabel2) {
  if ( document.getssn.other17.value!="")
  {
     if ( chkNumeric(document.getssn.other17.value)==false)
     {
  	document.getssn.message.value=myLabel1 + " Not Numeric";
	document.getssn.other17.focus();
  	return false;
     }
     document.getssn.other17change.value=1;
  }
     
  if ( document.getssn.other18.value!="")
  {
     if ( chkNumeric(document.getssn.other18.value)==false)
     {
  	document.getssn.message.value=myLabel2 + " Not Numeric";
	document.getssn.other18.focus();
  	return false;
     }
     document.getssn.other18change.value=1;
  }
  return true;
}
function passFields2(wkdate1,wkdate2) {
   if (wkdate1 != document.getssn.other15.value)
   {
     document.getssn.other15change.value=1;
   }
   if (wkdate2 != document.getssn.other16.value)
   {
     document.getssn.other16change.value=1;
   }
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
if (isset($_POST['other11']))
{
	$other11 = $_POST['other11'];
}
if (isset($_GET['other11']))
{
	$other11 = $_GET['other11'];
}
if (isset($_POST['other12']))
{
	$other12 = $_POST['other12'];
}
if (isset($_GET['other12']))
{
	$other12 = $_GET['other12'];
}
if (isset($_POST['other13']))
{
	$other13 = $_POST['other13'];
}
if (isset($_GET['other13']))
{
	$other13 = $_GET['other13'];
}
if (isset($_POST['other14']))
{
	$other14 = $_POST['other14'];
}
if (isset($_GET['other14']))
{
	$other14 = $_GET['other14'];
}
if (isset($_POST['other15']))
{
	$other15 = $_POST['other15'];
}
if (isset($_GET['other15']))
{
	$other15 = $_GET['other15'];
}
if (isset($_POST['other16']))
{
	$other16 = $_POST['other16'];
}
if (isset($_GET['other16']))
{
	$other16 = $_GET['other16'];
}
if (isset($_POST['other17']))
{
	$other17 = $_POST['other17'];
}
if (isset($_GET['other17']))
{
	$other17 = $_GET['other17'];
}
if (isset($_POST['other18']))
{
	$other18 = $_POST['other18'];
}
if (isset($_GET['other18']))
{
	$other18 = $_GET['other18'];
}
if (isset($_POST['other19']))
{
	$other19 = $_POST['other19'];
}
if (isset($_GET['other19']))
{
	$other19 = $_GET['other19'];
}
if (isset($_POST['other20']))
{
	$other20 = $_POST['other20'];
}
if (isset($_GET['other20']))
{
	$other20 = $_GET['other20'];
}
if (isset($_POST['other11change']))
{
	$other11change = $_POST['other11change'];
}
if (isset($_GET['other11change']))
{
	$other11change = $_GET['other11change'];
}
if (isset($_POST['other12change']))
{
	$other12change = $_POST['other12change'];
}
if (isset($_GET['other12change']))
{
	$other12change = $_GET['other12change'];
}
if (isset($_POST['other13change']))
{
	$other13change = $_POST['other13change'];
}
if (isset($_GET['other13change']))
{
	$other13change = $_GET['other13change'];
}
if (isset($_POST['other14change']))
{
	$other14change = $_POST['other14change'];
}
if (isset($_GET['other14change']))
{
	$other14change = $_GET['other14change'];
}
if (isset($_POST['other15change']))
{
	$other15change = $_POST['other15change'];
}
if (isset($_GET['other15change']))
{
	$other15change = $_GET['other15change'];
}
if (isset($_POST['other16change']))
{
	$other16change = $_POST['other16change'];
}
if (isset($_GET['other16change']))
{
	$other16change = $_GET['other16change'];
}
if (isset($_POST['other17change']))
{
	$other17change = $_POST['other17change'];
}
if (isset($_GET['other17change']))
{
	$other17change = $_GET['other17change'];
}
if (isset($_POST['other18change']))
{
	$other18change = $_POST['other18change'];
}
if (isset($_GET['other18change']))
{
	$other18change = $_GET['other18change'];
}
if (isset($_POST['other19change']))
{
	$other19change = $_POST['other19change'];
}
if (isset($_GET['other19change']))
{
	$other19change = $_GET['other19change'];
}
if (isset($_POST['other20change']))
{
	$other20change = $_POST['other20change'];
}
if (isset($_GET['other20change']))
{
	$other20change = $_GET['other20change'];
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
$Query = "select field11, dd_other11, rm_other11, 
field12, dd_other12, rm_other12, 
field13, dd_other13, rm_other13, 
field14, dd_other14, rm_other14, 
field15, dd_other15, rm_other15, 
field16, dd_other16, rm_other16, 
field17, dd_other17, rm_other17, 
field18, dd_other18, rm_other18, 
field19, dd_other19, rm_other19, 
field20, dd_other20, rm_other20  
from ssn_group where ssn_group = 'DEFAULT' ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read SSN_GROUP!<BR>\n");
	exit();
}
$wk_other = array();
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_other[11] = array($Row2[0],$Row2[1], $Row2[2])  ;
		$wk_other[12] = array($Row2[3],$Row2[4], $Row2[5])  ;
		$wk_other[13] = array($Row2[6],$Row2[7], $Row2[8])  ;
		$wk_other[14] = array($Row2[9],$Row2[10], $Row2[11])  ;
		$wk_other[15] = array($Row2[12],$Row2[13], $Row2[14])  ;
		$wk_other[16] = array($Row2[15],$Row2[16], $Row2[17])  ;
		$wk_other[17] = array($Row2[18],$Row2[19], $Row2[20])  ;
		$wk_other[18] = array($Row2[21],$Row2[22], $Row2[23])  ;
		$wk_other[19] = array($Row2[24],$Row2[25], $Row2[26])  ;
		$wk_other[20] = array($Row2[27],$Row2[28], $Row2[29])  ;
}
//print_r($wk_other);
if (isset($ssn))
{
	$Query = "select issn.wh_id, issn.locn_id FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
	}
	if (isset($other11change) and isset($other12change) )
	{
		include "transaction.php";
		if ($other11change == 1)
		{
			// do other11 change transaction
			$my_message = dotransaction("NI11", "A", $ssn, $current_location, "", $other11, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "OK";
			}
			$message .= $wk_other[11][0] . " " . $my_responsemessage;
		}
		if ($other12change == 1)
		{
			// do brand change transaction
			$my_message = dotransaction("NI12", "A", $ssn, $current_location, "", $other12, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[12][0] . " " . $my_responsemessage;
		}
		if ($other13change == 1)
		{
			// do 13 change transaction
			$my_message = dotransaction("NI13", "A", $ssn, $current_location, "", $other13, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[13][0] . " " . $my_responsemessage;
		}
		if ($other14change == 1)
		{
			// do other14 transaction
			$my_message = dotransaction("NI14", "A", $ssn, $current_location, "", $other14, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[14][0] . " " . $my_responsemessage;
		}
		if ($other15change == 1)
		{
			// do other15 change transaction
			list($wk_month, $wk_day, $wk_date_rest) = explode('-',$other15);
			$other15 = $wk_day . "-" . $wk_month . "-" . $wk_date_rest;
			$my_message = dotransaction("NI15", "A", $ssn, $current_location, "", $other15, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[15][0] . " " . $my_responsemessage;
		}
		if ($other16change == 1)
		{
			// do other16 change transaction
			list($wk_month, $wk_day, $wk_date_rest) = explode('-',$other16);
			$other16 = $wk_day . "-" . $wk_month . "-" . $wk_date_rest;
			$my_message = dotransaction("NI16", "A", $ssn, $current_location, "", $other16, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[16][0] . " " . $my_responsemessage;
		}
		if ($other17change == 1)
		{
			// do other17 change transaction
			$my_message = dotransaction("NI17", "A", $ssn, $current_location, "", $other17, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[17][0] . " " . $my_responsemessage;
		}
		if ($other18change == 1)
		{
			// do other18 change transaction
			$my_message = dotransaction("NI18", "A", $ssn, $current_location, "", $other18, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[18][0] . " " . $my_responsemessage;
		}
		if ($other19change == 1)
		{
			// do other19 change transaction
			$my_message = dotransaction("NI19", "A", $ssn, $current_location, "", $other19, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[19][0] . " " . $my_responsemessage;
		}
		if ($other20change == 1)
		{
			// do other20 change transaction
			$my_message = dotransaction("NI20", "A", $ssn, $current_location, "", $other20, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= $wk_other[20][0] . " " . $my_responsemessage;
		}
	}
	$Query = "select issn.wh_id, issn.locn_id, ssn.other11, ssn.other12, ssn.other13, ssn.other14, ssn.other15_date, ssn.other16_date, ssn.other17_qty, ssn.other18_qty, ssn.other19, ssn.other20 FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_other11 = $Row[2] ;
		$current_other12 = $Row[3];
		$current_other13 = $Row[4];
		$current_other14 = $Row[5];
		$current_other15 = $Row[6];
		$current_other16 = $Row[7];
		$current_other17 = $Row[8];
		$current_other18 = $Row[9];
		$current_other19 = $Row[10];
		$current_other20 = $Row[11];
	}
	ibase_free_result($Result);
	if ($current_other16 > "")
	{
		list($wk_month, $wk_day, $wk_date_rest) = explode('/',$current_other16);
		$current_other16 = $wk_day . "-" . $wk_month . "-" . $wk_date_rest;
	}
	if ($current_other15 > "")
	{
		list($wk_month, $wk_day, $wk_date_rest) = explode('/',$current_other15);
		$current_other15 = $wk_day . "-" . $wk_month . "-" . $wk_date_rest;
	}
}
echo("<FORM action=\"drop1120.php\" method=\"post\" name=getssn onsubmit=\"return passFields2('" . $current_other15 . "','" . $current_other16 . "');\" >");
echo("<INPUT type=\"hidden\" name=\"other11change\" value=\"0\" >");
echo("<INPUT type=\"hidden\" name=\"other12change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other13change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other14change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other15change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other16change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other17change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other18change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other19change\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"other20change\" value=\"0\">");

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
if ($wk_other[11][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[11][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '11' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[11][0]);
		echo(":</td><td><SELECT name=\"other11\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other11change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other11))
			{
				if ($current_other11 == $Row[0])
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
			//if (isset($current_other11))
			{
				if ($current_other11 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other11 . "\" selected>" . $current_other11 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[11][0]);
		echo(":</td><td><input type=\"text\" name=\"other11\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other11))
		{
			echo $current_other11;
		}
		echo("\" onchange=\"document.getssn.other11change.value=1\">");
		echo ("</td>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[11][0]);
	echo(":</td><td><input type=\"text\" name=\"other11\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other11))
	{
		echo $current_other11;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[16][2] == "TRUE")
{
	// allowed to change on remote
	{
		// no drop down
		echo("<td>");
		echo($wk_other[16][0]);
		echo(":</td><td><input type=\"text\" name=\"other16\" size=\"20\" maxlength=\"20\" value=\"");
		if (isset($current_other16))
		{
			echo $current_other16;
		}
		echo("\" onchange=\"document.getssn.other16change.value=1\">");
		echo("<a href=\"javascript:show_calendar('document.getssn.other16', document.getssn.other16.value);\"><img src=\"cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Click here to POPup the date\"></a>");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[16][0]);
	echo(":</td><td><input type=\"text\" name=\"other16\" readonly=\"Y\" size=\"20\" maxlength=\"20\" value=\"");
	if (isset($current_other16))
	{
		echo $current_other16;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[12][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[12][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '12' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[12][0]);
		echo(":</td><td><SELECT name=\"other12\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other12change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other12))
			{
				if ($current_other12 == $Row[0])
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
			//if (isset($current_other12))
			{
				if ($current_other12 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other12 . "\" selected>" . $current_other12 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[12][0]);
		echo(":</td><td><input type=\"text\" name=\"other12\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other12))
		{
			echo $current_other12;
		}
		echo("\" onchange=\"document.getssn.other12change.value=1\">");
		echo ("</td>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[12][0]);
	echo(":</td><td><input type=\"text\" name=\"other12\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other12))
	{
		echo $current_other12;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[17][2] == "TRUE")
{
	// allowed to change on remote
	{
		// no drop down
		// must ensure numeric
		echo("<td>");
		echo($wk_other[17][0]);
		echo(":</td><td><input type=\"text\" name=\"other17\" size=\"10\" maxlength=\"10\" value=\"");
		if (isset($current_other17))
		{
			echo $current_other17;
		}
		//echo("\" onchange=\"document.getssn.other17change.value=1\">");
		echo("\" onchange=\"return processEditNum('" . $wk_other[17][0] . "','" . $wk_other[18][0] . "');\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[17][0]);
	echo(":</td><td><input type=\"text\" name=\"other17\" readonly=\"Y\" size=\"10\" maxlength=\"10\" value=\"");
	if (isset($current_other17))
	{
		echo $current_other17;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[13][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[13][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '13' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[13][0]);
		echo(":</td><td><SELECT name=\"other13\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other13change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other13))
			{
				if ($current_other13 == $Row[0])
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
			//if (isset($current_other13))
			{
				if ($current_other13 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other13 . "\" selected>" . $current_other13 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[13][0]);
		echo(":</td><td><input type=\"text\" name=\"other13\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other13))
		{
			echo $current_other13;
		}
		echo("\" onchange=\"document.getssn.other13change.value=1\">");
		echo ("</td>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[13][0]);
	echo(":</td><td><input type=\"text\" name=\"other13\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other13))
	{
		echo $current_other13;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[18][2] == "TRUE")
{
	// allowed to change on remote
	{
		// no drop down
		// must ensure numeric
		echo("<td>");
		echo($wk_other[18][0]);
		echo(":</td><td><input type=\"text\" name=\"other18\" size=\"10\" maxlength=\"10\" value=\"");
		if (isset($current_other18))
		{
			echo $current_other18;
		}
		//echo("\" onchange=\"document.getssn.other18change.value=1\">");
		echo("\" onchange=\"return processEditNum('" . $wk_other[17][0] . "','" . $wk_other[18][0] . "');\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[18][0]);
	echo(":</td><td><input type=\"text\" name=\"other18\" readonly=\"Y\" size=\"10\" maxlength=\"10\" value=\"");
	if (isset($current_other18))
	{
		echo $current_other18;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[14][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[14][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '14' ORDER BY description ";
		echo("<tr><td>");
		echo($wk_other[14][0]);
		echo(":</td><td><SELECT name=\"other14\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other14change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other14))
			{
				if ($current_other14 == $Row[0])
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
			//if (isset($current_other14))
			{
				if ($current_other14 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other14 . "\" selected>" . $current_other14 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[14][0]);
		echo(":</td><td><input type=\"text\" name=\"other14\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other14))
		{
			echo $current_other14;
		}
		echo("\" onchange=\"document.getssn.other14change.value=1\">");
		echo ("</td>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[14][0]);
	echo(":</td><td><input type=\"text\" name=\"other14\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other14))
	{
		echo $current_other14;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[19][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[19][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '19' ORDER BY description ";
		echo("<td>");
		echo($wk_other[19][0]);
		echo(":</td><td><SELECT name=\"other19\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other19change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other19))
			{
				if ($current_other19 == $Row[0])
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
			//if (isset($current_other19))
			{
				if ($current_other19 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other19 . "\" selected>" . $current_other19 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<td>");
		echo($wk_other[19][0]);
		echo(":</td><td><input type=\"text\" name=\"other19\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other19))
		{
			echo $current_other19;
		}
		echo("\" onchange=\"document.getssn.other19change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[19][0]);
	echo(":</td><td><input type=\"text\" name=\"other19\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other19))
	{
		echo $current_other19;
	}
	echo("\" >");
	echo ("</td></tr>");
}
if ($wk_other[15][2] == "TRUE")
{
	// allowed to change on remote
	{
		// no drop down
		echo("<tr><td>");
		echo($wk_other[15][0]);
		echo(":</td><td><input type=\"text\" name=\"other15\" size=\"20\" maxlength=\"20\" value=\"");
		if (isset($current_other15))
		{
			echo $current_other15;
		}
		echo("\" onchange=\"document.getssn.other15change.value=1\">");
		echo("<a href=\"javascript:show_calendar('document.getssn.other15', document.getssn.other15.value);\"><img src=\"cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Click here to POPup the date\"></a>");
		echo ("</td>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<tr><td>");
	echo($wk_other[15][0]);
	echo(":</td><td><input type=\"text\" name=\"other15\" readonly=\"Y\" size=\"20\" maxlength=\"20\" value=\"");
	if (isset($current_other15))
	{
		echo $current_other15;
	}
	echo("\" >");
	echo ("</td>");
}
if ($wk_other[20][2] == "TRUE")
{
	// allowed to change on remote
	if ($wk_other[20][1] == "TRUE")
	{
		// a drop down
		$Query = "SELECT description FROM global_conditions where other_no = '20' ORDER BY description ";
		echo("<td>");
		echo($wk_other[20][0]);
		echo(":</td><td><SELECT name=\"other20\" size=\"1\" class=\"sel3\" width=\"20\" onchange=\"document.getssn.other20change.value=1\">\n");
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Glocal Conditions!<BR>\n");
			exit();
		}
		$wk_selected = "N";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if (isset($current_other20))
			{
				if ($current_other20 == $Row[0])
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
			//if (isset($current_other20))
			{
				if ($current_other20 == "")
				{
					echo( "<OPTION value=\"\" selected>NO VALUE\n");
				}
				else
				{
					echo( "<OPTION value=\"" . $current_other20 . "\" selected>" . $current_other20 . " Not in Global Condition Master List\n");
				}
			}
		}
		echo ("</SELECT></td></tr>");
	} /* end of drop down */
	else
	{
		// no drop down
		echo("<td>");
		echo($wk_other[20][0]);
		echo(":</td><td><input type=\"text\" name=\"other20\" size=\"20\" maxlength=\"50\" value=\"");
		if (isset($current_other20))
		{
			echo $current_other20;
		}
		echo("\" onchange=\"document.getssn.other20change.value=1\">");
		echo ("</td></tr>");
	} /* end input no drop down */
}
else
{
	// not allowed to change on remote
	echo("<td>");
	echo($wk_other[20][0]);
	echo(":</td><td><input type=\"text\" name=\"other20\" readonly=\"Y\" size=\"20\" maxlength=\"50\" value=\"");
	if (isset($current_other20))
	{
		echo $current_other20;
	}
	echo("\" >");
	echo ("</td></tr>");
}
//release memory
ibase_free_result($Result2);
ibase_free_result($Result);
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
	echo("<td><form action=\"dropmodel.php\" method=\"post\" name=model onsubmit=\"return passFields();\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back" >');
	echo("</form>\n");
	echo("</td>\n");
	echo("<td><form action=\"drop610.php\" method=\"post\" name=nextmodel onsubmit=\"return passFields();\" >\n");
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
	echo("document.getssn.other11.focus();");
}
else
{
	echo('document.getssn.message.value="Enter Changes";');
	echo("document.getssn.other11.focus();");
}
?>
</script>
</html>
