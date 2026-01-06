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
if (isset($_COOKIE['BDCSData']))
{
	list($label_no, $order, $ssn, $prod_no, $uom, $description, $required_qty, $location, $dummy, $dummy2) = explode("|", $_COOKIE["BDCSData"]);
}
	
if (isset($_POST['locnfound']))
{
	$location_found = $_POST['locnfound'];
}
if (isset($_GET['locnfound']))
{
	$location_found = $_GET['locnfound'];
	//echo("locn found ".$location_found);
}
if (isset($_POST['scannedssn']))
{
	$scannedssn = $_POST['scannedssn'];
}
if (isset($_GET['scannedssn']))
{
	$scannedssn = $_GET['scannedssn'];
}
// want ssn label desc
$Query = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control<BR>\n");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}
//release memory
ibase_free_result($Result);

if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.ssn_id, p1.allow_substitute , s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= "join pick_item p1 on p1.pick_label_no = '" . $label_no . "' ";
	$Query .= "where s1.ssn_id = '" . $ssn . "'";
}
else
{
	$wk_wh = "";
	$wk_company = "";
	// get wh and company from the order
	$Query = "select p2.wh_id, p2.company_id "; 
	$Query .= "from pick_order p2 ";
	$Query .= "where p2.pick_order = '" . $order . "' ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Order!<BR>\n");
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$wk_wh = $Row[0];
			$wk_company = $Row[1];
		}
	}
	//release memory
	ibase_free_result($Result);

	// get user flags
	$wk_sysuser = "";
	$wk_usercomp = "";
	$wk_inventoryop = "";
	$Query = "SELECT sys_admin, company_id, inventory_operator from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
		$wk_usercomp = $Row[1];
		$wk_inventoryop = $Row[2];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_company == "")
	{
		if (($wk_sysuser == "T") or ($wk_inventoryop == "T"))
		{
			/* if im sysadmin then can access  all companys */
			$Query1 = "select company_id from company ";
		}
		else
		{
			/* if im not sysadmin then can only access companys in access company */
			$Query1 = "select company_id from company where company_id in (select company_id from access_company where  user_id ='" . $tran_user . "') ";
		}
		//echo($Query);
	}
	else
	{
		$Query1 = "'" . $wk_company. "'";
	}

	if ($wk_wh == "")
	{
		if ($wk_sysuser == "T") 
		{
			/* if im sysadmin then can access  all warehouses */
			$Query2 = "select wh_id from warehouse where wh_id < 'X' or wh_id >'X~'  ";
		}
		else
		{
			/* if im not sysadmin then can only access companys in access user */
			$Query2 = "select wh_id from warehouse where wh_id in (select wh_id from access_user where  user_id ='" . $tran_user . "') ";
		}
	}
	else
	{
		$Query2 = "'" . $wk_wh. "'";
	}

	// wh must match wh of order
	//$Query .= " and s3.wh_id in (" . $Query2 . ") ";
	// company must match company of order
	$Query = "select s3.locn_id, s3.current_qty, s3.ssn_id, 'N', s3.wh_id "; 
	$Query .= "from issn s3 ";
	$Query .= " where s3.prod_id = '" . $prod_no . "' ";
	$Query .= " and s3.wh_id = '" . substr($location, 0, 2) . "' and s3.locn_id = '" . substr($location, 2, strlen($location) - 2) . "'";
	$Query .= " and s3.company_id in (" . $Query1 . ") ";
	$Query .= " and pos('" . $allowed_status . "',s3.issn_status,0,1) > -1";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_ssn = 0;

echo("<FONT size=\"2\">\n");
echo("<FORM action=\"checkfromssn.php\" method=\"post\" name=getssn>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
echo("Pick</TD><TD><INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\"></TD><TD>");
echo("SO</TD><TD><INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"></TD></TR></TABLE><BR><BR>");
//echo("Pick<INPUT type=\"text\" readonly name=\"label\" size=\"7\" value=\"$label_no\">");
//echo("SO<INPUT type=\"text\" readonly name=\"order\" size=\"15\" value=\"$order\"><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo("<TR><TD>");
if ($ssn <> '')
{
	//echo("SSN<INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\">");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\">");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("SSN</TD><TD><INPUT type=\"text\" readonly name=\"ssn\" size=\"8\" value=\"$ssn\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\">");
	echo("</TD></TR></TABLE><BR><BR>");
}
else
{
	//echo("Part: <INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\" >");
	//echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"8\" value=\"$uom\" >");
	//echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("Part</TD><TD><INPUT type=\"text\" readonly name=\"prod\" size=\"30\" value=\"$prod_no\"></TD><TD>");
	echo("<INPUT type=\"text\" readonly name=\"uom\" size=\"2\" value=\"$uom\"></TD></TR></TABLE><BR><BR>");
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<TR><TD>");
	echo("<INPUT type=\"text\" readonly name=\"desc\" size=\"20\" value=\"$description\" >");
	echo("</TD></TR></TABLE><BR><BR>");
}
// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Location</TH>\n");
echo("<TH>Qty Available</TH>\n");
echo("<TH>SSN</TH>\n");
echo ("</TR>\n");

$allow_sub = "";
$rcount = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	echo("<TD>".$Row[0]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	echo("<TD>".$Row[2]."</TD>\n");
	echo ("</TR>\n");
	$allow_sub = $Row[3];
}

echo ("</TABLE>\n");
echo("Qty Reqd <INPUT type=\"text\" readonly name=\"required_qty\" size=\"4\" value=\"$required_qty\" ><BR>");
echo("<INPUT type=\"hidden\" readonly name=\"allow_sub\" value=\"$allow_sub\" >");
echo("<INPUT type=\"hidden\" readonly name=\"substitute\"  >");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo("SSN: <INPUT type=\"text\" name=\"scannedssn\" size=\"20\">");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan SSN</TH>\n");
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
	echo("<FORM action=\"getfromlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons('Accept', 'getfromlocn.php');
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getfromlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<SCRIPT>
<?php
{
	if (isset($location_found))
	{
		if ($location_found == 0)
		{
			if ($allow_sub == 'Y')
			{
				echo("var dowhat;\n");
				echo("dowhat=confirm(\"Substitute SSN ?\");\n");
				echo("if(dowhat)\n");
				echo("{\n");
					echo("document.getssn.scannedssn.value=\"$scannedssn\";\n");
					echo("document.getssn.substitute.value=\"Y\";\n");
					echo("document.forms.getssn.submit();\n");
				echo("}\n");
					/*
					// send browser accept 
					// with a field updated for this
					// then checkfromssn
					// will update the pick_item
					// and continue 
					*/
				echo("else\n");
				echo("{\n");
					echo("alert(\"Scan Correct SSN\");\n");
					echo("document.getssn.scannedssn.focus();\n");
				echo("}\n");
			}
			else
			{
				echo("alert(\"SSN Substitute Not Allowed\");\n");
				echo("alert(\"Scan Correct SSN\");\n");
				echo("document.getssn.scannedssn.focus();\n");
			}
		}
		else
		{
			if ($location_found == 2)
			{
				echo("alert(\"Reserved SSN Scan Another\");\n");
				echo("document.getssn.scannedssn.focus();\n");
			}
			else
			{
				echo("alert(\"Can't Read the SSN - try another\");\n");
				echo("document.getssn.scannedssn.focus();\n");
			}
		}
	}
	else
	{
		echo("document.getssn.scannedssn.focus();\n");
	}
	//echo("document.getssn.scannedssn.focus();\n");
}
?>
</SCRIPT>
</HTML>
