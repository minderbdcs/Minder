<?php
include "../login.inc";
?>
<html>
<link rel=stylesheet type="text/css" href="getorder.css">
<script type="text/javascript">
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
$wk_location = "";
$wk_orders_list = array();
if (isset($_POST['order']))
{
	$wk_order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$wk_order = $_GET['order'];
}
if (isset($_POST['location']))
{
	$wk_location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$wk_location = $_GET['location'];
}
echo("<FONT size=\"2\">\n");

$got_items = 0;

$wk_from_device = "";

if ($wk_order <> "" )
{
	$wk_orders_list [] = $wk_order;
}
if ($wk_location <> "" and $wk_order == "" )
{
	$wk_wh_id = substr($wk_location,0,2);
	$wk_locn_id =  substr($wk_location,2,strlen($wk_location) - 2);
	$wk_query5 = "
select p1.pick_order 
 from pick_order p1
 join pick_item p2 on  p2.pick_order = p1.pick_order
 join pick_item_detail p3 on p3.pick_label_no = p2.pick_label_no
 join issn i1 on i1.ssn_id=p3.ssn_id
 left outer join pick_location p4 on p4.pick_order = p1.pick_order and p4.pick_location_status in ('OP','DS')
 join control c1 on c1.record_id = 1
 where  p1.pick_status in ('OP','DA')
 and    p2.pick_line_status in  ('PL','PG')
 and p3.pick_detail_status  in ('PL','PG')
 and p3.qty_picked > 0
 and ( ( p4.wh_id = '" . $wk_wh_id . "' and p4.locn_id = '" . $wk_locn_id . "' ) or 
       ( i1.wh_id = '" . $wk_wh_id . "' and i1.locn_id = '" . $wk_locn_id . "' ) )
 group by   p1.pick_order 
	";
	if (!($Result5 = ibase_query($Link, $wk_query5)))
	{
		echo("Unable to Read Locations!<BR>\n");
		exit();
	}
	$wk_have_locn_data = 0;
	while ( ($Row5 = ibase_fetch_row($Result5)) ) 
	{
		$wk_have_locn_data = 1;
		$wk_orders_list [] = $Row5[0];
	}
}

