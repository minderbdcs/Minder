<?php
include "../login.inc";
?>
<html>
<head>
<title>View Orders</title>
<link rel=stylesheet type="text/css" href="fromlocn.css">
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
</head>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbtran = ibase_trans(IBASE_DEFAULT, $Link);

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
$Query = "select pick_order_type, procedure_name  from pick_mode ";
$Query .= "where pick_mode_no = '" . $pickmode . "'";


if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}

$got_ssn = 0;

echo "<body>";
//echo ("<div>\n");
echo ("<div id=\"locns3\">\n");
// echo headers
echo("<form action=\"ViewAllocate.php\" method=\"post\" name=getordersssn>\n");
echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
echo ("<table BORDER=\"1\">\n");
echo ("<tr>");
echo("<th>Order</th>\n");
echo("<th>SSN/Product</th>\n");
echo("<th>Qty</th>\n");
echo ("</tr>");

// Fetch the results from the database.

while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	
	$Query2 = "SELECT WK_ORDER FROM  ";
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
		//echo ("<tr>");
		//echo("<td>".$Row2[0]."</td>\n");
		//echo ("</tr>");
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
			echo ("<tr>");
			$wk_lines++;
			if ($wk_lines == 1)
			{
				echo("<td>".$Row2[0]."</td>\n");
			}
			else
			{
				echo("<td></td>\n");
			}
			if ($Row3[0] == "")
			{
				echo("<td>".$Row3[1]."</td>\n");
			}
			else
			{
				echo("<td>".$Row3[0]."</td>\n");
			}
			echo("<td>".$Row3[2]."</td>\n");
			echo ("</tr>");
		}
		//release memory
		ibase_free_result($Result3);
	}
}
echo ("</table>\n");
echo ("</div>\n");
if (isset($Result2))
{
	//release memory
	ibase_free_result($Result2);
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbtran);

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
//echo("</form>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	echo ("<div ID=\"col1\">\n");
	// html 4.0 browser
	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Allocate","ViewType.php","Y","Back_50x100.gif","Back","allocatepicks.gif");

/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
echo ("</div>\n");
}
?>
