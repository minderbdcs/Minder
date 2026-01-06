<?php
include "../login.inc";
echo "<html>";
echo "<head>";
echo '<link rel=stylesheet type="text/css" href="fromlocn.css">';
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="getorder.css">
<script type="text/javascript">
function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getperson.salesorder.value = myorder; 
  	document.getperson.submit(); 
	return true;
}
</script>

<style type="text/css">
body {
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<?php
/*
go to a screen that has the order no and enter the trolley location to use 
allow upto the number of orders requested .
then can allocate and do the poal

want to enter part or all of an order
then get the pick item lines for the order show status and label and product
get the pack_sscc for each line
show the record_id pick order label_no product sscc status qty shipped qty_ordered.
	sum the qty_shipped and for the line versus qty_ordered

*/

require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include("transaction.php");

echo("</head>\n");
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

include("logme.php");

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$wk_order = "";

if (isset($_POST['matchorders']))
{
	$matchorders = $_POST['matchorders'];
}
if (isset($_GET['matchorders']))
{
	$matchorders = $_GET['matchorders'];
}

$current_pickCmp  = getBDCScookie($Link, $tran_device, "CURRENT_PICK_COMPANY"  );

function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',false);
	$pager->Render(14);

}

echo "<body>";
$wk_max_orders = 0;
$wk_max_products = 0;
$wk_PickRestrictByZone = "F";

echo("<FORM action=\"GetSSCC.Qry.php\" method=\"post\" name=\"getdetails\">\n");
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\"><br>\n");
$got_ssn = 0;
echo ("<table BORDER=\"0\">\n");
echo ("<tr>\n");

echo ("<td colspan=\"2\">\n");
$rcount = 0;

echo ("<tr><td>\n");
//
$wk_zone_allow_as = "F";

/* =================================================================================== */

$wk_tot_locns = 0;
$wk_tot_orders = 0;
$Query = "select count( distinct p2.pick_order)  "; 
	$Query .= "from pick_item p1 ";
	$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";

	//$Query .= "where p1.pick_line_status in ('OP','UP') ";
	$Query .= "where  ";
	$Query .= "  p2.pick_status in ('OP','DA') ";
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query .= "and   p1.pick_order like '%" . $matchorders . "%' ";
		}
	}
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Orders Count!<BR>\n");
	exit();
}
while (($Row3 = ibase_fetch_row($Result))) {
	$wk_tot_orders = $Row3[0];
}

//release memory
ibase_free_result($Result);

/* ==================================================================================== */

// check allowed to pick companys
$wk_pick_allowed_company = array();
$Query = "select options.description  from options where options.group_code='PICK' and options.code starting 'COMPANY'  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options <BR>\n");
}
else
{
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pick_allowed_company []  = $Row[0] ;
	}
}
//release memory
ibase_free_result($Result);



echo ("<tr><td>\n");
//echo("Orders Matching:</td><td><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
echo("Order Matching:</td><td colspan=\"3\"><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
echo( "\"  size=\"15\" maxlength=\"25\"  >\n");
echo ("</td></tr>\n");
echo ("</table>\n");
/*
echo("<INPUT name=\"message\" type=\"text\" size=\"60\" readonly value=\"" );
if (isset($wk_message))
{
	echo($wk_message);
}
echo("\">\n");
*/
//================================================================
		$Query0 = "SELECT po.pick_order";
		$Query0 .= " from pick_order po  ";
		//$Query0 .= " where po.pick_order LIKE  '%" . $matchorders . "%'";
		$Query0 .= " where po.pick_status in ('OP','DA')  " ;
		$Query0 .= " and   po.pick_order LIKE  " ;
		$Query = "SELECT po.pick_order,po.company_id, po.p_country, po.pick_status,";
		$Query .= "po.over_sized, ";
		$Query .= " mer_day(po.create_date) || '/' || mer_month(po.create_date) || '/' || mer_year(po.create_date) || ' ' || mer_hour(po.create_date) || ':' || mer_minute(po.create_date) ";
		$Query .= " , po.address_label_date ";
		$Query .= " , mer_day(po.address_label_date) || '/' || mer_month(po.address_label_date) || '/' || mer_year(po.address_label_date) || ' ' || mer_hour(po.address_label_date) || ':' || mer_minute(po.address_label_date) ";
		$Query .= ",po.net_weight ";
		$Query .= ",po.volume, po.other1 ";
		$Query .= " from pick_order po  ";
		//$Query .= " where po.pick_order = '" . $wk_order . "'";
		$Query .= " where po.pick_order = " ;
//============================================================================
		$Query3 = "SELECT pi.pick_label_no,  pi.prod_id, pi.pick_order_qty, pi.picked_qty, pi.pick_line_status, pi.over_sized,";
		$Query3 .= " mer_day(pi.create_date) || '/' || mer_month(pi.create_date) || '/' || mer_year(pi.create_date) || ' ' || mer_hour(pi.create_date) || ':' || mer_minute(pi.create_date) ";
		$Query3 .= ", pi.pick_pick_finish ";
		$Query3 .= ", mer_day(pi.pick_pick_finish) || '/' || mer_month(pi.pick_pick_finish) || '/' || mer_year(pi.pick_pick_finish) || ' ' || mer_hour(pi.pick_pick_finish) || ':' || mer_minute(pi.pick_pick_finish) ";
		$Query3 .= " ,pi.wh_id,pi.pick_location,pi.user_id,pi.ssn_id ";
		$Query3 .= " from pick_item pi ";
		//$Query3 .= " where pi.pick_order = '" . $wk_order . "'";
		$Query3 .= " where pi.pick_order = " ;
//============================================================================
		$Query4 = "select p3.record_id, p3.ps_pick_label_no, p3.ps_product_gtin, p3.ps_qty_ordered as order_qty,  p3.ps_qty_shipped as shipped_qty, p3.ps_sscc_status, p3.record_id ";
		$Query4 .= ", mer_day(p3.ps_create_date) || '/' || mer_month(p3.ps_create_date) || '/' || mer_year(p3.ps_create_date) || ' ' || mer_hour(p3.ps_create_date) || ':' || mer_minute(p3.ps_create_date) ";
		$Query4 .= ", p3.ps_sscc, p3.ps_out_sscc ";
		$Query4 .= ", p3.ps_awb_consignment_no, p3.ps_out_awb_consignment_no ";
		$Query4 .= ", p3.ps_despatch_id, p3.ps_out_despatch_id ";
		$Query4 .= "from pack_sscc p3 ";
		$Query4 .= "where  p3.ps_pick_label_no =  ";
//============================================================================
	
$Query5 = "select distinct p3.record_id, p2.pick_order as PO, p1.pick_label_no as label, p3.ps_sscc_status, p3.ps_product_gtin as prod , p3.ps_qty_shipped as shipped_qty, p3.ps_qty_ordered as order_qty  "; 
	$Query5 .= "from pick_item p1 ";
	$Query5 .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
	$Query5 .= "join pack_sscc p3 on p1.pick_label_no = p3.ps_pick_label_no ";
	$Query5 .= "where  ";
	//$Query5 .= "  p2.pick_status in ('OP','DA','DX') ";
	$Query5 .= "  p2.pick_status in ('OP','DA') ";
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query5 .= "and   p1.pick_order like '%" . $matchorders . "%' ";
		}
	}
	$Query5 .= "order by p2.pick_order, p1.pick_label_no, p3.record_id ";
