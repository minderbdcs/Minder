<?php
include "../login.inc";
?>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");
include("logme.php");
?>
<html>
 <head>
  <title>Get Next Product to Replenish</title>
<link rel=stylesheet type="text/css" href="nextprod.css">
<?php
include "viewport.php";
?>
 </head>
 <body>
  <h2>Replenish Get Next Product</h2>
<?php
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$wk_doit = "";
if (isset($_POST['doit']))
{
	$wk_doit = $_POST['doit'];
}
if (isset($_GET['doit']))
{
	$wk_doit = $_GET['doit'];
}

// my screen id
$wk_replen_screen = 'RP03';

$current_despatchZone = getBDCScookie($Link, $tran_device, "CURRENT_DESPATCH_ZONE"  );

echo("<FONT size=\"2\">\n");

$Query = "select distinct prod_id "; 
	$Query .= "from transfer_request ";
	$Query .= "where trn_status in ('AL','PG','PL') ";
	$Query .= "and device_id = '$tran_device' ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Transfer Request!<BR>\n");
	exit();
}

$wk_products = "";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_products .= $Row[0] . " ";
}
//release memory
ibase_free_result($Result);

if ($wk_products <> "")
{
	echo ("<B><FONT COLOR=RED>You Already Have Product </FONT>");
	echo ("<FONT COLOR=BLUE>$wk_products</FONT></B><BR>\n");
}

$wk_company = "";
$Query = "select description "; 
	$Query .= "from options ";
	$Query .= "where group_code = 'CMPREPLEN' ";
	$Query .= "and code = '$wk_replen_screen' ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_company = $Row[0] ;
}
//release memory
ibase_free_result($Result);

$Query = "select zone.default_device_id  "; 

$current_despatchZone = getBDCScookie($Link, $tran_device, "CURRENT_DESPATCH_ZONE"  );
/*
want either the 1st of the top priority
or
the next (alloc_qty) lines
*/
{
	/* create an array with 
	priority code
	its description
	its start priority
	its end priority
	*/
	$Query1 = "SELECT code, description from options where group_code='REPLENPRIS' ";
	//echo($Query1);
	$wk_prioritys = Array();
	if (!($Result1 = ibase_query($Link, $Query1)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	while ( ($Row1 = ibase_fetch_row($Result1)) ) 
	{
		$wk_code = $Row1[0];
		$wk_desc = $Row1[1];
		$wk_start = 0;
		$wk_end = 9999;
		$wk_alloc = 1;
		$Query5 = "SELECT code, description from options where group_code='REPLENPRI' and code in ( '" . $wk_code . "|START','" . $wk_code . "|END','" . $wk_code . "|ALLOC') ";
		if (!($Result5 = ibase_query($Link, $Query5)))
		{
			echo("Unable to Read Options2!<BR>\n");
			//exit();
		}
		while ( ($Row5 = ibase_fetch_row($Result5)) ) 
		{
			if ($Row5[0] == ($wk_code . "|START"))
			{
				$wk_start = $Row5[1];
			}
			elseif ($Row5[0] == ($wk_code . "|END"))
			{
				$wk_end = $Row5[1];
			}
			else
			{
				$wk_alloc = $Row5[1];
			}
		}
		//release memory
		ibase_free_result($Result5);
		if (array_key_exists($wk_code, $wk_prioritys))
		{
			$wk_priority = $wk_prioritys[$wk_code];
			$wk_priority[1] = $wk_desc;
			$wk_priority[2] = $wk_start;
			$wk_priority[3] = $wk_end;
			$wk_priority[4] = $wk_alloc;
			$wk_prioritys[$wk_code] = $wk_priority;
		}
		else
		{
			$wk_priority = array();
			$wk_priority[1] = $wk_desc;
			$wk_priority[2] = $wk_start;
			$wk_priority[3] = $wk_end;
			$wk_priority[4] = $wk_alloc;
			$wk_prioritys[$wk_code] = $wk_priority;
		}
	}
	//release memory
	ibase_free_result($Result1);
}

$Query = "SELECT FIRST 1 P1.TRN_LINE_NO, P1.PROD_ID, P1.TRN_PRIORITY
FROM TRANSFER_REQUEST P1
JOIN PROD_PROFILE P2 ON P2.PROD_ID = P1.PROD_ID
AND P2.COMPANY_ID = P1.COMPANY_ID  
WHERE  P1.DEVICE_ID IS NULL 
AND  P1.TRN_STATUS IN ('OP')
AND  COALESCE(P1.ZONE_C, '" . $current_despatchZone . "') = '" . $current_despatchZone  . "'
ORDER BY P1.TRN_PRIORITY"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Requests!<BR>\n");
	exit();
}

$wk_prod = "";
$wk_priority1 = $Row[2];
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_tr_id = $Row[0];
	$wk_prod = $Row[1];
	$wk_priority1 = $Row[2];
}
//release memory
ibase_free_result($Result);

