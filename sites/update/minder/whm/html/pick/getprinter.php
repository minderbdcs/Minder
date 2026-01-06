<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

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
if (isset($_POST['reprint']))
{
	$reprint = $_POST['reprint'];
}
if (isset($_GET['reprint']))
{
	$reprint = $_GET['reprint'];
}
	
$Query = "select count(*) from pick_item ";
$Query .= " where pick_line_status in ('AL','PG')";
$Query .= " and device_id = '".$tran_device."'";
$Query .= " and (not prod_id is NULL)";
$Query .= " and (prod_id <> '')";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Total!<BR>\n");
	exit();
}

$got_tot = 0;

$wk_select = " ";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$got_tot = 0;
		}
		else
		{
			$got_tot = $Row[$i];
		}
	}
}

//release memory
ibase_free_result($Result);

/*
if ($got_tot == 0)
{
	// no products
}
*/
// want ssn label desc
/*
$Query = "select p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, p1.pick_location, s1.current_qty, sum(s3.current_qty), p1.pick_order "; 
$Query .= "from pick_item p1 ";
$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
$Query .= "left outer join issn s3 on s3.prod_id = p1.prod_id  ";
$Query .= " and s3.locn_id = p1.pick_location  ";
$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
$Query .= " where p1.pick_line_status in ('AL', 'PG')" ;
$Query .= " and device_id = '".$tran_device."'";
//$Query .= " order by p1.pick_location ";
$Query .= " group by p1.pick_location, p1.ssn_id, p1.pick_label_no, p1.prod_id, s2.ssn_type, p2.short_desc, s1.current_qty, p1.pick_order";
*/
// first get the pick_item stuff
$Query = "select p1.ssn_id, p1.pick_label_no, p1.prod_id, p1.pick_location, p1.pick_order_qty, p1.picked_qty, p1.pick_order , p1.pick_label_date "; 
$Query .= "from pick_item p1 ";
$Query .= " where p1.pick_line_status in ('AL', 'PG')" ;
$Query .= " and device_id = '".$tran_device."'";
$Query .= " order by p1.pick_location ";
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>SSN/Product</TH>\n");
echo("<TH>Order</TH>\n");
echo("<TH>Label No</TH>\n");
echo("<TH>Description</TH>\n");
echo("<TH>Qty</TH>\n");
echo ("</TR>\n");

$wkDoReprint = "Y";
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo ("<TR>\n");
	echo("<TD>".$Row[3]."</TD>\n");
	if ($Row[0] <> "")
	{
		echo("<TD>".$Row[0]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[2]."</TD>\n");
	}
	echo("<TD>".$Row[6]."</TD>\n");

	echo("<TD>".$Row[1]."</TD>\n");
	// desc
	if ($Row[0] <> "")
	{
		// an ssn
		$Query2 = "select s2.ssn_type "; 
		$Query2 .= "from issn s1 ";
		$Query2 .= "join ssn s2 on s2.ssn_id = s1.original_ssn ";
		$Query2 .= "where s1.ssn_id = '$Row[0]' ";
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read SSN!<BR>\n");
			exit();
		}
		if ( ($Row2 = ibase_fetch_row($Result2)) ) {
			echo("<TD>".$Row2[0]."</TD>\n");
		}
		//release memory
		ibase_free_result($Result2);
	}
	else
	{
		// a product
		$Query2 = "select p2.short_desc "; 
		$Query2 .= "from prod_profile p2 ";
		$Query2 .= "where p2.prod_id = '$Row[2]' ";
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read Product!<BR>\n");
			exit();
		}
		if ( ($Row2 = ibase_fetch_row($Result2)) ) {
			echo("<TD>".$Row2[0]."</TD>\n");
		}
		//release memory
		ibase_free_result($Result2);
	}
	//qty
	if ($Row[5] <> "")
	{
		// have a picked qty
		$topick = $Row[4] - $Row[5];
		echo("<TD>".$topick ."</TD>\n");
	}
	else
	{
		// just show order qty
		echo("<TD>".$Row[4]."</TD>\n");
	}
	//pick label printed
	if ($Row[7] == "")
	{
		$wkDoReprint = "N";
	}
	echo ("</TR>\n");
	//$rcount++;
}

if ($wkDoReprint == "Y" )
{
	$reprint = "Y";
}

echo ("</TABLE>\n");
//release memory
ibase_free_result($Result);

//echo total
echo("<FORM action=\"transactionPL.php\" method=\"post\" name=getprint>\n");
echo("Products: <INPUT type=\"text\" name=\"total\" size=\"2\" value=\"$got_tot\" >");
//echo("Printer: <INPUT type=\"text\" name=\"printer\" size=\"2\" value=\"PA\" ><BR>");
echo("Printer: <SELECT name=\"printer\">\n");
$Query = "select device_id from sys_equip where device_type='PR' order by device_id";
// Fetch the results from the database.
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Printers<BR>\n");
	exit();
}
while (($Row = ibase_fetch_row($Result))) {
	//echo($Row[1] . "<INPUT type=\"checkbox\" name=\"type\" value=\"" . $Row[0] . "\">\n");
	echo("<OPTION value=\"" . $Row[0] . "\">$Row[0]\n");
}
echo("</SELECT>\n");

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

if (isset($reprint))
{
	echo("<INPUT type=\"hidden\" name=\"reprint\" value=\"$reprint\">");
	//echo("Label to Reprint: <INPUT type=\"text\" name=\"label\" size=\"7\" ><BR>");
}
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
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"cancel.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'cancel.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='cancel.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo ("<TABLE ALIGN=\"BOTTOM\">\n");
echo ("<TR>\n");
if (isset($reprint))
{
	//echo("<TD>Enter Printer and Label to Reprint</TD>\n");
	echo("<TD>Enter Printer for Reprint</TD>\n");
}
else
{
	echo("<TD>Enter Printer</TD>\n");
}
echo ("</TR>\n");
echo ("</TABLE >\n");
echo ("</BODY >\n");
echo ("<SCRIPT>\n");
echo("document.getprint.printer.focus();\n");
echo ("</SCRIPT>\n");
?>
