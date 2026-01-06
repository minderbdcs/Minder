<?php
include "../login.inc";
?>
<html>
 <head>
  <title>View Replenish Status</title>
<?php
include "viewport.php";
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
<body BGCOLOR="#FFFFF0">

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

// my screen id
$wk_replen_screen = 'RP03';

$wk_company = "";
$Query = "select description "; 
	$Query .= "from options ";
	$Query .= "where group_code = 'CMPREPLEN' ";
	$Query .= "and code = '$wk_replen_screen' ";
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Options!<BR>\n");
	exit();
}

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_company = $Row[0] ;
}
//release memory
ibase_free_result($Result);

echo("<h2>Replenish Status</h2>\n");
echo("<FORM action=\"replenish_Menu.php\" method=\"post\" name=getdetails>\n");
$wk_last_priority = 0;
echo ("<TABLE BORDER=\"1\">\n");
/*
echo ("<TR><TH colspan=\"5\">Waiting Products</TH></TR>\n");
echo ("<TR><TH>#</TH>\n");
echo ("<TH>Priority</TH>\n");
echo ("<TH>Dev</TH>\n");
echo ("<TH>User</TH>\n");
echo ("<TH>Prod</TH></TR>\n");
*/
{	
	/* create an array with 
	priority code
	its description
	its start priority
	its end priority
	*/
	$Query1 = "SELECT code, description from options where group_code='REPLENPRIS' ";
	//echo($Query1);
	$wk_prioritys = Array();
	if (!($Result1 = ibase_query($Link, $Query1)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	while ( ($Row1 = ibase_fetch_row($Result1)) ) 
	{
		$wk_code = $Row1[0];
		$wk_desc = $Row1[1];
		$wk_start = 0;
		$wk_end = 9999;
		$Query5 = "SELECT code, description from options where group_code='REPLENPRI' and code in ( '" . $wk_code . "|START','" . $wk_code . "|END') ";
		if (!($Result5 = ibase_query($Link, $Query5)))
		{
			echo("Unable to Read Options2!<BR>\n");
			//exit();
		}
		while ( ($Row5 = ibase_fetch_row($Result5)) ) 
		{
			if ($Row5[0] == ($wk_code . "|START"))
			{
				$wk_start = $Row5[1];
			}
			else
			{
				$wk_end = $Row5[1];
			}
		}
		//release memory
		ibase_free_result($Result5);
		if (array_key_exists($wk_code, $wk_prioritys))
		{
			$wk_priority = $wk_prioritys[$wk_code];
			$wk_priority[1] = $wk_desc;
			$wk_priority[2] = $wk_start;
			$wk_priority[3] = $wk_end;
			$wk_prioritys[$wk_code] = $wk_priority;
		}
		else
		{
			$wk_priority = array();
			$wk_priority[1] = $wk_desc;
			$wk_priority[2] = $wk_start;
			$wk_priority[3] = $wk_end;
			$wk_prioritys[$wk_code] = $wk_priority;
		}
	}
	//release memory
	ibase_free_result($Result1);

	$wk_priority_head = "";
	$Query2 = "SELECT count(distinct pi.prod_id),pi.trn_priority,pi.device_id,se.last_person   ";
	$Query2 .= "from transfer_request pi  " ;
	$Query2 .= "JOIN PROD_PROFILE P2 ON P2.PROD_ID = pi.PROD_ID  AND P2.COMPANY_ID = pi.company_id ";
	$Query2 .= "left outer join sys_equip se on se.device_id = pi.device_id " ;
	//$Query2 .= "where pi.trn_status in ('OP','AL','PG','PL') and (pi.to_locn_id is not null) " ;
	$Query2 .= "where pi.trn_status in ('OP','AL','PG','PL')  " ;
	$Query2 .= "group by pi.trn_priority, pi.device_id, se.last_person " ;
	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read Requests!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		if ($wk_last_priority <> $Row3[1])
		{
			// a new priority
			$wk_last_priority_head = $wk_priority_head;
			/* must select the priority from the table */
			$wk_mypri_code = "";
			$wk_mypri_desc = "";
			$wk_mypri_start = 0;
			$wk_mypri_end = 9999;

			foreach ($wk_prioritys as $Key_results => $Value_results) 
			{
				$wk_this_code = $Key_results;
				$wk_this_desc = $Value_results[1];
				$wk_this_start = $Value_results[2];
				$wk_this_end = $Value_results[3];
				if (($Row3[1] >= $wk_this_start) and ($Row3[1] <= $wk_this_end))
				{
					$wk_mypri_code = $wk_this_code;
					$wk_mypri_desc = $wk_this_desc;
					$wk_mypri_start = $wk_this_start;
					$wk_mypri_end = $wk_this_end;
				}
			}
			$wk_priority_head = $wk_mypri_desc;
			/*
			if ($Row3[1] == 5)
			{
				$wk_priority_head = "Highest";
			}
			elseif ($Row3[1] == 10)
			{
				$wk_priority_head = "Next";
			}
			*/
			$wk_last_priority = $Row3[1];
			if ($wk_last_priority_head <> $wk_priority_head)
			{
				// do the header stuff
				echo ("<TR><TH colspan=\"6\">Waiting Products - $wk_priority_head Priority</TH></TR>\n");
				echo ("<TR><TH>#</TH>\n");
				echo ("<TH>Priority</TH>\n");
				echo ("<TH>Dev</TH>\n");
				echo ("<TH>User</TH>\n");
				echo ("<TH>Prod</TH>\n");
				echo ("<TH>To Zone</TH></TR>\n");
			}
		}
		echo ("<TR><TD>$Row3[0]</TD>\n");
		echo ("<TD>$Row3[1]</TD>\n");
		if ($Row3[2] == "")
		{
			echo ("<TD colspan=\"2\">Unallocated</TD>\n");
		}
		else
		{
			echo ("<TD>$Row3[2]</TD>\n");
			echo ("<TD>$Row3[3]</TD>\n");
		}
		if ($Row3[2] == "")
		{
			$Query3 = "select first 1 prod_id,to_wh_id,zone_c from transfer_request where device_id is null and trn_status in ('OP')  and trn_priority = '" . $Row3[1] . "' ";
		}
		else
		{
			$Query3 = "select first 1 prod_id,to_wh_id,zone_c from transfer_request where device_id='" . $Row3[2] . "' and trn_status in ('AL','PG','PL') order by trn_priority ";
		}
		if (!($Result3 = ibase_query($Link, $Query3)))
		{
			echo("Unable to Read No Products!<BR>\n");
		}
		if ( ($Row4 = ibase_fetch_row($Result3)) ) 
		{
			if ($Row3[2] == "")
			{
				echo ("<TD>$Row4[0] ...</TD>\n");
				echo ("<TD>$Row4[1] $Row4[2] ...</TD>\n");
			}
			else
			{
				echo ("<TD>$Row4[0]</TD>\n");
				echo ("<TD>$Row4[1] $Row4[2]</TD>\n");
			}
		}
		else
		{
			//echo ("<TD></TD>\n");
			echo ("<TD colspan=\"2\">Unallocated</TD>\n");
		}
		//release memory
		ibase_free_result($Result3);
		echo ("</TR>\n");
	}
	//release memory
	ibase_free_result($Result2);
}
echo ("</TABLE>\n");
echo ("<TABLE BORDER=\"1\">\n");
//echo ("<TR><TH colspan=\"4\">Waiting Return Products</TH></TR>\n");
echo ("<TR><TH colspan=\"4\">Over Supply Products</TH></TR>\n");
echo ("<TR><TH>#</TH>\n");
echo ("<TH>Dev</TH>\n");
echo ("<TH>User</TH>\n");
echo ("<TH>Prod</TH></TR>\n");
{
	//$Query2 = "SELECT count(distinct pi.prod_id),pi.device_id,se.last_person   ";
	$Query2 = "SELECT sum(i1.current_qty), pi.device_id,se.last_person ,pi.prod_id  ";
	$Query2 .= "from transfer_request pi  " ;
	$Query2 .= "JOIN PROD_PROFILE P2 ON P2.PROD_ID = pi.PROD_ID ";
	$Query2 .= "AND P2.COMPANY_ID = pi.company_id ";
	$Query2 .= "left outer join sys_equip se on se.device_id = pi.device_id  " ;
	$Query2 .= "join issn i1 on pi.prod_id = i1.prod_id and pi.company_id = i1.company_id and pi.to_wh_id = i1.wh_id  and i1.issn_status in ('PA','ST') and exists (  " ;
	//$Query2 .= " select l1.locn_id from location l1 where  pi.to_wh_id  = l1.wh_id and i1.locn_id = l1.locn_id  and l1.store_type = 'DS' ) " ;
	$Query2 .= " select l1.locn_id from location l1 where  pi.to_wh_id  = l1.wh_id and i1.locn_id = l1.locn_id  and l1.zone_c  = pi.zone_c ) " ;

	//$Query2 .= "where pi.trn_status in ('OP','AL','PG','PL') and (pi.from_locn_id is not null) " ;
	$Query2 .= "where pi.trn_status in ('AL','PG','PL')  " ;
/*
       SELECT SUM( ISSN.CURRENT_QTY ) ^M
         FROM ISSN^M
         JOIN LOCATION ON ISSN.WH_ID = LOCATION.WH_ID AND ISSN.LOCN_ID = LOCATION.LOCN_ID  AND LOCATION.STORE_TYPE = 'DS'
         WHERE ISSN.PROD_ID = pi.prod_id^M
            AND ISSN.COMPANY_ID = pi.company_id^M
            AND ISSN.WH_ID = pi.to_wh_id ^M
            AND ISSN.ISSN_STATUS IN ( 'PA','ST')
*/

	//$Query2 .= "group by pi.device_id, se.last_person " ;
	$Query2 .= "group by pi.device_id, se.last_person, pi.prod_id " ;

	//echo($Query2);
	if (!($Result2 = ibase_query($Link, $Query2)))
	{
		echo("Unable to Read No Products!<BR>\n");
		exit();
	}
	while ( ($Row3 = ibase_fetch_row($Result2)) ) 
	{
		echo ("<TR><TD>$Row3[0]</TD>\n");
		echo ("<TD>$Row3[1]</TD>\n");
		echo ("<TD>$Row3[2]</TD>\n");
/*
		//$Query3 = "select first 1 prod_id from transfer_request where device_id='" . $Row3[1] . "' and trn_status in ('AL','PG','PL')  and (from_locn_id is not null) order by pick_line_priority ";
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
*/
		echo ("<TD>$Row3[3]</TD>\n");
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
//echo("</FORM>\n");
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	whm2buttons("Accept","replenish_Menu.php", "Y","Back_50x100.gif","Back","accept.gif");
}
?>
</body>
</html>
