<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Putaway get SSN From</title>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<?php
//phpinfo();
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="getfromlocn.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="getfromlocn.css">');
}
?>
</head>
<body>

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
	
$message  = '';
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
$from_location = '';
if (isset($_POST['location']))
{
	$from_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$from_location = $_GET['location'];
}
if (isset($_POST['grn']))
{
	list($from_ssn, $from_location) = explode("|", $_POST['grn'] . "|");
	$seen_product = "Y";
}
if (isset($_GET['grn']))
{
	list($from_ssn, $from_location) = explode("|", $_GET['grn'] . "|");
	$seen_product = "Y";
}
if (isset($_POST['donetest']))
{
	$wk_donetest = "Y";
}
if (isset($_GET['donetest']))
{
	$wk_donetest = "Y";
}
include "checkdata.php";
if (isset($from_location))
{
	{
		// not a location
		$wk_my_message = $message;
		$field_type = checkForTypein($from_location , 'LOCATION' ); 
		if ($field_type == "none")
		{
			$wk_my_message .= "Not a Location";
			$message = $wk_my_message;
			//$message .= "Not a Location";
			header("Location: getfromlocn.php?message=" . urlencode($message) );
		}
		else {
			if ($startposn > 0){
				$wk_data = substr($from_location, $startposn);
				$from_location = $wk_data;
			}
		}
	}
}
if (isset($from_ssn))
{
	$Query = "select issn_status from issn where ssn_id = '" . $from_ssn . "' "; 
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}

	if ( ($Row = ibase_fetch_row($Result)) ) {
		$ssn_status = $Row[0];
	}
	else
	{
		$ssn_status = "";
	}

	//release memory
	ibase_free_result($Result);

	if ((!isset($wk_donetest)) && ($ssn_status == "TS"))
	{
		header("Location: ../test/GetType.php?ssn_id=". urlencode($from_ssn) . "&grn=".urlencode($from_ssn . ",". $from_location . "," . "../putaway.prod2/getfromssn.php"));
		exit();
	}
	elseif ($ssn_status == "PA")
	{
		header("Location: transactionOL.php?ssn=". urlencode($from_ssn) . "&from_location=".urlencode($from_location) . "&seenprod=Y");
		exit();
	}
	else
	{
		if (isset($wk_donetest))
		{
			$message .= "Failed Test";
		}
	}
}

//include "checkdatajs.php";
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
//whm2scanvars($Link, 'ssn','BARCODE', 'SSN');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
  {
	return true;
  }
}
</script>

<?php
echo ("<div id=\"locns5\">\n");
//if (isset($message))
if ($message !== "")
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
include"2buttons.php";
$device_wh = '';
$Query = "select wh_id "; 
$Query .= "from location "; 
$Query .= "where locn_id = '".$tran_device."'";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read location!<BR>\n");
	exit();
}

if ( ($Row = ibase_fetch_row($Result)) ) {
	$device_wh = $Row[0];
}

//release memory
ibase_free_result($Result);

$tot_locns = 0;

$Query = "SELECT issn.wh_id, issn.locn_id, issn.ssn_id, pp.home_locn_id, issn.prod_id, issn.current_qty, pp.short_desc, issn.original_ssn ,pp2.home_locn_id, pp2.short_desc, cy.home_locn_id, pp.prod_id "; 
$Query .= "FROM issn  ";
$Query .= "LEFT OUTER JOIN prod_profile pp ON issn.prod_id = pp.prod_id  ";
$Query .= "AND  issn.company_id = pp.company_id  ";
$Query .= "LEFT OUTER JOIN prod_profile pp2 ON issn.prod_id = pp2.prod_id  ";
$Query .= "AND  'ALL' = pp.company_id  ";
$Query .= "LEFT OUTER JOIN company cy ON issn.company_id = cy.company_id  ";
$Query .= " WHERE issn.issn_status in ('PA','TS') " ;
$Query .= " AND issn.current_qty > 0";
$Query .= " AND issn.wh_id IN ('";
$Query .= substr($from_location,0,2);
if ($device_wh <> substr($from_location,0,2))
{
	$Query .= "','" . $device_wh;
}
$Query .= "')";
$Query .= " AND issn.locn_id IN ('";
$Query .= substr($from_location,2,strlen($from_location) - 2)."','";
$Query .= $tran_device."')";
//$Query .= " ORDER BY issn.ssn_id" ;
$Query .= " ORDER BY issn.prod_id, issn.original_ssn, issn.ssn_id" ;

