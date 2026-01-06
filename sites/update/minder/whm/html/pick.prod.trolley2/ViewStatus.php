<?php
include "../login.inc";
?>
<html>
<head>
<?php
include "viewport.php";
?>
<title>Pick From Location</title>
<style type="text/css">
body {
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: 0.8em;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
</head>
<body>

<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
?>
<?php
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

echo("<FORM action=\"pick_Menu.php\" method=\"post\" name=getdetails>\n");
echo ("<table BORDER=\"1\">\n");
echo ("<tr><th>Waiting Orders</th></tr>\n");
echo ("<tr><th>#</th>\n");
echo ("<th>Dev</th>\n");
echo ("<th>Cmp</th>\n");
echo ("<th>Ctry</th>\n");
//echo ("<th>Who</th></tr>\n");
echo ("<th>Who</th>\n");
echo ("<th>Zone</th>\n");
echo ("<th>Sub</th></tr>\n");
{	
// check allowed to pick companys
$wk_pick_allowed_company = array();
$Query = "select options.description  from options where options.group_code='PICK' and options.code = 'VIEWSTATUS'  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options <BR>\n");
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pick_allowed_company []  = $Row[0] ;
	}
}
//release memory
ibase_free_result($Result);

	$Query2 = "SELECT count(distinct pi.pick_order),pi.device_id,po.company_id,po.p_country,se.last_person   ";
	//$Query2 .= "from pick_item pi left outer join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('OP','AL','PG','PL') " ;
	$Query2 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('OP','AL','PG','PL','Al','Pg','Pl') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person " ;
	$Query2 = "SELECT count(distinct pi.pick_order),pi.device_id,po.company_id,po.p_country,se.last_person ,po.zone_c, po.pick_order_sub_type  ";
	$Query2 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('OP','AL','PG','PL','Al','Pg','Pl','UP') " ;
	$Query2 .= "and po.pick_status in ('OP','DA') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person , po.zone_c, po.pick_order_sub_type " ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Orders!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<tr><td>$Row3[0]</td>\n");
		echo ("<td>$Row3[1]</td>\n");
		echo ("<td>$Row3[2]</td>\n");
		echo ("<td>$Row3[3]</td>\n");
		echo ("<td>$Row3[4]</td>\n");
		echo ("<td>$Row3[5]</td>\n");
		echo ("<td>$Row3[6]</td>\n");
		// if "VIEWORDER" not in $wk_pick_allowed_company
		$wk_posn = strpos( implode(",",$wk_pick_allowed_company),"NO VIEWORDER");
		//if ($Row3[0] == 1)
		if ($wk_posn === False)
		{
			$Query3 = "SELECT first 1 pi.pick_order  ";
			//$Query3 .= "from pick_item pi where pi.pick_line_status in ('AL','PG','PL') " ;
			//$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order where pi.pick_line_status in ('AL','PG','PL','OP') " ;
			//$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order where pi.pick_line_status in ('AL','PG','PL','OP','Al','Pg','Pl') " ;
			$Query3 .= "from pick_order po join pick_item pi  on po.pick_order = pi.pick_order where po.pick_status in ( 'DA','OP') and pi.pick_line_status in ('AL','PG','PL','OP','Al','Pg','Pl','UP') " ;
			if (is_null($Row3[1])) {
				$Query3 .= "and pi.device_id is null " ;
			} else {
				$Query3 .= "and pi.device_id = '$Row3[1]' " ;
			}
			$Query3 .= "and po.company_id = '" . $Row3[2] . "' " ;
			if (is_null($Row3[3])) {
				$Query3 .= "and po.p_country is null " ;
			} else {
				$Query3 .= "and po.p_country = '" . $Row3[3] . "' " ;
			}
			if (is_null($Row3[5])) {
				$Query3 .= "and po.zone_c    is null " ;
			} else {
				$Query3 .= "and po.zone_c    = '" . $Row3[5] . "' " ;
			}
			if (is_null($Row3[6])) {
				$Query3 .= "and po.pick_order_sub_type is null " ;
			} else {
				$Query3 .= "and po.pick_order_sub_type   = '" . $Row3[6] . "' " ;
			}
			$Query3 .= "order by  po.pick_due_date, po.pick_priority " ;
			//echo($Query3);
			if (!($Result3 = ibase_query($Link, $Query3)))
			{
				echo("Unable to Read No Orders!<BR>\n");
			}
			while ( ($Row4 = ibase_fetch_row($Result3)) ) 
			{
				echo ("<td>$Row4[0]</td>\n");
			}
			//release memory
			ibase_free_result($Result3);
		}
		echo ("</tr>\n");
	}
	//release memory
	ibase_free_result($Result2);
}
echo ("</table>\n");
  