{
	/* must select the priority from the table */
	$wk_mypri_code = "";
	$wk_mypri_desc = "";
	$wk_mypri_start = 0;
	$wk_mypri_end = 9999;
	$wk_mypri_alloc = 1;

	foreach ($wk_prioritys as $Key_results => $Value_results) 
	{
		$wk_this_code = $Key_results;
		$wk_this_desc = $Value_results[1];
		$wk_this_start = $Value_results[2];
		$wk_this_end = $Value_results[3];
		$wk_this_alloc = $Value_results[4];
		if (($wk_priority1 >= $wk_this_start) and ($wk_priority1 <= $wk_this_end))
		{
			$wk_mypri_code = $wk_this_code;
			$wk_mypri_desc = $wk_this_desc;
			$wk_mypri_start = $wk_this_start;
			$wk_mypri_end = $wk_this_end;
			$wk_mypri_alloc = $wk_this_alloc;
		}
	}
	$wk_priority_head = $wk_mypri_desc;
}
$Query = "SELECT FIRST " . $wk_mypri_alloc . " P1.TRN_PRIORITY, P1.PROD_ID ,P1.TO_WH_ID, COALESCE(P1.ZONE_C,'" . $current_despatchZone  . "') 
FROM TRANSFER_REQUEST P1
JOIN PROD_PROFILE P2 ON P2.PROD_ID = P1.PROD_ID
AND P2.COMPANY_ID = P2.COMPANY_ID  
WHERE  P1.DEVICE_ID IS NULL 
AND  P1.TRN_STATUS IN ('OP')
AND  COALESCE(P1.ZONE_C, '" . $current_despatchZone . "') = '" . $current_despatchZone  . "'
ORDER BY P1.TRN_PRIORITY"; 

//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Requests!<BR>\n");
}

echo ("<TABLE BORDER=\"1\">\n");
$wk_have_header = "N";
$wk_row_cnt = 0;
while ( ($Row = ibase_fetch_row($Result)) ) 
{
	if ($wk_have_header == "N")
	{
		// do the header stuff
		echo ("<TR><TH colspan=\"5\">Waiting Products - $wk_priority_head Priority</TH></TR>\n");
		echo ("<TR><TH>#</TH>\n");
		//echo ("<TH>Priority</TH>\n");
		echo ("<TH>Products</TH>\n");
		echo ("<TH>TO Zone</TH></TR>\n");
		$wk_have_header = "Y";
	}
	$wk_row_cnt = $wk_row_cnt + 1;
	echo ("<TR><TD>$wk_row_cnt</TD>\n");
	//echo ("<TD>$Row[0]</TD>\n");
	echo ("<TD>$Row[1]</TD>\n");
	echo ("<TD>$Row[2]-");
	echo ("$Row[3]</TD></TR>\n");
}
echo ("</TABLE>\n");
//echo "Next Prod [" . $wk_prod . "]" . "Req_ID [" . $wk_tr_id . "]";


if ($wk_doit <> "" and $wk_products == "")
{
	// want to use the zone for the user and device
	// if there is more than one
	$transaction_type = "RTAL";
	//$my_object = $wk_prod;
	$my_object = $wk_company;
	$my_object = $current_despatchZone ;
	$my_source = 'SSSSSSSSS';
	//$tran_tranclass = "P";
	//$tran_tranclass = "Q";
	$tran_tranclass = "Z";
	//$tran_qty = $wk_tr_id;
	//$tran_qty = 0;
	$tran_qty = $wk_mypri_alloc;
	$my_sublocn = $tran_device;
	$location = "          ";
	$my_ref = "Allocate Trans Req of prod to handheld" ;

	$my_message = "";
	$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = " ";
	}
	if (($my_responsemessage == " ") or
	    ($my_responsemessage == ""))
	{
		$my_responsemessage = "Processed successfully ";
	}

	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=all>\n");
}
else
{
	if ($wk_products <> "")
	{
		echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=all>\n");
	}
	else
	{
		// no accept yet
		echo("<FORM action=\"" .  basename($_SERVER["PHP_SELF"]) . "\" method=\"post\" name=all>\n");
	}
	if (isset($wk_message))
	{
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
	}
	echo("<INPUT type=\"hidden\" name=\"doit\" value=\"T\">");
}

echo ("<div id=\"col4\">\n");
//if ($got_orders == 1)
if ($wk_doit == "")
{
	echo ("<TABLE>\n");
	echo ("<TR>\n");
	echo("<TH>Select 'Accept' or 'Back' to Exit...</TH>\n");
	echo ("</TR>\n");
	echo ("</TABLE>\n");
}
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
whm2buttons('Accept', 'replenish_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
echo ("</TABLE>");
echo ("</div>\n");
/*
<script type="text/javascript">
document.forms.all.submit();
</script>
*/
if ($wk_doit <> "")
{
	echo ("<script type=\"text/javascript\">\n");
	echo "document.forms.all.submit();\n";
	echo "</script>\n";
}
?>
</body>
</html>
