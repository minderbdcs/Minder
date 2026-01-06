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
<script language="JavaScript" src="ts_picker.js">
</script>
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

echo("<FORM action=\"WipDay2.php\" method=\"get\" name=getdetails>\n");
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
echo("\" onchange=\"document.getdetails.workdaychange.value=1\">");
echo("<a href=\"Javascript:show_calendar('document.getdetails.workday', document.getdetails.workday.value);\"><img src=\"cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Click here to POPup the date\"></a>");
echo ("</td></tr>");
echo ("</table>\n");
// despatched in day
$Query = "SELECT po.company_id, po.p_country, count(distinct po.pick_order) from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  where po.pick_status='DX' and pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and pd.pickd_exit <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
table_result($Query, $Query2, "<th>Despatched</th>",  "T" );

// address labeled but not despatched
echo ("<table border=\"1\">\n");
echo ("<tr><th>Addressed</th><th>but Not</th><th>Despatched</th></tr>\n");
if (isset($workday))
{
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po where po.pick_status<>'DX' and po.address_label_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and po.address_label_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) ";
	$Query2 .= "order by po.company_id,po.p_country";

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
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

// weighed but not addressed
echo ("<table border=\"1\">\n");
echo ("<tr><th>Weighed</th><th>but Not</th><th>Addressed</th></tr>\n");
if (isset($workday))
{
	$Query2 = "SELECT po.company_id, po.p_country, po.pick_order from pick_order po join pick_despatch pd on pd.awb_consignment_no = po.pick_order  where (po.pick_status<>'DX' or  pd.pickd_exit >= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) and (po.address_label_date is null or po.address_label_date >  cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp)) ";
	//$Query2 = "SELECT company_id, p_country, pick_order from pick_order where pick_status='DX' ";
	$Query2 .= "order by po.company_id,po.p_country";

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
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
