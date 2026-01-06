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

if (isset($_POST['label']))
{
	$label_no = $_POST['label'];
}
if (isset($_GET['label']))
{
	$label_no = $_GET['label'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['product']))
{
	$prod_no = $_POST['product'];
}
if (isset($_GET['product']))
{
	$prod_no = $_GET['product'];
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
if (isset($_POST['description']))
{
	$description = $_POST['description'];
}
if (isset($_GET['description']))
{
	$description = $_GET['description'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
if (isset($_POST['nolocations']))
{
	$nolocations = $_POST['nolocations'];
}
if (isset($_GET['nolocations']))
{
	$nolocations = $_GET['nolocations'];
}

$rcount = 0;

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);


if ($ssn == '')
{
	$Query = "select  to_wh_id, to_locn_id "; 
	$Query .= "from transfer_request  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and trn_status in ('PL','PG')";
	$Query .= " and prod_id = '" . $prod_no . "'";
	$Query .= " and to_wh_id = '" . substr($location,0,2) . "' and to_locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
}
//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

//print($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: replenish_Menu.php?message=Unable+to+Read+Picks");
	exit();
}

$location_found = 0;
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if (trim($Row[0].$Row[1]) == $location)
	{
		// location is valid
		$location_found = 1;
	}
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

{
	if ($location_found == 0)
	{
		$wk_next_page = "gettolocn.php";
	}
	else
	{
		$wk_next_page = "gettoqty.php";
	}
	echo("<FORM action=\"" . $wk_next_page . "\" method=\"post\" name=gettolocn>\n");
	echo("<INPUT type=\"hidden\" name=\"product\" value=\"" . $prod_no . "\" >");
	echo("<INPUT type=\"hidden\" name=\"description\" value=\"" . $description . "\" >");
	echo("<INPUT type=\"hidden\" name=\"nolocations\" value=\"" . $nolocations . "\" >");

	if ($location_found == 0)
	{
		echo("<INPUT type=\"hidden\" name=\"locnfound\" value=\"0\">");
	}
	else
	{
		echo("<INPUT type=\"hidden\" name=\"location\" value=\"" . $location . "\">");
	}
	echo("<INPUT type=\"submit\" name=\"sendme\">");
	echo("</form>\n");
	echo("<script>\n");
	echo("document.gettolocn.submit();\n");
	echo("</script>\n");

}
?>
