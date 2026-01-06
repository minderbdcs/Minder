<?php
include "../login.inc";
if (isset($_POST['doaulo']))
{
	$doaulo='Y';
}
if (isset($_GET['doaulo']))
{
	$doaulo='Y';
}
// Set the variables for the database access:
require_once('DB.php');
require('db_access.php');

if (isset($doaulo))
{
	{
		// 1st time in screen
		// save original fields
		$cookiedata = "";
		if (isset($_POST['reference'])) 
		{
			$cookiedata .= $_POST['reference'];
			$reference = $_POST['reference'];
		}
		if (isset($_GET['reference'])) 
		{
			$cookiedata .= $_GET['reference'];
			$reference = $_GET['reference'];
		}
		$cookiedata .= '|';
		if (isset($_POST['location'])) 
		{
			$cookiedata .= $_POST['location'];
			$location = $_POST['location'];
		}
		if (isset($_GET['location'])) 
		{
			$cookiedata .= $_GET['location'];
			$location = $_GET['location'];
		}
		$cookiedata .= '|';
		//setcookie("BDCSData","$cookiedata", time()+86400, "/");
		$other_fields = "reference=".$reference."&location=".$location;
	}
	
	$tran_tranclass = "A";
	$tran_qty = 1;
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	//$message = "";
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	{
		$tran_tranid = "AULO";
		/* write transaction */
		$Query = "EXECUTE PROCEDURE ADD_TRAN('";
		if (isset($_POST['location'])) 
		{
			$Query .= substr($_POST['location'],0,2)."','";
			$Query .= substr($_POST['location'],2,strlen($_POST['location']) - 2)."','";
		}
		if (isset($_GET['location'])) 
		{
			$Query .= substr($_GET['location'],0,2)."','";
			$Query .= substr($_GET['location'],2,strlen($_GET['location']) - 2)."','";
		}
		$Query .= "','";
		$Query .= $tran_tranid."','";
		$Query .= $tran_tranclass."','";
		$tran_trandate = date("Y-M-d H:i:s");
		$Query .= $tran_trandate."','";
		if (isset($_POST['reference'])) 
		{
			$Query .= $_POST['reference']."',";
		}
		if (isset($_GET['reference'])) 
		{
			$Query .= $_GET['reference']."',";
		}
		$Query .= $tran_qty.",'F','','MASTER',0,'','SSSSSSSSS','";
		$Query .= $tran_user."','";
		$Query .= $tran_device."')";
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Add Transaction!<BR>\n");
			exit();
		}
		/* ibase_free_result($Result); */
		unset($Result); 
		/* must get the record id just created */
		$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
		if (isset($_POST['location'])) 
		{
			$Query .= substr($_POST['location'],0,2)."' AND LOCN_ID = '";
			$Query .= substr($_POST['location'],2,strlen($_POST['location']) - 2)."' AND OBJECT = '";
		}
		if (isset($_GET['location'])) 
		{
			$Query .= substr($_GET['location'],0,2)."' AND LOCN_ID = '";
			$Query .= substr($_GET['location'],2,strlen($_GET['location']) - 2);
		}
		$Query .= $tran_trandate."' AND TRN_CODE = '";
		$Query .= $tran_tranclass."' AND DEVICE_ID = '";
		$Query .= $tran_device."' AND COMPLETE = 'F'";
		/* echo($Query); */
		$tran_recordid = NULL;
		if (!($Result = ibase_query($Link, $Query)))
		{
	/*
			 echo("Unable to Read Transaction!<BR>\n"); 
			 exit(); 
	*/
			 //transaction is archived - is ok 
		}
		else
		if (($row = ibase_fetch_row($Result)))
		{
			$tran_recordid =  $row[0];
			ibase_free_result($Result);
			unset($Result); 
		}
	
		/* echo("got record id ".$tran_recordid); */
		/* process procedure */
		if (isset($tran_recordid))
		{
			/* must get the record id just updated */
			$Query = "SELECT error_text, complete RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
			/* echo($Query); */
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query table!<BR>\n");
				exit();
			}
			$tran_error = NULL;
			$tran_complete = NULL;
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_error =  $row[0];
				$tran_complete =  $row[1];
			}
			ibase_free_result($Result);
			unset($Result); 
			if (isset($tran_complete))
			{
				echo($tran_error);
				//$message .= $tran_error;
				if ($tran_complete == "F")
				{
					$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
					if (!($Result = ibase_query($Link, $Query)))
					{
						echo("Unable to Update Transaction!<BR>\n");
						//$message .= ": Unable to Update Transaction";
					}
					/* ibase_free_result($Result); */
					unset($Result);
				}
			}
			else
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to Update Transaction!<BR>\n");
					//$message .= "Unable to Update Transaction";
				}
				/* ibase_free_result($Result); */
				unset($Result);
			}
		}
		else
		{
			echo("Processed OK\n");
			//$message = "Processed OK";
		}
	}
	
	//release memory
	if (isset($Result)) 
	{
		ibase_free_result($Result);
	}
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
	
}

