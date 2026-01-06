<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get ISSN you are working on</title>
<?php
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="issn.css">
<script type="text/javascript">
var strEmpty = "";
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "-0123456789.";
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
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFF00">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	include "logme.php";
	include "checkdatajs.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// create js for location check
	whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
	whm2scanvars($Link, 'ssn','BARCODE', 'BARCODE');
	whm2scanvars($Link, 'altssn','ALTBARCODE', 'ALTBARCODE');
?>

<script type="text/javascript">
function processEdit() {
  var csum;
  var mytype;
  document.getissn.message.value=" ";
  mytype = checkSsn(document.getissn.qty.value); 
  if (mytype == "none")
  {
	/* not an ssn - try an alt ssn */
	mytype = checkAltssn(document.getissn.qty.value); 
	if (mytype == "none")
	{
		/* not an ssn - try a location */
  		mytype = checkLocn(document.getissn.qty.value); 
		if (mytype == "none")
		{
			/* must be a qty */
		}
		else
		{
			/* a location */
			document.getissn.prodlocn.value = document.getissn.qty.value;
			document.getissn.ssn.value = "";
			document.getissn.qty.value = "";
			document.getissn.audited.value="N";
			document.getissn.submit();
			return true;
		}
	}
	else
	{
		/* an alt ssn */
		document.getissn.ssn.value = document.getissn.qty.value;
		document.getissn.qty.value = "";
		document.getissn.audited.value="N";
		return false;
	}
  }
  else
  {
	/* an ssn */
	document.getissn.ssn.value = document.getissn.qty.value;
	document.getissn.qty.value = "";
	document.getissn.audited.value="N";
	return false;
  }
  if ( document.getissn.qty.value=="")
  {
  	document.getissn.message.value="Must Enter a Qty";
        var tophalf = document.getElementById('message');
        tophalf.style.display = 'block';
	document.getissn.qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getissn.qty.value)==false)
  {
  	document.getissn.message.value="Must be Numeric";
        var tophalf = document.getElementById('message');
        tophalf.style.display = 'block';
	document.getissn.qty.value = "";
	document.getissn.qty.focus();
  	return false;
  }
  csum = 0
  csum += (document.getissn.qty.value * 1)
  if (( csum) < 0)
  {
  	document.getissn.message.value="ISSN Cannot have its Qty Set Negative";
        var tophalf = document.getElementById('message');
        tophalf.style.display = 'block';
	document.getissn.qty.value = "";
	document.getissn.qty.focus();
  	return false;
  }
  return true;
}
function processEdit2(how) {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getissn.prodlocn.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
	document.getissn.prodlocn.focus();
  	return false;
  }
  else
  {
	document.getissn.audited.value="N";
	if (how == 1)
 	{
		document.getissn.submit();
	}
	return true;
  }
}
function processEdit4(how) {
/* # check for valid ssn */
  var mytype;
  mytype = checkSsn(document.getissn.ssn.value); 
  if (mytype == "none")
  {
	/* not an ssn - try an alt ssn */
	mytype = checkAltssn(document.getissn.ssn.value); 
	if (mytype == "none")
	{
		/* not an ssn - try a location */
  		mytype = checkLocn(document.getissn.ssn.value); 
		if (mytype == "none")
		{
			alert("Not an SSN or Location"); 
			/* document.getissn.message.value="Not an SSN or Location"; */
			document.getissn.ssn.value = "";
			document.getissn.ssn.focus();
  			return false;
		}
		else
		{
			/* a location */
			document.getissn.prodlocn.value = document.getissn.ssn.value;
			document.getissn.ssn.value = "";
			document.getissn.audited.value="N";
			if (how == 1)
 			{
				document.getissn.submit();
			}
			return true;
		}
	}
	else
	{
		/* an alt ssn */
		document.getissn.audited.value="N";
		if (how == 1)
 		{
			document.getissn.qty.focus();
		}
		return false;
	}
  }
  else
  {
	/* an ssn */
	document.getissn.audited.value="N";
	if (how == 1)
 	{
		document.getissn.qty.focus();
	}
	return false;
  }
}
</script>

