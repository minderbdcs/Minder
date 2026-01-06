<?php
include "../login.inc";
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
$wk_tot_orders = 0;
$wk_tot_products = 0;
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR><TH>Waiting Orders</TH></TR>\n");
echo ("<TR><TH>#</TH>\n");
echo ("<TH>Dev</TH>\n");
echo ("<TH>Cmp</TH>\n");
echo ("<TH>Ctry</TH>\n");
echo ("<TH>Who</TH></TR>\n");
{	
	$Query2 = "SELECT count(distinct pi.pick_order),pi.device_id,po.company_id,po.p_country,se.last_person   ";
	$Query2 .= "from pick_item pi left outer join pick_order po on po.pick_order = pi.pick_order left outer join sys_equip se on se.device_id = pi.device_id  " ;
	$Query2 .= "where pi.pick_line_status in ('AL','PG','PL','OP','UP') and po.pick_status in ('OP','DA') " ;
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person " ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Orders!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<TR><TD>$Row3[0]</TD>\n");
		echo ("<TD>$Row3[1]</TD>\n");
		echo ("<TD>$Row3[2]</TD>\n");
		echo ("<TD>$Row3[3]</TD>\n");
		echo ("<TD>$Row3[4]</TD>\n");
		//if ($Row3[0] == 1)
		{
			$Query3 = "SELECT first 1 pi.pick_order  ";
			//$Query3 .= "from pick_item pi where pi.pick_line_status in ('AL','PG','PL') " ;
			//$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order where pi.pick_line_status in ('AL','PG','PL') " ;
			$Query3 .= "from pick_item pi join pick_order po on po.pick_order = pi.pick_order  " ;
			$Query3 .= "where pi.pick_line_status in ('AL','PG','PL','OP','UP') and po.pick_status in ('OP','DA') " ;
			if ($Row3[1] == "")
			{
				$Query3 .= "and (pi.device_id = '" . $Row3[1] . "' " ;
				$Query3 .= " or pi.device_id is null) " ;
			}
			else
			{
				$Query3 .= "and pi.device_id = '$Row3[1]' " ;
			}
			if ($Row3[2] == "")
			{
				$Query3 .= "and (po.company_id = '" . $Row3[2] . "' " ;
				$Query3 .= " or po.company_id is null) " ;
			}
			else
			{
				$Query3 .= "and po.company_id = '" . $Row3[2] . "' " ;
			}
			if ($Row3[3] == "")
			{
				$Query3 .= "and (po.p_country = '" . $Row3[3] . "' " ;
				$Query3 .= " or po.p_country is null) " ;
			}
			else
			{
				$Query3 .= "and po.p_country = '" . $Row3[3] . "' " ;
			}
			//echo($Query3);
			if (!($Result3 = ibase_query($Link, $Query3)))
			{
				echo("Unable to Read No Orders2!<BR>\n");
			}
			while ( ($Row4 = ibase_fetch_row($Result3)) ) 
			{
				echo ("<TD>$Row4[0]</TD>\n");
			}
			//release memory
			ibase_free_result($Result3);
		}
		echo ("</TR>\n");
	}
	//release memory
	ibase_free_result($Result2);
}
echo ("</TABLE>\n");
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR><TH>Waiting Lines</TH></TR>\n");
echo ("<TR><TH>#</TH>\n");
echo ("<TH>Dev</TH>\n");
echo ("<TH>Cmp</TH>\n");
echo ("<TH>Ctry</TH>\n");
echo ("<TH>Who</TH>\n");
echo ("</TR>\n");
{
	$Query2 = "SELECT count(*),pi.device_id,po.company_id,po.p_country,se.last_person   ";
	//$Query2 .= "from pick_item pi left outer join pick_order po on pi.pick_order = po.pick_order left outer join sys_equip se on se.device_id = pi.device_id WHERE pi.pick_line_status in ('AL','PG','PL') ";
	$Query2 .= "from pick_item pi left outer join pick_order po on pi.pick_order = po.pick_order left outer join sys_equip se on se.device_id = pi.device_id  ";
	$Query2 .= "where pi.pick_line_status in ('AL','PG','PL','OP','UP') and po.pick_status in ('OP','DA') " ;
//	$Query2 .= " AND (NOT pi.prod_id IS NULL)";
	$Query2 .= "group by pi.device_id, po.company_id,po.p_country,se.last_person " ;

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Lines!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<TR><TD>$Row3[0]</TD>\n");
		echo ("<TD>$Row3[1]</TD>\n");
		echo ("<TD>$Row3[2]</TD>\n");
		echo ("<TD>$Row3[3]</TD>\n");
		echo ("<TD>$Row3[4]</TD>\n");
		$Query3 = "select first 1 prod_id from pick_item join pick_order on pick_order.pick_order = pick_item.pick_order where pick_line_status in ('AL','PG','PL','OP','UP')  and (reason is null or reason = '' or reason ='NOT ENOUGH STOCK') ";
		if ($Row3[1] == "")
		{
			// device is null
			$Query3 .= "and device_id is null ";
		}
		else
		{
			// device is not null
			$Query3 .= "and device_id='" . $Row3[1] . "' ";
		}
		if ($Row3[2] == "" )
		{
			// company is null
			$Query3 .= "and pick_order.company_id is null ";
		}
		else
		{
			// company is not null
			$Query3 .= "and pick_order.company_id = '" . $Row3[2] . "' ";
		}
		if ($Row3[3] == "" )
		{
			// country is null
			$Query3 .= "and (pick_order.p_country is null  ";
			$Query3 .= "or pick_order.p_country = '" . $Row3[3] . "') ";
		}
		else
		{
			// country is not null
			$Query3 .= "and pick_order.p_country = '" . $Row3[3] . "' ";
		}
		$Query3 .= "order by pick_line_priority ";
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read No Products!<BR>\n");
		}
		if ( ($Row4 = ibase_fetch_row($Result3)) ) 
		{
			echo ("<TD>$Row4[0]</TD>\n");
		}
		else
		{
			echo ("<TD></TD>\n");
		}
		//release memory
		ibase_free_result($Result3);
		echo ("</TR>\n");
	}
	//release memory
	ibase_free_result($Result2);
}

// want ssn label desc

echo ("</TABLE>\n");
// echo headers

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo total
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Allocate Next Pick\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Allocate Next Pick<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</FORM>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Accept","pick_Menu.php", "Y","Back_50x100.gif","Back","accept.gif");
}
?>