if (isset($_POST['doauob']))
{
	$doauob='Y';
}
if (isset($_GET['doauob']))
{
	$doauob='Y';
}
if (isset($doauob))
{

	{
		// 1st time in screen
		// save original fields
		$cookiedata = "";
		if (isset($_POST['reference'])) 
		{
			$cookiedata .= $_POST['reference'];
			$reference = $_POST['reference'];
		}
		if (isset($_GET['reference'])) 
		{
			$cookiedata .= $_GET['reference'];
			$reference = $_GET['reference'];
		}
		$cookiedata .= '|';
		if (isset($_POST['location'])) 
		{
			$cookiedata .= $_POST['location'];
			$location = $_POST['location'];
		}
		if (isset($_GET['location'])) 
		{
			$cookiedata .= $_GET['location'];
			$location = $_GET['location'];
		}
		$cookiedata .= '|';
		if (isset($_POST['ssn'])) 
		{
			$cookiedata .= $_POST['ssn'];
			$ssn = $_POST['ssn'];
		}
		if (isset($_GET['ssn'])) 
		{
			$cookiedata .= $_GET['ssn'];
			$ssn = $_GET['ssn'];
		}
		//setcookie("BDCSData","$cookiedata", time()+86400, "/");
		$other_fields = "reference=".$reference."&location=".$location."&ssn=".$ssn;
	}
	
	$tran_tranclass = "A";
	$tran_qty = 1;
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	//$message = "";
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		//header("Location: GetSTKSSN.php?message=Can+t+connect+to+DATABASE!&".$other_fields);
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	{
		$tran_tranid = "AUOB";
		/* write transaction */
		$Query = "EXECUTE PROCEDURE ADD_TRAN('";
		if (isset($_POST['location'])) 
		{
			$Query .= substr($_POST['location'],0,2)."','";
			$Query .= substr($_POST['location'],2,strlen($_POST['location']) - 2)."','";
		}
		if (isset($_GET['location'])) 
		{
			$Query .= substr($_GET['location'],0,2)."','";
			$Query .= substr($_GET['location'],2,strlen($_GET['location']) - 2)."','";
		}
		if (isset($_POST['ssn'])) 
		{
			$Query .= $_POST['ssn']."','";
		}
		if (isset($_GET['ssn'])) 
		{
			$Query .= $_GET['ssn']."','";
		}
		$Query .= $tran_tranid."','";
		$Query .= $tran_tranclass."','";
		$tran_trandate = date("Y-M-d H:i:s");
		$Query .= $tran_trandate."','";
		if (isset($_POST['reference'])) 
		{
			$Query .= $_POST['reference']."',";
		}
		if (isset($_GET['reference'])) 
		{
			$Query .= $_GET['reference']."',";
		}
		$Query .= $tran_qty.",'F','','MASTER',0,'','SSSSSSSSS','";
		$Query .= $tran_user."','";
		$Query .= $tran_device."')";
	
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Add Transaction!<BR>\n");
			//header("Location: GetSTKSSN.php?message=Unable+to+Add+Transaction!&".$other_fields);
			exit();
		}
		/* ibase_free_result($Result); */
		unset($Result); 
		/* must get the record id just created */
		$Query = "SELECT RECORD_ID FROM TRANSACTIONS WHERE WH_ID ='";
		if (isset($_POST['location'])) 
		{
			$Query .= substr($_POST['location'],0,2)."' AND LOCN_ID = '";
			$Query .= substr($_POST['location'],2,strlen($_POST['location']) - 2)."' AND OBJECT = '";
		}
		if (isset($_GET['location'])) 
		{
			$Query .= substr($_GET['location'],0,2)."' AND LOCN_ID = '";
			$Query .= substr($_GET['location'],2,strlen($_GET['location']) - 2)."' AND OBJECT = '";
		}
		if (isset($_POST['ssn'])) 
		{
			$Query .= $_POST['ssn']."' AND TRN_DATE = '";
		}
		if (isset($_GET['ssn'])) 
		{
			$Query .= $_GET['ssn']."' AND TRN_DATE = '";
		}
		$Query .= $tran_trandate."' AND TRN_CODE = '";
		$Query .= $tran_tranclass."' AND DEVICE_ID = '";
		$Query .= $tran_device."' AND COMPLETE = 'F'";
		/* echo($Query); */
		$tran_recordid = NULL;
		if (!($Result = ibase_query($Link, $Query)))
		{
	/*
			 echo("Unable to Read Transaction!<BR>\n"); 
			 exit(); 
	*/
			 //transaction is archived - is ok 
		}
		else
		{
			if (isset($Result))
			{
				if (($row = ibase_fetch_row($Result)))
				{
					$tran_recordid =  $row[0];
					ibase_free_result($Result);
				}
			}
			unset($Result); 
		}
	
		/* echo("got record id ".$tran_recordid); */
		/* process procedure */
		if (isset($tran_recordid))
		{
			/* must get the record id just updated */
			$Query = "SELECT error_text, complete RECORD_ID FROM TRANSACTIONS WHERE RECORD_ID = ".$tran_recordid;
			/* echo($Query); */
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query table!<BR>\n");
				//header("Location: GetSTKSSN.php?message=Unable+to+Query+Transaction!&".$other_fields);
				exit();
			}
			$tran_error = NULL;
			$tran_complete = NULL;
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_error =  $row[0];
				$tran_complete =  $row[1];
			}
			ibase_free_result($Result);
			unset($Result); 
			if (isset($tran_complete))
			{
				echo($tran_error);
				//$message .= $tran_error;
				if ($tran_complete == "F")
				{
					$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
					if (!($Result = ibase_query($Link, $Query)))
					{
						echo("Unable to Update Transaction!<BR>\n");
						//$message .= ": Unable to Update Transaction";
					}
					/* ibase_free_result($Result); */
					unset($Result);
				}
			}
			else
			{
				$Query = "UPDATE TRANSACTIONS SET complete = 'T' WHERE RECORD_ID = ".$tran_recordid;
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to Update Transaction!<BR>\n");
					//$message .= "Unable to Update Transaction";
				}
				/* ibase_free_result($Result); */
				unset($Result);
			}
		}
		else
		{
			echo("Processed OK\n");
			//$message = "Processed OK";
		}
	}
	
	//release memory
	if (isset($Result)) 
	{
		ibase_free_result($Result);
	}
	
	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
	
}
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
	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	include "2buttons.php";
