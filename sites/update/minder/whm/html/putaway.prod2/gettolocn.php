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
  <title>Putaway get Location To</title>
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
//include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
/*
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
*/
  {
  	document.getlocn.submit();
	return true;
  }
}
</script>

<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}
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

//var_dump($wk_use_company_home);
//release memory
ibase_free_result($Result3);

//$Query = "SELECT issn.wh_id, issn.locn_id, issn.ssn_id, st.putaway_location, pp.home_locn_id, issn.prod_id, issn.current_qty, st.description, pp.short_desc, sn.home_locn_id "; 
$Query = "SELECT issn.wh_id, issn.locn_id, issn.ssn_id, st.putaway_location, pp.home_locn_id, issn.prod_id, issn.current_qty, st.description, pp.short_desc, sn.home_locn_id, pp2.home_locn_id, pp2.short_desc, cy.home_locn_id, pp.prod_id, issn.original_ssn "; 
$Query .= "FROM issn  ";
$Query .= "JOIN ssn sn ON issn.original_ssn = sn.ssn_id  ";
$Query .= "LEFT OUTER JOIN ssn_type st ON sn.ssn_type = st.code  ";

//$Query .= "left outer join prod_profile pp on issn.prod_id = pp.prod_id  ";
$Query .= "LEFT OUTER JOIN prod_profile pp ON issn.prod_id = pp.prod_id  ";
$Query .= "AND  issn.company_id = pp.company_id  ";
$Query .= "LEFT OUTER JOIN prod_profile pp2 ON issn.prod_id = pp2.prod_id  ";
$Query .= "AND  'ALL' = pp.company_id  ";
$Query .= "LEFT OUTER JOIN company cy ON issn.company_id = cy.company_id  ";

$Query .= " WHERE issn.issn_status IN ('PA','TS')" ;
$Query .= " AND issn.current_qty > 0";
$Query .= " and issn.wh_id in ('";
$Query .= $device_wh."')";
$Query .= " and issn.locn_id in ('";
$Query .= $tran_device."')";
//$Query .= " order by issn.ssn_id" ;
$Query .= " ORDER BY issn.prod_id, issn.original_ssn, issn.ssn_id" ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_scanned = 0;

echo ("<div id=\"locns5\">\n");
if (isset($message))
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"gettossn.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">\n");
// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
//echo("<th>SSN</th>\n");
echo("<th>SSN/Prod No</th>\n");
//echo("<th>INTO</th>\n");
echo("<th>INTO/Desc</th>\n");
echo("<th>Qty</th>\n");
//echo("<th>Prod No</th>\n");
//echo("<th>Qty</th>\n");
//echo("<th>Description</th>\n");

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
	// if the product changes
	if ($Row[5] != $wk_last_product)
	{
		$wk_last_product = $Row[5];
		// now the prod stuff
		echo ("<tr>\n");
		echo("<td>".$Row[5]."</td>\n"); // issn.prod_id
		if ($Row[5] == '') {
			//ssn
			echo("<td colspan=\"3\">".$Row[7]."</td>\n"); // ssn_type.description
		} else {
			if ($Row[13] == '') {
				// product not found for the current company so use ALL
				echo("<td colspan=\"3\">".$Row[11]."</td>\n"); // prod_profile.short_desc
			} else {
				echo("<td colspan=\"3\">".$Row[8]."</td>\n"); // prod_profile.short_desc
			}
		}
		echo ("</tr>\n");
	}
	echo ("<tr>\n");
	$wk_last_ssn = $Row[14];
	echo("<td>".$Row[2]."</td>\n"); // issn.ssn_id
	$tot_scanned += 1;
	// now home locn = default location
	if (($wk_use_company_home == 'T')  and ($Row[12] != '')) {
		echo("<td>".$Row[12]."</td>\n");
	} else { 
		if ($Row[5] == '') { // if issn.prod_id is empty
			//ssn
			if ($Row[9] == '') { // ssn.home_locn_id is empty
				//no home_locn_id for ssn 
				//use putaway_location from ssn_type
				echo("<td>".$Row[3]."</td>\n");
			}
			else
			{
				//use home_locn_id for ssn 
				echo("<td>".$Row[9]."</td>\n");
			}
		}
		else
		{
			//prod
			//use home_locn_id for prod
			if ($Row[13] == '') {
				// product not found for the current company so use ALL
				echo("<td>".$Row[10]."</td>\n");
			} else {
				echo("<td>".$Row[4]."</td>\n");
			}
		}
	}

	echo("<td>".$Row[6]."</td>\n"); // issn.current_qty
	echo ("</tr>\n");

/*
	echo ("<tr>\n");
	echo("<td>".$Row[5]."</td>\n"); // issn.prod_id
	if ($Row[5] == '') {
		//ssn
		echo("<td colspan=\"3\">".$Row[7]."</td>\n"); // ssn_type.description
	}
	else
	{
		echo("<td colspan=\"3\">".$Row[8]."</td>\n"); // prod_profile.short_desc
	}
	echo ("</tr>\n");
*/
}

echo ("</table>\n");
echo ("</div>\n");
echo ("<div id=\"locns6\">\n");
echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\"");
/* echo(" onchange=\"document.getlocn.submit();\">"); */
echo(" onchange=\"return processEdit();\">");
echo("Total Scanned <INPUT type=\"text\" readonly name=\"qtyscan\" size=\"4\" value=\"$tot_scanned\" ><BR>");

echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Scan INTO Location</th>\n");
echo ("</tr>\n");
//echo ("</table>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
*/
{
	$alt = "Accept";
	// Create a table.
	//echo ("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	echo ("<td>");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</FORM>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
/*
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
}
//echo total
//echo("</FORM>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//close
//ibase_close($Link);

?>
<script type="text/javascript">
<?php
{
	echo("document.getlocn.location.focus();\n");
}
?>
</div>
</script>
</body>
</html>
