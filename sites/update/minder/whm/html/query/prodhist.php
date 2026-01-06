<html>
<head>
<title>Last 7 Days Despatches  for a Product</title>
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
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['location'])) 
{
	$lastlocation = $_POST["location"];
}
else
{
	$lastlocation = "";
}
if (isset($_POST['prod'])) 
{
	$lastprod = $_POST["prod"];
}
else
{
	$lastprod = "";
}
if (isset($_POST['despatchdate'])) 
{
	$lastdate = $_POST["despatchdate"];
}
else
{
	$lastdate = "TODAY";
}
//phpinfo();
//echo("last prod:" . $lastprod);
//echo(" last locn:" . $lastlocation);
//echo(" last date:" . $lastdate);

// want upto seven days prior to today
$Query = "SELECT issn.prod_id, pp.long_desc, issn.locn_id, issn.despatched_date , sum(issn.prev_qty) from issn left outer join prod_profile pp on issn.prod_id = pp.prod_id and issn.company_id = pp.company_id ";

$Query .= " where 
	issn.wh_id = 'XD' and
  	issn.issn_status = 'DX' and
        issn.into_date > add_time('TODAY',-7,0,0,0,0) and 
  	issn.prod_id = '".$lastprod."' and
	(issn.locn_id > '".$lastlocation."' or
	 (issn.despatched_date > cast('".$lastdate."' as date) and
	 issn.locn_id = '".$lastlocation."'))
	group by issn.prod_id, pp.long_desc, issn.locn_id , issn.despatched_date ";
// Create a table.
echo ("<TABLE BORDER=\"1\">\n");

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query table!<BR>\n");
	exit();
}

// echo headers
echo ("<TR>\n");
echo("<TH>Product</TH>\n");
echo("<TH>Desciption</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Date</TH>\n");
echo("<TH>Qty</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit))
{
	$rcount++;
 	echo ("<TR>\n");
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($i <> 3)
		{
 			//echo ("<TD>$Row[$i]</TD>\n");
 			echo ("<TD>" . htmlspecialchars($Row[$i]) . "</TD>\n");
		}
		else
		{
			list($desp_date, $desp_time) = explode(" ", $Row[$i]);
 			echo ("<TD>" . $desp_date . "</TD>\n");
		}
	}
 	$lastlocation = $Row[2];
 	$lastprod = $Row[0];
 	$lastdate = $desp_date;
 	echo ("</TR>\n");
}
echo ("</TABLE>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo (" <FORM action=\"prodhist.php\" method=\"post\" name=showstock>");
echo (" <P>");
echo ("<INPUT type=\"hidden\" name=\"location\" value = \"".$lastlocation."\"> ");
echo ("<INPUT type=\"hidden\" name=\"prod\" value = \"".$lastprod."\"> ");
echo ("<INPUT type=\"hidden\" name=\"despatchdate\" value = \"".$lastdate."\"> ");
//whm2buttons('More', 'getlocn.php');
whm2buttons('More',"getlocn.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"more.gif");
?>
</body>
</html>

