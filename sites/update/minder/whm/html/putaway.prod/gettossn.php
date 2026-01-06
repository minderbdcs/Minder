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
  <title>Putaway get To SSN</title>
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
  mytype = checkLocn(document.getssn.ssn.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
*/
  {
	return true;
  }
}
</script>

<?php
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
if (isset($_GET['message']))
{
	$message = $_GET['message'];
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
$Query .= " where issn.issn_status in ('PA','TS')" ;
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

echo ("<div id=\"locns5\">\n");
if (isset($message))
{
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
echo("<FONT size=\"2\">\n");
echo("<form action=\"transactionIL.php\" method=\"post\" name=getssn ONSUBMIT=\"return processEdit();\">\n");
// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
//echo("<th>SSN</th>\n");
echo("<th>SSN/Prod No</th>\n");
//echo("<th>INTO</th>\n");
echo("<th>INTO/Desc</th>\n");
echo("<th>Qty</th>\n");
//echo("<th>Prod No</th>\n");
//echo("<th>Description</th>\n");
echo ("</tr>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo("<td>".$Row[2]."</td>\n");
	$tot_scanned += 1;
	if ($Row[5] == '') {
		//ssn
		if ($Row[9] == '') {
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
		echo("<td>".$Row[4]."</td>\n");
	}
	echo("<td>".$Row[6]."</td>\n");
	echo ("</tr>\n");
	echo ("<tr>\n");
	echo("<td>".$Row[5]."</td>\n");
	if ($Row[5] == '') {
		//ssn
		echo("<td colspan=\"3\">".$Row[7]."</td>\n");
	}
	else
	{
		echo("<td colspan=\"3\">".$Row[8]."</td>\n");
	}
	echo ("</tr>\n");
}

echo ("</table>\n");
echo ("</div>\n");
echo ("<div id=\"locns6\">\n");
echo("Location INTO: <INPUT type=\"text\" readonly name=\"location2\" size=\"10\" value=\"". substr($to_location,2,strlen($to_location) - 2). "\"><br>");
echo("<INPUT type=\"hidden\" readonly name=\"to_location\" value=\"". $to_location. "\" >");
echo("<INPUT type=\"hidden\" readonly name=\"transaction_type\" value=\"TRIL\" >");
echo("Total to Putaway <INPUT type=\"text\" readonly name=\"qtyscan\" size=\"4\" value=\"$tot_scanned\" ><BR>");

echo("Input: <INPUT type=\"text\" name=\"ssn\" size=\"20\"");
echo(" onchange=\"document.getssn.submit();\">");
echo ("<table>\n");
echo ("<tr>\n");
echo("<th colspan=\"2\">Scan SSN or Location</th>\n");
echo ("</tr>\n");
//echo ("</table>\n");
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
//echo("</form>\n");

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
	echo("<form action=\"transactionIL.php\" method=\"post\" name=putaway>\n");
	echo("<INPUT type=\"hidden\" readonly name=\"to_location\" value=\"". $to_location. "\" ><BR>");
	echo("<INPUT type=\"hidden\" readonly name=\"transaction_type\" value=\"TRLI\" ><BR>");
	echo("<INPUT type=\"submit\" name=\"putaway\" value=\"Putaway All\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
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
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"transactionIL.php?transaction_type=TRLI&to_location=$to_location\" method=\"post\" name=putawayALL>\n");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Putaway+All&fromimage=');
	echo('Blank_Button_50x100.gif" alt="putawayAll">');
*/
	echo('SRC="/icons/whm/putawayall.gif" alt="putawayAll">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
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
</div>
</body>
</html>
