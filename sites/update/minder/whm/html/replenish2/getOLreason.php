<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';

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
$scannedssn = '';
if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $scannedssn, $picked_qty) = explode("|", $_COOKIE["BDCSData"] . "||||||||||");
}
	
if (isset($_POST['label']))
{
	$label_no = $_POST['label'];
	$wk_update_cook = "P";
}
if (isset($_GET['label']))
{
	$label_no = $_GET['label'];
	$wk_update_cook = "G";
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['prod']))
{
	$prod_no = $_POST['prod'];
}
if (isset($_GET['prod']))
{
	$prod_no = $_GET['prod'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['desc']))
{
	$description = $_POST['desc'];
}
if (isset($_GET['desc']))
{
	$description = $_GET['desc'];
}
if (isset($_POST['required_qty']))
{
	$required_qty = $_POST['required_qty'];
}
if (isset($_GET['required_qty']))
{
	$required_qty = $_GET['required_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['scannedssn']))
{
	$scannedssn = $_POST['scannedssn'];
}
if (isset($_GET['scannedssn']))
{
	$scannedssn = $_GET['scannedssn'];
}
if (isset($_POST['picked_qty']))
{
	$picked_qty = $_POST['picked_qty'];
}
if (isset($_GET['picked_qty']))
{
	$picked_qty = $_GET['picked_qty'];
}
if (isset($prod_no))
{
	if ($prod_no == "NOPROD")
	{
		$picked_qty = $required_qty;
		if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
		{
			echo("Unable to Connect!<BR>\n");
			exit();
		}
		$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
		$Query = "select default_wh_id from control";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read control!<BR>\n");
			exit();
		}
		// Fetch the results from the database.
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_default_wh = $Row[0];
		}
		$location = $wk_default_wh . "NEWLABEL";
		//release memory
		ibase_free_result($Result);

		//commit
		ibase_commit($dbTran);

		//close
		//ibase_close($Link);
	}
}
if (isset($wk_update_cook))
{
	// save original fields
	$cookiedata = "";
	{
		$cookiedata .= $label_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $order;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $ssn;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $prod_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $uom;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $description;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $required_qty;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $location;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $scannedssn;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $picked_qty;
	}
	$cookiedata .= '|';
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
}

include "2buttons.php";

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"transactionOL.php\" method=\"post\" name=getreason\n>");
echo("Line<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
if ($ssn <> '')
{
	echo("SSN<INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\">");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\">");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
}
else
{
	echo("Prod: <INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\" >");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\" >");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"30\" value=\"$description\" ><BR>");
}
echo("ShortFall Reason<SELECT name=\"reference\" >");
echo("<OPTION value=\"No Stock\">No Stock");
echo("<OPTION value=\"Damaged Stock\">Damaged Stock");
echo("<OPTION value=\"Wrong Stock\">Wrong Stock");
echo("<OPTION value=\"None\">No Reason");
echo("</SELECT>");

echo("<BR>Qty Reqd<INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\">");
echo("Picked<INPUT type=\"text\" readonly name=\"qtypicked\" size=\"4\" value=\"$picked_qty\">");

echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Enter Reason</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
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
//echo total
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getfromssn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if (isset($wk_update_cook))
	{
			//whm2buttons('Accept', 'getfromlocn.php');
			whm2buttons('Accept',"getfromlocn.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
	}
	else
	{
		if ($ssn <> "")
		{
			//whm2buttons('Accept', 'getfromssn.php');
			whm2buttons('Accept',"getfromssn.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
		}
		else
		{
			//whm2buttons('Accept', 'pickcomplete.php');
			whm2buttons('Accept',"pickcomplete.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
		}
	}
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromssn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<SCRIPT>
<?php
	if ($prod_no == "NOPROD")
	{
		echo("document.getreason.submit();\n");
	}
	else
	{
		echo("document.getreason.reference.focus();\n");
	}
?>
</SCRIPT>
</HTML>
