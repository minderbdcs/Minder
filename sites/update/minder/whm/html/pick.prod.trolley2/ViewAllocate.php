<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
?>
<SCRIPT type="text/javascript">
function processEdit(maxorder, maxproduct) {
  /* document.getdetails.message.value="in process edit"; */
  if ( document.getdetails.maxorders.value>maxorder)
  {
  	document.getdetails.message.value="Must Be less than the Maximum" + maxorder;
	document.getdetails.maxorders.focus();
  	return false;
  }
  if ( document.getdetails.maxproducts.value>maxproduct)
  {
  	document.getdetails.message.value="Must Be Less than the Maximum" + maxproduct;
	document.getdetails.maxproducts.focus();
  	return false;
  }
  document.getdetails.message.value=" ";
  return true;
}
function processSubmit() {
  document.allocateorders.pickuser.value = document.getdetails.pickuser.value;
  document.allocateorders.allocatedevice.value = document.getdetails.allocatedevice.value;
  document.allocateorders.pickdevice.value = document.getdetails.pickdevice.value;
  document.allocateorders.maxorders.value = document.getdetails.maxorders.value;
  document.allocateorders.maxproducts.value = document.getdetails.maxproducts.value;
  document.getdetails.message.value=" ";
  return true;
}
</SCRIPT>
<?php
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo("<FORM action=\"ViewAllocate.php\" method=\"post\" name=getdetails>\n");
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

$pickuser = $tran_user;
if (isset($_POST['pickuser']))
{
	$pickuser = $_POST['pickuser'];
}
if (isset($_GET['pickuser']))
{
	$pickuser = $_GET['pickuser'];
}
$allocatedevice = $tran_device;
if (isset($_POST['allocatedevice']))
{
	$allocatedevice = $_POST['allocatedevice'];
}
if (isset($_GET['allocatedevice']))
{
	$allocatedevice = $_GET['allocatedevice'];
}
$pickdevice = "";
if (isset($_POST['pickdevice']))
{
	$pickdevice = $_POST['pickdevice'];
}
if (isset($_GET['pickdevice']))
{
	$pickdevice = $_GET['pickdevice'];
}
//echo("pickdevice " . $pickdevice);
if (isset($_POST['maxorders']))
{
	$wk_max_orders2 = $_POST['maxorders'];
}
if (isset($_GET['maxorders']))
{
	$wk_max_orders2 = $_GET['maxorders'];
}
if (isset($_POST['maxproducts']))
{
	$wk_max_products2 = $_POST['maxproducts'];
}
if (isset($_GET['maxproducts']))
{
	$wk_max_products2 = $_GET['maxproducts'];
}
$wk_message = "";
if (isset($_POST['message']))
{
	$wk_message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$wk_message = $_GET['message'];
}

$Query = "select pick_order_type, procedure_name  from pick_mode ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}
$wk_tot_orders = 0;
$wk_tot_products = 0;
if ( ($Row = ibase_fetch_row($Result)) ) {
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	
	$Query2 = "SELECT count(*) FROM  ";
	$Query2 .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$Query2 .= $pickordermodes . "','" ;
	$Query2 .= $pickordernos . "','" ;
	$Query2 .= $pickorderstatuses . "','" ;
	$Query2 .= $pickorderprioritys . "','" ;
	$Query2 .= $pickorderids . "')" ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Orders!<BR>\n");
		exit();
	}
	if ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		$wk_tot_orders = $Row3[0];
	}
	//release memory
	ibase_free_result($Result2);
	$Query2 = "SELECT COUNT(DISTINCT prod_id) FROM  ";
	$Query2 .= "pick_item WHERE pick_order IN ( ";
	$Query2 .= "SELECT wk_order FROM ";
	$Query2 .= $pick_procedure . "('" . $pickordertypes . "','" ;
	$Query2 .= $pickordermodes . "','" ;
	$Query2 .= $pickordernos . "','" ;
	$Query2 .= $pickorderstatuses . "','" ;
	$Query2 .= $pickorderprioritys . "','" ;
	$Query2 .= $pickorderids . "')" ;
	$Query2 .= ") AND (NOT prod_id IS NULL)";

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Products!<BR>\n");
		exit();
	}
	if ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		$wk_tot_products = $Row3[0];
	}
	//release memory
	ibase_free_result($Result2);
}
//release memory
ibase_free_result($Result);

// want ssn label desc

