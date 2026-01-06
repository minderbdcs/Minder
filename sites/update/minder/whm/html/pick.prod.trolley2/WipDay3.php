<?php
header("Content-Type:text/xml");
header("Cache-Control:no-cache");
header("Pragma:no-cache");
require_once 'DB.php';
require 'db_access.php';

if (isset($_POST['workday']))
{
	$workday = $_POST['workday'];
}
if (isset($_GET['workday']))
{
	$workday = $_GET['workday'];
}
if (isset($workday))
{
	//echo ($workday);
	list($work_p1,$work_p2) = explode(" ",$workday);
	list($work_dd, $work_mm, $work_yy) = explode("-", $work_p1);
}
$wk_method = "";
if (isset($_POST['method']))
{
	$wk_method = $_POST['method'];
}
if (isset($_GET['method']))
{
	$wk_method = $_GET['method'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function table_result($Query, $Query2, $wk_title,  $wk_totals )
{
	global $Link,$workday;
	//echo ("<p>Day $workday</p>\n");
	echo ("<table border=\"1\">\n");
	$wk_tr_cnt = 0;
	echo ("<tr>$wk_title</tr>\n");
	if (isset($workday))
	{
		if ($wk_totals == "T")
		{
			$wk_query = $Query;
		}
		else
		{
			$wk_query = $Query2;
		}
		//echo($Query);
		if (!($Result2 = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Despatched Orders!<BR>\n");
			exit();
		}
		$wk_last_company = "";
		$wk_last_country = "";
		$wk_orders_per_line = 10;
		$wk_orders_on_line = 0;
		$wk_first = "T";
		while ( ($Row = ibase_fetch_row($Result2)) ) 
		{
			$wk_nl = 'N';
			$wk_hd = 'N';
			$wk_company = $Row[0];
			$wk_country = $Row[1];
			$wk_order = $Row[2];
			if ($wk_company <> $wk_last_company)
			{
				$wk_last_company = $wk_company;
				$wk_nl = 'Y';
				$wk_hd = 'Y';
			}
			if ($wk_country <> $wk_last_country)
			{
				$wk_last_country = $wk_country;
				$wk_nl = 'Y';
				$wk_hd = 'Y';
			}
			$wk_orders_on_line = $wk_orders_on_line + 1;
			if ($wk_orders_on_line > $wk_orders_per_line)
			{
				$wk_nl = 'Y';
			}
			if ($wk_hd == "Y")
			{
				$wk_orders_on_line = 1;
				if ($wk_first == "T")
				{
					$wk_first = "F";
				}
				else
				{
					if ($wk_totals == "T")
					{
						echo ("<td>$wk_order_tot</td></tr>\n");
					}
					else
					{
						if ($wk_tr_cnt == 1)
						{
							echo ("</tr>\n");
							$wk_tr_cnt = $wk_tr_cnt - 1;
						}
					}
				}
				echo ("<tr><td>$wk_company</td>\n");
				$wk_tr_cnt = $wk_tr_cnt + 1;
				echo ("<td>$wk_country</td>\n");
			}
			if ($wk_nl == "Y")
			{
				$wk_orders_on_line = 1;
				if ($wk_tr_cnt == 1)
				{
					echo ("</tr>");
					$wk_tr_cnt = $wk_tr_cnt - 1;
				}
				//echo ("<tr><td>$wk_order</td>\n");
 				echo ("<tr><td><input type=\"button\" value=\"$wk_order\" onclick=\"ajaxEngine.sendRequest('myOrder','order=$wk_order');\"/></td>\n");
				$wk_tr_cnt = $wk_tr_cnt + 1;
			}
			else
			{
 				echo ("<td><input type=\"button\" value=\"$wk_order\" onclick=\"ajaxEngine.sendRequest('myOrder','order=$wk_order');\"/></td>\n");
				//echo ("<td>$wk_order</td>\n");
			}
		}
		if ($wk_tr_cnt == 1)
		{
			echo ("</tr>\n");
			$wk_tr_cnt = $wk_tr_cnt - 1;
		}
		//release memory
		ibase_free_result($Result2);
	}
	
	//echo ("tableresult:" + $wk_tr_cnt);
	echo ("</table>\n");
}

function table_resultsum($Query, $Query2, $wk_title,  $wk_totals )
{
	global $Link,$workday;
	//echo ("<p>Day $workday</p>\n");
	echo ("<table border=\"1\">\n");
	$wk_tr_cnt = 0;
	echo ("<tr>$wk_title</tr>\n");
	if (isset($workday))
	{
		if ($wk_totals == "T")
		{
			$wk_query = $Query;
		}
		else
		{
			$wk_query = $Query2;
		}
		//echo($Query);
		if (!($Result2 = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Despatched Orders!<BR>\n");
			exit();
		}
		$wk_last_company = "";
		$wk_last_country = "";
		$wk_orders_per_line = 10;
		$wk_orders_on_line = 0;
		$wk_first = "T";
		while ( ($Row = ibase_fetch_row($Result2)) ) 
		{
			$wk_nl = 'N';
			$wk_hd = 'N';
			$wk_company = $Row[0];
			$wk_country = $Row[1];
			$wk_order = $Row[2];
			if ($wk_company <> $wk_last_company)
			{
				$wk_last_company = $wk_company;
				$wk_nl = 'Y';
				$wk_hd = 'Y';
			}
			if ($wk_country <> $wk_last_country)
			{
				$wk_last_country = $wk_country;
				$wk_nl = 'Y';
				$wk_hd = 'Y';
			}
			$wk_orders_on_line = $wk_orders_on_line + 1;
			if ($wk_orders_on_line > $wk_orders_per_line)
			{
				$wk_nl = 'Y';
			}
			if ($wk_hd == "Y")
			{
				$wk_orders_on_line = 1;
				if ($wk_first == "T")
				{
					$wk_first = "F";
				}
				else
				{
					if ($wk_totals == "T")
					{
						echo ("<td>$wk_order_tot</td></tr>\n");
					}
				}
				echo ("<tr><td>$wk_company</td>\n");
				$wk_tr_cnt = $wk_tr_cnt + 1;
				echo ("<td>$wk_country</td>\n");
				$wk_order_tot = 0;
			}
			if ($wk_nl == "Y")
			{
				$wk_orders_on_line = 1;
				if ($wk_totals != "T")
				{
					echo ("</tr>");
				}
				if ($wk_totals == "T")
				{
					$wk_order_tot = $wk_order_tot + $wk_order;
				}
				else
				{
					echo ("<tr><td>$wk_order</td>\n");
					$wk_tr_cnt = $wk_tr_cnt + 1;
				}
			}
			else
			{
				if ($wk_totals == "T")
				{
					$wk_order_tot = $wk_order_tot + $wk_order;
				}
				else
				{
					echo ("<td>$wk_order</td>\n");
				}
			}
		}
		if ($wk_first == "T")
		{
			$wk_first = "F";
		}
		else
		{
			if ($wk_totals == "T")
			{
				echo ("<td>$wk_order_tot</td>\n");
			}
			echo ("</tr>\n");
			$wk_tr_cnt = $wk_tr_cnt - 1;
		}
		//release memory
		ibase_free_result($Result2);
	}
	
	//echo ("tableresultsum:" + $wk_tr_cnt);
	echo ("</table>\n");
}

echo "<ajax-response>
<response type=\"element\" id=\"$wk_method\">";
// weighed but not addressed
 $wk_title = "";
if ($wk_method == "despatched")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
	$Query .= " where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query .= "group by po.company_id,po.p_country ";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query2 .= "order by po.company_id,po.p_country";
}
if ($wk_method == "labeled")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
	$Query .= " where  (pd.pickd_exit is null or pd.pickd_exit > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";  
	$Query .= " and po.address_label_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and po.address_label_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query .= "group by po.company_id,po.p_country";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
	$Query2 .= " where  (pd.pickd_exit is null or pd.pickd_exit > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp))   ";
	$Query2 .= " and po.address_label_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and po.address_label_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query2 .= "order by po.company_id,po.p_country";
}
if ($wk_method == "weighed")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order ";
	$Query .= " where (pd.pickd_exit is null or  pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query .= " and  (pd.create_date < cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query .= " and (po.address_label_date is null or po.address_label_date >  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query .= "group by po.company_id,po.p_country";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order ";
	$Query2 .= " where (pd.pickd_exit is null or  pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 .= " and  (pd.create_date < cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 .= " and (po.address_label_date is null or po.address_label_date >  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 .= "order by po.company_id,po.p_country";
}
if ($wk_method == "picked")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
	$Query .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query .= " and pi.over_sized = 'T' ";
	$Query .= "group by po.company_id,po.p_country,po.pick_order ";
	$Query .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
	$Query .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
	$Query2 .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 .= " and pi.over_sized = 'T' ";
	$Query2 .= "group by po.company_id,po.p_country, po.pick_order ";
	$Query2 .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
	$Query2 .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
}
if ($wk_method == "pickedoversized")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
	$Query .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query .= " and pi.over_sized = 'T' ";
	$Query .= "group by po.company_id,po.p_country,po.pick_order ";
	$Query .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
	$Query .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
	$Query2 .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 .= " and pi.over_sized = 'T' ";
	$Query2 .= "group by po.company_id,po.p_country, po.pick_order ";
	$Query2 .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
	$Query2 .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
}
if ($wk_method == "received")
{
	$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN'  where pi.create_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query .= "group by po.company_id,po.p_country,po.pick_order having (max(pi.pick_pick_finish) is null or max(pi.pick_pick_finish) > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN'  where pi.create_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query2 .= "group by po.company_id,po.p_country,po.pick_order having (max(pi.pick_pick_finish) is null or max(pi.pick_pick_finish) > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	//table_resultsum($Query, $Query2, $wk_title,  "T" );
}
$wk_title=ucwords($wk_method);
table_result($Query, $Query2, $wk_title,  "F" );
echo "</response>
</ajax-response>";
?>
