<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
?>
<html>
 <head>
  <title>Sales Orders Processed</title>
 </head>
<script language="JavaScript" src="/whm/includes/ts_picker.js">
</script>
<script src="/whm/includes/prototype.js"></script>
<script src="/whm/includes/rico.js"></script>
<link rel=stylesheet type="text/css" href="WipDay2.css">
<script type="text/javascript">
function callRICO()
{
   ajaxEngine.registerRequest('myOrdersList', 'WipDay3.php');
   ajaxEngine.registerRequest('myOrder', 'WipDay4.php');
   ajaxEngine.registerAjaxElement('despatched'); 
   ajaxEngine.registerAjaxElement('labeled'); 
   ajaxEngine.registerAjaxElement('weighed');
   ajaxEngine.registerAjaxElement('picked');
   ajaxEngine.registerAjaxElement('pickedoversized');
   ajaxEngine.registerAjaxElement('received'); 
   ajaxEngine.registerAjaxElement('order'); 
}
</script>
</head>
<body onload=" callRICO();">
<?php
/*
* want orders despatched DSDX
orders worked on 
orders not completed
*/
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
if (isset($_POST['workday']))
{
	$workday = $_POST['workday'];
}
if (isset($_GET['workday']))
{
	$workday = $_GET['workday'];
}
if (!isset($workday))
{
	$workday = date('d-m-y H:i:s');
}
if (isset($workday))
{
	list($work_p1,$work_p2) = explode(" ",$workday);
	list($work_dd, $work_mm, $work_yy) = explode("-", $work_p1);
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
	echo ("<table border=\"1\">\n");
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
				echo ("<tr><td>$wk_company</td>\n");
				echo ("<td>$wk_country</td>\n");
			}
			if ($wk_nl == "Y")
			{
				$wk_orders_on_line = 1;
				{
					echo ("</tr>");
				}
				echo ("<tr><td>$wk_order</td>\n");
			}
			else
			{
				echo ("<td>$wk_order</td>\n");
			}
		}
		echo ("</tr>\n");
		//release memory
		ibase_free_result($Result2);
	}
	
	echo ("</table>\n");
}

function table_resultsum($Query, $Query2, $wk_title,  $wk_totals )
{
	global $Link,$workday;
	echo ("<table border=\"1\">\n");
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
		}
		//release memory
		ibase_free_result($Result2);
	}
	
	echo ("</table>\n");
}

echo("<FORM action=\"WipDay2.php\" method=\"post\" name=getdetails>\n");
echo("<input type=\"hidden\" name=\"workdaychange\" value=\"0\">");
echo ("<table border=\"1\">\n");
echo ("<tr><th>Request Details</th></tr>\n");
echo("<tr><td>");
echo("Day");
echo(":</td><td><input type=\"text\" name=\"workday\" size=\"20\" maxlength=\"20\" value=\"");
if (isset($workday))
{
	echo("$workday");
}
echo("\" onchange=\"document.forms.getdetails.submit()\">");
echo("<a href=\"Javascript:show_calendar('document.getdetails.workday', document.getdetails.workday.value);\"><img src=\"cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Click here to POPup the date\"></a>");
echo ("</td></tr>");
echo ("</table>\n");
echo("<div id=\"order\"></div>\n");
// despatched in day
 $wk_title = "<th>Despatched</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=despatched');\"/></th>";
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
$Query .= " where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query .= "group by po.company_id,po.p_country ";
$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query2 .= "order by po.company_id,po.p_country";
table_result($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"despatched\"></div>\n");

// address labeled but not despatched
 $wk_title = "<th>Addressed</th><th>but Not</th><th>Despatched</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=labeled');\"/></th>";
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
$Query .= " where  (pd.pickd_exit is null or pd.pickd_exit > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";  
$Query .= " and po.address_label_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and po.address_label_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query .= "group by po.company_id,po.p_country";
$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order  ";
$Query2 .= " where  (pd.pickd_exit is null or pd.pickd_exit > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp))   ";
$Query2 .= " and po.address_label_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and po.address_label_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query2 .= "order by po.company_id,po.p_country";
table_result($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"labeled\"></div>\n");

// weighed but not addressed
 $wk_title = "<th>Weighed</th><th>but Not</th><th>Addressed</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=weighed');\"/></th>";
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
table_result($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"weighed\"></div>\n");

// picked but not weighed
 //$wk_title = "<th>Picked</th><th>but Not</th><th>Weighed</th>";
 $wk_title = "<th>Picked</th><th>but Not</th><th>Weighed</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=picked');\"/></th>";
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
$Query .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query .= " and pi.over_sized = 'F' ";
$Query .= "group by po.company_id,po.p_country,po.pick_order ";
$Query .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
$Query .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order and pi.pick_line_status <> 'CN' ";
$Query2 .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query2 .= " and pi.over_sized = 'F' ";
$Query2 .= "group by po.company_id,po.p_country, po.pick_order";
$Query2 .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
$Query2 .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
//table_result($Query, $Query2, $wk_title,  "T" );
table_resultsum($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"picked\"></div>\n");
// picked but not weighed
 //$wk_title = "<th>Picked</th><th>but Not</th><th>Weighed</th>";
 $wk_title = "<th>Picked</th><th>OverSize</th><th>but Not</th><th>Weighed</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=pickedoversized');\"/></th>";
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN' ";
$Query .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query .= " and pi.over_sized = 'T' ";
$Query .= "group by po.company_id,po.p_country,po.pick_order ";
$Query .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
$Query .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po left outer join pick_despatch pd on pd.awb_consignment_no = po.pick_order and pi.pick_line_status <> 'CN' ";
$Query2 .= " where (pd.create_date is null or  pd.create_date >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
$Query2 .= " and pi.over_sized = 'T' ";
$Query2 .= "group by po.company_id,po.p_country, po.pick_order";
$Query2 .= " having (max(pi.pick_pick_finish) >=  cast('$work_mm/$work_dd/$work_yy' as timestamp)) ";
$Query2 .= " and (max(pi.pick_pick_finish) <=  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
//table_result($Query, $Query2, $wk_title,  "T" );
table_resultsum($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"pickedoversized\"></div>\n");
// received but not picked
// received but not picked
 $wk_title = "<th>Received</th><th>but Not</th><th>Picked</th><th><input type=\"button\" value=\"Get Orders\" onclick=\"ajaxEngine.sendRequest('myOrdersList','workday=' + '" . urlencode($workday) . "','method=received');\"/></th>";
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN'  where pi.create_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query .= "group by po.company_id,po.p_country,po.pick_order having (max(pi.pick_pick_finish) is null or max(pi.pick_pick_finish) > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";

$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_item pi on pi.pick_order = po.pick_order and pi.pick_line_status <> 'CN'  where pi.create_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query2 .= "group by po.company_id,po.p_country,po.pick_order having (max(pi.pick_pick_finish) is null or max(pi.pick_pick_finish) > cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
table_resultsum($Query, $Query2, $wk_title,  "T" );
echo("<div class=\"center\" id=\"received\"></div>\n");

//commit
ibase_commit($dbTran);

//close
ibase_close($Link);

{
	// html 4.0 browser
	echo("<table border=\"0\" align=\"LEFT\">");
	whm2buttons("Accept","pick_Menu.php", "Y","Back_50x100.gif","Back","accept.gif");
}
?>
</body>
</html>
