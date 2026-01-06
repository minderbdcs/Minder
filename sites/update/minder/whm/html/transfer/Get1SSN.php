<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Get SSN you are working on</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	//include "checkdatajs.php";
	include "checkdata.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Transfer_Menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	// create js for location check
	//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
	//whm2scanvars($Link, 'devlocn','DEVICE', 'DEVICE');
/*

<script type="text/javascript">
function processEdit(myValue) {
-* # check for valid location *-
  var mytype;
  -* mytype = checkLocn(document.getssn.tolocn.value);  *-
  mytype = checkLocn(myValue); 
  if (mytype == "none")
  {
	mytype = checkDevlocn(myValue);
	if (mytype == "none")
	{
		alert("Not a Location");
  		return false;
	}
	else
	{
		return true;
	}
  }
  else
  {
	return true;
  }
}
</script>
*/

// ========================================================================================================================

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
	if (isset($_POST['tolocn'])) 
	{
		$tolocn = $_POST['tolocn'];
	}
	if (isset($_GET['tolocn'])) 
	{
		$tolocn = $_GET['tolocn'];
	}
	if (isset($_POST['moveanswer'])) 
	{
		$move_answer = $_POST['moveanswer'];
	}
	if (isset($_GET['moveanswer'])) 
	{
		$move_answer = $_GET['moveanswer'];
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
	//phpinfo();
	if (isset($move_answer))
	{
		$ok_answer = "F";
		foreach ($move_answer as $Key_answer => $Value_answer)
		{
			if ($Value_answer == "FAILED")
			{
				$ok_answer = "T";
			}
			if ($Value_answer == "PASSED")
			{
				$ok_answer = "T";
			}
		}
		if ($ok_answer == "F")
		{
			unset($move_answer);
		}
	}
	if (isset($ssn))
	{
	        // trim it
		$ssn = trim($ssn);
		if ($ssn <> "")
		{
			// do check a valid ssn
			$field_type = checkForTypein($ssn, 'BARCODE' ); 
			if ($field_type == "none")
			{
				// perhaps an alt barcode
				$field_type = checkForTypein($ssn, 'ALTBARCODE' ); 
				if ($field_type == "none")
				{
					// a dont know
					//unset($ssn);
					//echo ("<B><FONT COLOR=RED>Not a Valid SSN!</FONT></B>\n");
					$wk_dummy = 1;
				}
				elseif ($startposn > 0)
				{
					$wk_realdata = substr($ssn,$startposn);
					$ssn = strtoupper($wk_realdata);
				}
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($ssn,$startposn);
					$ssn = strtoupper($wk_realdata);
				}
			}
		}
	}
	if (isset($ssn))
	{
		// check that ssn exists
		//$Query = "select original_ssn, current_qty, wh_id, locn_id, issn_status, prod_id, pick_order, prev_qty from issn where ssn_id = '" . $ssn . "'";
		$Query = "select i1.original_ssn, i1.current_qty, i1.wh_id, i1.locn_id, i1.issn_status, i1.prod_id, i1.pick_order, i1.prev_qty, c1.return_generates_credit, i1.company_id from issn i1 join control c1 on c1.record_id = 1 where i1.ssn_id = '" . $ssn . "'";
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
			$currentprod = $Row[5];
			$currentpickorder = $Row[6];
			$currentprevqty = $Row[7];
			$currentdocredit = $Row[8];
			$currentcompany = $Row[9];
			$currentorigssn = $Row[0];
			//echo(substr($currentwh,0,1));
/*
*/
			$currentdocredit = "F";
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
		ibase_free_result($Result); 
	}
	if (isset($ssn))
	{
		if (isset($currentpickorder))
		{
			// get the order for the pick label no
			$Query = "select p1.pick_order from pick_item p1 where p1.pick_label_no = '" . $currentpickorder . "'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query PICK ITEM!<br>\n");
			}
			else
			if (($Row = ibase_fetch_row($Result)))
			{
				$currentpickorderno = $Row[0];
			}
			ibase_free_result($Result); 
			if (!isset($currentpickorderno))
			{
				$Query = "select p1.pick_order from pick_item_dx p1 where p1.pick_label_no = '" . $currentpickorder . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to query PICK ITEM DX!<br>\n");
				}
				else
				if (($Row = ibase_fetch_row($Result)))
				{
					$currentpickorderno = $Row[0];
				}
				ibase_free_result($Result); 
			}
		}
	}
	if (isset($ssn))
	{
		if (isset($tolocn))
		{
	        	// trim it
			$tolocn = trim($tolocn);
			if ($tolocn <> "")
			{
				// do check a valid location
				$field_type = checkForTypein($tolocn, 'LOCATION' ); 
				if ($field_type == "none")
				{
					// perhaps a device
					$field_type = checkForTypein($tolocn, 'DEVICE' ); 
					if ($field_type == "none")
					{
						// a dont know
						unset($tolocn);
						echo ("<B><FONT COLOR=RED>Not a Valid Location!</FONT></B>\n");
					}
					else
					if ($startposn > 0)
					{
						$wk_realdata = substr($tolocn,$startposn);
						$tolocn = strtoupper($wk_realdata);
					}
				}
				else
				{
					if ($startposn > 0)
					{
						$wk_realdata = substr($tolocn,$startposn);
						$tolocn = strtoupper($wk_realdata);
					}
				}
			}
		}
	}
	/* 
	if have ssn and location and no answer
		check the sys_moves as to whether ask a question for the move
		if not 
			set the answer as noreturn 
			just continue to the transaction
		else
			ask the question
	*/
	if (isset($ssn))
	{
		if (isset($tolocn))
		{
			if ($tolocn <> "")
			{
				// get the move_stat of this location
				// check that location exists
				$Query = "select move_stat from location where wh_id = '";
				$Query .= substr($tolocn,0,2)."'";
		 		$Query .= " and locn_id = '" ;
				$Query .= substr($tolocn,2,strlen($tolocn) - 2)."'";
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to query Location!<br>\n");
					exit();
				}
				else
				if (($Row = ibase_fetch_row($Result)))
				{
					$movestat = $Row[0];
				}
				else
				{
					$movestat = '';
				}
				ibase_free_result($Result); 

				if (!isset($move_answer))
				{
					// check/get that move question 
					$Query = "select sys_moves.move_question_id, test_questions.question from sys_moves join test_questions on test_questions.question_id = sys_moves.move_question_id where sys_moves.from_status = '" . $currentstatus . "' and sys_moves.into_status = '" . $movestat . "'";
					if (!($Result = ibase_query($Link, $Query)))
					{
						echo("Unable to query SYS MOVES!<br>\n");
						exit();
					}
					else
					if (($Row = ibase_fetch_row($Result)))
					{
						$move_question_id = $Row[0];
						$move_question = $Row[1];
					}
					else
					{
						$move_question_id = null;
						$move_question = '';
					}
					if ($move_question == '')
					{
						unset($move_question);
						$move_answer = array("FAILED");
					}
					ibase_free_result($Result); 
					
				}
			}
		}
	}
	/* 
	if have ssn and location and answer
		if answer is noreturn
		else
			save the current issn.pick_order
			update the issn as in the move profile for DI
			(issn.pick_order = null, issn.issn_status = new status)
	*/
	$wk_trbk_message = "";
	$wk_trol_message = "";
	$wk_tril_message = "";
	$wk_do_transfer = "T";
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if (isset($ssn))
	{
		if (isset($tolocn))
		{
			if ($tolocn <> "")
			{
				if (isset($move_answer))
				{
					foreach ($move_answer as $Key_answer => $Value_answer)
					{
						if ($Value_answer == "PASSED")
						{
							/* do the trbk */
                                                        /*
                                                        */
	$my_source = 'SSBSSKSSS';
	if ($currentqty <= 0) 
	{
		$tran_qty = $currentprevqty;
	} else {
		$tran_qty = $currentqty;
	}
	//$location = $currentwh . $currentlocn;
	$location = $tolocn;
	$my_object = $ssn;
	$my_sublocn = "";
	$my_ref = "Return SSN " ;
	// what is the company
	// product
	// order
	$tran_company = $currentcompany;
	$tran_product = $currentprod;
	//$currentpickorder; /* this is the pick label no  - so need the try the pick_item or pick_item_dx */
	if (isset($currentpickorderno))
	{
		$tran_order = $currentpickorderno;
	} else {
		$tran_order = $currentpickorder;
	}
		
	$wk_trbk_message = "";
	$wk_trol_message = "";
	$wk_tril_message = "";
	$my_message = "";
	//$my_message = dotransaction_response("TRBK", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	$my_message = dotransaction_response("TRBK", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, $tran_product, $tran_company, $tran_order);
	if ($my_message > "")
	{
		list($my_mess_field1, $my_mess_field2, $my_mess_field3, $my_mess_label) = explode("|", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	$wk_trbk_message .= "TRBK:" . $my_responsemessage . " ";
	if ($my_responsemessage <> "Processed successfully ")
	{
		$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	$last_ssn = $ssn;
	unset($ssn);
	$wk_do_transfer = "F";
	//commit
	ibase_commit($dbTran);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
						}
					}
				}
			}
		}
	}
	// do transaction
	if (isset($ssn))
	{
		if (isset($tolocn))
		{
			if ($tolocn <> "")
			{
				if (isset($move_answer))
				{
					if ($wk_do_transfer == "T")
					{
/*
*/
							// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = 1;
	$location = $currentwh . $currentlocn;
	$my_object = $ssn;
	$my_sublocn = "";
	$my_ref = "Starting SSN Transfer" ;

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
	$wk_trol_message .= $my_responsemessage . " ";
	if ($my_responsemessage <> "Processed successfully ")
	{
		$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		//commit
		ibase_commit($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	}
	else
	{
		$tran_qty = 0;
		//$location = $currentwh . $currentlocn;
		$location = $tolocn;
		$my_object = $ssn;
		$my_sublocn = "";
		$my_ref = "Completing SSN Transfer " ;

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
		$wk_tril_message .=  $my_responsemessage . " ";
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else
		{
			$last_ssn = $ssn;
			unset($ssn);
		}
		//commit
		ibase_commit($dbTran);
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
						}
					}
				}
			}
		}
	}
	/* 
	if ($my_responsemessage == "Processed successfully ")
	if answer yesreturn and site does credit line on return
		insert a line in the order for the saved pick_order (line) status DX
			for the issn (last_ssn)
		insert a line in pick_item_detail for the issn in the new line
	*/
	
	$wk_trbk_message = trim($wk_trbk_message);
	$wk_trol_message = trim($wk_trol_message);
	$wk_tril_message = trim($wk_tril_message);
	if ($wk_trbk_message <> "") 
	{
		if ($wk_trbk_message <> "Processed successfully") {
			echo ("<b><font color=RED>TRBK:$wk_trbk_message</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			}
		} else {
			echo ("<b><font color=GREEN>TRBK OK</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			}
		}
	}
	if ($wk_trol_message <> "") 
	{
		if ($wk_trol_message <> "Processed successfully") {
			echo ("<b><font color=RED>TROL:$wk_trol_message</font></b>\n");
		} else {
			echo ("<b><font color=GREEN>TROL OK</font></b>\n");
		}
	}
	if ($wk_tril_message <> "") 
	{
		if ($wk_tril_message <> "Processed successfully") {
			echo ("<b><font color=RED>TRIL:$wk_tril_message</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
			}
		} else {
			echo ("<b><font color=GREEN>TRIL OK</font></b>\n");
			if ($wkMyBW == "IE60")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			} elseif ($wkMyBW == "IE65")
			{
				echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
			}
		}
	}
	if (isset($ssn))
	{
		if (isset($move_question))
		{
  			echo("<h4 ALIGN=\"LEFT\">Choose Response</h4>");
		}
		else
		{
  			echo("<h4 ALIGN=\"LEFT\">Enter To Location</h4>");
		}
	}
	else
	{
  		echo("<h4 ALIGN=\"LEFT\">Enter SSN</h4>");
	}

?>

 <form action="Get1SSN.php" method="post" name=getssn>
 <P>
<?php
echo("<input type=\"text\" name=\"message\" readonly size=\"30\" ><br>");
echo("SSN:      <input type=\"text\" name=\"ssnfrom\"");
if (isset($ssn)) 
{
	echo(" value=\"".$ssn."\"");
}
echo(" size=\"20\"");
//echo(" maxlength=\"20\"><BR>\n");
echo(" maxlength=\"24\" onchange=\"document.getssn.submit()\"><br>\n");
if (isset($ssn))
{
	if (!isset($move_question))
	{
		echo("Current Qty <input type=\"text\" name=\"currentqty\"");
		if (isset($currentqty)) 
		{
			echo(" value=\"".$currentqty."\"");
		}
		echo(" size=\"4\"");
		echo(" readonly><br>\n");
		echo("Location <input type=\"text\" name=\"currentwh\"");
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
		echo("Status <input type=\"text\" name=\"currentstatus\"");
		if (isset($currentstatus)) 
		{
			echo(" value=\"".$currentstatus."\"");
		}
		echo(" size=\"2\"");
		echo(" readonly><br>\n");
		echo("Product <input type=\"text\" name=\"currentprod\"");
		if (isset($currentprod)) 
		{
			echo(" value=\"".$currentprod."\"");
		}
		echo(" size=\"30\"");
		echo(" readonly><br>\n");
	}
	echo("To Location: <input type=\"text\" name=\"tolocn\"");
	if (isset($tolocn)) 
	{
		echo(" value=\"".$tolocn."\"");
	}
	echo(" size=\"10\"");
	//echo(" maxlength=\"12\" onchange=\"return processEdit()\"><BR>\n");
	//echo(" maxlength=\"12\" onchange=\"return processEdit(document.getssn.tolocn.value)\"><BR>\n");
	echo(" maxlength=\"13\" ><br>\n");
	if (isset($move_question))
	{
		echo("<p>" . $move_question . "</p>\n");
		echo("<p>");
		// check/get that move question 
		$Query = "select  inspect_pass_criteria, valid_response from valid_responses where question_id = '" . $move_question_id . "'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query SYS MOVES!<br>\n");
			exit();
		}
		while (($Row = ibase_fetch_row($Result))) {
		 	echo ("<input type=\"checkbox\" name=\"moveanswer[]\" value=\"" . $Row[0] . "\">" . $Row[1] . "<br>");
		}
		echo("</p>");
	}
}

{
	// html 4.0 browser
 	echo("<table border=\"0\" align=\"left\">");
	if (isset($ssn))
	{
		whm2buttons('Transfer', 'Transfer_Menu.php',"N","Back_50x100.gif","Back","adjustssn.gif","N");
	}
	else
	{
		whm2buttons('Accept', 'Transfer_Menu.php',"N","Back_50x100.gif","Back","accept.gif","N");
	}
/*
	echo("<TD>");
	echo("<FORM action=\"../login/logout.php\" method=\"post\" name=getout>\n");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/LogOut_50x100.gif" alt="Logout">');
	echo("</FORM>");
	echo("</TD>");
*/
	echo("</tr>");
	echo("</table>");
}
if (isset($Result))
{
	ibase_free_result($Result); 
}
//commit
ibase_commit($dbTran);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
?>
</P>
<script type="text/javascript">
<?php
	if (isset($ssn))
	{
		if (isset($move_question))
		{
			echo("document.getssn.moveanswer.focus();");
		}
		else
		{
			echo("document.getssn.tolocn.focus();");
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
