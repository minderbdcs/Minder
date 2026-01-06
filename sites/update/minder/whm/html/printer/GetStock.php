<html>
<head>
<?php
 include "viewport.php";
?>
<title>Current Stock Listing</title>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
// Set the variables for the database access:

/*
	need to get the last received date and qty from the ssn
	for this product
*/
function lastgrn($wk_prod)
{
global $Link;
       $Query = "SELECT first 1 grn, po_receive_date FROM SSN ";
       $Query .= "WHERE PROD_ID = '" . $wk_prod . "' ";
       $Query .= "AND PO_RECEIVE_DATE = ( SELECT MAX(PO_RECEIVE_DATE) FROM SSN WHERE PROD_ID = '" . $wk_prod . "') ";
	// gives the grn
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query SSN!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
 		$wk_grn = $Row[0];
 		$wk_po_date = $Row[1];
	}
	//release memory
	ibase_free_result($Result);

	if (isset($wk_grn))
	{
		$Query = "SELECT sum(original_qty) from ssn  where prod_id = '" . $wk_prod . "'";
		$Query .= "and grn = '" . $wk_grn . "'" ;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query SSN2!<BR>\n");
			exit();
		}
		// Fetch the results from the database.
		if (($Row = ibase_fetch_row($Result)) )
		{
 			$wk_grn_tot = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
		if (isset($wk_grn_tot))
		{
			return array($wk_po_date, $wk_grn_tot);
		}
		else
		{
			return array($wk_po_date, "");
		}
	}

	return array("", "");
}
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
if (isset($_POST['wh_id'])) 
{
	$lastwhid = $_POST["wh_id"];
}
else
{
	$lastwhid = "";
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
if (isset($_POST['status'])) 
{
	$laststatus = $_POST["status"];
}
else
{
	$laststatus = "";
}
//echo("last prod:" . $lastprod);
//echo(" last wh:" . $lastwhid);
//echo(" last locn:" . $lastlocation);
//echo(" last status:" . $laststatus);

if (isset($lastprod))
{
	$Query = "SELECT first ". $rscr_limit . " issn.prod_id, pp.long_desc,issn.wh_id, issn.locn_id, issn.issn_status, sum(issn.current_qty) from issn left outer join prod_profile pp on pp.prod_id = issn.prod_id";
	
	$Query .= " where 
	  	(issn.prod_id > '".$lastprod."' or
		 (issn.wh_id > '".$lastwhid."' and
	  	  issn.prod_id = '".$lastprod."') or
		 (issn.locn_id > '".$lastlocation."' and
		  issn.wh_id = '".$lastwhid."' and
	  	  issn.prod_id = '".$lastprod."') or
	  	 (issn.issn_status > '".$laststatus."' and
	  	  issn.prod_id = '".$lastprod."' and
		  issn.locn_id = '".$lastlocation."' and
		  issn.wh_id = '".$lastwhid."')) and
		(issn.wh_id < 'x' or issn.wh_id > 'x~') 
		group by issn.prod_id, pp.long_desc, issn.wh_id, issn.locn_id , issn.issn_status ";
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
	echo("<TH>WH</TH>\n");
	echo("<TH>Location</TH>\n");
	echo("<TH>Status</TH>\n");
	echo("<TH>Qty</TH>\n");
	echo("<TH>GRN Date</TH>\n");
	echo("<TH>GRN Qty</TH>\n");
	echo ("</TR>\n");
	
	$rcount = 0;
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit))
	{
		$rcount++;
	 	echo ("<TR>\n");
		for ($i=0; $i<ibase_num_fields($Result); $i++)
		{
	 		echo ("<TD>$Row[$i]</TD>\n");
		}
	 	$lastlocation = $Row[3];
	 	$lastwhid = $Row[2];
		$lastlastprod = $lastprod;
		if ($rcount == 1)
		{
			$lastlastprod = "";
		}
	 	$lastprod = $Row[0];
	 	$laststatus = $Row[4];
		if ($lastlastprod <> $lastprod)
		{
			list($wk_po_date, $wk_po_qty) = lastgrn($lastprod);
		}
	 	echo ("<TD>$wk_po_date</TD>\n");
	 	echo ("<TD>$wk_po_qty</TD>\n");
	
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
	echo (" <FORM action=\"GetStock.php\" method=\"post\" name=showstock>");
	echo (" <P>");
	echo ("<INPUT type=\"hidden\" name=\"location\" value = \"".$lastlocation."\"> ");
	echo ("<INPUT type=\"hidden\" name=\"wh_id\" value = \"".$lastwhid."\"> ");
	//echo ("<INPUT type=\"hidden\" name=\"prod\" value = \"".$lastprod."\"> ");
	echo ("Last Product <INPUT type=\"text\" name=\"prod\" value=\"". $lastprod . "\"  ");
	echo(" onfocus=\"document.showstock.prod.value=''\" ");
	echo(" onchange=\"document.showstock.submit()\" ><br>\n");
	echo ("<INPUT type=\"hidden\" name=\"status\" value = \"".$laststatus."\"> ");
	whm2buttons('More', 'print_Menu.php',"Y","Back_50x100.gif","Back","more.gif");
}
else
{
	echo("<H4>Enter Product to View</H4>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo (" <FORM action=\"GetStock.php\" method=\"post\" name=showstock>");
	echo ("<INPUT type=\"text\" name=\"prod\"  ");
	echo(" onchange=\"document.showstock.submit()\" ><br>\n");
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.showstock.prod.focus();</script>");
}
?>
</body>
</html>
