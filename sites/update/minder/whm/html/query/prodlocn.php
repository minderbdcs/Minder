<?php
session_start();
?>
<html>
<head>
<title>Retrieving Product Locations from Database</title>
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
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
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
if (isset($_POST['product'])) 
{
	$product = $_POST["product"];
}
else
{
	if (isset($_GET['product'])) 
	{
		$product = $_GET["product"];
	}
	else
	{
		$product = "";
	}
}
if (isset($_POST['kitid'])) 
{
	$kit_id = $_POST["kitid"];
}
if (isset($_GET['kit_id'])) 
{
	$kit_id = $_GET["kitid"];
}
if (isset($_POST['kitlocation'])) 
{
	$lastkitlocation = $_POST["kitlocation"];
}
else
{
	$lastkitlocation = "";
}
if (isset($_POST['kitwh_id'])) 
{
	$lastkitwhid = $_POST["kitwh_id"];
}
else
{
	$lastkitwhid = "";
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
//want current wh and whether a sys admin
$Query = "select sys_user.sys_admin,session.description from sys_user join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where sys_user.user_id = '" . $tran_user . "' " ; 
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query user!<BR>\n");
	exit();
}
$wk_sysadmin = "";
$wk_current_wh = "";
while (($Row = ibase_fetch_row($Result)) )
{
	$wk_sysadmin = $Row[0];
	$wk_current_wh = $Row[1];
}
//want locns for product
$Query = "select first " . $rscr_limit . " issn.wh_id, issn.locn_id ,location.locn_name, issn.issn_status, sum(issn.current_qty) ";
$Query .= "from issn ";
$Query .= "left outer join location on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
$Query .= "where issn.prod_id = '".$product."' ";
$Query .= "and issn.current_qty <> 0  ";
$Query .= "and (issn.wh_id > '".$lastwhid."' or ";
$Query .= "     (issn.locn_id > '".$lastlocation."' and issn.wh_id = '".$lastwhid."')) ";
if ($wk_sysadmin <> 'T')
{
	$Query .= "and (issn.wh_id < 'X' or issn.wh_id > 'X~') ";
	// can only see the current wh
	$Query .= "and issn.wh_id = '" . $wk_current_wh . "'  ";
}
$Query .= "group by issn.wh_id, issn.locn_id, location.locn_name, issn.issn_status ";

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
	print("Unable to query product!<BR>\n");
	exit();
}
// echo headers
echo ("<TR>\n");
// for ($i=0; $i<ibase_num_fields($Result); $i++)
// {
// 	$info = ibase_field_info($Result, $i);
// 	echo("<TH>{$info["name"]}</TH>\n");
// }

echo("<TH>WH</TH>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Name</TH>\n");
echo("<TH>Status</TH>\n");
echo("<TH>Qty</TH>\n");
echo ("</TR>\n");

