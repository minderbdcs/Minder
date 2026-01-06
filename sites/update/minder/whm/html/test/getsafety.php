<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Location you have tested</title>
<?php
include "viewport.php";
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	include "checkdatajs.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: test_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// create js for location check
	whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit2() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.safetylocn.value); 
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
</script>

<?php

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

		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	if (isset($_POST['safetylocn'])) 
	{
		$safetylocn = $_POST['safetylocn'];
	}
	if (isset($_GET['safetylocn'])) 
	{
		$safetylocn = $_GET['safetylocn'];
	}
	if (isset($_POST['safetylocn2'])) 
	{
		$safetylocn2 = $_POST['safetylocn2'];
	}
	if (isset($_GET['safetylocn2'])) 
	{
		$safetylocn2 = $_GET['safetylocn2'];
	}

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
	$wk_docommit = "F";
	if (isset($safetylocn))
	{
		if (isset($reason))
		{
			$Query = "select ssn.ssn_id  ";
			$Query .= "from ssn ";
			$Query .= "where  ";
			$Query .= " ssn.wh_id = '" ; 
			$Query .= substr($safetylocn,0,2)."' AND ssn.locn_id = '";
			$Query .= substr($safetylocn,2,strlen($safetylocn) - 2) . "' ";
			$Query .= " and ssn.loan_safety_check = 'T' ";
			$Query .= "and (ssn.status_ssn < 'X' or ssn.status_ssn > 'X~') ";
			$Query .= "and (ssn.loan_last_safety_check_date is null or diffdate(ssn.loan_last_safety_check_date, 'TODAY', 4) > 30) ";
			//echo ($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to query ssn!<BR>\n");
				exit();
			}
			
			$ssns = array();
			// Fetch the results from the database.
			while (($Row = ibase_fetch_row($Result)) )
			{
				$ssns[] = $Row[0];
			}
		
			//print_r($ssns);
			//release memory
			//$Result->free();
			ibase_free_result($Result);
			foreach ($ssns as $ssn)
			{
				// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = 0;
	$location = $safetylocn;
	$my_object = $ssn;
	$my_sublocn = "";
	$my_ref = date("Y/m/d H:i:s") ;
	$my_ref .= '||' ;
	if (isset($reason))
	{
		$my_ref .= $reason . '|';
	}
	if (strlen($my_ref) > 40)
	{
		$my_ref = substr($my_ref,0,40);
	}

	$wk_docommit = "T";
	$my_message = "";
	$my_message = dotransaction("NITS", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$message .= $ssn . " " . urldecode($my_mess_label) . " ";
	}


			}
			unset($safetylocn);
		}
	}
	if (isset($message))
	{
		if ($message <> "")
		{
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
	}
  	
  	echo("<H3 ALIGN=\"LEFT\">Safety Test by Location</H3>");
	if (!isset($safetylocn))
	{
  		echo("<H4 ALIGN=\"LEFT\">Enter Location</H4>");
	}

?>

 <form action="getsafety.php" method="post" name=getlocn ONSUBMIT="return processEdit2();">
 <P>
<?php
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"40\" ><br>");
echo("Location: <INPUT type=\"text\" name=\"safetylocn\"");
if (isset($safetylocn)) 
{
	echo(" value=\"".$safetylocn."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\" onchange=\"document.getlocn.submit()\"><BR>\n");
if (isset($safetylocn))
{
		//want ssns due for test
		$Query = "select ssn.ssn_id, ssn.status_ssn, diffdate(ssn.loan_last_safety_check_date, 'TODAY', 4)  ";
		$Query .= "from ssn ";
		$Query .= "left outer join location on location.wh_id = ssn.wh_id and location.locn_id = ssn.locn_id ";
		$Query .= "where  ";
		$Query .= " ssn.wh_id = '" ; 
		$Query .= substr($safetylocn,0,2)."' AND ssn.locn_id = '";
		$Query .= substr($safetylocn,2,strlen($safetylocn) - 2) . "' ";
		$Query .= " and ssn.loan_safety_check = 'T' ";
		$Query .= "and (ssn.status_ssn < 'X' or ssn.status_ssn > 'X~') ";
		$Query .= "and (ssn.loan_last_safety_check_date is null or diffdate(ssn.loan_last_safety_check_date, 'TODAY', 4) > 30) ";
		$Query .= "order by location.locn_name, ssn.status_ssn ";
		
		// Create a table.
		echo ("<TABLE BORDER=\"1\">\n");
		
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query ssn!<BR>\n");
			exit();
		}
		// echo headers
		echo ("<TR>\n");
		echo("<TH>SSN</TH>\n");
		echo("<TH>Status</TH>\n");
		echo("<TH>Days Since Last Test</TH>\n");
		echo ("</TR>\n");
		
		// Fetch the results from the database.
		while (($Row = ibase_fetch_row($Result)) )
		{
		 	echo ("<TR>\n");
			for ($i=0; $i<=2; $i++)
			{
		 		echo ("<TD>$Row[$i]</TD>\n");
			}
		 	echo ("</TR>\n");
		}
		echo ("</TABLE>\n");
		
		//release memory
		//$Result->free();
		ibase_free_result($Result);
	echo("Reason: <INPUT type=\"text\" name=\"reason\"");
	if (isset($reason)) 
	{
		echo(" value=\"".$reason."\"");
	}
	echo(" size=\"40\"");
	echo(" maxlength=\"40\" ><br>\n");

}

{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if (isset($safetylocn))
	{
		whm2buttons('Update', 'test_menu.php',"Y","Back_50x100.gif","Back","TEST_50x100.gif");
	}
	else
	{
		whm2buttons('Accept', 'test_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
if ($wk_docommit == "T")
{
	//commit
	ibase_commit($dbTran);
}
?>
</P>
<script type="text/javascript">
<?php
	if (!isset($safetylocn))
	{
		echo("document.getlocn.safetylocn.focus();");
	}
?>
</script>
</body>
</html>
