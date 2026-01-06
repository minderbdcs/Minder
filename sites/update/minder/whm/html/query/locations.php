<html>
<head>
<title>Retrieving Data from a Database</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
$rcount = 0;
// $lastlocation = "location";
if (isset($_POST['location'])) 
{
	$lastlocation = $_POST["location"];
}
else
{
	$lastlocation = "";
}
if (isset($_POST['wh_id'])) 
{
	$lastwhid = $_POST["wh_id"];
}
else
{
	$lastwhid = "";
}
//$Link = DB::connect($dsn,true);
//if (DB::isError($Link))
//{
//	echo("Unable to Connect!<BR>\n");
//	echo($Link->getMessage());
//	exit();
//}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// $Query = "SELECT wh_id, locn_id, locn_name from ".$TableName;
// $Query = "SELECT wh_id, locn_id, locn_name from location where locn_id >= '".$lastlocation."' order by wh_id, locn_id ";
$Query = "SELECT wh_id, locn_id, locn_name 
	from location 
	where wh_id > '".$lastwhid."' or 
	(locn_id > '".$lastlocation."' and
	 wh_id = '".$lastwhid."')
	order by wh_id, locn_id ";
// Create a table.
echo ("<TABLE BORDER=\"1\">\n");

//$Result = $Link->query($Query);
//if (DB::isError($Result))
//{
//	echo("Unable to query locations!<BR>\n");
//	exit();
//}

if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query locations!<BR>\n");
	exit();
}
// echo headers
echo ("<TR>\n");
// for ($i=0; $i<ibase_num_fields($Result); $i++)
// {
// 	$info = ibase_field_info($Result, $i);
// 	echo("<TH>{$info["name"]}</TH>\n");
// }

echo("<TH>Repository</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Name</TH>\n");
echo ("</TR>\n");

// Fetch the results from the database.
//while ( ($Row = $Result->fetchRow())  and ($rcount < $rscr_limit) ) {
while (($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit))
{
 	echo ("<TR>\n");
 	$lastlocation = $Row[1];
 	$lastwhid = $Row[0];
	// for ($i=0; $i<ibase_num_fields($Result); $i++)
	for ($i=0; $i<3; $i++)
	{
		if ($i == 1)
		{
			echo("<TD>");
			//echo("<FORM action=\"../ssn/ssn.php\" method=\"post\" name=getssn>\n");
			echo("<FORM action=\"./ssn.php\" method=\"post\" name=getssn>\n");
			echo("<INPUT type=\"hidden\" name=\"location\" value=\"$Row[0]$Row[$i]\">\n");
			echo("<INPUT type=\"submit\" name=\"locn_id\" value=\"$Row[$i]\">\n");
			echo("</FORM>\n");
			echo("</TD>");
		}
		else
		{
 			echo ("<TD>$Row[$i]</TD>\n");
		}
	}
 	echo ("</TR>\n");
	$rcount++;
}
echo ("</TABLE>\n");

//release memory
//$Result->free();
ibase_free_result($Result);

//commit
//$Link->commit();
ibase_commit($dbTran);



//close
//$Link->disconnect();
ibase_close($Link);

echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
//echo (" <FORM action=\"locations.php\" method=\"post\" name=showlocation>");
echo (" <FORM action=\"locations.php\" method=\"post\" name=showlocation>");
echo (" <P>");
echo ("<INPUT type=\"hidden\" name=\"location\" value = \"".$lastlocation."\"> ");
echo ("<INPUT type=\"hidden\" name=\"wh_id\" value = \"".$lastwhid."\"> ");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"more\" value=\"More!\">\n");
	echo("</FORM>\n");
	//echo("<FORM action=\"../query/query.php\" method=\"post\" name=goback>\n");
	echo("<FORM action=\"./query.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('More', '../query/query.php');
	whm2buttons('More',"../query/query.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"more.gif");
/*
	echo("<BUTTON name=\"more\" value=\"More!\" type=\"submit\">\n");
	echo("More<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../query/query.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
document.showlocation.more.focus();
</script>
</body>
</html>

