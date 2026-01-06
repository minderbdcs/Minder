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

$got_tot = 0;


$pickmode = "";
if (isset($_POST['pickmode']))
{
	$pickmode = $_POST['pickmode'];
}
if (isset($_GET['pickmode']))
{
	$pickmode = $_GET['pickmode'];
}
$pickordertypes = "";
if (isset($_POST['pickordertypes']))
{
	$pickordertypes = $_POST['pickordertypes'];
}
if (isset($_GET['pickordertypes']))
{
	$pickordertypes = $_GET['pickordertypes'];
}
if ($pickordertypes == "")
{
	$pickordertypes = "GETALL";
}
$pickordermodes = "GETALL";
$pickordernos = "";
if (isset($_POST['pickordernos']))
{
	$pickordernos = $_POST['pickordernos'];
}
if (isset($_GET['pickordernos']))
{
	$pickordernos = $_GET['pickordernos'];
}
if ($pickordernos == "")
{
	$pickordernos = "GETALL";
}
$pickorderstatuses = "GETALL";
$pickorderprioritys = "GETALL";
$pickorderids = "GETALL";
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
$Query = "select pick_order_type, procedure_name  from pick_mode ";
$Query .= "where pick_mode_no = '" . $pickmode . "'";


if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}

$got_ssn = 0;

// echo headers
//echo("<FORM action=\"ViewAllocate.php\" method=\"post\" name=getordersssn>\n");
echo("<FORM action=\"AllocateOrders.php\" method=\"post\" name=getordersssn>\n");
echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>");
echo("<TH>Order</TH>\n");
echo("<TH>SSN/Product</TH>\n");
echo("<TH>Qty</TH>\n");
echo ("</TR>");

// Fetch the results from the database.

while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	
	$Query2 = "SELECT FIRST ". $rscr_limit . " WK_ORDER FROM  ";
	$Query2 .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$Query2 .= $pickordermodes . "','" ;
	$Query2 .= $pickordernos . "','" ;
	$Query2 .= $pickorderstatuses . "','" ;
	$Query2 .= $pickorderprioritys . "','" ;
	$Query2 .= $pickorderids . "')" ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Orders!<BR>\n");
		exit();
	}
	while (($Row2 = ibase_fetch_row($Result2)) ) 
	{
		$wk_order = $Row2[0];
		//echo ("<TR>");
		//echo("<TD>".$Row2[0]."</TD>\n");
		//echo ("</TR>");
		$wk_lines = 0;
		$Query3 = "select p1.ssn_id, p1.prod_id,  sum(p1.pick_order_qty) "; 
		$Query3 .= "from pick_item p1 ";
		$Query3 .= " where p1.pick_line_status in ('OP', 'UP')" ;
		$Query3 .= " and p1.pick_order = '" . $wk_order . "'";
		$Query3 .= " group by p1.prod_id, p1.ssn_id ";
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read Lines!<BR>\n");
			exit();
		}
		while (($Row3 = ibase_fetch_row($Result3)) ) 
		{
			echo ("<TR>");
			$wk_lines++;
			if ($wk_lines == 1)
			{
				echo("<TD>".$Row2[0]."</TD>\n");
			}
			else
			{
				echo("<TD></TD>\n");
			}
			if ($Row3[0] == "")
			{
				echo("<TD>".$Row3[1]."</TD>\n");
			}
			else
			{
				echo("<TD>".$Row3[0]."</TD>\n");
			}
			echo("<TD>".$Row3[2]."</TD>\n");
			echo ("</TR>");
		}
		//release memory
		ibase_free_result($Result3);
	}
}
echo ("</TABLE>\n");
if (isset($Result2))
{
	//release memory
	ibase_free_result($Result2);
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo total
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Allocate Next Pick\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Allocate Next Pick<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Allocate","ViewType.php","Y","Back_50x100.gif","Back","allocatepicks.gif");

/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