<?php
	//include "logme.php";
	if (isset($_COOKIE['SaveUser']))
	{
		list($tran_user, $tran_device,$UserType) = explode("|", $_COOKIE['SaveUser']);
	}

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
	}
	if (isset($_POST['message']))
	{
		$message = $_POST['message'];
	}
	//$message = "";
	if (isset($_POST['product'])) 
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product'])) 
	{
		$product = $_GET['product'];
	}
	if (isset($_POST['owner'])) 
	{
		$owner = $_POST['owner'];
	}
	if (isset($_GET['owner'])) 
	{
		$owner = $_GET['owner'];
	}
	if (isset($_POST['qty'])) 
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty'])) 
	{
		$qty = $_GET['qty'];
	}
	if (isset($_POST['prodlocn'])) 
	{
		$prodlocn = $_POST['prodlocn'];
	}
	if (isset($_GET['prodlocn'])) 
	{
		$prodlocn = $_GET['prodlocn'];
	}
	if (isset($_POST['ssn'])) 
	{
		$ssn = $_POST['ssn'];
	}
	if (isset($_GET['ssn'])) 
	{
		$ssn = $_GET['ssn'];
	}
	if (isset($ssn))
	{
		if ($ssn == "")
		{
			unset ($ssn);
			unset ($qty);
		}
	}
	if (isset($_POST['audited'])) 
	{
		$wk_audited = $_POST['audited'];
	}
	if (isset($_GET['audited'])) 
	{
		$wk_audited = $_GET['audited'];
	}
	if (!isset($wk_audited))
	{
		$wk_audited = "N";
	}
	//echo("audited received :" . $wk_audited);