echo ("<table BORDER=\"1\">\n");
echo ("<tr><th>No Stock Orders</th></tr>\n");
echo ("<tr><th>#</th>\n");
echo ("<th>Dev</th>\n");
echo ("<th>Cmp</th>\n");
echo ("<th>Ctry</th>\n");
//echo ("<th>Who</th></tr>\n");
echo ("<th>Who</th>\n");
echo ("<th>Zone</th>\n");
echo ("<th>Sub</th></tr>\n");
{	
// check allowed to pick companys
$wk_pick_allowed_company = array();
$Query = "select options.description  from options where options.group_code='PICK' and options.code = 'VIEWSTATUS'  "; 
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options <BR>\n");
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_pick_allowed_company []  = $Row[0] ;
	}
}
//release memory
ibase_free_result($Result);

	$Query2 = "SELECT count(distinct pi.pick_order),pi.device_id,po.company_id,po.p_country,se.last_person   ";
	//$Query2 .= "from pick_item pi left outer join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('OP','AL','PG','PL') " ;
	$Query2 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('AS','HD') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person " ;
	$Query2 = "SELECT count(distinct pi.pick_order),pi.device_id,po.company_id,po.p_country,se.last_person, po.zone_c, po.pick_order_sub_type   ";
	$Query2 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id where pi.pick_line_status in ('AS','HD') " ;
	$Query2 .= "and po.pick_status in ('OP','DA') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person, po.zone_c, po.pick_order_sub_type " ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Orders!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<tr><td>$Row3[0]</td>\n");
		echo ("<td>$Row3[1]</td>\n");
		echo ("<td>$Row3[2]</td>\n");
		echo ("<td>$Row3[3]</td>\n");
		echo ("<td>$Row3[4]</td>\n");
		echo ("<td>$Row3[5]</td>\n");
		echo ("<td>$Row3[6]</td>\n");
		// if "VIEWORDER" not in $wk_pick_allowed_company
		$wk_posn = strpos( implode(",",$wk_pick_allowed_company),"NO VIEWORDER");
		//if ($Row3[0] == 1)
		if ($wk_posn === False)
		{
			$Query3 = "SELECT first 1 pi.pick_order  ";
			//$Query3 .= "from pick_item pi where pi.pick_line_status in ('AL','PG','PL') " ;
			//$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order where pi.pick_line_status in ('AL','PG','PL','OP') " ;
			//$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order where pi.pick_line_status in ('AS','HD') " ;
			$Query3 .= "from pick_order po join pick_item pi  on po.pick_order = pi.pick_order where po.pick_status in ( 'DA','OP') and pi.pick_line_status in ('AS','HD') " ;
			if (is_null($Row3[1])) {
				$Query3 .= "and pi.device_id is null " ;
			} else {
				$Query3 .= "and pi.device_id = '$Row3[1]' " ;
			}
			$Query3 .= "and po.company_id = '" . $Row3[2] . "' " ;
			if (is_null($Row3[3])) {
				$Query3 .= "and po.p_country is null " ;
			} else {
				$Query3 .= "and po.p_country = '" . $Row3[3] . "' " ;
			}
			if (is_null($Row3[5])) {
				$Query3 .= "and po.zone_c    is null " ;
			} else {
				$Query3 .= "and po.zone_c    = '" . $Row3[5] . "' " ;
			}
			if (is_null($Row3[6])) {
				$Query3 .= "and po.pick_order_sub_type is null " ;
			} else {
				$Query3 .= "and po.pick_order_sub_type   = '" . $Row3[6] . "' " ;
			}
			$Query3 .= "order by  po.pick_due_date, po.pick_priority " ;
			//echo($Query3);
			if (!($Result3 = ibase_query($Link, $Query3)))
			{
				echo("Unable to Read No Orders!<BR>\n");
			}
			while ( ($Row4 = ibase_fetch_row($Result3)) ) 
			{
				echo ("<td>$Row4[0]</td>\n");
			}
			//release memory
			ibase_free_result($Result3);
		}
		echo ("</tr>\n");
	}
	//release memory
	ibase_free_result($Result2);
}
echo ("</table>\n");

echo ("<table BORDER=\"1\">\n");
echo ("<tr><th>Waiting Lines</th></tr>\n");
echo ("<tr><th>#</th>\n");
echo ("<th>Dev</th>\n");
echo ("<th>Cmp</th>\n");
echo ("<th>Ctry</th>\n");
echo ("<th>Who</th>\n");
echo ("<th>Zone</th>\n");
echo ("<th>Sub</th>\n");
echo ("</tr>\n");
{
	$Query2 = "SELECT count(*),pi.device_id,po.company_id,po.p_country,se.last_person   ";
	//$Query2 .= "from pick_item pi left outer join pick_order po on pi.pick_order = po.pick_order left outer join sys_equip se on se.device_id = pi.device_id WHERE pi.pick_line_status in ('AL','PG','PL','OP') ";
	$Query2 .= "from pick_item pi left outer join pick_order po on pi.pick_order = po.pick_order left outer join sys_equip se on se.device_id = pi.device_id WHERE pi.pick_line_status in ('AL','PG','PL','OP','Al','Pg','Pl') ";
//	$Query2 .= " AND (NOT pi.prod_id IS NULL)";
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person " ;
	$Query2 = "SELECT count(*),pi.device_id,po.company_id,po.p_country,se.last_person, po.zone_c, po.pick_order_sub_type   ";
	$Query2 .= "from pick_item pi left outer join pick_order po on pi.pick_order = po.pick_order left outer join sys_equip se on se.device_id = pi.device_id WHERE pi.pick_line_status in ('AL','PG','PL','OP','Al','Pg','Pl') ";
	$Query2 .= "and po.pick_status in ('OP','DA') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person, po.zone_c, po.pick_order_sub_type " ;

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Lines!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<tr><td>$Row3[0]</td>\n");
		echo ("<td>$Row3[1]</td>\n");
		echo ("<td>$Row3[2]</td>\n");
		echo ("<td>$Row3[3]</td>\n");
		echo ("<td>$Row3[4]</td>\n");
		echo ("<td>$Row3[5]</td>\n");
		echo ("<td>$Row3[6]</td>\n");
		echo ("</tr>\n");
	}
	//release memory
	ibase_free_result($Result2);
}

// want ssn label desc

echo ("</table>\n");
// echo headers

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

{
	// html 4.0 browser
	echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Accept","pick_Menu.php", "Y","Back_50x100.gif","Back","accept.gif");
}
?>
</body>
</html>
