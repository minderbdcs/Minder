<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get SSN you are working on</title>
<?php
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="../nopad.css">
<link rel=stylesheet type="text/css" href="ssn.css">
<script type="text/javascript">
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "-0123456789";
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
function processEdit(currentqty ) {
  	var csum;
  document.getssn.message.value=" ";
  if ( document.getssn.qty.value=="")
  {
  	document.getssn.message.value="Must Enter an Adjustment";
	document.getssn.qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getssn.qty.value)==false)
  {
  	document.getssn.message.value="Must be Numeric";
	document.getssn.qty.value = "";
	document.getssn.qty.focus();
  	return false;
  }
  csum = currentqty * 1
  csum += (document.getssn.qty.value * 1)
  if (( csum) < 0)
  {
  	document.getssn.message.value="SSN Cannot have its Qty Set Negative";
	document.getssn.qty.value = "";
	document.getssn.qty.focus();
  	return false;
  }
  return true;
}
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if (isset($_COOKIE['SaveUser']))
	{
		list($tran_user, $tran_device,$UserType) = explode("|", $_COOKIE['SaveUser']);
	}

function checkadjust($confirmpw )
{
	global $Link;

	// check that the password allowes stock adjusts
	$wk_confirm = 'F';
	$wk_confirm_user = '';
	$Query = "select stock_adjust, user_id from sys_user where pass_word = '" . $confirmpw . "'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query SYS_USER!<br>\n");
		exit();
	}
	else
	while (($Row = ibase_fetch_row($Result)))
	{
		if ($Row[0] == 'T')
		{
			$wk_confirm = $Row[0];
			$wk_confirm_user = $Row[1];
		}
	}
	ibase_free_result($Result);
	return ($wk_confirm == 'T');
} // end of function

	$doconfirm = "F";
	// check that ssn exists
	$Query = "select stock_adjust_confirm from control";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query CONTROL!<br>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$doconfirm = $Row[0];
	}
	if ($doconfirm == "")
	{
		$doconfirm = 'F';
	}

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
	}
	if (isset($_POST['message']))
	{
		$message = $_POST['message'];
	}
	if (isset($message))
	{

		if ($message == 'connect')
		{
			echo ("<B><FONT COLOR=RED>Can't Connect to DATABASE!</FONT></B>\n");
		}
		else
		if ($message == 'query')
		{
			echo ("<B><FONT COLOR=RED>Can't Query ISSN!</FONT></B>\n");
		}
		else
		if ($message == 'nossn')
		{
			echo ("<B><FONT COLOR=RED>SSN Not Found!</FONT></B>\n");
		}
		else
		{
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
	}
	if (isset($_POST['ssnfrom'])) 
	{
		$ssn = $_POST['ssnfrom'];
	}
	if (isset($_GET['ssnfrom'])) 
	{
		$ssn = $_GET['ssnfrom'];
	}
	if (isset($_POST['qty'])) 
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty'])) 
	{
		$qty = $_GET['qty'];
	}
	if (isset($_POST['confirmadjust'])) 
	{
		$confirmadjust = $_POST['confirmadjust'];
	}
	if (isset($_GET['confirmadjust'])) 
	{
		$confirmadjust = $_GET['confirmadjust'];
	}
	$reason = '';
	if (isset($_POST['reason'])) 
	{
		$reason = $_POST['reason'];
	}
	if (isset($_GET['reason'])) 
	{
		$reason = $_GET['reason'];
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
	if (isset($ssn))
	{
		// check that ssn exists
		$Query = "select ssn_id, current_qty, wh_id, locn_id, issn_status from issn where ssn_id = '" . $ssn . "'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query ISSN!<br>\n");
			exit();
		}
		else
		if (($Row = ibase_fetch_row($Result)))
		{
			$currentqty = $Row[1];
			$currentwh = $Row[2];
			$currentlocn = $Row[3];
			$currentstatus = $Row[4];
			//echo(substr($currentwh,0,1));
			if (substr($currentwh, 0,1) == 'X')
			{
				unset ($ssn);
				echo ("<B><FONT COLOR=RED>SSN Not in Adjustable Warehouse!</FONT></B>\n");
			}
			if (substr($currentstatus, 0,1) == 'X')
			{
				unset ($ssn);
				echo ("<B><FONT COLOR=RED>SSN Not of Adjustable Status!</FONT></B>\n");
			}
		}
		else
		{
			unset ($ssn);
			echo ("<B><FONT COLOR=RED>SSN Not Found!</FONT></B>\n");
		}
	}
	if (isset($ssn))
	{
		if (isset($qty))
		{
			if ($qty <> 0)
			{
				if (($qty + $currentqty ) >= 0)
				{
/*
					if (isset($image_x) and isset($image_y))
					{
						if ($image_x > 0 and $image_y > 0)
						{
*/
					if ($doconfirm == 'F' or checkadjust($confirmadjust))
					{
							// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = 1;
	$location = $currentwh . $currentlocn;
	$my_object = $ssn;
	$my_sublocn = "";
	$my_ref = "Starting SSN Adjustment " . $reason ;

	$my_message = "";
	$my_message = dotransaction_response("TROL", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	if ($my_responsemessage <> "Processed successfully ")
	{
		$message .= $my_responsemessage;
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	else
	{
		$tran_qty = $qty;
		$location = $currentwh . $currentlocn;
		$my_object = $ssn;
		$my_sublocn = "";
		$my_ref = "SSN Adj " . $reason ;

		$my_message = "";
		$my_message = dotransaction_response("TRIL", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else
		{
			unset($ssn);
		}
	}
/*
						}
					}
*/
					}
					else
					{
						$message .= "Not a Stock Adjust Allowed Password";
						echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
						$wk_get_confirm = 'T';
					}
				}
			}
		}
	}
	if (isset($ssn))
	{
		if (isset($wk_get_confirm))
		{
  			echo("<h4 ALIGN=\"LEFT\">Enter Adjustment Password</h4>");
		}
		else
		{
  			echo("<h4 ALIGN=\"LEFT\">Enter Adjustment Qty</h4>");
		}
	}
	else
	{
  		echo("<h4 ALIGN=\"LEFT\">Enter SSN</h4>");
	}

?>

 <form action="GetSSNAdj.php" method="post" name=getssn
<?php
if (isset($ssn))
{
	echo(" onsubmit=\"return processEdit(" . $currentqty . ")\">\n");
}
else
{
	echo(">\n");
}
//echo("<P>\n");
echo("<input type=\"hidden\" name=\"doconfirm\" value=\"" . $doconfirm . "\" >");
echo("<input type=\"text\" name=\"message\" readonly size=\"40\" ><br>");
echo("<label for=\"ssnfrom\">SSN</label>");
//echo("SSN:      <input type=\"text\" name=\"ssnfrom\"");
echo("<input type=\"text\" name=\"ssnfrom\"");
if (isset($ssn)) 
{
	echo(" value=\"".$ssn."\"");
}
echo(" size=\"20\"");
//echo(" maxlength=\"20\"><BR>\n");
echo(" maxlength=\"20\" onchange=\"document.getssn.submit()\"><br>\n");
if (isset($ssn))
{
	echo("<label for=\"currentqty\">Qty</label>");
	//echo("Current Qty <input type=\"text\" name=\"currentqty\"");
	echo("<input type=\"text\" name=\"currentqty\"");
	if (isset($currentqty)) 
	{
		echo(" value=\"".$currentqty."\"");
	}
	echo(" size=\"4\"");
	//echo(" readonly><br>\n");
	echo(" readonly>\n");
	echo("<br>\n");
	//echo("<br>\n");
	echo("<label for=\"currentwh\">Location</label>");
	//echo("Location <input type=\"text\" name=\"currentwh\"");
	echo("<input type=\"text\" name=\"currentwh\"");
	if (isset($currentwh)) 
	{
		echo(" value=\"".$currentwh."\"");
	}
	echo(" size=\"2\"");
	echo(" readonly>\n");
	echo("<input type=\"text\" name=\"currentlocn\"");
	if (isset($currentlocn)) 
	{
		echo(" value=\"".$currentlocn."\"");
	}
	echo(" size=\"10\"");
	echo(" readonly><br>\n");
	echo("<label for=\"currentstatus\">Status</label>");
	//echo("Status <input type=\"text\" name=\"currentstatus\"");
	echo("<input type=\"text\" name=\"currentstatus\"");
	if (isset($currentstatus)) 
	{
		echo(" value=\"".$currentstatus."\"");
	}
	echo(" size=\"2\"");
	echo(" readonly><br>\n");
	echo("<label for=\"qty\">Qty to Adjust</label>");
	//echo("Qty to Adjust: <input type=\"text\" name=\"qty\"");
	echo("<input type=\"text\" name=\"qty\"");
	if (isset($qty)) 
	{
		echo(" value=\"".$qty."\"");
	}
	echo(" size=\"4\"");
	echo(" maxlength=\"4\" onchange=\"return processEdit(" . $currentqty . ")\"><br>\n");
	if ($doconfirm == 'T')
	{
		echo("Confirm Adjust Password: <input type=\"password\" name=\"confirmadjust\"");
		echo(" size=\"8\"");
		echo(" ><br>\n");
		
	}
	echo("<label for=\"reason\">Reason</label>");
	//echo("Reason for Adjustment: <input type=\"text\" name=\"reason\"");
	echo("<input type=\"text\" name=\"reason\"");
	echo(" size=\"40\" maxlength=\"40\" ");
	echo(" ><br>\n");
}

{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if (isset($ssn))
	{
		whm2buttons('Adjust', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","adjustssn.gif");
	}
	else
	{
		whm2buttons('Accept', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
//commit
ibase_commit($dbTran);
?>
</P>
<script type="text/javascript">
<?php
	if (isset($ssn))
	{
		if (isset($wk_get_confirm))
		{
			echo("document.getssn.confirmadjust.focus();");
		}
		else
		{
			echo("document.getssn.qty.focus();");
		}
	}
	else
	{
		echo("document.getssn.ssnfrom.focus();");
	}
?>
</script>
</body>
</html>
