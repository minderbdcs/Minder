<?php
include "../login.inc";
echo("<html>\n");
echo("<head>\n");
 include "viewport.php";
echo("</head>\n");

require_once 'DB.php';
require 'db_access.php';

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
	
$to_location = '';
if (isset($_POST['location']))
{
	$to_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$to_location = $_GET['location'];
}
//echo("to locn:".$to_location);
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

$Query = "select issn.wh_id, issn.locn_id, issn.ssn_id, st.putaway_location, pp.home_locn_id, issn.prod_id, issn.current_qty, st.description, pp.short_desc, sn.home_locn_id "; 
$Query .= "from issn  ";
$Query .= "join ssn sn on issn.original_ssn = sn.ssn_id  ";
$Query .= "left outer join ssn_type st on sn.ssn_type = st.code  ";
$Query .= "left outer join prod_profile pp on issn.prod_id = pp.prod_id  ";
$Query .= " where issn.issn_status in ('PA')" ;
$Query .= " and issn.wh_id in ('";
$Query .= $device_wh."')";
$Query .= " and issn.locn_id in ('";
$Query .= $tran_device."')";
$Query .= " order by issn.ssn_id" ;
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}


$rcount = 0;
$got_ssn = 0;
$tot_scanned = 0;

echo("<body>\n");
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionIL.php\" method=\"post\" name=getssn\n>");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>SSN</TH>\n");
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
	echo("<TD>".$Row[2]."</TD>\n");
	$tot_scanned += 1;
	if ($Row[5] == '') {
		//ssn
		if ($Row[9] == '') {
			//no home_locn_id for ssn 
			//use putaway_location from ssn_type
			echo("<TD>".$Row[3]."</TD>\n");
		}
		else
		{
			//use home_locn_id for ssn 
			echo("<TD>".$Row[9]."</TD>\n");
		}
	}
	else
	{
		//prod
		//use home_locn_id for prod
		echo("<TD>".$Row[4]."</TD>\n");
	}
	echo("<TD>".$Row[5]."</TD>\n");
	echo("<TD>".$Row[6]."</TD>\n");
	if ($Row[5] == '') {
		//ssn
		echo("<TD>".$Row[7]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[8]."</TD>\n");
	}
	echo ("</TR>\n");
}

echo ("</TABLE>\n");
echo("Location INTO: <INPUT type=\"text\" readonly name=\"location2\" size=\"10\" value=\"". substr($to_location,2,strlen($to_location) - 2). "\">");
echo("<INPUT type=\"hidden\" readonly name=\"to_location\" value=\"". $to_location. "\" ><BR>");
echo("<INPUT type=\"hidden\" readonly name=\"transaction_type\" value=\"TRIL\" ><BR>");
echo("Total to Putaway <INPUT type=\"text\" readonly name=\"qtyscan\" size=\"4\" value=\"$tot_scanned\" ><BR>");

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
	echo("<FORM action=\"transactionIL.php\" method=\"post\" name=putaway>\n");
	echo("<INPUT type=\"hidden\" readonly name=\"to_location\" value=\"". $to_location. "\" ><BR>");
	echo("<INPUT type=\"hidden\" readonly name=\"transaction_type\" value=\"TRLI\" ><BR>");
	echo("<INPUT type=\"submit\" name=\"putaway\" value=\"Putaway All\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"transactionIL.php?transaction_type=TRLI&to_location=$to_location\" method=\"post\" name=putawayALL>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=Putaway+All&fromimage=');
	echo('Blank_Button_50x100.gif" alt="putawayAll"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
	/*
	echo("<BUTTON name=\"putaway\" type=\"button\" onfocus=\"location.href='transactionIL.php?transaction_type=TRLI&to_location=$to_location';\">\n");
	echo("Putaway All<IMG SRC=\"/icons/layout.gif\" alt=\"putaway\"></BUTTON>\n");
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