$Query2 = "SELECT st.putaway_location, st.description, sn.home_locn_id "; 
$Query2 .= "FROM ssn sn  ";
$Query2 .= "LEFT OUTER JOIN ssn_type st ON sn.ssn_type = st.code  ";
$Query2 .= " WHERE sn.ssn_id = '";

$wk_use_company_home = 'F';
$Query3 = "SELECT description "; 
$Query3 .= "FROM options  ";
$Query3 .= " WHERE group_code='PUTAWAY' and code='COMPANY.HOME_LOCN_ID' ";

if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Read Options!<BR>\n");
	exit();
}

if ( ($Row3 = ibase_fetch_row($Result3)) ) {
	$wk_use_company_home = $Row3[0];
}

//release memory
ibase_free_result($Result3);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read ssns!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_scanned = 0;
$tot_inlocn = 0;

echo("<FONT size=\"2\">\n");
echo("<form action=\"transactionOL.php\" method=\"post\" name=getssn ONSUBMIT=\"return processEdit();\">\n");
// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
echo("<th>SSN/Prod No</th>\n");
echo("<th>Selected</th>\n");
echo("<th>INTO/Description</th>\n");
echo ("</tr>\n");

$rcount = 0;
$wk_last_product = "ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ";
$wk_last_ssn = "ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZzz";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	// get the ssn details
	{
		if ($Row[4] == '') {
			// an ssn so get the ssn fields
			$Query1 = $Query2 . $Row[7] . "'";
			// use original ssn in ssn query
			if (!($Result2 = ibase_query($Link, $Query1)))
			{
				echo("Unable to Read ssns!<BR>\n");
				exit();
			}
			if ( !($Row2 = ibase_fetch_row($Result2)) ) {
				echo("Unable to Read ssns!<BR>\n");
				exit();
			}
		}
	}
	// if the product changes
	if ($Row[4] != $wk_last_product)
	{
		$wk_last_product = $Row[4];
		// now the prod stuff
		echo ("<tr>\n");
		echo("<td>".$Row[4]."</td>\n"); // prod_id
		echo("<td>".$Row[5]."</td>\n"); // current qty
		if ($Row[4] == '') {
			//ssn
			echo("<td colspan=\"4\">".$Row2[1]."</td>\n"); // ssn_type description
		}
		else
		{
			if ($Row[11] == '') {
				// product not found for the current company so use ALL
				echo("<td colspan=\"4\">".$Row[9]."</td>\n"); // short_desc
			} else {
				echo("<td colspan=\"4\">".$Row[6]."</td>\n"); // short_desc
			}
		}
		echo ("</tr>\n");
	}

	//if ($Row[7] != $wk_last_ssn)
	{
		$wk_last_ssn = $Row[7];
		$include_me = 'n';
		//$include_me = 'y';
		if (($Row[0] == substr($from_location,0,2)) and 
		(trim($Row[1]) == substr($from_location,2,strlen($from_location) - 2))) {
			echo ("<tr>\n");
			echo("<td>".$Row[2]."</td>\n");
			$tot_inlocn += 1;
			$include_me = 'y';
		}
		else
		{
			if (($Row[0] == $device_wh) and 
			(trim($Row[1]) == $tran_device)) {
				echo ("<tr>\n");
				echo("<td></td>\n");
				$include_me = 'y';
			}
		}
		if (($Row[0] == $device_wh) and 
		(trim($Row[1]) == $tran_device)) {
			echo("<td>".$Row[2]."</td>\n");
			$tot_scanned += 1;
		}
		else
		{
			if ($include_me == 'y') {
				echo("<td></td>\n");
			}
		}
		if ($include_me == 'y') {
			/*		
			if ($Row[4] == '') {
				// an ssn so get the ssn fields
				$Query1 = $Query2 . $Row[7] . "'";
				// use original ssn in ssn query
				if (!($Result2 = ibase_query($Link, $Query1)))
				{
					echo("Unable to Read ssns!<BR>\n");
					exit();
				}
				if ( !($Row2 = ibase_fetch_row($Result2)) ) {
					echo("Unable to Read ssns!<BR>\n");
					exit();
				}
			}
			*/
			if (($wk_use_company_home == 'T')  and ($Row[10] != '')) {
				echo("<td>".$Row[10]."</td>\n");
			} else { 
				if ($Row[4] == '') {
					//ssn
					if ($Row2[2] == '') {
						//no home_locn_id for ssn 
						//use putaway_location from ssn_type
						echo("<td>".$Row2[0]."</td>\n");
					}
					else
					{
						//use home_locn_id for ssn 
						echo("<td>".$Row2[2]."</td>\n");
					}
				}
				else
				{
					//prod
					//use home_locn_id for prod
					if ($Row[11] == '') {
						// product not found for the current company so use ALL
						echo("<td>".$Row[8]."</td>\n");
					} else {
						echo("<td>".$Row[3]."</td>\n");
					}
				}
			}
			//echo("<td>".$Row[4]."</td>\n");
			//echo("<td>".$Row[5]."</td>\n");
			$tot_locns += 1;
			echo ("</tr>\n");
			/*
			// now the prod stuff
			echo ("<tr>\n");
			echo("<td>".$Row[4]."</td>\n"); // prod_id
			echo("<td>".$Row[5]."</td>\n"); // current qty
			if ($Row[4] == '') {
				//ssn
				echo("<td colspan=\"4\">".$Row2[1]."</td>\n"); // ssn_type description
			}
			else
			{
				if ($Row[11] == '') {
					// product not found for the current company so use ALL
					echo("<td colspan=\"4\">".$Row[9]."</td>\n"); // short_desc
				} else {
					echo("<td colspan=\"4\">".$Row[6]."</td>\n"); // short_desc
				}
			}
			echo ("</tr>\n");
			*/
	
		}
	}
	if ($Row[4] == '') {
		// an ssn
		//release memory
		ibase_free_result($Result2);
	}
}

