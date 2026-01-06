<?php
include "../login.inc";
?>
<html>
<link rel=stylesheet type="text/css" href="getorder.css">
<script>
function ClearOrder()
{
document.all.order.value = "";
}
</script>
<?php
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
	
$wk_order = "";
if (isset($_POST['order']))
{
	$wk_order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$wk_order = $_GET['order'];
}
echo("<h1>Prepare Order for Address Reprint</h1>\n");
//echo("<FONT size=\"2\">\n");

$got_items = 0;

$got_items = 0;
$wk_from_device = "";

if ($wk_order <> "" )
{
	// have order 
	// change status from ds to dc

	include ("transaction.php");

	$location = $tran_device . ' ';
	$tran_qty = 1;
	$trans_type = "PKUS";
	$my_sublocn = "";
	$tran_tranclass = "L";
	$my_source = 'SSBSSKSSS';
	$my_ref = 'DS|DC|';
	$my_object = $wk_order; /* order to update */

	$my_message = "";
	$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	//echo $my_message;

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	
	// change status from dx to dc
	$my_ref = 'DX|DC|';
	$my_message = "";
	$my_message = dotransaction_response($trans_type, $tran_tranclass, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$my_responsemessage = urldecode($my_mess_label) . " ";
	}
	else
	{
		$my_responsemessage = "";
	}
	//echo $my_message;

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
	

	echo("<FORM action=\"" .  basename($_SERVER["PHP_SELF"])   . "\" method=\"post\" name=all>\n");
	if (isset($wk_message))
	{
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
	}
	// do inputs
	echo ("<TABLE>\n");
	echo("<TR><TD>");
	echo("Order:</TD><TD><INPUT name=\"order\" value=\"$wk_order\" size=\"15\" onfocus=\"ClearOrder();\" >\n");
	echo ("</TABLE>\n");

	/*
	 orders status create date
	 oversized 
	 plao date
	 weighed date
	 despatched date
	 for each line
	   prod order qty 
	   picked qty (picked date)
	   status
	   device
	   user id
	   over sized
	   pick_location
	   wh_id
	   
	*/
	$Query = "SELECT po.company_id, po.p_country, po.pick_order, po.pick_status,";
	$Query .= "po.over_sized, ";
	$Query .= " mer_day(po.create_date) || '/' || mer_month(po.create_date) || '/' || mer_year(po.create_date) || ' ' || mer_hour(po.create_date) || ':' || mer_minute(po.create_date) ";
	$Query .= " , po.address_label_date ";
	$Query .= " , mer_day(po.address_label_date) || '/' || mer_month(po.address_label_date) || '/' || mer_year(po.address_label_date) || ' ' || mer_hour(po.address_label_date) || ':' || mer_minute(po.address_label_date) ";
	$Query .= ",po.net_weight ";
	$Query .= " from pick_order po  ";
	$Query .= " where po.pick_order = '" . $wk_order . "'";
	$Query2 = "SELECT  ";
	$Query2 .= "pd.despatch_status ";
	$Query2 .= ",pd.pack_type, ";
	$Query2 .= " mer_day(pd.create_date) || '/' || mer_month(pd.create_date) || '/' || mer_year(pd.create_date) || ' ' || mer_hour(pd.create_date) || ':' || mer_minute(pd.create_date) ";
	$Query2 .= " ,pd.pickd_exit, ";
	$Query2 .= " mer_day(pd.pickd_exit) || '/' || mer_month(pd.pickd_exit) || '/' || mer_year(pd.pickd_exit) || ' ' || mer_hour(pd.pickd_exit) || ':' || mer_minute(pd.pickd_exit) ";
	$Query2 .= ", pd.pickd_wt_actual";
	$Query2 .= " from pick_despatch pd ";
	$Query2 .= " where pd.awb_consignment_no = '" . $wk_order . "'";
	$Query3 = "SELECT  pi.prod_id, pi.pick_order_qty, pi.picked_qty, pi.pick_line_status, pi.over_sized,";
	$Query3 .= " mer_day(pi.create_date) || '/' || mer_month(pi.create_date) || '/' || mer_year(pi.create_date) || ' ' || mer_hour(pi.create_date) || ':' || mer_minute(pi.create_date) ";
	$Query3 .= ", pi.pick_pick_finish ";
	$Query3 .= ", mer_day(pi.pick_pick_finish) || '/' || mer_month(pi.pick_pick_finish) || '/' || mer_year(pi.pick_pick_finish) || ' ' || mer_hour(pi.pick_pick_finish) || ':' || mer_minute(pi.pick_pick_finish) ";
	$Query3 .= " ,pi.wh_id,pi.pick_location,pi.user_id ";
	$Query3 .= " from pick_item pi ";
	$Query3 .= " where pi.pick_order = '" . $wk_order . "'";
	//echo ("<table border=\"1\">\n");
	// do query 1 order
	{
		$wk_query = $Query;
		//echo($Query);
		if (!($Result = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Orders!<BR>\n");
			exit();
		}
		$wk_have_data = 0;
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			//echo ("<tr>\n");
			if ($wk_have_data == 0)
			{
				echo("<div id=\"" . $Row[3] . "\">\n"); 
				echo ("<table border=\"1\">\n");
			 	echo ("<tr>\n");
				$wk_have_data = 1;
				echo ("<td>Cmp</td>");
				echo ("<td>Cntry</td>");
				echo ("<td>Order</td>");
				echo ("<td>Order Status</td>");
				echo ("<td>Over Sized</td>");
				echo ("<td>Created</td>");
				echo ("<td>Address Label</td>");
				echo ("<td>Expected Weight</td>");
				echo ("</tr><tr>\n");
			}
			else
			{
				echo ("<tr>\n");
			}
			for ($i=0; $i<ibase_num_fields($Result); $i++)
			{
				if ($i == 6)
				{
					if ($Row[$i] == "")
					{
						$wk_date_null = "T";
					}
					else
					{
						$wk_date_null = "F";
					}
				}
				if ($i == 7)
				{
					if ($wk_date_null == "F")
					{
						echo ("<td>$Row[$i]</td>\n");
					}
					else
					{
						echo ("<td></td>\n");
					}
				}
				if (($i < 6) or ($i > 7))
				{
					echo ("<td>$Row[$i]</td>\n");
				}
			}
			echo ("</tr>\n");
			echo ("</table>\n");
			echo ("</div>\n");
		}
		//release memory
		ibase_free_result($Result);
	}
	// do query 2 despatch
	{
		$wk_query = $Query2;
		//echo($Query2);
		if (!($Result = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Orders!<BR>\n");
			exit();
		}
		$wk_have_data = 0;
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			echo ("<tr>\n");
			if ($wk_have_data == 0)
			{
				echo ("<table border=\"1\">\n");
				$wk_have_data = 1;
				echo ("<td></td>");
				echo ("<td></td>");
				echo ("<td></td>");
				echo ("<td>Despatch Status</td>");
				echo ("<td>Pack Type</td>");
				echo ("<td>Weighed</td>");
				echo ("<td>Despatch Exit</td>");
				echo ("<td>Actual Weight</td>");
				echo ("</tr><tr>\n");
				echo ("<td></td>");
				echo ("<td></td>");
				echo ("<td></td>");
			}
			for ($i=0; $i<ibase_num_fields($Result); $i++)
			{
				if ($i == 3)
				{
					if ($Row[$i] == "")
					{
						$wk_date_null = "T";
					}
					else
					{
						$wk_date_null = "F";
					}
				}
				if ($i == 4)
				{
					if ($wk_date_null == "F")
					{
						echo ("<td>$Row[$i]</td>\n");
					}
					else
					{
						echo ("<td></td>\n");
					}
				}
				if (($i < 3) or ($i > 4))
				{
					echo ("<td>$Row[$i]</td>\n");
				}
			}
			echo ("</tr>\n");
			echo ("</table>\n");
		}
		//release memory
		ibase_free_result($Result);
	}
	// do query 3 lines
	{
		$wk_query = $Query3;
		//echo($Query3);
		if (!($Result = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Orders!<BR>\n");
			exit();
		}
		$wk_have_data = 0;
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			echo ("<tr>\n");
			if ($wk_have_data == 0)
			{
				echo ("<table border=\"1\">\n");
				$wk_have_data = 1;
				echo ("<td>Prod</td>");
				echo ("<td>Order Qty</td>");
				echo ("<td>Picked Qty</td>");
				echo ("<td>Line Status</td>");
				echo ("<td>Over Sized</td>");
				echo ("<td>Created</td>");
				echo ("<td>Picked</td>");
				echo ("<td>WH</td>");
				echo ("<td>Location</td>");
				echo ("<td>User</td>");
				echo ("</tr><tr>\n");
			}
			for ($i=0; $i<ibase_num_fields($Result); $i++)
			{
				if ($i == 6)
				{
					if ($Row[$i] == "")
					{
						$wk_date_null = "T";
					}
					else
					{
						$wk_date_null = "F";
					}
				}
				if ($i == 7)
				{
					if ($wk_date_null == "F")
					{
						echo ("<td>$Row[$i]</td>\n");
					}
					else
					{
						echo ("<td></td>\n");
					}
				}
				if (($i < 6) or ($i > 7))
				{
					echo ("<td>$Row[$i]</td>\n");
				}
			}
			echo ("</tr>\n");
		}
		//release memory
		ibase_free_result($Result);
	}

	//commit
	ibase_commit($dbTran);
	
	//close
	ibase_close($Link);
}
else
{
	// no order yet
	echo("<FORM action=\"" .  basename($_SERVER["PHP_SELF"])   . "\" method=\"post\" name=all>\n");
	if (isset($wk_message))
	{
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
	}
	// do inputs
	echo ("<TABLE>\n");
	echo("<TR><TD>");
	echo("Order</TD><TD><INPUT type=\"text\" name=\"order\" size=\"15\" onfocu=\"ClearOrder();\" ></TD></TR>");
}

	echo("<TR>");
	{
		echo("<TH>Enter Sales Order to Change</TH>\n");
	}
	echo ("</TR>\n");
	echo ("</TABLE>\n");
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<TR>");
	echo ("<TD>");
{
	whm2buttons('ChangeOrder', 'pick_Menu.php', "Y","Back_50x100.gif","Back","accept.gif");
}
echo "<script>\n";
if ($wk_order <> "")
{
	//echo "document.forms.all.submit();\n";
	echo "document.all.GetOrder.focus();\n";
}
else
{
	echo "document.all.order.focus();\n";
}
echo "</script>\n";
?>
</html>
