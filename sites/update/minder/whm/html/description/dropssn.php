<?php
include "../login.inc";
?>
<html>
<head>
  <title>SSN Description via Drop Downs</title>
<?php
include "viewport.php";
{
	echo('<link rel=stylesheet type="text/css" href="dropssn.css">');
}
?>
 </head>
<script>

function passFields() {
 document.model.currentssn.value = document.getssn.ssn.value;
 return true
}
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
		if (document.getssn.ssn.value != lastvalue)
		{
			document.getssn.typechange.value=0
			document.getssn.genchange.value=0
		}
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
if (isset($_POST['sntype']))
{
	$sntype = $_POST['sntype'];
}
if (isset($_GET['sntype']))
{
	$sntype = $_GET['sntype'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['generic']))
{
	$generic = $_POST['generic'];
}
if (isset($_GET['generic']))
{
	$generic = $_GET['generic'];
}
if (isset($_POST['typechange']))
{
	$typechange = $_POST['typechange'];
}
if (isset($_GET['typechange']))
{
	$typechange = $_GET['typechange'];
}
if (isset($_POST['genchange']))
{
	$genchange = $_POST['genchange'];
}
if (isset($_GET['genchange']))
{
	$genchange = $_GET['genchange'];
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
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
whm2scanvars($Link, 'ssn','BARCODE', 'SSN');


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


echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
if (isset($ssn))
{
	$Query = "select issn.wh_id, issn.locn_id, ssn.ssn_type, ssn.generic,issn.current_qty FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_type = $Row[2] ;
		$current_generic = $Row[3];
		$current_qty = $Row[4];
	}
	if (isset($typechange) and isset($genchange) )
	{
		include "transaction.php";
		//if ($typechange == 1)
		{
			if (checkauob() == "T")
			{
				// do auob change transaction
				$my_message = dotransaction_response("AUOB", "A", $ssn, $current_location, "", "SSN Seen", $current_qty, "SSSSSSSSS", $tran_user, $tran_device, "N");
				if ($my_message > "")
				{
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				}
				else
				{
					$my_responsemessage = "Processed OK";
					$my_responsemessage = " ";
				}
				if (($my_responsemessage == " ") or
	     			    ($my_responsemessage == ""))
				{
					$my_responsemessage = "Processed successfully ";
				}
			}
		}
		if ($typechange == 1)
		{
			// do type change transaction
			$my_message = dotransaction("NITP", "A", $ssn, $current_location, "", $sntype, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_responsemessage = urldecode($my_mess_label) . " ";
			}
			else
			{
				$my_responsemessage = "Processed OK";
			}
			$message .= "Type " . $my_responsemessage;
		}
		if ($genchange == 1)
		{
			// do generic change transaction
			$my_message = dotransaction("NIOB", "A", $ssn, $current_location, "", $generic, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
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
			$message .= " Generic " . $my_responsemessage;
		}

		$Query = "select ssn.ssn_type, ssn.generic FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read ISSN!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$current_type = $Row[0] ;
			$current_generic = $Row[1];
		}
	}
}
echo("<FORM action=\"dropssn.php\" method=\"post\" name=getssn ONSUBMIT=\"return processEdit(0,'");
if (isset($ssn))
{
	echo $ssn;
}
echo ("');\">");
echo("<INPUT type=\"hidden\" name=\"typechange\" value=\"0\" >");
echo("<INPUT type=\"hidden\" name=\"genchange\" value=\"0\">");
if (isset($current_location))
{
	echo("<INPUT type=\"hidden\" name=\"current_location\" value=\"$current_location\" >");
	echo("Current Location: <INPUT type=\"text\" readonly name=\"wk_current_location\" size=\"10\" value=\"". substr($current_location,2,strlen($current_location) - 2) . "\" >");
}

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"50\" class=\"message\"><br>\n");
echo('<table border="0">');
echo("<tr><td>");
echo("Ssn:</td><td><INPUT type=\"text\" name=\"ssn\" maxlength=\"20\" size=\"20\" onchange=\"return processEdit(0,'");
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
$Query = "SELECT code, description FROM ssn_type ORDER BY description ";
echo("Type:</td><td><SELECT name=\"sntype\" size=\"1\" class=\"sel3\" onchange=\"document.getssn.typechange.value=1\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Type!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($current_type))
	{
		if ($current_type == $Row[0])
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
	//if (isset($current_type))
	{
		if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
		else
		{
			echo( "<OPTION value=\"" . $current_type . "\" selected>" . $current_type . " Not in Types Master List\n");
		}
	}
}
echo ("</SELECT></td></tr>");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<tr><td>");
echo("Generic:</td><td><SELECT name=\"generic\" size=\"1\" class=\"sel3\" onchange=\"document.getssn.genchange.value=1\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (isset($current_generic))
	{
		if ($current_generic == $Row[0])
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
	//if (isset($current_generic))
	{
		if ($current_generic == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
		else
		{
			echo( "<OPTION value=\"" . $current_generic . "\" selected>" . $current_generic . " Not in Generics Master List\n");
		}
	}
}
echo ("</SELECT></td></tr>");
//release memory
ibase_free_result($Result);
//commit
ibase_commit($dbTran);
echo ("<BR>");
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./desc_menu.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
	echo("<tr><td><form action=\"dropmodel.php\" method=\"post\" name=model onsubmit=\"return passFields();\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/more.gif" alt="Model .. Status" >');
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
