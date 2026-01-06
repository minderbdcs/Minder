<?php
header("Content-Type:text/xml");
header("Cache-Control:no-cache");
header("Pragma:no-cache");
require_once 'DB.php';
require 'db_access.php';

if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['order']))
{
	$location = $_GET['location'];
}
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo "<ajax-response>
<response type=\"element\" id=\"picked\">";
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
$Query = "SELECT pl.pick_order, pl.wh_id, pl.locn_id, pl.device_id   ";
$Query .= " from pick_location pl   ";
$Query .= " where pl.wh_id  = '" . substr($location,0,2) . "'";
$Query .= " and  pl.locn_id  = '" . substr($location,2,strlen($location) - 2) . "'";
$Query .= " and  pl.pick_location_status  in  ('OP','DS')";
$Query .= " and  pl.device_id <> '" . $tran_device . "'";
echo ("<table border=\"1\">\n");
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
		echo ("<tr>\n");
		if ($wk_have_data == 0)
		{
			$wk_have_data = 1;
			echo ("<td>Order</td>");
			echo ("<td>WH</td>");
			echo ("<td>Location</td>");
			echo ("<td>DEvice</td>");
			echo ("</tr><tr>\n");
		}
		for ($i=0; $i<ibase_num_fields($Result); $i++)
		{
			{
				echo ("<td>$Row[$i]</td>\n");
			}
		}
		echo ("</tr>\n");
	}
	//release memory
	ibase_free_result($Result);
}
	
	
echo ("</table>\n");
echo "</response>
</ajax-response>";
?>
