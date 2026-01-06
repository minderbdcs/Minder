<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Other Address Labels</title>
</head>
<?php
require_once 'DB.php';
require 'db_access.php';
$instruction1 = "";
$instruction2 = "";
if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['person']))
{
	$person = $_POST['person'];
}
if (isset($_GET['person']))
{
	$person = $_GET['person'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
if (!isset($printer))
{
	$printer = "PC";
}
if (isset($_POST['addressfrom']))
{
	$addressfrom = $_POST['addressfrom'];
}
if (isset($_GET['addressfrom']))
{
	$addressfrom = $_GET['addressfrom'];
}
if (isset($_POST['qty']))
{
	$qty = $_POST['qty'];
}
if (isset($_GET['qty']))
{
	$qty = $_GET['qty'];
}
if (!isset($qty))
{
	$qty = 1;
}
if (isset($_POST['instruction1']))
{
	$instruction1 = $_POST['instruction1'];
}
if (isset($_GET['instruction1']))
{
	$instruction1 = $_GET['instruction1'];
}
if (isset($_POST['instruction2']))
{
	$instruction2 = $_POST['instruction2'];
}
if (isset($_GET['instruction2']))
{
	$instruction2 = $_GET['instruction2'];
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select load_prefix from control ";
//echo($Query);

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Control !<BR>\n");
	exit();
}

//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);
echo("<body>\n");
echo("<FONT size=\"2\">\n");
if (isset($person) and isset($addressfrom) )
{
	echo("<FORM action=\"transactionAO.php\" method=\"post\" name=getlabel\n>");
}
else
{
	echo("<FORM action=\"getaddrother.php\" method=\"post\" name=getlabel\n>");
}

if (isset($person))
{
	echo("Contact <INPUT type=\"text\" name=\"person\" readonly size=\"10\" value=\"$person\"><BR>");
}
else
{
	//$Query = "select person_id,contact_first_name,contact_last_name from person where person_type in ( 'CS', 'CR', 'RP', 'LE') order by last_name, first_name ";
	$Query = "select person_id,contact_first_name,contact_last_name from person where (person_type in ('RP', 'LE') or person_type starting 'C') order by last_name, first_name ";
	//echo($Query);

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Get Contact for Choice!<BR>\n");
		exit();
	}

	echo("Contact:<SELECT name=\"person\" size=\"1\" >\n");

	while ( ($Row = ibase_fetch_row($Result)) ) {
		echo( "<OPTION value=\"$Row[0]\">$Row[0] $Row[1] $Row[2]\n");
	}
	echo("</SELECT>\n");
}
if (isset($person))
{
	if (isset($addressfrom))
	{
		echo("Address From <INPUT type=\"text\" name=\"addressfrom\" readonly size=\"1\" value=\"$addressfrom\">");
		
		echo("<BR>Instructions<BR><INPUT type=\"text\" name=\"instruction1\" size=\"40\" maxlength=\"40\" value=\"$instruction1\"></BR>");
		echo("<INPUT type=\"text\" name=\"instruction2\" size=\"37\" maxlength=\"37\" value=\"$instruction2\"></BR>");
		echo("Printer <INPUT type=\"text\" name=\"printer\" size=\"2\" value=\"$printer\"></BR>");
		echo("Qty <INPUT type=\"text\" name=\"qty\" size=\"3\" value=\"$qty\"></BR>");
	}
	else
	{
		echo("Address From:<SELECT name=\"addressfrom\" size=\"1\" >\n");

		echo( "<OPTION value=\"M\" SELECTED>Contacts Mail Address\n");
		echo( "<OPTION value=\"S\">Site Address\n");
		echo("</SELECT>\n");
	}
}

echo ("<TABLE>\n");
echo ("<TR>\n");
if (isset($person))
{
	if(isset($addressfrom))
	{
		echo("<TH>Enter Instructions ,Printer and Qty for Print</TH>\n");
	}
	else
	{
		echo("<TH>Select Address Type to Use</TH>\n");
	}
}
else
{
	echo("<TH>Select Contact for Print</TH>\n");
}
echo ("</TR>\n");
echo ("</TABLE>\n");
// Create a table.
echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
echo ("<TR>");
echo ("<TD>");
echo("<INPUT type=\"IMAGE\" ");  
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	if (isset($person))
	{
		echo("<INPUT type=\"submit\" name=\"print\" value=\"Accept\">\n");
	}
	else
	{
		echo("<INPUT type=\"submit\" name=\"submit\" value=\"Accept\">\n");
	}
}
else
*/
{
	if (isset($person) and isset($addressfrom) )
	{
		echo('SRC="/icons/whm/Print_50x100.gif" alt="Print"></INPUT>');
/*
		echo("<BUTTON name=\"print\" value=\"Print\" type=\"submit\" >\n");
		echo("Print<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
	else
	{
		$alt = "Accept";
/*
		echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
		echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '"></INPUT>');
/*
		echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
		echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
*/
	}
}
echo("</FORM>\n");
echo ("</TD>");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	$backto = "./despatch_menu.php";
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
if (!isset($person) )
{
	//release memory
	ibase_free_result($Result);

	//commit
	//ibase_commit($dbTran);
}

//close
//ibase_close($Link);

echo("<script type=\"text/javascript\">\n");
{
	if (isset($person))
	{
		if (isset($addressfrom))
		{
			echo("document.getlabel.instructions.focus();\n");
		}
		else
		{
			echo("document.getlabel.addressfrom.focus();\n");
		}
	}
	else
	{
		echo("document.getlabel.person.focus();\n");
	}
}
?>
</script>
</html>