//echo($Query5);
//============================================================================
$wkTData = array();
$wkOrderNo = '';
if (isset($matchorders))
{
	if ($matchorders <> "")
	{
		//$wk_query = $Query0;
		$wk_query = $Query0 . "'%" . $matchorders  . "%'";
		$wk_orders_list = array();
		//echo($Query0);
		if (!($Result = ibase_query($Link, $wk_query)))
		{
			echo("Unable to Read Orders!<BR>\n");
			exit();
		}
		$wk_have_data = 0;
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			for ($i=0; $i<ibase_num_fields($Result); $i++)
			{
				if ($i == 0)
				{
					$wkOrderNo = $Row[$i] ;
					$wk_orders_list[$wkOrderNo] = $wkOrderNo;
				}
			}
		}
		//release memory
		ibase_free_result($Result);
print_r($wk_orders_list,true);
		//$wk_order = $matchorders;
		foreach( $wk_orders_list as  $wk_order)
		// do query 1 order
		{
			//$wk_query = $Query;
			$wk_query = $Query . "'" . $wk_order . "'";
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
					echo("<div>\n"); 
					echo ("<table border=\"1\">\n");
				 	echo ("<tr>\n");
					$wk_have_data = 1;
					echo ("<td>Order</td>");
					echo ("<td>Cmp</td>");
					echo ("<td>Cntry</td>");
					echo ("<td>Status</td>");
					echo ("<td>Large</td>");
					echo ("<td>Created</td>");
					echo ("<td>Addr</td>");
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
							echo ("<td class=\"" . $Row[3] . "\">");
							echo ("$Row[$i]</td>\n");
						}
						else
						{
							echo ("<td class=\"" . $Row[3] . "\">");
							echo ("</td>\n");
						}
					}
					if (($i < 6) or ($i > 7))
					{
						echo ("<td class=\"" . $Row[3] . "\">");
						echo ("$Row[$i]</td>\n");
					}
				}
				echo ("</tr>\n");
				echo ("</table>\n");
				echo ("</div>\n");
			}
			//release memory
			ibase_free_result($Result);
			//==========================================================
			// do query 3 lines
			echo ("<div>\n");
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
					//echo ("<tr>\n");
					if ($wk_have_data == 0)
					{
						echo ("<table border=\"1\">\n");
						echo ("<tr>\n");
						$wk_have_data = 1;
						echo ("<td>Split</td>");
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
					} else {
						echo ("<tr>\n");
					}
					// the check boxes space filler
					echo ("<td>");
					echo ("</td>\n");
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
					//echo($wk_query);
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
							if ($j == 0)
							{
								echo ("<td class=\"DSGS\">");
								echo("<input type=\"checkbox\" name=\"reserveme[]\" value=\"$Row4[$j]\" onfocus=\"saveMe('" . $Row4[$j] . "');\"></td>\n");
							} else {
								echo ("<td class=\"" . $Row4[5] . "\">");
								echo ("$Row4[$j]</td>\n");
							}
						}
						echo ("</tr>\n");
						$wkSSNNo = $Row4[1];
					}
					//release memory
					ibase_free_result($Result4);
				}
				//release memory
				ibase_free_result($Result3);
			}
			//=====================================================
			echo ("</table>\n");
			echo ("</div>\n");

		}
	}
}
//==========================================================================
//dopager($Query5);

echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
{
	whm2buttons('GetOrder', 'print_Menu.php', "Y","Back_50x100.gif","Back","nextorder.gif");
}

echo("<FORM action=\"transactionGS.php\" method=\"post\" name=getperson\n>");
echo("<INPUT type=\"hidden\" name=\"salesorder\"> ");  
echo("<INPUT type=\"hidden\" name=\"matchorders\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
echo( "\"  >\n");
echo("</FORM>");
?>
</body>
</html>
