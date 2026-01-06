<html>
 <head>
  <title>View Orders</title>
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";
include "repage.php";
echo '<link rel=stylesheet type="text/css" href="ViewOrders.css">';
?>
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
  <h3>View Orders</h3>
<?php
//    ==========================================================================================================================

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$got_tot = 0;

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

$pickmode = "";
if (isset($_POST['pickmode']))
{
	$pickmode = $_POST['pickmode'];
}
if (isset($_GET['pickmode']))
{
	$pickmode = $_GET['pickmode'];
}
if ($pickmode == "") 
{
	$pickmode = getBDCScookie($Link, $tran_device, "pickmode" );
} else {
	setBDCScookie($Link, $tran_device, "pickmode", $pickmode);
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
	$pickordertypes = getBDCScookie($Link, $tran_device, "pickordertypes" );
} else {
	setBDCScookie($Link, $tran_device, "pickordertypes", $pickordertypes);
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
	$pickordernos = getBDCScookie($Link, $tran_device, "pickordernos" );
} else {
	setBDCScookie($Link, $tran_device, "pickordernos", $pickordernos);
}

if ($pickordernos == "")
{
	$pickordernos = "GETALL";
}
$pickorderstatuses = "GETALL";
$pickorderprioritys = "GETALL";
$pickorderids = "GETALL";

//need to save some variables
echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
// then default to those values


$Query = "select pick_order_type, procedure_name  from pick_mode ";
$Query .= "where pick_mode_no = '" . $pickmode . "'";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}

$got_ssn = 0;

// for pagination
// want to know # lines in total
$wkNumRows = 0;
while ( ($Row = ibase_fetch_row($Result)) ) {
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	
	$Query2 = "SELECT COUNT(*)  FROM  ";
	$Query2 .= "(SELECT ORDERS.WK_ORDER, COALESCE(p1.PROD_ID,'') AS PROD_IDS, COALESCE(p1.SSN_ID,'') AS SSN_IDS  FROM  ";
	$Query2 .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$Query2 .= $pickordermodes . "','" ;
	$Query2 .= $pickordernos . "','" ;
	$Query2 .= $pickorderstatuses . "','" ;
	$Query2 .= $pickorderprioritys . "','" ;
	$Query2 .= $pickorderids . "') AS ORDERS " ;
	$Query2 .= "JOIN PICK_ITEM p1 "; 
	$Query2 .= " ON  p1.PICK_ORDER = ORDERS.WK_ORDER ";
	$Query2 .= " WHERE    p1.PICK_LINE_STATUS IN ('OP', 'UP')" ;
//	$Query2 .= " GROUP BY ORDERS.WK_ORDER, p1.PROD_ID, p1.SSN_ID ) ";
	$Query2 .= "  ) ";
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Orders!<BR>\n");
		exit();
	}
	while (($Row2 = ibase_fetch_row($Result2)) )  {
		$wkNumRows = $Row2[0];
	}
	//release memory
	ibase_free_result($Result2);
}
	//release memory
	ibase_free_result($Result);
// and lines per page
$wkLinesPerPage = 7;
	$wkQueryList = " ORDERS.WK_ORDER, COALESCE(p1.PROD_ID,'') || COALESCE( p1.SSN_ID,'') AS ITEM  ";
	$wkQueryList .= ",  (COALESCE(p1.PICK_ORDER_QTY,0) - COALESCE(p1.PICKED_QTY,0)) AS ORDER_QTY FROM "; 
	$wkQueryList .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$wkQueryList .= $pickordermodes . "','" ;
	$wkQueryList .= $pickordernos . "','" ;
	$wkQueryList .= $pickorderstatuses . "','" ;
	$wkQueryList .= $pickorderprioritys . "','" ;
	$wkQueryList .= $pickorderids . "') AS ORDERS " ;
	$wkQueryList .= "JOIN PICK_ITEM p1 "; 
	$wkQueryList .= " ON  p1.PICK_ORDER = ORDERS.WK_ORDER ";
	$wkQueryList .= " WHERE    p1.PICK_LINE_STATUS IN ('OP', 'UP')" ;
	//$wkQueryList .= " GROUP BY ORDERS.WK_ORDER, p1.PROD_ID, p1.SSN_ID  ";

echo("<FORM action=\"ViewAllocate.php\" method=\"post\" name=getordersssn>\n");
echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");


// echo headers
$wkHeaders = "<table BORDER=\"1\" class=\"pg\">\n";
$wkHeaders .= "<tr>";
$wkHeaders .= "<th>Order</th>\n";
$wkHeaders .= "<th>SSN/Product</th>\n";
$wkHeaders .= "<th>Qty</th>\n";
$wkHeaders .= "</tr>";

bdcsRepage($Link, $wkNumRows, $wkLinesPerPage, $wkQueryList, $wkHeaders);

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
	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Allocate","ViewType.php","Y","Back_50x100.gif","Back","allocatepicks.gif");

/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
</body>
</html>