$got_ssn = 0;
$Query = "select user_id from sys_user order by user_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}
echo("Allocate to User:<SELECT name=\"pickuser\" >\n");
// Fetch the results from the database.
while (($Row2 = ibase_fetch_row($Result))) {
	if ($Row2[0] == $pickuser)
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
	}
}
echo("</SELECT>\n");
//release memory
ibase_free_result($Result);
$Query = "select device_id from sys_equip where device_type = 'HH' order by device_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}
echo("<BR>Allocate to Device:<SELECT name=\"allocatedevice\" >\n");
// Fetch the results from the database.
while (($Row2 = ibase_fetch_row($Result))) {
	if ($Row2[0] == $allocatedevice)
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
	}
}
echo("</SELECT>\n");

//release memory
ibase_free_result($Result);
echo ("<TABLE BORDER=\"0\">\n");
echo ("<TR><TD>\n");
$Query = "select device_id from sys_equip where device_type = 'TR' order by device_id";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Users!<BR>\n");
	exit();
}
echo("Pick to Device:<SELECT name=\"pickdevice\" onchange=\"document.getdetails.submit\" >\n");
// Fetch the results from the database.
$rcount = 0;
while (($Row2 = ibase_fetch_row($Result))) {
	if ($pickdevice == $Row2[0])
	{
		echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
	}
	else
	{
		if ($pickdevice == "" and $rcount == 0)
		{
			$pickdevice = $Row2[0];
			echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[0]\n");
		}
		else
		{
			echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[0]\n");
		}
	}
	$rcount++;
}
echo("</SELECT>\n");

//release memory
ibase_free_result($Result);
echo ("</TD><TD>\n");
$wk_tot_locns = 0;
if ($pickdevice > "")
{
	$Query = "select count(*) from location where locn_id starting '" . $pickdevice . "' and locn_stat = 'OK'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Locations!<BR>\n");
		exit();
	}
	if (($Row2 = ibase_fetch_row($Result))) {
		$wk_tot_locns = $Row2[0];
	}
	//release memory
	ibase_free_result($Result);
}
echo("Maximum Locations:<Input name=\"totallocations\" type=\"text\" value=\"" . $wk_tot_locns . "\" readonly size=\"3\" >\n");
echo ("</TD></TR>\n");
echo ("<TR><TD>\n");
echo("Waiting Orders:<INPUT name=\"waitingorders\" type=\"text\" value=\"" . $wk_tot_orders . "\" readonly size=\"3\" >\n");
echo ("</TD><TD>\n");
$wk_max_orders = 0;
$wk_max_products = 0;
$Query = "select max_pick_orders, max_pick_products from control ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
if (($Row2 = ibase_fetch_row($Result))) {
	$wk_max_orders = $Row2[0];
	$wk_max_products = $Row2[1];
}
if (!isset($wk_max_orders2))
{
	$wk_max_orders2 = $wk_max_orders;
}
if (!isset($wk_max_products2))
{
	$wk_max_products2 = $wk_max_products;
}
echo("Maximum Orders:<INPUT name=\"maxorders\" type=\"text\" value=\"" . $wk_max_orders2 . "\"  size=\"3\" maxlength=\"3\" onchange=\"return processEdit('". $wk_max_orders . "','" . $wk_max_products . "');\" >\n");
echo ("</TD></TR>\n");
echo ("<TR><TD>\n");
echo("Waiting Products:<INPUT name=\"waitingproducts\" type=\"text\" value=\"" . $wk_tot_products . "\" readonly size=\"3\" >\n");
echo ("</TD><TD>\n");
echo("Maximum Products:<INPUT name=\"maxproducts\" type=\"text\" value=\"" . $wk_max_products2 . "\"  size=\"3\" maxlength=\"3\" onchange=\"return processEdit('" . $wk_max_orders. "','" . $wk_max_products . "');\" >\n");
echo ("</TD></TR>\n");
echo ("</TABLE>\n");
// echo headers

echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" . $wk_message . "\">\n");
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
	whm2buttons("Accept","../mainmenu.php", "N","Back_50x100.gif","Back","accept.gif");
	$alt = "Allocate Orders";
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"AllocateOrders.php\" method=\"post\" onsubmit=\"return processSubmit();\" name=allocateorders>\n");
	echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
	echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
	echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
	echo("<input name=\"pickuser\" type=\"hidden\" value=\"" . $pickuser . "\">");
	echo("<input name=\"allocatedevice\" type=\"hidden\" value=\"" . $allocatedevice . "\">");
	echo("<input name=\"pickdevice\" type=\"hidden\" value=\"" . $pickdevice . "\">");
	echo("<input name=\"maxorders\" type=\"hidden\" value=\"" . $wk_max_orders2 . "\">");
	echo("<input name=\"maxproducts\" type=\"hidden\" value=\"" . $wk_max_products2 . "\">");
	echo("<INPUT type=\"IMAGE\" ");  
	{
/*
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/allocatepicks.gif" alt="' . $alt . '"></INPUT>');
	}
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
