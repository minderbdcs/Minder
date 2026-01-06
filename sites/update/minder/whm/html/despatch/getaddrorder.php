<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Order Address Labels</title>
</head>
<?php
require_once 'DB.php';
require 'db_access.php';

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (!isset($printer))
{
	$printer = "PC";
}
$addressfrom = "O";
if (isset($_POST['addressfrom']))
{
	$addressfrom = $_POST['addressfrom'];
}
if (isset($_GET['addressfrom']))
{
	$addressfrom = $_GET['addressfrom'];
}
if (isset($_POST['qty']))
{
	$qty = $_POST['qty'];
}
if (isset($_GET['qty']))
{
	$qty = $_GET['qty'];
}
if (!isset($qty))
{
	$qty = 1;
}
$instruction1 = "";
$instruction2 = "";
if (isset($_POST['instruction1']))
{
	$instruction1 = $_POST['instruction1'];
}
if (isset($_GET['instruction1']))
{
	$instruction1 = $_GET['instruction1'];
}
if (isset($_POST['instruction2']))
{
	$instruction2 = $_POST['instruction2'];
}
if (isset($_GET['instruction2']))
{
	$instruction2 = $_GET['instruction2'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include "checkdata.php";

if (isset($order))
{
	//global $field_type, $field_starts, $startposn, $message;
	$field_type = checkForTypein($order, 'SALESORDER' ); 
	if ($field_type == "none")
	{
		// not a sales order
		//echo("not a sales order type ");
		$order_data = $order;
	}
	else
	{
		//echo("field starts " . $startposn );
		//echo("message " . $message );
		$order_data = substr($order, $startposn);
	}
	$order = $order_data;
}
if (isset($order))
{
	$got_order = 0;
	$Query = "select 1 from pick_order where pick_order = '" . $order . "'" ;
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Order!<BR>\n");
		exit();
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$got_order = $Row[0];
		}

	}
	if ($got_order == 0)
	{
		echo ("<B><FONT COLOR=RED>Order Not Found</FONT></B>\n");
		unset($order);
	}
	//release memory
	ibase_free_result($Result);
	//commit
	ibase_commit($dbTran);

}
echo("<body>\n");

echo("<FONT size=\"2\">\n");
if (isset($order) and isset($addressfrom) )
{
	echo("<FORM action=\"transactionAO.php\" method=\"post\" name=getlabel\n>");
}
else
{
	echo("<FORM action=\"getaddrorder.php\" method=\"post\" name=getlabel\n>");
}

if (isset($order))
{
	echo("Order <INPUT type=\"text\" name=\"order\" size=\"10\" value=\"$order\"><BR>");
}
else
{
	echo("Order:<INPUT type=\"text\" name=\"order\" size=\"10\" ><BR>\n");

}
if (isset($order))
{
	$addressfrom = "O";
	echo("<INPUT type=\"hidden\" name=\"addressfrom\" value=\"$addressfrom\">");
	echo("Address From <INPUT type=\"text\" name=\"addressfromdesc\" readonly size=\"5\" value=\"Order\">");
		
	echo("<BR>Instructions<BR><INPUT type=\"text\" name=\"instruction1\" size=\"40\" maxlength=\"40\" value=\"$instruction1\"></BR>");
	echo("<INPUT type=\"text\" name=\"instruction2\" size=\"37\" maxlength=\"37\" value=\"$instruction2\"></BR>");
	echo("Printer <INPUT type=\"text\" name=\"printer\" size=\"2\" value=\"$printer\"></BR>");
	echo("Qty <INPUT type=\"text\" name=\"qty\" size=\"3\" value=\"$qty\"></BR>");
}

echo ("<TABLE>\n");
echo ("<TR>\n");
if (isset($order))
{
	echo("<TH>Enter Instructions ,Printer and Qty for Print</TH>\n");
}
else
{
	echo("<TH>Enter Order for Print</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
// Create a table.
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<TR>");
echo ("<TD>");
echo("<INPUT type=\"IMAGE\" ");  
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	if (isset($order))
	{
		echo("<INPUT type=\"submit\" name=\"print\" value=\"Accept\">\n");
	}
	else
	{
		echo("<INPUT type=\"submit\" name=\"submit\" value=\"Accept\">\n");
	}
}
else
*/
{
	if (isset($order) )
	{
		echo('SRC="/icons/whm/Print_50x100.gif" alt="Print"></INPUT>');
/*
		echo("<BUTTON name=\"print\" value=\"Print\" type=\"submit\" >\n");
		echo("Print<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
	else
	{
		$alt = "Accept";
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '"></INPUT>');
/*
		echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
		echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
}
echo("</FORM>\n");
echo ("</TD>");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	$backto = "./despatch_menu.php";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
if (!isset($order) )
{
	//release memory
	//ibase_free_result($Result);

	//commit
	//ibase_commit($dbTran);
}

//close
//ibase_close($Link);

echo("<script type=\"text/javascript\">\n");
{
	if (isset($order))
	{
		echo("document.getlabel.instruction1.focus();\n");
	}
	else
	{
		echo("document.getlabel.order.focus();\n");
	}
}
?>
</script>
</body>
</html>