/*
	if (isset($_POST['ssncnt'])) 
	{
		$wk_ssncnt = $_POST['ssncnt'];
	}
	if (isset($_GET['ssncnt'])) 
	{
		$wk_ssncnt = $_GET['ssncnt'];
	}
*/
	if (isset($_POST['recordid'])) 
	{
		$wk_record_id = $_POST['recordid'];
	}
	if (isset($_GET['recordid'])) 
	{
		$wk_record_id = $_GET['recordid'];
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
	$wk_docommit = "N";
	if (isset($prodlocn))
	{
		$Query = "select locn_name ";
		$Query .= "from location ";
		$Query .= "where wh_id = '" . substr($prodlocn,0,2) . "' and locn_id='";
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "'";
		
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query Location!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$locn_desc = $Row[0];
		}
		else
		{
			$message .= "Not a Valid Location";
			unset($prodlocn);
		}
		//commit
		ibase_free_result($Result);
		ibase_commit($dbTran);
	}
	if (isset($ssn))
	{
		$Query = "select ssn.storage_uom, issn.prod_id, issn.audited ";
		$Query .= "from issn ";
		$Query .= "join ssn on issn.original_ssn = ssn.ssn_id ";
		$Query .= "where issn.ssn_id = '" . $ssn ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query SSN!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_uom = $Row[0];
			$wk_current_prod = $Row[1];
			$wk_audited = $Row[2];
		}
		if (!isset($wk_uom))
		{
			$wk_uom = "EA";
		}
		ibase_free_result($Result);
	}
	if ((isset($prodlocn)) and (!isset($qty)) and (!isset($ssn)))
	{
		$wk_last_location = getBDCScookie($Link, $tran_device, "stocktakelocation");
		if ($wk_last_location <> $prodlocn)
		{
			// check whether a STLX is required
			// do the STLX - no - this is done in the STLO
			// do transactions for new location
			$my_source = 'SSBSSKSSS';
			$tran_qty = 0;
			$location = $prodlocn;
			$my_object = "";
			$my_sublocn = "";
			$my_ref = "Stocktake Location" ;
		
			$my_message = "";
			$tran_tran = "STLO";
			$my_message = dotransaction_response($tran_tran, "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_message_response = urldecode($my_mess_label) . " || ";
				list($my_mess, $wk_ssncnt) = explode("|", $my_message_response);
				if (!isset($message))
				{
					$message = "";
				}
				$message .= $my_mess ;
			}
			else
			{
				$message .= "New Location " . $prodlocn;
			}
			setBDCScookie($Link, $tran_device, "stocktakelocation", $prodlocn);
		}
		$wk_docommit = "Y";
	}
	if (isset($ssn))
	{
		if (isset($qty))
		{
			if ($qty > 0)
			{
				// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = $qty;
	$location = $prodlocn;
	$my_object = $ssn;
	$my_sublocn = "";
	if (!isset($wk_record_id))
	{
		$wk_record_id = "";
	}
	$my_ref = $qty . "|" . $wk_record_id . "|"   ;

	$my_message = "";
	$tran_tran = "STIS";
	//echo("audit" . $wk_audited);
	if ($wk_audited == "R")
	{
		$wk_tran_code = "R";
		$my_sublocn = ""; // want the record id here
		//$my_ref = $wk_record_id . "||" ;
	}
	else
	{
		$wk_tran_code = "F";
	}
	$my_message = dotransaction_response($tran_tran, $wk_tran_code, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$message .= urldecode($my_mess_label) . " ";
		$my_message_response = urldecode($my_mess_label) . " ||||||| ";
		list($my_mess, $wk_record_id, $wk_action, $wk_variance, $wk_status) = explode("|", $my_message_response);
		$message .= $my_mess ;
	}
	{
		$Query = "select audited ";
		$Query .= "from issn ";
		$Query .= "where issn.ssn_id = '" . $ssn ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query ISSN!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_audited = $Row[0];
		}
		if (!isset($wk_audited))
		{
			$wk_audited = "N";
		}
		if ($wk_audited == 'R')
		{
			$message = "Item Seen but Recount Required";
			unset($qty);
		}
		else
		{
			$message .= "Item " . $ssn . " Seen ";
			unset($ssn);
			$wk_issn_seen = "T";
/*
			if (isset($wk_ssncnt))
			{
				if ($wk_ssncnt > 0)
				{
					$wk_ssncnt = $wk_ssncnt - 1;
				}
			}
*/
		}
		ibase_free_result($Result);

	}
	$wk_docommit = "Y";
			}
		}
	}
	if (isset($prodlocn))
	{
		$Query = "select count(*) ";
		$Query .= "from issn ";
		$Query .= "where issn.wh_id = '"  ;
		$Query .= substr($prodlocn,0,2)."' ";
		$Query .= "and issn.locn_id = '" ;
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2)."'  ";
		$Query .= "and issn.audited = 'M' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query ISSN count!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_ssncnt = $Row[0];
		}
	}

 	//echo("<H3 ALIGN=\"LEFT\">Stocktake ISSN</H3>");
	if (isset($prodlocn))
	{
		if (isset($ssn))
		{
  			echo("<h4 align=\"LEFT\">");
			if ($wk_audited == 'R')
			{
				echo("Recount Required - ");
			}
  			//echo("<h4 align=\"LEFT\">Enter Qty</h4>");
  			//echo("<h4 align=\"LEFT\">Enter Qty");
  			echo("Enter Qty");
			if (isset ($wk_ssncnt))
			{
				echo(" (".$wk_ssncnt . " ToGo)");
			}
  			echo("</h4>");
		}
		else
		{
			if (isset($wk_issn_seen))
			{
  				echo("<h4 align=\"LEFT\">Scan next ISSN");
			}
			else
			{
  				//echo("<h4 align=\"LEFT\">Enter SSN and Qty</h4>");
  				echo("<h4 align=\"LEFT\">Enter SSN and Qty");
			}
			if (isset ($wk_ssncnt))
			{
				echo(" (".$wk_ssncnt . " ToGo)");
			}
  			echo("</h4>");
		}
	}
	else
	{
  		echo("<h4 align=\"LEFT\">Enter Location</h4>");
	}

