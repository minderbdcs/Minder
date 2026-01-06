<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	require 'adodb/adodb-pager.inc.php';
	//require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',false);
	$pager->Render(7);

}

$Query = "select count(*) from pick_item p1 ";
$Query .= "join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query .= " where pick_line_status in ('OP','UP')";
$Query .= " and p3.pick_status in ('OP','DA')" ;
$Query2 = "select count(*) from pick_item p1 ";
$Query2 .= "join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query2 .= " where pick_line_status in ('OP','UP')";
$Query2 .= " and p3.pick_status in ('OP','DA')" ;
$Query2 .= " and p1.ssn_id is not null" ;
$Query3 = "select count(*) from pick_item p1 ";
$Query3 .= "join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query3 .= " where pick_line_status in ('OP','UP')";
$Query3 .= " and p3.pick_status in ('OP','DA')" ;
$Query3 .= " and p1.prod_id is not null" ;
//echo($Query);
//$Query .= " where pick_line_status in ('OP','CN')";
//$Query .= " where p1.pick_line_status in ('OP')";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}
if (!($Result2 = ibase_query($Link, $Query2)))
{
	echo("Unable to Read SSN Picks!<BR>\n");
	exit();
}
if (!($Result3 = ibase_query($Link, $Query3)))
{
	echo("Unable to Read Prod Picks!<BR>\n");
	exit();
}


$got_tot = 0;
$got_tot_ssn = 0;
$got_tot_prod = 0;

$wk_select = " ";
// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	for ($i=0; $i<ibase_num_fields($Result); $i++)
	{
		if ($Row[0] == "")
		{
			$got_tot = 0;
		}
		else
		{
			$got_tot = $Row[$i];
		}
	}
}
if ( ($Row = ibase_fetch_row($Result2)) ) {
	for ($i=0; $i<ibase_num_fields($Result2); $i++)
	{
		if ($Row[0] == "")
		{
			$got_tot_ssn = 0;
		}
		else
		{
			$got_tot_ssn = $Row[$i];
		}
	}
}
if ( ($Row = ibase_fetch_row($Result3)) ) {
	for ($i=0; $i<ibase_num_fields($Result3); $i++)
	{
		if ($Row[0] == "")
		{
			$got_tot_prod = 0;
		}
		else
		{
			$got_tot_prod = $Row[$i];
		}
	}
}

//release memory
ibase_free_result($Result);
ibase_free_result($Result2);
ibase_free_result($Result3);

// want ssn label desc
$Query = "select first ".$rxml_limit. " p1.ssn_id, p1.pick_label_no, s2.ssn_type, p2.short_desc, p1.prod_id, p1.pick_order "; 
$Query .= "from pick_item p1 ";
$Query .= "left outer join issn s1 on s1.ssn_id = p1.ssn_id ";
$Query .= "left outer join ssn s2 on s2.ssn_id = s1.original_ssn ";
$Query .= "left outer join prod_profile p2 on p2.prod_id = p1.prod_id ";
$Query .= "left outer join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query .= " where p1.pick_line_status in ('OP','UP')" ;
$Query .= " and p3.pick_status in ('OP','DA')" ;
//echo($Query);
//$Query .= " where p1.pick_line_status in ('OP','CN')" ;
//$Query .= " where p1.pick_line_status in ('OP')" ;
$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.wip_prelocn_ordering, s1.locn_id, p1.pick_location, p1.wip_postlocn_ordering" ;
                
/*
// only want ssn's
$Query = "select p1.ssn_id as ISSN, p1.pick_label_no as Label, case when s2.ssn_type is not null then  s2.ssn_type else s2.ssn_description end  as Description,  p1.pick_order as SalesOrder "; 
$Query .= "from pick_item p1 ";
$Query .= "join issn s1 on s1.ssn_id = p1.ssn_id ";
$Query .= "join ssn s2 on s2.ssn_id = s1.original_ssn ";
$Query .= " join pick_order p3 on p3.pick_order = p1.pick_order ";
$Query .= " where p1.pick_line_status in ('OP','UP')" ;
$Query .= " and p3.pick_status in ('OP','DA')" ;
//echo($Query);
//$Query .= " where p1.pick_line_status in ('OP','CN')" ;
//$Query .= " where p1.pick_line_status in ('OP')" ;
$Query .= " order by p3.pick_priority, p3.wip_ordering, p1.wip_prelocn_ordering, s1.locn_id, p1.pick_location, p1.wip_postlocn_ordering" ;
*/
$rcount = 0;

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Picks!<BR>\n");
	exit();
}

$got_ssn = 0;

// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>SSN</TH>\n");
echo("<TH>Order</TH>\n");
echo("<TH>Label No</TH>\n");
echo("<TH>Description</TH>\n");
echo ("</TR>\n");

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) and ($rcount < $rscr_limit) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	if ($Row[0] <> "")
	{
		echo("<TD>".$Row[0]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[4]."</TD>\n");
	}
	echo("<TD>".$Row[5]."</TD>\n");
	echo("<TD>".$Row[1]."</TD>\n");
	if ($Row[0] <> "")
	{
		echo("<TD>".$Row[2]."</TD>\n");
	}
	else
	{
		echo("<TD>".$Row[3]."</TD>\n");
	}
	echo ("</TR>\n");
	$rcount++;
}

echo ("</TABLE>\n");

//release memory
//ibase_free_result($Result);

//dopager($Query);

//commit
//ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo total
echo("<FORM action=\"transactionAL.php\" method=\"post\" name=getssn>\n");
echo("Total: <INPUT type=\"text\" readonly name=\"total\" size=\"3\" value=\"$got_tot\" >");
echo("SSN: <INPUT type=\"text\" readonly name=\"totalssn\" size=\"3\" value=\"$got_tot_ssn\" >");
echo("Prod: <INPUT type=\"text\" readonly name=\"total\" size=\"3\" value=\"$got_tot_prod\" >");
echo("Allocate: <INPUT type=\"text\" name=\"qty\" size=\"4\" value=\"1\" >");
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
	whm2buttons("Allocate Next Pick","../mainmenu.php");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