// Fetch the results from the database.
//while ( ($Row = $Result->fetchRow())  and ($rcount < $rscr_limit) ) {
while (($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit))
{
 	echo ("<TR>\n");
 	$lastlocation = $Row[1];
 	$lastwhid = $Row[0];
	// for ($i=0; $i<ibase_num_fields($Result); $i++)
	for ($i=0; $i<5; $i++)
	{
		if ($i == 1)
		{
			echo("<TD>");
			echo("<FORM action=\"./ssn.php\" method=\"post\" name=getssn>\n");
			echo("<INPUT type=\"hidden\" name=\"location\" value=\"$Row[0]$Row[$i]\">\n");
			echo("<INPUT type=\"hidden\" name=\"product\" value=\"$product\">\n");
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

if (isset($kit_id))
{
	// show a table of kit parts locations
	//want locns for product
	$Query = "select first " . $rscr_limit . " issn.wh_id, issn.locn_id ,product_kit.prod_id,issn.issn_status, sum(issn.current_qty) ";
	$Query .= "from product_kit ";
	$Query .= "left outer join issn on issn.prod_id = product_kit.prod_id ";
	$Query .= "and (issn.wh_id > '".$lastkitwhid."' or ";
	$Query .= "     (issn.locn_id > '".$lastkitlocation."' and issn.wh_id = '".$lastkitwhid."')) ";
	$Query .= "and (issn.wh_id < 'X' or issn.wh_id > 'X~') ";
	$Query .= "where product_kit.kit_id = '".$product."' and product_kit.status = 'OK' ";
	if ($wk_sysadmin <> 'T')
	{
		// can only see the current wh
		$Query .= "and issn.wh_id = '" . $wk_current_wh . "'  ";
	}
	$Query .= "group by issn.wh_id, issn.locn_id, product_kit.prod_id, issn.issn_status ";
	// Create a table.
	echo ("Kit's Products");
	echo ("<TABLE BORDER=\"1\">\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query product!<BR>\n");
		exit();
	}
	// echo headers
	echo ("<TR>\n");
	echo("<TH>WH</TH>\n");
	echo("<TH>Location</TH>\n");
	echo("<TH>Product</TH>\n");
	echo("<TH>Status</TH>\n");
	echo("<TH>Qty</TH>\n");
	echo ("</TR>\n");
	// Fetch the results from the database.
	$rcount = 0;
	while (($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit))
	{
	 	echo ("<TR>\n");
	 	$lastkitlocation = $Row[1];
	 	$lastkitwhid = $Row[0];
		for ($i=0; $i<5; $i++)
		{
			if ($i == 1)
			{
				echo("<TD>");
				if (($Row[0] > "") or ($Row[1] > ""))
				{
					echo("<FORM action=\"./ssn.php\" method=\"post\" name=getssn>\n");
					echo("<INPUT type=\"hidden\" name=\"location\" value=\"$Row[0]$Row[$i]\">\n");
					echo("<INPUT type=\"submit\" name=\"locn_id\" value=\"$Row[$i]\">\n");
					echo("</FORM>\n");
				}
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

}

//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
ibase_close($Link);

echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
//echo (" <FORM action=\"locations.php\" method=\"post\" name=showprodlocn>");
echo (" <FORM action=\"prodlocn.php\" method=\"post\" name=showprodlocn>");
echo (" <P>");
echo ("<INPUT type=\"hidden\" name=\"location\" value = \"".$lastlocation."\"> ");
echo ("<INPUT type=\"hidden\" name=\"wh_id\" value = \"".$lastwhid."\"> ");
//echo ("<INPUT type=\"hidden\" name=\"product\" value = \"".$product."\"> ");
echo ("Product <INPUT type=\"text\" name=\"product\" value = \"".$product."\" readonly size=\"30\" ><BR> ");
list ($wk_po_date, $wk_po_qty) = lastgrn($product);
echo ("Last GRN Date<INPUT type=\"text\" name=\"prodgrndate\" value = \"".$wk_po_date."\" readonly size=\"19\" > ");
echo ("<br>GRN Qty <INPUT type=\"text\" name=\"prodgrnqty\" value = \"".$wk_po_qty."\" readonly size=\"4\" > ");

if (isset($kit_id))
{
	echo ("<INPUT type=\"hidden\" name=\"kitid\" value = \"".$kit_id."\"> ");
	echo ("<INPUT type=\"hidden\" name=\"kitlocation\" value = \"".$lastkitlocation."\"> ");
	echo ("<INPUT type=\"hidden\" name=\"kitwh_id\" value = \"".$lastkitwhid."\"> ");
}
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
	//whm2buttons('More', '../query/query.php', 'N');
	whm2buttons('More',"../query/query.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"more.gif");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"prodhist.php\" method=\"post\" name=history>\n");
	echo ("<INPUT type=\"hidden\" name=\"prod\" value = \"".$product."\"> ");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=View+History&fromimage=');
	echo('Blank_Button_50x100.gif" alt="7Days">');
*/
	echo('SRC="/icons/whm/viewhistory.gif" alt="7Days">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
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
document.showprodlocn.more.focus();
</script>
</body>
</html>

