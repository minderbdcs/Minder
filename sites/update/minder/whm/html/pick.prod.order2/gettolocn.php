<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<SCRIPT>
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
  {
	return true;
  }
}
</SCRIPT>

<?php
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
function type_desc($typein)
{
	switch($typein)
	{
		case "M":
			return "All";
			break;
		case "I":
			return "SSN";
			break;
		case "D":
			return "Label";
			break;
		case "P":
			return "Product";
			break;
	}
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
	
$type='';
$order='';
$order_no='';
$label='';
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
$original_type = $type;
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if ($type == 'I')
{
	# must the order
	# 1st try ssn
	$Query = "select pick_order, pick_label_no "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " and ssn_id = '".$order."'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Pick Items!<BR>\n");
		exit();
	}
	$got_ssn = 0;
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$got_ssn = 1;
		$order_no = $Row[0];
		$label = $Row[1];
	}
	//release memory
	ibase_free_result($Result);
	if ($got_ssn == 0)
	{
		# try label
		$Query = "select pick_order, pick_label_no "; 
		$Query .= "from pick_item  ";
		$Query .= " where device_id = '".$tran_device."'";
		$Query .= " and pick_line_status = 'PL'";
		$Query .= " and pick_label_no = '".$order."'";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Pick Label Items!<BR>\n");
			exit();
		}
		$got_label = 0;
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$got_label = 1;
			$type = 'D';
			$order_no = $Row[0];
			$label = $Row[1];
		}
		//release memory
		ibase_free_result($Result);
		if ($got_label == 0)
		{
			# try ssn in product
			$Query = "select pi.pick_order, pi.pick_label_no "; 
			$Query .= "from pick_item pi  ";
			$Query .= "join pick_item_detail pd  ";
			$Query .= "on pi.pick_label_no = pd.pick_label_no  ";
			$Query .= " where pi.device_id = '".$tran_device."'";
			$Query .= " and pi.pick_line_status = 'PL'";
			$Query .= " and pd.ssn_id = '".$order."'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read Pick product ssns!<BR>\n");
				exit();
			}
			$got_issn = 0;
			if ( ($Row = ibase_fetch_row($Result)) ) {
				$got_issn = 1;
				$order_no = $Row[0];
				$label = $Row[1];
			}
			//release memory
			ibase_free_result($Result);
			if ($got_issn == 0)
			{
				$got_product = 1;
				$order_no = $order;
				$type = 'P';
			}
		}
	}
}
else
{
	// take all
	$type = 'M';
}

if ($type == 'M')
{
	$Query = "select count(*) "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
}
else
{
	if ($type == 'D')
	{
		$Query = "select count(*) "; 
		$Query .= "from pick_item  ";
		$Query .= " where device_id = '".$tran_device."'";
		$Query .= " and pick_line_status = 'PL'";
		$Query .= " and pick_label_no = '".$order."'";
	}
	else
	{
		if ($type == 'I')
		{
			$Query = "select count(*) "; 
			$Query .= "from pick_item pi ";
			$Query .= "join pick_item_detail pd ";
			$Query .= "on pi.pick_label_no = pd.pick_label_no ";
			$Query .= " where pi.device_id = '".$tran_device."'";
			$Query .= " and pi.pick_line_status = 'PL'";
			$Query .= " and pd.ssn_id = '".$order."'";
		}
		else
		{
			# product type
			$Query = "select count(*) "; 
			$Query .= "from pick_item  ";
			$Query .= " where device_id = '".$tran_device."'";
			$Query .= " and pick_line_status = 'PL'";
			$Query .= " and prod_id = '".$order."'";
		}
	}
}

//echo($Query);

$got_items = 0;
$qtyreqd = 0;
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

if ( ($Row = ibase_fetch_row($Result)) ) {
	$got_items = 1;
	$qtyreqd = $Row[0];
}
//release memory
ibase_free_result($Result);

if ($type == 'M')
{
	$Query = "select despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " order by pick_order";
}
else
{
	if ($type == 'D')
	{
		$Query = "select despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
		$Query .= "from pick_item  ";
		$Query .= " where device_id = '".$tran_device."'";
		$Query .= " and pick_line_status = 'PL'";
		$Query .= " and pick_label_no = '".$order."'";
	}
	else
	{
		if ($type == 'I')
		{
			$Query = "select pi.despatch_location, pi.ssn_id, pi.prod_id, pi.pick_label_no, pi.pick_order "; 
			$Query .= "from pick_item pi ";
			$Query .= "join pick_item_detail pd ";
			$Query .= "on pi.pick_label_no = pd.pick_label_no ";
			$Query .= " where pi.device_id = '".$tran_device."'";
			$Query .= " and pi.pick_line_status = 'PL'";
			$Query .= " and pd.ssn_id = '".$order."'";
		}
		else
		{
			# product type
			$Query = "select despatch_location, ssn_id, prod_id, '', '' "; 
			$Query .= "from pick_item  ";
			$Query .= " where device_id = '".$tran_device."'";
			$Query .= " and pick_line_status = 'PL'";
			$Query .= " and prod_id = '".$order."'";
			$Query .= " group by despatch_location, prod_id, ssn_id ";
		}
	}
}

//echo($Query);
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_items = 0;

//echo("<FONT size=\"2\">\n");
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
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
	if ($Row[1] == "")
	{
		echo("<TD>".$Row[2]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[1]."</TD>\n");
	}
	echo("<TD>".$Row[3]."</TD>\n");
	echo("<TD>".$Row[4]."</TD>\n");
	echo ("</TR>\n");
}

echo ("</TABLE>\n");
echo("<FORM action=\"transactionIL.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");
if ($original_type == 'I')
{
	echo("Despatch Transfer<BR>Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\">");
}
else
{
	echo("Despatch Transfer All<BR>Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\">");
}
// echo(" Type <INPUT type=\"text\" name=\"typedesc\" value=\"" . type_desc($type) . "\" readonly size=\"8\" ><BR>");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"" . $type . "\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label\" >");
echo("<INPUT type=\"hidden\" name=\"qty\" value=\"$qtyreqd\" >");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Despatch/Trolley Location</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo("</FORM>\n");
{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if ($original_type == 'I')
	{
		//whm2buttons('Accept', 'gettoso.php','N');
		whm2buttons('Accept',"gettoso.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettoso.php';\">\n");
*/
	}
	else
	{
		//whm2buttons('Accept', 'gettomethod.php' );
		whm2buttons('Accept',"gettomethod.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
*/
	}
/*
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<SCRIPT>
document.getlocn.location.focus();
</SCRIPT>
</HTML>
