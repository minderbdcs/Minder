<?php
include "../login.inc";
echo("<html>\n");
echo("<head>\n");
include "viewport.php";
echo("</head>\n");
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
require_once 'DB.php';
require 'db_access.php';
include"2buttons.php";

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
	
$from_location = '';
if (isset($_POST['location']))
{
	$from_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$from_location = $_GET['location'];
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

//release memory
ibase_free_result($Result);

$tot_locns = 0;
/*
$Query = "SELECT issn.wh_id, issn.locn_id, issn.ssn_id, st.putaway_location, pp.home_locn_id, issn.prod_id, issn.current_qty, st.description, pp.short_desc, sn.home_locn_id "; 
$Query .= "FROM issn  ";
$Query .= "JOIN ssn sn ON issn.original_ssn = sn.ssn_id  ";
$Query .= "LEFT OUTER JOIN ssn_type st ON sn.ssn_type = st.code  ";
$Query .= "LEFT OUTER JOIN prod_profile pp ON issn.prod_id = pp.prod_id  ";
$Query .= " WHERE issn.issn_status ='PA' " ;
$Query .= " AND issn.wh_id IN ('";
$Query .= substr($from_location,0,2)."','";
$Query .= $device_wh."')";
$Query .= " AND issn.locn_id IN ('";
$Query .= substr($from_location,2,strlen($from_location) - 2)."','";
$Query .= $tran_device."')";
$Query .= " ORDER BY issn.ssn_id" ;
//echo($Query);
// must split this up so that don't read all ssn - query optimiser
*/

$Query = "SELECT issn.wh_id, issn.locn_id, issn.ssn_id, pp.home_locn_id, issn.prod_id, issn.current_qty, pp.short_desc, issn.original_ssn "; 
$Query .= "FROM issn  ";
$Query .= "LEFT OUTER JOIN prod_profile pp ON issn.prod_id = pp.prod_id  ";
$Query .= " WHERE issn.issn_status ='PA' " ;
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
$Query .= " ORDER BY issn.ssn_id" ;

$Query2 = "SELECT st.putaway_location, st.description, sn.home_locn_id "; 
$Query2 .= "FROM ssn sn  ";
$Query2 .= "LEFT OUTER JOIN ssn_type st ON sn.ssn_type = st.code  ";
$Query2 .= " WHERE sn.ssn_id = '";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read ssns!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_scanned = 0;
$tot_inlocn = 0;

echo("<body>\n");

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionOL.php\" method=\"post\" name=getssn\n>");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>SSN</TH>\n");
echo("<TH>Selected</TH>\n");
echo("<TH>INTO</TH>\n");
echo("<TH>Prod No</TH>\n");
echo("<TH>Qty</TH>\n");
echo("<TH>Description</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	$include_me = 'n';
	//$include_me = 'y';
	if (($Row[0] == substr($from_location,0,2)) and 
	(trim($Row[1]) == substr($from_location,2,strlen($from_location) - 2))) {
		echo ("<TR>\n");
		echo("<TD>".$Row[2]."</TD>\n");
		$tot_inlocn += 1;
		$include_me = 'y';
	}
	else
	{
		if (($Row[0] == $device_wh) and 
		(trim($Row[1]) == $tran_device)) {
			echo ("<TR>\n");
			echo("<TD></TD>\n");
			$include_me = 'y';
		}
	}
	if (($Row[0] == $device_wh) and 
	(trim($Row[1]) == $tran_device)) {
		echo("<TD>".$Row[2]."</TD>\n");
		$tot_scanned += 1;
	}
	else
	{
		if ($include_me == 'y') {
			echo("<TD></TD>\n");
		}
	}
	if ($include_me == 'y') {
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
		if ($Row[4] == '') {
			//ssn
			if ($Row2[2] == '') {
				//no home_locn_id for ssn 
				//use putaway_location from ssn_type
				echo("<TD>".$Row2[0]."</TD>\n");
			}
			else
			{
				//use home_locn_id for ssn 
				echo("<TD>".$Row2[2]."</TD>\n");
			}
		}
		else
		{
			//prod
			//use home_locn_id for prod
			echo("<TD>".$Row[3]."</TD>\n");
		}
		echo("<TD>".$Row[4]."</TD>\n");
		echo("<TD>".$Row[5]."</TD>\n");
		if ($Row[4] == '') {
			//ssn
			echo("<TD>".$Row2[1]."</TD>\n");
		}
		else
		{
			echo("<TD>".$Row[6]."</TD>\n");
		}
		$tot_locns += 1;
		echo ("</TR>\n");
		if ($Row[4] == '') {
			//release memory
			ibase_free_result($Result2);
		}
	}
}

echo ("</TABLE>\n");
//echo("Location FROM <INPUT type=\"text\" readonly name=\"location\" size=\"8\" value=\"". substr($from_location,2,strlen($from_location) - 2). "\" ><BR>");
echo ("<TABLE>\n");
echo ("<TR>");
echo ("<TD>");
echo("Location FROM </TD>");
echo("<TD><INPUT type=\"text\" readonly name=\"wh\" size=\"2\" value=\"". substr($from_location,0,2). "\"></TD>");
echo("<TD><INPUT type=\"text\" readonly name=\"location\" size=\"8\" value=\"". substr($from_location,2,strlen($from_location) - 2). "\" ></TD>");
echo ("</TR>");
echo ("</TABLE>\n");
echo("<INPUT type=\"hidden\" readonly name=\"from_location\" value=\"". $from_location. "\" ><BR>");
echo("Scanned <INPUT type=\"text\" readonly name=\"qtyscanned\" size=\"4\" value=\"$tot_scanned\" ><BR>");
echo("Total SSNs <INPUT type=\"text\" readonly name=\"qtyinlocn\" size=\"4\" value=\"$tot_inlocn\" ><BR>");

echo("Input: <INPUT type=\"text\" name=\"ssn\" size=\"20\"");
echo(" onchange=\"document.getssn.submit();\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan SSN or Location</TH>\n");
echo ("</TR>\n");
//echo ("</TABLE>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo total
//echo("</FORM>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"gettolocn.php\" method=\"post\" name=putaway>\n");
	echo("<INPUT type=\"submit\" name=\"putaway\" value=\"Putaway\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Accept', 'gettolocn.php');
	$alt = "Accept";
	$backto = "gettolocn.php";
	// Create a table.
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	$alt = "Putaway";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=putaway>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	if ($tot_scanned == 0)
	{
		echo ("<TR>");
		echo ("<TD>");
		echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=back>\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		echo ("</TR>");
	}
	echo ("</TABLE>");

/*
	echo("<BUTTON name=\"putaway\" type=\"button\" onfocus=\"location.href='gettolocn.php';\">\n");
	echo("Putaway<IMG SRC=\"/icons/layout.gif\" alt=\"putaway\"></BUTTON>\n");
*/
}
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
