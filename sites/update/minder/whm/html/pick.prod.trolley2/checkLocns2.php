<?php
header("Content-Type:text/xml");
header("Cache-Control:no-cache");
header("Pragma:no-cache");
require_once 'DB.php';
require 'db_access.php';

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo "<ajax-response>
<response type=\"element\" id=\"locnsempty\">";
$Query = "SELECT l1.wh_id, l1.locn_id    ";
$Query .= " from location l1   ";
$Query .= " left outer join pick_location pl on l1.wh_id = pl.wh_id and l1.locn_id = pl.locn_id and pl.pick_location_status in ('OP','DS')   ";
$Query .= " where l1.moveable_locn = 'T' ";
$Query .= " and  pl.record_id is null ";
$Query .= " and (not exists (select issn.ssn_id from issn where issn.wh_id=l1.wh_id and issn.locn_id = l1.locn_id ) )  ";
echo ("<table border=\"1\">\n");
// do query 1 order
{
	$wk_query = $Query;
	//echo($Query);
	if (!($Result = ibase_query($Link, $wk_query)))
	{
		echo("Unable to Read Location!<BR>\n");
		exit();
	}
	$wk_have_data = 0;
	while ( ($Row = ibase_fetch_row($Result)) ) 
	{
		echo ("<tr>\n");
		if ($wk_have_data == 0)
		{
			$wk_have_data = 1;
			echo ("<td>WH</td>");
			echo ("<td>Location</td>");
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
