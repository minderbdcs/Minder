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

$got_ssn = 0;

// echo headers
echo ("<TABLE BORDER=\"1\">\n");
echo ("<TR>\n");
echo("<TH>Pick Type</TH>\n");
echo("<TH>Description</TH>\n");
echo ("</TR>\n");

$Query = "select pick_mode_no, parse(description,'.',1), parse(description,'.',2) from pick_mode ";
$Query .= "order by pick_mode_no ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	echo ("<TR>\n");
	{
		echo("<TD>");
		echo("<FORM action=\"ViewInputs.php\" method=\"post\" name=viewnextline>\n");
		echo("<input type=\"hidden\" name=\"pickmode\" value=\"" . $Row[0] . "\"></input>\n");
		echo("<input type=\"submit\" name=\"submit\" value=\"" . trim($Row[1]) . "\"></input>\n");

		echo("</FORM></TD>\n");
		echo("<TD>".$Row[2]."</TD>\n");
	}
}

echo ("</TABLE>\n");
//release memory
ibase_free_result($Result);

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
	$backto = "pick_Menu.php";
	$alt2 = "Back";
	$image2 = "Back_50x100.gif"; 

	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=" . $alt2 . ">\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/' . $image2 . '" alt="' . $alt2 . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");

/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
