<?php
include "../login.inc";
if (isset($_COOKIE['BDCSData']))
{
	list($orig_reference, $orig_location, $orig_ssn) = explode("|", $_COOKIE["BDCSData"]);
	if (isset($_POST['ssn'])) 
	{
		$ssn = $_POST['ssn'];
	}
	if (isset($_GET['ssn'])) 
	{
		$ssn = $_GET['ssn'];
	}
	if ($orig_ssn != $ssn)
	{
		// ssn changed so go back
		$header_line = "Location: GetSSN.php?reference=";
		if (isset($_POST['reference'])) 
		{
			$header_line .= $_POST['reference'];
		}
		if (isset($_GET['reference'])) 
		{
			$header_line .= $_GET['reference'];
		}
		$header_line .= "&location=";
		if (isset($_POST['location'])) 
		{
			$header_line .= $_POST['location'];
		}
		if (isset($_GET['location'])) 
		{
			$header_line .= $_GET['location'];
		}
		$header_line .= "&ssn=";
		if (isset($_POST['ssn'])) 
		{
			$header_line .= $_POST['ssn'];
		}
		if (isset($_GET['ssn'])) 
		{
			$header_line .= $_GET['ssn'];
		}
		setcookie("BDCSData","", time()+1186400, "/");
		//$header_line .= "&cook=".$_COOKIE["BDCSData"];
		header ($header_line);
	}
}
else
{
	// 1st time in screen
	// save original fields
	$cookiedata = "";
	if (isset($_POST['reference'])) 
	{
		$cookiedata .= $_POST['reference'];
	}
	if (isset($_GET['reference'])) 
	{
		$cookiedata .= $_GET['reference'];
	}
	$cookiedata .= '|';
	if (isset($_POST['location'])) 
	{
		$cookiedata .= $_POST['location'];
	}
	if (isset($_GET['location'])) 
	{
		$cookiedata .= $_GET['location'];
	}
	$cookiedata .= '|';
	if (isset($_POST['ssn'])) 
	{
		$cookiedata .= $_POST['ssn'];
	}
	if (isset($_GET['ssn'])) 
	{
		$cookiedata .= $_GET['ssn'];
	}
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
}
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Get the Description you are applying to the SSN</title>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
/*
	still to do
	scanning of type to change the labels used when entering data
	use trans_class
	use sources ???
	interprete location and ssn in description
	changing of ssn and location fields
		(ssn means must auob - )
		currently on change of ssn go to previous screen
		enforces an auob when reneter screen
	scanning of products
		- qtys
	use trans_qtys for products
	scanning of pr ssn's (ie ask for qty) still 'A' class
*/
// Set the variables for the database access:
require_once('DB.php');
require('db_access.php');
include "2buttons.php";

$tran_tranclass = "A";
$tran_qty = 1;
// get the sum qty
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function getcurrent($ssn)
{
	global $Link, $dbTran;
	$Query = "select issn.wh_id, issn.locn_id, ssn.purchase_price, ssn.other17_qty, issn.current_qty FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
		$current_price = $Row[2] ;
		$current_oqty = $Row[3] ;
		$current_qty = $Row[4] ;
	}
	//release memory
	ibase_free_result($Result);
	return array($current_location, $current_price, $current_oqty, $current_qty);
}


