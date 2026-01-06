<?php
include "../login.inc";
?>
<HTML>
 <HEAD>
  <TITLE>SSN Notes</TITLE>
<?php
include "viewport.php";
{
	echo('<link rel=stylesheet type="text/css" href="notes.css">');
}
?>
 </HEAD>
<?php
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';

$received_ssn_qty = 0;
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_POST['currentssn2']))
{
	$ssn = $_POST['currentssn2'];
}
if (isset($_GET['currentssn2']))
{
	$ssn = $_GET['currentssn2'];
}
if (isset($_POST['currentssn']))
{
	$ssn = $_POST['currentssn'];
}
if (isset($_GET['currentssn']))
{
	$ssn = $_GET['currentssn'];
}
if (isset($_POST['comment']))
{
	$comment = $_POST['comment'];
}
if (isset($_GET['comment']))
{
	$comment = $_GET['comment'];
}
if (isset($_POST['from']))
{
	$from = $_POST['from'];
}
if (isset($_GET['from']))
{
	$from = $_GET['from'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($comment))
{
	//echo "have comment";
	if (trim($comment) == "")
	{
		unset($comment);
	}
}
if (isset($ssn))
{
	$Query = "select issn.original_ssn from issn where issn.ssn_id = '" . $ssn . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$original_ssn = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
if (isset($comment) and isset($ssn) )
{
	{
		$my_source = 'SSBSSKSSS';
		$tran_qty = 0;
		$tran_ref_x = $comment; 
		if (strlen($tran_ref_x) > 70)
		{
			$tran_ref_x = substr($tran_ref_x, 0, 70);
		}
		
		$my_object = sprintf("%20.20s%s", $original_ssn, substr($tran_ref_x,0,10));
		echo( $my_object);
		$location = substr($tran_ref_x,10,10);
		$my_sublocn = substr($tran_ref_x, 20, 10);
		$my_ref = substr($tran_ref_x,30,40) ;
	
		$my_message = "";
		$my_message = dotransaction("NINB", "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		}
		else
		{
			$my_responsemessage = "";
		}
		if ($my_responsemessage <> "Processed successfully ")
		{
			$message .= $my_responsemessage;
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
	}
}
echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<FORM action=\"notes.php\" method=\"post\" name=getnotes>");
if (isset($from))
{
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"$from\" >");
}
if (isset($ssn))
{
	echo("<INPUT type=\"hidden\" name=\"currentssn\" value=\"$ssn\" >");
}

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
echo("<INPUT type=\"text\" name=\"currentssn2\" maxlength=\"20\" size=\"20\" readonly=\"Y\" value=\"$ssn\">\n");
echo("Current Note<textarea name=\"currentcomment\" readonly=\"Y\" rows=\"5\" cols=\"50\">");
if (isset($ssn))
{
	$Query = "select ssn.notes from issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "'";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read SSN!<BR>\n");
		exit();
	}
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$current_comment = $Row[0];
		if ($current_comment <> "")
			ibase_blob_echo($current_comment);
	}
	//release memory
	//ibase_free_result($Result);
}
/*
if (isset($current_comment))
{
	echo($current_comment);
}
*/
echo("</textarea>");
echo("<br>New Note<textarea name=\"comment\" rows=\"2\" cols=\"40\" maxlength=\"78\"></textarea>");
echo("</DIV>\n");
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\" id=\"col1\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"" . $from . "\"  method=\"post\"  name=getssnback>\n");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("<INPUT type=\"hidden\" name=\"currentssn\" value=\"$ssn\">\n");
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");

}
echo("</div>\n");
?>
<SCRIPT>
<?php
{
	echo("document.getnotes.message.value=\"Enter Notes\";\n");
	echo("document.getnotes.comment.focus();\n");
}
	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
	}
		
	//commit
	ibase_commit($dbTran);
?>
</SCRIPT>
</HTML>

