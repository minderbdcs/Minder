<html>
<head>
<?php
 include "viewport.php";
?>
<title>Last 7 Days Despatches Listing</title>
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
/*
else
{
	$lastprod = "";
}
*/
if (isset($_POST['despatchdate'])) 
{
	$lastdate = $_POST["despatchdate"];
}
else
{
	$lastdate = "TODAY";
}
//echo("last prod:" . $lastprod);
//echo(" last locn:" . $lastlocation);
//echo(" last date:" . $lastdate);

if (isset($lastprod))
{
	// want upto seven days prior to today
	$Query = "SELECT first ". $rscr_limit . " issn.prod_id, pp.long_desc, issn.locn_id, issn.despatched_date , sum(issn.prev_qty) from issn left outer join prod_profile pp on pp.prod_id = issn.prod_id";
	
	$Query .= " where 
		issn.wh_id = 'XD' and
	  	issn.issn_status = 'DX' and
	        issn.into_date > add_time('TODAY',-7,0,0,0,0) and
	  	(issn.prod_id > '".$lastprod."' or
		 (issn.locn_id > '".$lastlocation."' and
	  	  issn.prod_id = '".$lastprod."') or
		 (issn.despatched_date > cast('".$lastdate."' as date) and
		  issn.locn_id = '".$lastlocation."' and
	  	  issn.prod_id = '".$lastprod."') )
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
	 			echo ("<TD>$Row[$i]</TD>\n");
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
	echo (" <FORM action=\"GetOldStock.php\" method=\"post\" name=showstock>");
	echo (" <P>");
	echo ("<INPUT type=\"hidden\" name=\"location\" value = \"".$lastlocation."\"> ");
//	echo ("<INPUT type=\"hidden\" name=\"prod\" value = \"".$lastprod."\"> ");
	echo ("Last Product <INPUT type=\"text\" name=\"prod\" value=\"". $lastprod . "\"  ");
	echo(" onfocus=\"document.showstock.prod.value=''\" ");
	echo(" onchange=\"document.showstock.submit()\" ><br>\n");
	echo ("<INPUT type=\"hidden\" name=\"despatchdate\" value = \"".$lastdate."\"> ");
	whm2buttons('More', 'print_Menu.php',"Y","Back_50x100.gif","Back","more.gif");
}
else
{
	echo("<H4>Enter Product to View</H4>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo (" <FORM action=\"GetOldStock.php\" method=\"post\" name=showstock>");
	echo ("<INPUT type=\"text\" name=\"prod\"  ");
	echo(" onchange=\"document.showstock.submit()\" ><br>\n");
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.showstock.prod.focus();</script>");
}
?>
</body>
</html>
