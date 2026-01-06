<?php
include "../login.inc";
?>
<html>
<head>
  <title>SSN Price and Other 17</title>
<?php
{
	echo('<link rel=stylesheet type="text/css" href="dropssn.css">');
}
?>
 </head>
<script>

function processEdit(dosubmit, lastvalue) {
  var mytype;
  /* document.getssn.message.value="in process edit"; */
  if ( document.getssn.ssn.value=="")
  {
  	document.getssn.message.value="Must Enter the SSN";
	document.getssn.ssn.focus()
  	return false
  }
  mytype = checkSsn(document.getssn.ssn.value); 
  if (mytype == "none")
  {
	alert("Not an SSN");
	document.getssn.ssn.value=""
	document.getssn.ssn.focus()
  	return false;
  }
  else
  {
  	if (dosubmit == 1)
	{
		document.getssn.ssnchange.value="T"
		document.getssn.submit()	
	}
	return true;
  }
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "checkdatajs.php";
include "transaction.php";

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
if (isset($_POST['other17']))
{
	$other17 = $_POST['other17'];
}
if (isset($_GET['other17']))
{
	$other17 = $_GET['other17'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['poprice']))
{
	$poprice = $_POST['poprice'];
}
if (isset($_GET['poprice']))
{
	$poprice = $_GET['poprice'];
}
if (isset($_POST['ssnchange']))
{
	$ssnchange = $_POST['ssnchange'];
}
if (isset($_GET['ssnchange']))
{
	$ssnchange = $_GET['ssnchange'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function checkauob()
{
	global $Link, $dbTran;
	$Query = "SELECT SEND_AUOB FROM CONTROL";
	//echo($Query); 
	$wk_auob = "";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read CONTROL!<BR>\n");
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_auob =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}
	// null means yes
	if ($wk_auob == "")
	{
		$wk_auob = "T";
	}
	return $wk_auob;
}

$wk_sysuser = "F";
$wk_saleuser = "F";
$wk_credituser = "F";
{
	$Query = "SELECT sys_admin,sale_manager,credit_manager from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
		$wk_saleuser = $Row[1];
		$wk_credituser = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
}
//release memory
//ibase_free_result($Result);

$Query = "select field17, dd_other17, rm_other17 
from ssn_group where ssn_group = 'DEFAULT' ";
//echo($Query);
$wk_17_label = "";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Group!<BR>\n");
}
else
{
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_17_label = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}

// create js for check pass values to notes
echo "<script>\n";
echo "function passFields(passType) {\n";
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
whm2scanvars($Link, 'ssn','BARCODE', 'SSN');

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
$wk_doclear = "F";
if (isset($ssn))
{
	/*
	transactions
	AUOB
	NIPP
	NIxx other 17
	*/
	$Query = "select issn.wh_id, issn.locn_id, ssn.purchase_price, ssn.other17_qty, issn.current_qty FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_price = $Row[2] ;
		$current_oqty = $Row[3] ;
		$current_qty = $Row[4] ;
	}
	//release memory
	ibase_free_result($Result);
	if ($ssnchange == "T")
	{
		// a new ssn so clear passwd values
		$poprice = $current_price;
		$other17 = $current_oqty;
	}
	else
	{
		if (checkauob() == "T")
		{
			// do seen asset transaction
			$my_message = dotransaction("AUOB", "A", $ssn, $current_location, "", "ssn seen ", $current_qty, "SSSSSSSSS", $tran_user, $tran_device, "N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Ped OK";
			}
			$message .= "Seen " . $ssn . " " . $my_responsemessage;
		}
		// set to clear
		$wk_doclear = "T";
	}
	if (isset($poprice) )
	{
		if ($poprice <> 0)
		{
			if ($poprice <> $current_price)
			{
				// do price change transaction
				$my_message = dotransaction("NIPP", "A", $ssn, $current_location, "", "price changed ", 100 * $poprice, "SSSSSSSSS", $tran_user, $tran_device, "N");
				if ($my_message > "")
				{
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				}
				else
				{
					$my_responsemessage = "Ped OK";
				}
				$message .= "Price " . $my_responsemessage;
			}
		}
	}
	if (isset($other17) )
	{
		if ($other17 <> 0)
		{
			if ($other17 <> $current_oqty)
			{
				// do qty change transaction
				$my_message = dotransaction("NI17", "A", $ssn, $current_location, "", $other17, $other17, "SSSSSSSSS", $tran_user, $tran_device, "N");
				if ($my_message > "")
				{
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				}
				else
				{
					$my_responsemessage = "Ped OK";
				}
				//$message .= "Repair " . $my_responsemessage;
				$message .= $wk_17_label . $my_responsemessage;
			}
		}
	}
}
// if set to clear
if ($wk_doclear == "T")
{
	unset($ssn);
	unset($poprice);
	unset($other17);
}
echo("<FORM action=\"dropssn2.php\" method=\"post\" name=getssn ONSUBMIT=\"return processEdit(0,'");
if (isset($ssn))
{
	echo $ssn;
}
echo ("');\">");
if (isset($current_location))
{
	echo("<INPUT type=\"hidden\" name=\"current_location\" value=\"$current_location\" >");
}

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"50\" class=\"message\"><br>\n");
echo('<table border="0">');
echo("<tr><td>");
echo("<INPUT type=\"hidden\" name=\"ssnchange\" value=\"N\">");
echo("Ssn:</td><td><INPUT type=\"text\" name=\"ssn\" maxlength=\"20\" size=\"20\" onchange=\"return processEdit(1,'");
if (isset($ssn))
{
	echo $ssn;
}
echo ("');\" value=\"");
if (isset($ssn))
{
	echo $ssn;
}
echo ("\" ></td></tr>\n");
echo("<tr><td>");
echo("PO$</td><td><INPUT type=\"text\" name=\"poprice\" size=\"14\" value=\"$poprice\"  >\n");
echo("</td></tr><tr><td>");
//echo("Repair$:</td><td><INPUT type=\"text\" name=\"other17\" size=\"10\" value=\"$other17\" >\n");
echo($wk_17_label . ":</td><td><INPUT type=\"text\" name=\"other17\" size=\"10\" value=\"$other17\" >\n");
echo ("</td></tr>");
//commit
ibase_commit($dbTran);
echo ("<BR>");

if (($wk_sysuser != "T") and ($wk_saleuser != "T") and ($wk_credituser != "T"))
{
	whm2buttons('Accept', 'desc_menu.php?message=Not+Available+to+You',"Y","Cancel_50x100.gif","Back","accept.gif");
	echo("<SCRIPT>document.Back.submit();</SCRIPT>");
}
else
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./desc_menu.php" ,"N" ,"Cancel_50x100.gif" ,"Back" ,"accept.gif");
	echo("<tr>");
	echo("<td><form action=\"notes.php\" method=\"post\" name=notes onsubmit=\"return passFields(1);\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn2\" > ");  
	echo("<input type=\"hidden\" name=\"from\" value=\"dropssn2.php\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/comment.gif" alt="Notes" >');
	echo("</form>\n");
	echo("</td></tr></table>\n");
}
echo("</div>\n");
echo("<script>");
if (isset($message))
{
	echo("document.getssn.message.value=\"" . $message . " Scan SSN" . "\";");
	echo('document.getssn.ssn.focus();');
}
else
{
	echo('document.getssn.message.value="Scan SSN";');
	echo('document.getssn.ssn.focus();');
}
?>
</script>
</html>