?>
  <h4 ALIGN="LEFT">Enter SSN</h4>

 <TABLE BORDER="0" ALIGN="LEFT">
 <FORM action="GetSTKSSN.php" method="post" name=getssn>
 <P>
<?php
echo("<INPUT type=\"hidden\" name=\"reference\"");
if (isset($_POST['reference'])) 
{
	echo(" value=\"".$_POST['reference']."\"");
}
if (isset($_GET['reference'])) 
{
	echo(" value=\"".$_GET['reference']."\"");
}
echo(">");
echo("<INPUT type=\"hidden\" name=\"doauob\" value=\"Y\">");
echo("Location: <INPUT type=\"text\" readonly name=\"location\"");
if (isset($_POST['location'])) 
{
	echo(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	echo(" value=\"".$_GET['location']."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\"><BR>\n");
echo("Last SSN: <INPUT type=\"text\" readonly name=\"lastssn\"");
if (isset($_POST['ssn'])) 
{
	echo(" value=\"".$_POST['ssn']."\"");
}
if (isset($_GET['ssn'])) 
{
	echo(" value=\"".$_GET['ssn']."\"");
}
echo(" size=\"20\"");
echo(" maxlength=\"20\"><BR>\n");
echo("SSN: <INPUT type=\"text\" name=\"ssn\"");
/*
if (isset($_POST['ssn'])) 
{
	echo(" value=\"".$_POST['ssn']."\"");
}
if (isset($_GET['ssn'])) 
{
	echo(" value=\"".$_GET['ssn']."\"");
}
*/
echo(" size=\"20\"");
echo(" maxlength=\"20\"");
echo(" onchange=\"document.getssn.submit();\"><BR>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"GetSTKLocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	whm2buttons('Send', 'GetSTKLocn.php',"Y","Back_50x100.gif","Back","accept.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='GetSTKLocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</P>
<script type="text/javascript">
document.getssn.ssn.focus();
</script>
</body>
</html>