?>

 <form action="GetSTKISSN.php" method="post" name=getissn ONSUBMIT="return processEdit2(0);">
<?php
echo("<INPUT type=\"hidden\" name=\"audited\" value=\"" . $wk_audited . "\">");
//echo("<INPUT type=\"text\" name=\"audited\" value=\"" . $wk_audited . "\">");
if (isset ($wk_ssncnt))
{
	echo("<INPUT type=\"hidden\" name=\"ssncnt\" value=\"" . $wk_ssncnt . "\">");
}
if (isset ($wk_record_ids))
{
	echo("<INPUT type=\"hidden\" name=\"recordid\" value=\"" . $wk_record_id . "\">");
}
echo("<p><label for=\"prodlocn\">Location</label><input type=\"text\"  id=\"prodlocn\" name=\"prodlocn\" class=\"locationform\"");
if (isset($prodlocn)) 
{
	echo(" value=\"".$prodlocn."\"");
}
echo(" size=\"12\"");
echo(" maxlength=\"10\" onfocus=\"document.getissn.prodlocn.value=strEmpty\" onchange=\"return processEdit2(1)");
if (isset($ssn))
{
	echo(";document.getissn.ssn.value=strEmpty");
}
echo("\" />\n");
echo("</p>\n");

if (isset($prodlocn))
{

	/* save screen space */
	{
		echo("<p><label for=\"ssn\">ISSN</label><input type=\"text\"  id=\"ssn\" name=\"ssn\" class=\"locationform\"");
		if (isset($ssn)) 
		{
			echo(" value=\"".$ssn."\"");
		}
		echo(" size=\"20\"");
		echo(" maxlength=\"20\" onchange=\"document.getissn.qty.value=strEmpty;return processEdit4(1)\" /></p>\n");
		echo("<p><label for=\"qty\">Qty</label><input type=\"text\"  id=\"qty\" name=\"qty\" class=\"locationform\"");
		if (isset($qty)) 
		{
			echo(" value=\"".$qty."\"");
		}
		echo(" size=\"7\"");
		echo(" maxlength=\"20\" onchange=\"return processEdit()\" ");
		//echo("  onfocus=\"return processEdit4(0)\" /></p>\n");
		echo("   /></p>\n");
	}
}
/*
	if (isset($message))
	{
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
*/
	echo("<div  id=\"message\" name=\"message\" > ");
	if (isset($message)) 
	{
		echo("<input type=\"text\"  id=\"message\" name=\"message\" readonly size=\"60\" class=\"message\" ");
		if ($message <> "")
		{
			echo(" value=\"".$message."\"");
		}
	}
	else
	{
		echo("<input type=\"text\"  id=\"message\" name=\"message\" readonly size=\"60\" class=\"message\" ");
	}
	echo("   /></p>\n");
	echo("</div>\n");

{
	// html 4.0 browser
 	echo("<table border=\"0\" align=\"LEFT\">");
	if (isset($qty))
	{
		whm2buttons('Accept', 'EndISSN.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
	else
	{
		whm2buttons('Accept', 'EndISSN.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
//commit
//ibase_commit($dbTran);
?>
</P>
<script type="text/javascript">
<?php
	if (isset($prodlocn))
	{
		if (isset($ssn) and ($ssn <> ""))
		{
			echo("document.getissn.qty.focus();");
		}
		else
		{
			echo("document.getissn.ssn.focus();");
		}
	}
	else
	{
		echo("document.getissn.prodlocn.focus();");
	}
if ($wk_docommit == "Y")
{
	ibase_commit($Link);
}
ibase_close($Link);
?>
</script>
</body>
</html>
