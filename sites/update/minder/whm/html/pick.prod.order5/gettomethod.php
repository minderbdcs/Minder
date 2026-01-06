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
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;
	
{
	$Query = "select despatch_location, ssn_id, pick_label_no, pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and (ssn_id > '')";
	$Query .= " order by pick_order";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;
$got_orders = 0;
$last_order = "";

echo("<FONT size=\"2\">\n");
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>SSN/Product</TH>\n");
echo("<TH>Label No</TH>\n");
echo("<TH>Order</TH>\n");
echo ("</TR>\n");

$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	{
		echo("<TD>".$Row[1]."</TD>\n");
	}
	echo("<TD>".$Row[2]."</TD>\n");
	echo("<TD>".$Row[3]."</TD>\n");
	if ($Row[3] <> $last_order)
	{
		$last_order = $Row[3];
		$got_orders++;
	}
	echo ("</TR>\n");
}
//release memory
ibase_free_result($Result);

{
	$Query = "select despatch_location, prod_id "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and prod_id > ''";
	$Query .= " group by despatch_location, prod_id";
}
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items++;
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo("<TD></TD>\n");
	echo("<TD></TD>\n");
	echo ("</TR>\n");
}
//release memory
ibase_free_result($Result);
echo ("</TABLE>\n");
echo("Tot Items <INPUT type=\"text\" readonly name=\"qtyitems\" size=\"1\" value=\"$got_items\" ><BR>");


{
	$Query = "select first 1 1 "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status in ('AL', 'PG') ";
}
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_back_items = 0;
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$got_back_items++;
	//$got_back_items++;
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo ("<TABLE>\n");
echo ("<TR>\n");
//if ($got_orders == 1)
{
	echo("<TH>Select All or By Pieces</TH>\n");
}
/*
else
{
	echo("<TH>Select Sales Order</TH>\n");
}
*/
echo ("</TR>\n");
echo ("</TABLE>\n");
/*
//if ($got_orders == 1)
{
	// always allow all
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		echo("<FORM action=\"gettolocn.php\" method=\"post\" name=all>\n");
		echo("<INPUT type=\"submit\" name=\"all\" value=\"All\">\n");
		echo("</FORM>\n");
	}
	else
	{
		echo("<BUTTON name=\"all\" type=\"button\" onfocus=\"location.href='gettolocn.php';\">\n");
		echo("All<IMG SRC=\"/icons/forward.gif\" alt=\"all\"></BUTTON>\n");
	}
}
*/
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<FORM action=\"gettoso.php\" method=\"post\" name=so>\n");
	echo("<INPUT type=\"submit\" name=\"so\" value=\"Sales Order\">\n");
	echo("</FORM>\n");
}
else
{
	echo("<BUTTON name=\"so\" type=\"button\" onfocus=\"location.href='gettoso.php';\">\n");
	echo("Sales Order<IMG SRC=\"/icons/hand.right.gif\" alt=\"so\"></BUTTON>\n");
}
*/
if ($got_back_items == 0)
{
	$back_screen = "pick_Menu.php";
}
else
{
	$back_screen = "getfromlocn.php";
}
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"$back_screen\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='$back_screen';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<FORM action=\"gettolocn.php\" method=\"post\" name=all>\n");
whm2buttons('All', $back_screen, "N");
echo ("<TR>");
echo ("<TD>");
echo("<FORM action=\"gettoso.php\" method=\"post\" name=so>\n");
echo("<INPUT type=\"IMAGE\" ");  
/*
echo('SRC="/icons/whm/button.php?text=' . "By+Pieces" . '&fromimage=');
echo('Blank_Button_50x100.gif" alt="' . "SO" . '"></INPUT>');
*/
echo('SRC="/icons/whm/pieces.gif" alt="' . "SO" . '"></INPUT>');
echo("</FORM>");
echo ("</TD>");
echo ("</TR>");
echo ("</TABLE>");
?>
<SCRIPT>
<?php
{
	//if ($got_orders == 1)
	{
		// always allow all
/*
		if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
		{
			echo("document.all.all.focus();\n");
		}
		else
		{
			echo("document.all.focus();\n");
		}
*/
		echo("document.all.All.focus();\n");
	}
/*
	else
	{
		if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
		{
			echo("document.so.so.focus();\n");
		}
		else
		{
			echo("document.so.focus();\n");
		}
	}
*/
}
?>
</SCRIPT>
</HTML>