/*

if wk_order is poulated 
then do it 
else
	if len($wk_orders_list) > 0 
		for $wk_order in $wk_orders_list 
			do it
	else
		screen to enter a sales order

*/
//if ($wk_order <> "" )
if (sizeof($wk_orders_list) > 0)
{
	foreach ($wk_orders_list as $wk_key  => $wk_order)
	{
		// have order 
		echo("<form action=\"" . basename($_SERVER['PHP_SELF']) . "\" method=\"post\" name=all>\n");
		if (isset($wk_message))
		{
			//$message = $_GET['message'];
			echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
		}
		// do inputs
		echo ("<table>\n");
		echo("<tr><td>");
		echo("Order:</td><td><input name=\"order\" value=\"$wk_order\" size=\"15\" onfocus=\"ClearOrder();\" >\n");
		echo ("</table>\n");
	
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
		$Query = "SELECT po.pick_order,po.company_id, po.p_country, po.pick_order, po.pick_status,";
		$Query .= "po.over_sized, ";
		$Query .= " mer_day(po.create_date) || '/' || mer_month(po.create_date) || '/' || mer_year(po.create_date) || ' ' || mer_hour(po.create_date) || ':' || mer_minute(po.create_date) ";
		$Query .= " , po.address_label_date ";
		$Query .= " , mer_day(po.address_label_date) || '/' || mer_month(po.address_label_date) || '/' || mer_year(po.address_label_date) || ' ' || mer_hour(po.address_label_date) || ':' || mer_minute(po.address_label_date) ";
		$Query .= ",po.net_weight ";
		$Query .= ",po.volume, po.other1 ";
		$Query .= " from pick_order po  ";
		//$Query .= " where po.pick_order = '" . $wk_order . "'";
		$Query .= " where po.pick_order LIKE  '%" . $wk_order . "%'";
	
		$Query2 = "SELECT  ";
		$Query2 .= "pd.despatch_status ";
		$Query2 .= ",pd.pack_type, ";
		$Query2 .= " mer_day(pd.create_date) || '/' || mer_month(pd.create_date) || '/' || mer_year(pd.create_date) || ' ' || mer_hour(pd.create_date) || ':' || mer_minute(pd.create_date) ";
		$Query2 .= " ,pd.pickd_exit, ";
		$Query2 .= " mer_day(pd.pickd_exit) || '/' || mer_month(pd.pickd_exit) || '/' || mer_year(pd.pickd_exit) || ' ' || mer_hour(pd.pickd_exit) || ':' || mer_minute(pd.pickd_exit) ";
		$Query2 .= ", pd.pickd_wt_actual";
		$Query2 .= " , pd.awb_consignment_no, pd.despatch_id ";
		$Query2 .= " , pd.pickd_carrier_id ";
		$Query2 .= " from pick_despatch pd ";
		//$Query2 .= " where pd.awb_consignment_no = '" . $wk_order . "'";
		$Query2 .= " where pd.despatch_id in (select despatch_id from manifest_order "; 
	
		$Query3 = "SELECT pi.pick_label_no,  pi.prod_id, pi.pick_order_qty, pi.picked_qty, pi.pick_line_status, pi.over_sized,";
		$Query3 .= " mer_day(pi.create_date) || '/' || mer_month(pi.create_date) || '/' || mer_year(pi.create_date) || ' ' || mer_hour(pi.create_date) || ':' || mer_minute(pi.create_date) ";
		$Query3 .= ", pi.pick_pick_finish ";
		$Query3 .= ", mer_day(pi.pick_pick_finish) || '/' || mer_month(pi.pick_pick_finish) || '/' || mer_year(pi.pick_pick_finish) || ' ' || mer_hour(pi.pick_pick_finish) || ':' || mer_minute(pi.pick_pick_finish) ";
		$Query3 .= " ,pi.wh_id,pi.pick_location,pi.user_id,pi.ssn_id ";
		$Query3 .= " from pick_item pi ";
		//$Query3 .= " where pi.pick_order = '" . $wk_order . "'";
		$Query3 .= " where pi.pick_order = " ;
	
		$Query4 = "SELECT  '',pid.ssn_id, '', pid.qty_picked, pid.pick_detail_status, '',";
		$Query4 .= " mer_day(pid.create_date) || '/' || mer_month(pid.create_date) || '/' || mer_year(pid.create_date) || ' ' || mer_hour(pid.create_date) || ':' || mer_minute(pid.create_date) ";
		$Query4 .= ", '' ";
		$Query4 .= " ,pid.from_wh_id,pid.from_locn_id,pid.user_id,pid.despatch_id ,pid.pick_detail_id ";
		$Query4 .= " from pick_item_detail pid ";
		//$Query4 .= " where pid.pick_label_no = " ;
		$Query4 .= " where pid.pick_detail_status <> 'CN' and pid.ssn_id <> 'SSN' and pid.qty_picked > 0 and pid.pick_label_no = " ;

		$Query6 = "SELECT  '','' , '', issn.current_qty, issn.issn_status , '',";
		$Query6 .= " mer_day(issn.create_date) || '/' || mer_month(issn.create_date) || '/' || mer_year(issn.create_date) || ' ' || mer_hour(issn.create_date) || ':' || mer_minute(issn.create_date) ";
		$Query6 .= ", '' ";
		$Query6 .= " ,issn.wh_id,issn.locn_id,issn.user_id,'' ,'' ";
		$Query6 .= " from issn ";
		$Query6 .= " where  issn.ssn_id  = " ;

		$wkOrderNo = '';
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
					//echo("<div id=\"" . $Row[4] . "\">\n"); 
					echo("<div>\n"); 
					echo ("<table border=\"1\">\n");
				 	echo ("<tr>\n");
					$wk_have_data = 1;
					echo ("<td>Order</td>");
					echo ("<td>Cmp</td>");
					echo ("<td>Cntry</td>");
					echo ("<td>Order</td>");
					echo ("<td>Status</td>");
					echo ("<td>Over Sized</td>");
					echo ("<td>Created</td>");
					echo ("<td>Addr Label</td>");
					echo ("<td>Weight</td>");
					echo ("<td>Volume</td>");
					echo ("</tr><tr>\n");
				}
				else
				{
					echo ("<tr>\n");
				}
				for ($i=0; $i<ibase_num_fields($Result); $i++)
				{
					if ($i == 0)
					{
						$wkOrderNo = $Row[$i] ;
					}
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
							echo ("<td class=\"" . $Row[4] . "\">");
							echo ("$Row[$i]</td>\n");
						}
						else
						{
							echo ("<td class=\"" . $Row[4] . "\">");
							echo ("</td>\n");
						}
					}
					if (($i < 6) or ($i > 7))
					{
						echo ("<td class=\"" . $Row[4] . "\">");
						echo ("$Row[$i]</td>\n");
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
			$wk_query = $Query2 . "('" . $wkOrderNo . "'))";
			//echo($Query2);
			if (!($Result2 = ibase_query($Link, $wk_query)))
			{
				echo("Unable to Read Orders!<BR>\n");
				exit();
			}
			$wk_have_data = 0;
			while ( ($Row2 = ibase_fetch_row($Result2)) ) 
			{
				echo ("<tr>\n");
				if ($wk_have_data == 0)
				{
					//echo("<div id=\"" . $Row2[0] . "\">\n"); 
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
					echo ("<td>AWB</td>");
					echo ("<td>Desp</td>");
					echo ("<td>Carrier</td>");
					echo ("</tr><tr>\n");
					echo ("<td></td>");
					echo ("<td></td>");
					echo ("<td></td>");
					echo ("<tr></tr>");
				}
				echo ("<td class=\"" . $Row2[0] . "\"></td>\n");
				echo ("<td class=\"" . $Row2[0] . "\"></td>\n");
				echo ("<td class=\"" . $Row2[0] . "\"></td>\n");
				for ($i=0; $i<ibase_num_fields($Result2); $i++)
				{
					if ($i == 3)
					{
						if ($Row2[$i] == "")
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
							echo ("<td>$Row2[$i]</td>\n");
						}
						else
						{
							echo ("<td></td>\n");
						}
					}
					if (($i < 3) or ($i > 4))
					{
						echo ("<td class=\"" . $Row2[0] . "\">");
						echo ("$Row2[$i]</td>\n");
					}
				}
				echo ("</tr>\n");
			}
			//release memory
			ibase_free_result($Result2);
			echo ("</table>\n");
			//echo("</div>\n"); 
		}
		// do query 3 lines
		{
			//$wk_query = $Query3;
			$wk_query = $Query3 . "'" . $wkOrderNo . "'";
			//echo($Query3);
			if (!($Result3 = ibase_query($Link, $wk_query)))
			{
				echo("Unable to Read Orders!<BR>\n");
				exit();
			}
			$wk_have_data = 0;
			while ( ($Row3 = ibase_fetch_row($Result3)) ) 
			{
				echo ("<tr>\n");
				if ($wk_have_data == 0)
				{
					echo ("<table border=\"1\">\n");
					$wk_have_data = 1;
					echo ("<td>Label</td>");
					echo ("<td>Prod/SSN</td>");
					echo ("<td>Order Qty</td>");
					echo ("<td>Picked Qty</td>");
					echo ("<td>Line Status</td>");
					echo ("<td>Over Sized</td>");
					echo ("<td>Created</td>");
					echo ("<td>Picked</td>");
					echo ("<td>WH</td>");
					echo ("<td>Location</td>");
					echo ("<td>User</td>");
					echo ("<td>Desp</td>");
					echo ("<td>ID</td>");
					echo ("</tr><tr>\n");
				}
				for ($i=0; $i<ibase_num_fields($Result3); $i++)
				{
					if ($i == 1)
					{
						if ($Row3[$i] == "")
						{
							$Row3[$i]  = $Row3[12];
						}
					}
					if ($i == 7)
					{
						if ($Row3[$i] == "")
						{
							$wk_date_null = "T";
						}
						else
						{
							$wk_date_null = "F";
						}
					}
					if ($i == 8)
					{
						if ($wk_date_null == "F")
						{
							echo ("<td class=\"" . $Row3[4] . "\">");
							echo ("$Row3[$i]</td>\n");
						}
						else
						{
							echo ("<td class=\"" . $Row3[4] . "\">");
							echo ("</td>\n");
						}
					}
					if (($i < 7) or (($i > 8) and ($i < 12)))
					{
						echo ("<td class=\"" . $Row3[4] . "\">");
						echo ("$Row3[$i]</td>\n");
					}
				}
				echo ("</tr>\n");
				$wkLabelNo = $Row3[0];
				$wk_query = $Query4 . "'" . $wkLabelNo . "'";
				//echo($Query4);
				if (!($Result4 = ibase_query($Link, $wk_query)))
				{
					echo("Unable to Read PIDs!<BR>\n");
					exit();
				}
				while ( ($Row4 = ibase_fetch_row($Result4)) ) 
				{
					echo ("<tr>\n");
					for ($j=0; $j<ibase_num_fields($Result4); $j++)
					{
						{
							echo ("<td class=\"" . $Row4[4] . "\">");
							echo ("$Row4[$j]</td>\n");
						}
					}
					echo ("</tr>\n");
					$wkSSNNo = $Row4[1];
					if ($wk_location != "")
					{
						$wk_query = $Query6 . "'" . $wkSSNNo . "'";
						if (!($Result6 = ibase_query($Link, $wk_query)))
						{
							echo("Unable to Read ISSNs!<BR>\n");
							exit();
						}
						while ( ($Row6 = ibase_fetch_row($Result6)) ) 
						{
							echo ("<tr>\n");
							for ($j=0; $j<ibase_num_fields($Result6); $j++)
							{
								{
									echo ("<td class=\"" . $Row6[4] . "\">");
									echo ("$Row6[$j]</td>\n");
								}
							}
							echo ("</tr>\n");
						}
						//release memory
						ibase_free_result($Result6);
					}
				}
				//release memory
				ibase_free_result($Result4);
			}
			//release memory
			ibase_free_result($Result3);
		}
	}


	//commit
	ibase_commit($dbTran);
	
	//close
	//ibase_close($Link);
}
else
{
	// no order yet
	echo("<form action=\"" . basename($_SERVER['PHP_SELF']) . "\" method=\"post\" name=all>\n");
	if (isset($wk_message))
	{
		//$message = $_GET['message'];
		echo ("<B><FONT COLOR=RED>$wk_message</FONT></B>\n");
	}
	// do inputs
	echo ("<table>\n");
	echo("<tr><td>");
	echo("Order</td><td><input type=\"text\" name=\"order\" size=\"15\" onfocu=\"ClearOrder();\" ></td></tr>");
}

	echo("<tr>");
	{

		echo("<th>Enter Shipping Order</th>\n");
	}
	echo ("</tr>\n");
	echo ("</table>\n");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	echo ("<td>");
{
	whm2buttons('GetOrder', 'Order_Menu.php', "N","Back_50x100.gif","Back","nextorder.gif","N");
	if ($wk_order  != "")
	{
	
		echo ("<td>");
		$alt = "Close Order";
		echo("<form action=\"closeOrder.php\" method=\"post\" name=closeOrder>\n");
		echo("<INPUT type=\"hidden\" name=\"order\" value=\"$wk_order\">");
		echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
		echo("</form>");
		echo ("</td>");
	}
	echo ("</tr>");
	echo ("</table>");
}
echo("<script type=\"text/javascript\">\n");
if (($wk_order <> "") or (sizeof($wk_orders_list) > 0))
{
}
else
{
	echo "document.all.order.focus();\n";
}
echo "</script>\n";
?>
</html>