if (isset($_POST['addssn'])) 
{
	$tran_type = "AUOB";
	$tran_tranclass = "A";
	$my_object = '';
	if (isset($_POST['ssn'])) 
	{
		$my_object = $_POST['ssn'];
	}
	if (isset($_GET['ssn'])) 
	{
		$my_object = $_GET['ssn'];
	}
	// get the sum qty
	
	//print_r(getcurrent($my_object));
	$mycurrent = getcurrent($my_object);
	$tran_qty = $mycurrent[3];
	$my_location = '';
	if (isset($_POST['location'])) 
	{
		$my_location = $_POST['location'];
	}
	if (isset($_GET['location'])) 
	{
		$my_location = $_GET['location'];
	}
	$my_sublocn = '';
	$my_ref = '';
	if (isset($_POST['reference'])) 
	{
		$my_ref = $_POST['reference'];
	}
	if (isset($_GET['reference'])) 
	{
		$my_ref = $_GET['reference'];
	}
	$my_source = 'SSBSSBSSS';

	include('transaction.php');
	$my_message = "";
	$my_message = dotransaction_response($tran_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
		//$message .= $my_responsemessage;
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		echo ("<B><FONT COLOR=RED>$my_responsemessage</FONT></B>\n");
	}
	else
	{
		//$message .= $my_responsemessage;
		echo ("<B>$my_responsemessage</B>\n");
	}

	//release memory
	if (isset($Result))
	{
		ibase_free_result($Result); 
		unset ($Result);
	}
}
$getmore_desc = NULL;
if (isset($_POST['description'])) 
{
	/* got a description */
	/* echo("<BR> Received Description:".$_POST['description'].":"); */
	/* find its type */
	$description = trim($_POST['description']);
	if (substr($description, 0, 1) == '/')
	{
	  /* if it begin a slash look at 2nd char for the type of desc */
		$tran_notok = FALSE;
		$desc_type = substr($description, 1, 1);
		switch($desc_type)
		{
		case '1':
			$tran_label = 'SSN Type';
			$tran_tranid = 'NITP';
			break;
		case '2':
			$tran_label = 'Generic Description';
			$tran_tranid = 'NIOB';
			break;
		case '3':
			$tran_label = 'Brand';
			$tran_tranid = 'NIBC';
			break;
		case '4':
			$tran_label = 'Model';
			$tran_tranid = 'NIMO';
			break;
		case '5':
			$tran_label = 'Cost Center';
			$tran_tranid = 'NICC';
			break;
		case '6':
			$tran_label = 'Serial Number';
			$tran_tranid = 'NISN';
			break;
		case '7':
			$tran_label = 'Status';
			$tran_tranid = 'NIST';
			break;
		case '8':
			$tran_label = 'Location Type';
			$tran_tranid = 'NILT';
			break;
		case '9':
			$tran_label = 'Location Status';
			$tran_tranid = 'NILS';
			break;
		case 'A':
			$tran_label = 'Appearance';
			$Query = "SELECT field1 from ssn_group where ssn_group = 'DEFAULT'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query ssn_group!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO1';
			break;
		case 'B':
			$tran_label = 'Operating Condition';
			$Query = "SELECT field2 from ssn_group where ssn_group = 'DEFAULT'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query ssn_group!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO2';
			break;
		case 'C':
			$tran_label = 'Completeness';
			$Query = "SELECT field3 from ssn_group where ssn_group = 'DEFAULT'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query ssn_group!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO3';
			break;
		case 'D':
			$tran_label = 'GID No.';
			$Query = "SELECT field4 from ssn_group where ssn_group = 'DEFAULT'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query ssn_group!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO4';
			break;
		case 'E':
			$tran_label = 'Grid Reference No.';
			$Query = "SELECT field5 from ssn_group where ssn_group = 'DEFAULT'";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query ssn_group!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO5';
			break;
		case 'F':
			$tran_label = 'Other 6';
			$Query = "SELECT ssn_type.field1 from issn join ssn on issn.original_ssn = ssn.ssn_id join ssn_type on ssn.ssn_type = ssn_type.code where issn.ssn_id = '";
			if (isset($_POST['ssn'])) 
			{
				$Query .= $_POST['ssn']."'";
			}
			if (isset($_GET['ssn'])) 
			{
				$Query .= $_GET['ssn']."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query type description!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO6';
			break;
		case 'G':
			$tran_label = 'Other 7';
			$Query = "SELECT ssn_type.field2 from issn join ssn on issn.original_ssn = ssn.ssn_id join ssn_type on ssn.ssn_type = ssn_type.code where issn.ssn_id = '";
			if (isset($_POST['ssn'])) 
			{
				$Query .= $_POST['ssn']."'";
			}
			if (isset($_GET['ssn'])) 
			{
				$Query .= $_GET['ssn']."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query type description!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO7';
			break;
		case 'H':
			$tran_label = 'Other 8';
			$Query = "SELECT ssn_type.field3 from issn join ssn on issn.original_ssn = ssn.ssn_id join ssn_type on ssn.ssn_type = ssn_type.code where issn.ssn_id = '";
			if (isset($_POST['ssn'])) 
			{
				$Query .= $_POST['ssn']."'";
			}
			if (isset($_GET['ssn'])) 
			{
				$Query .= $_GET['ssn']."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query type description!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO8';
			break;
		case 'I':
			$tran_label = 'Other 9';
			$Query = "SELECT ssn_type.field4 from issn join ssn on issn.original_ssn = ssn.ssn_id join ssn_type on ssn.ssn_type = ssn_type.code where issn.ssn_id = '";
			if (isset($_POST['ssn'])) 
			{
				$Query .= $_POST['ssn']."'";
			}
			if (isset($_GET['ssn'])) 
			{
				$Query .= $_GET['ssn']."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query type description!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIO9';
			break;
		case 'J':
			$tran_label = 'Other 10';
			$Query = "SELECT ssn_type.field5 from issn join ssn on issn.original_ssn = ssn.ssn_id join ssn_type on ssn.ssn_type = ssn_type.code where issn.ssn_id = '";
			if (isset($_POST['ssn'])) 
			{
				$Query .= $_POST['ssn']."'";
			}
			if (isset($_GET['ssn'])) 
			{
				$Query .= $_GET['ssn']."'";
			}
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query type description!<BR>\n");
			}
			if (($row = ibase_fetch_row($Result)))
			{
				$tran_label =  $row[0];
			}
			ibase_free_result($Result); 
			unset($Result);
			$tran_tranid = 'NIOA';
			break;
		case 'K':
			$tran_label = 'Not New Cond. Items';
			$tran_tranid = 'NIAP';
			break;
		case 'L':
			$tran_label = 'Label Status';
			$tran_tranid = 'NILX';
			break;
		case 'M':
			$tran_label = 'Faulty Cond. Items';
			$tran_tranid = 'NIOP';
			break;
		case 'N':
			$tran_label = 'Incomplete Cond. Items';
			$tran_tranid = 'NICP';
			break;
		case 'O':
			$tran_label = 'Copy Code';
			$tran_tranid = 'NIGC';
			break;
		case 'P':
			$tran_label = 'Legacy Item No';
			$tran_tranid = 'NILG';
			break;
		case 'Q':
			$tran_label = 'Maint. Support No';
			//$tran_tranid = 'NIOC';
			//$tran_tranid = 'NIOL'; /* other 19 */
			$tran_tranid = 'NI19'; /* other 19 */
			break;
		case 'R':
			$tran_label = 'Admin Type';
			$tran_tranid = 'NIRM';
			break;
		case 'S':
			$tran_label = 'Other 20';
			$tran_tranid = 'NI20';
			break;
		case 'T':
			$tran_label = 'Product';
			$tran_tranid = 'NIPC';
			break;
		default:
			$tran_notok = TRUE;
			echo("Unknown Description - Can't Process\n");
			break;
		}
		
		if ($tran_notok == FALSE)
		{
			if (strlen($description) == 2 )
			{
				if (isset($_POST['description2'])) 
				{
					$description2 = $_POST['description2'];
					if (substr($description2, 0, 1) == '/')
					{
						$description = $_POST['description2'];
					}
					else
					{
						$description .= $_POST['description2'];
					}
				}
				else
				{
	  				/* if data length 2 must go to prompt for data */
					$getmore_desc = "Y";
				}
			}
			if (strlen($description) > 2 )
			{
				$tran_type = $tran_tranid;
				//$tran_tranclass = "A";
				$my_object = '';
				if (isset($_POST['ssn'])) 
				{
					$my_object = $_POST['ssn'];
				}
				if (isset($_GET['ssn'])) 
				{
					$my_object = $_GET['ssn'];
				}
				$my_location = '';
				if (isset($_POST['location'])) 
				{
					$my_location = $_POST['location'];
				}
				if (isset($_GET['location'])) 
				{
					$my_location = $_GET['location'];
				}
				$my_sublocn = '';
				$my_ref = '';
				$my_ref = substr($description,2,strlen($description) - 2);
				//$tran_qty = 1;
				//print_r(getcurrent($my_object));
				$mycurrent = getcurrent($my_object);
				$tran_qty = $mycurrent[3];
				// get the issns qty
				$my_source = 'SSBSSBSSS';
			
				include('transaction.php');
				$my_message = "";
				$my_message = dotransaction($tran_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
				//echo ("my mess " . $my_message);
				if ($my_message > "")
				{
					list($my_mess_field, $my_mess_label) = explode("=", $my_message);
					$my_responsemessage = urldecode($my_mess_label) . " ";
				}
				else
				{
					$my_responsemessage = "";
				}
				//echo ("my resp mess" . $my_responsemessage);
				if ($my_responsemessage == "")
				{
					$my_responsemessage = "Processed successfully ";
				}
				if ($my_responsemessage <> "Processed successfully ")
				{
					//$message .= $my_responsemessage;
					//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
					echo ("<B><FONT COLOR=RED>" . $my_responsemessage . "</FONT></B>\n");
				}
				else
				{
					//$message .= $my_responsemessage;
					echo ("<B>" . $my_responsemessage . "</B>\n");
				}
			
				//release memory
				if (isset($Result))
				{
					ibase_free_result($Result); 
					unset ($Result);
				}
			}
		}
	}
	else
	{
	  /* if its a location -> update the location */
	  /* if its an ssn -> update the ssn */
	  /* otherwise echo error */
		echo("Not a Description - Cannot DO this yet\n");
	}
}

//release memory
if (isset($Result)) 
{
	ibase_free_result($Result);
}

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

 echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo("<FONT size=\"2\">\n");
echo("<H5 ALIGN=\"LEFT\">Scan Description</H5>\n");
echo("<FORM action=\"GetSlash.php\" method=\"post\" name=dummydesc>");
echo("Loc <I><INPUT type=\"text\" readonly name=\"location\"");
if (isset($_POST['location'])) 
{
	echo(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	echo(" value=\"".$_GET['location']."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\"></I>\n");
//echo(" >\n");
if (!isset($getmore_desc)) 
{
	echo("<br>SSN <I><INPUT type=\"text\" readonly name=\"ssn\"");
	if (isset($_POST['ssn'])) 
	{
		echo(" value=\"".$_POST['ssn']."\"");
	}
	if (isset($_GET['ssn'])) 
	{
		echo(" value=\"".$_GET['ssn']."\"");
	}
	echo(" size=\"8\"");
	echo(" maxlength=\"20\"></I>\n");
	//echo(" ></I>\n");
}
echo("</FORM>");

echo("<FORM action=\"GetSlash.php\" method=\"post\" name=getdesc>");
echo("<P>\n");
echo("<I><INPUT type=\"hidden\" name=\"reference\"");
if (isset($_POST['reference'])) 
{
	echo(" value=\"".$_POST['reference']."\"");
}
if (isset($_GET['reference'])) 
{
	echo(" value=\"".$_GET['reference']."\"");
}
echo(">\n");
echo("<INPUT type=\"hidden\" name=\"location\"");
if (isset($_POST['location'])) 
{
	echo(" value=\"".$_POST['location']."\"");
}
if (isset($_GET['location'])) 
{
	echo(" value=\"".$_GET['location']."\"");
}
echo(" >\n");
if (isset($getmore_desc)) 
{
	/* if data length 2 must go to prompt for data */
	echo("</I><INPUT type=\"hidden\" name=\"ssn\"");
	if (isset($_POST['ssn'])) 
	{
		echo(" value=\"".$_POST['ssn']."\"");
	}
	if (isset($_GET['ssn'])) 
	{
		echo(" value=\"".$_GET['ssn']."\"");
	}
	echo(">\n");
	echo("<INPUT type=\"hidden\" name=\"description\"");
	if (isset($_POST['description'])) 
	{
		echo(" value=\"".$_POST['description']."\"");
	}
	if (isset($_GET['description'])) 
	{
		echo(" value=\"".$_GET['description']."\"");
	}
	echo(">\n");
	echo("</FONT>\n");
	echo("<FONT size=\"2\">\n");
	echo($tran_label.": <INPUT type=\"text\" name=\"description2\"");
}
else
{
	echo("<INPUT type=\"hidden\" name=\"ssn\"");
	if (isset($_POST['ssn'])) 
	{
		echo(" value=\"".$_POST['ssn']."\"");
	}
	if (isset($_GET['ssn'])) 
	{
		echo(" value=\"".$_GET['ssn']."\"");
	}
	echo(" ></I>\n");
	echo("</FONT>\n");
	echo("<FONT size=\"2\">\n");
	echo("<br>DESCRIPTION: <INPUT type=\"text\" name=\"description\"");
}
echo(" size=\"40\"");
echo(" maxlength=\"40\"><BR>\n");
echo("</FONT>\n");
echo("<FONT size=\"2\">\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<INPUT type=\"submit\" name=\"send\" value=\"Send!\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"GetSSN.php\" method=\"post\" name=nextssn>\n");
	echo("<INPUT type=\"hidden\" name=\"reference\" value=\"");
	if (isset($_POST['reference'])) 
	{
		echo ($_POST['reference']);
	}
	if (isset($_GET['reference'])) 
	{
		echo ($_GET['reference']);
	}
	echo("\">");
	echo("<INPUT type=\"hidden\" name=\"location\" value=\"");
	if (isset($_POST['location'])) 
	{
		echo ($_POST['location']);
	}
	if (isset($_GET['location'])) 
	{
		echo ($_GET['location']);
	}
	echo("\">");
	echo("<INPUT type=\"submit\" name=\"nextssn\" value=\"Next SSN\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	//whm2buttons('Send','../mainmenu.php','N');
	whm2buttons('Send',"../mainmenu.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"send.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
	echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</FORM>\n");
	echo("<BUTTON name=\"nextssn\" type=\"button\" onfocus=\"location.href='GetSSN.php?reference=");
	if (isset($_POST['reference'])) 
	{
		echo ($_POST['reference']);
	}
	if (isset($_GET['reference'])) 
	{
		echo ($_GET['reference']);
	}
	echo("&location=");
	if (isset($_POST['location'])) 
	{
		echo ($_POST['location']);
	}
	if (isset($_GET['location'])) 
	{
		echo ($_GET['location']);
	}
	echo("';\">\n");
	echo("Next SSN<IMG SRC=\"/icons/hand.up.gif\" alt=\"nextssn\"></BUTTON>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/

	// Create a button
	$backto = "GetSSN.php?reference=";
	if (isset($_POST['reference'])) 
	{
		$backto = $backto . urlencode($_POST['reference']);
	}
	if (isset($_GET['reference'])) 
	{
		$backto = $backto . urlencode($_GET['reference']);
	}
	$backto = $backto . "&location=";
	if (isset($_POST['location'])) 
	{
		$backto = $backto . urlencode($_POST['location']);
	}
	if (isset($_GET['location'])) 
	{
		$backto = $backto . urlencode($_GET['location']);
	}
	$alt = "NextSSN";
	$alttext = "Next+SSN";
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alttext . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '"></INPUT>');
*/
	echo('SRC="/icons/whm/nextssn.gif" alt="' . $alt . '"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("</TABLE>");

}
echo("</P>\n");
?>
<script type="text/javascript">
<?php
if (isset($getmore_desc)) 
{
	echo("document.getdesc.description2.focus();\n");
}
else
{
	echo("document.getdesc.description.focus();\n");
}
?>
</script>
</body>
</html>