echo ("</table>\n");
echo ("</div>\n");
echo ("<div id=\"locns6\">\n");
echo ("<table>\n");
echo ("<tr>");
echo ("<td>");
echo("Location</td>");
echo("<td><INPUT type=\"text\" readonly name=\"wh\" size=\"2\" value=\"". substr($from_location,0,2). "\"></td>");
echo("<td><INPUT type=\"text\" readonly name=\"location\" size=\"8\" value=\"". substr($from_location,2,strlen($from_location) - 2). "\" ></td>");
echo ("</tr>");
echo ("</table>\n");
echo("<INPUT type=\"hidden\"  name=\"from_location\" value=\"". $from_location. "\" >");
echo("Input: <INPUT type=\"text\" name=\"ssn\" size=\"20\"");
echo(" onchange=\"document.getssn.submit();\">");
echo("<br>Total SSNs <INPUT type=\"text\" readonly name=\"qtyinlocn\" size=\"4\" value=\"$tot_inlocn\" >");
echo("Scanned <INPUT type=\"text\" readonly name=\"qtyscanned\" size=\"4\" value=\"$tot_scanned\" ><BR>");

echo ("<table>\n");
echo ("<tr>\n");
echo("<th colspan=\"3\">Scan SSN or Location</th>\n");
echo ("</tr>\n");
//echo ("</table>\n");
//echo total
//echo("</form>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

{
	// html 4.0 browser
	//whm2buttons('Accept', 'gettolocn.php');
	$alt = "Accept";
	$backto = "gettolocn.php";
	// Create a table.
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	echo ("<td  >");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	$alt = "Putaway";
	echo ("<td>");
	echo("<form action=\"" . $backto . "\" method=\"post\" name=putaway>\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/Putaway_50x100.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	//echo ("</tr>");
	if ($tot_scanned == 0)
	{
		//echo ("<tr>");
		echo ("<td>");
		echo("<form action=\"../mainmenu.php\" method=\"post\" name=back>\n");
		//echo("<INPUT type=\"IMAGE\" ");  
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
		echo("</form>");
		echo ("</td>");
		//echo ("</tr>");
	}
	echo ("</tr>");
	echo ("</table>");

/*
	echo("<BUTTON name=\"putaway\" type=\"button\" onfocus=\"location.href='gettolocn.php';\">\n");
	echo("Putaway<IMG SRC=\"/icons/layout.gif\" alt=\"putaway\"></BUTTON>\n");
*/
}
echo("</div>");
?>
<script type="text/javascript">
<?php
{
	echo("document.getssn.ssn.focus();\n");
}
?>
</script>
</body>
</html>
