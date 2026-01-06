<html>
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
	$type = 'M';
}

if ($type == 'M')
{
	$Query = "select count(*) "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	//$Query .= " order by pick_order";
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
	$Query = "select first 2 despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
	$Query .= "from pick_item  ";
	$Query .= " where device_id = '".$tran_device."'";
	$Query .= " and pick_line_status = 'PL'";
	$Query .= " order by pick_order";
}
else
{
	if ($type == 'D')
	{
		$Query = "select first 2 despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
		$Query .= "from pick_item  ";
		$Query .= " where device_id = '".$tran_device."'";
		$Query .= " and pick_line_status = 'PL'";
		$Query .= " and pick_label_no = '".$order."'";
	}
	else
	{
		if ($type == 'I')
		{
			$Query = "select first 2 pi.despatch_location, pi.ssn_id, pi.prod_id, pi.pick_label_no, pi.pick_order "; 
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
			$Query = "select first 2 despatch_location, ssn_id, prod_id, pick_label_no, pick_order "; 
			$Query .= "from pick_item  ";
			$Query .= " where device_id = '".$tran_device."'";
			$Query .= " and pick_line_status = 'PL'";
			$Query .= " and prod_id = '".$order."'";
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

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
//echo("<FONT size=\"2\">\n");
echo("<form action=\"transactionIL.php\" method=\"post\" name=getlocn>\n");
// echo headers
echo ("<table BORDER=\"1\">\n");
echo ("<tr>\n");
echo("<th>Location</th>\n");
echo("<th>SSN</th>\n");
echo("<th>Label No</th>\n");
echo("<th>Order</th>\n");
echo ("</tr>\n");

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

echo ("</table>\n");
if ($original_type == 'I')
{
	echo("Despatch Transfer<BR>Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"10\" ><BR>");
}
else
{
	echo("Despatch Transfer All<BR>Into Location<BR><INPUT type=\"text\" name=\"location\" size=\"10\" ><BR>");
}
echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\" >");
echo("<INPUT type=\"hidden\" name=\"order_no\" value=\"$order_no\" >");
echo("<INPUT type=\"hidden\" name=\"label\" value=\"$label\" >");
echo("<INPUT type=\"hidden\" name=\"qty\" value=\"$qtyreqd\" >");
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

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
//echo("</form>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	if ($original_type == 'I')
	{
		echo("<form action=\"gettoso.php\" method=\"post\" name=goback>\n");
	}
	else
	{
		echo("<form action=\"gettomethod.php\" method=\"post\" name=goback>\n");
	}
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
 	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	if ($original_type == 'I')
	{
		whm2buttons('Accept', 'gettoso.php');
/*
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettoso.php';\">\n");
*/
	}
	else
	{
		whm2buttons('Accept', 'gettomethod.php');
/*
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='gettomethod.php';\">\n");
*/
	}
/*
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Scan Despatch Location</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
?>
<script>
document.getlocn.location.focus();
</script>
</html>
