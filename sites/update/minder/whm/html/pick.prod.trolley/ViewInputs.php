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


$got_tot = 0;

$wk_select = " ";

$pickmode = "";
if (isset($_POST['pickmode']))
{
	$pickmode = $_POST['pickmode'];
}
if (isset($_GET['pickmode']))
{
	$pickmode = $_GET['pickmode'];
}
$Query = "select pick_order_type, procedure_name, pick_param_type, pick_param_mode, pick_param_ordno, pick_param_status, pick_param_priority, pick_param_id, description from pick_mode ";
$Query .= "where pick_mode_no = '" . $pickmode . "'";


if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read PickMode!<BR>\n");
	exit();
}

$got_ssn = 0;

// echo headers
echo("<FORM action=\"ViewInputs.php\" method=\"get\" name=getssn>\n");
echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
echo ("<TABLE BORDER=\"0\">\n");

// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($got_ssn == 0) {
		// echo headers
		$got_ssn = 1;
	}
	$pick_order_type = $Row[0];
	$pick_procedure = $Row[1];
	$pick_ptype = $Row[2];
	$pick_pmode = $Row[3];
	$pick_pordno = $Row[4];
	$pick_pstatus = $Row[5];
	$pick_ppriority = $Row[6];
	$pick_pid = $Row[7];
	if ($pick_ptype == "T")
	{
		// select box drop down muliple select
		echo ("<TR>\n");
		echo("<TD>Order Type:</TD>");
		echo ("<TD>");
		$Query2 = "select order_type, description from order_type where outwards = 'T' order by order_type";
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read OrderType!<BR>\n");
			exit();
		}
		$pickordertypes = "";
		if (isset($_POST['pickordertypes']))
		{
			// add to list
			$pickordertypes = $_POST['pickordertypes'];
			//echo("ordertypes  " . $pickordertypes);
		}
		if (isset($_GET['pickordertypes']))
		{
			// add to list
			$pickordertypes = $_GET['pickordertypes'];
			//echo("ordertypes1  " . $pickordertypes);
			//echo("ordertypes1end  " );
		}
		if (isset($_POST['pickordertype']))
		{
			$wk_pos = strpos($pickordertypes, $_POST['pickordertype']);
			if ($wk_pos === false)
			{
				// not found
				//echo("ordertype " . $_POST['pickordertype']);
				// add to list
				$pickordertypes = $pickordertypes . "|" . $_POST['pickordertype'];
				//echo("ordertypes " . $pickordertypes);
			}
			else
			{
				// found
				$wk_cnt = 1;
			}
		}
		if (isset($_GET['pickordertype']))
		{
			//echo("got ordertype");
			$wk_pos = strpos($pickordertypes, $_GET['pickordertype']);
			if ($wk_pos === false)
			{
				// not found
				//echo("ordertype1 " . $_GET['pickordertype']);
				// add to list
				$pickordertypes = $pickordertypes . "|" . $_GET['pickordertype'];
				//echo("ordertypes2 " . $pickordertypes);
			}
			else
			{
				// found
				$wk_cnt = 1;
			}
		}
		echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
		echo("<SELECT size=\"2\" name=\"pickordertype\" multiple >\n");
		// Fetch the results from the database.
		while (($Row2 = ibase_fetch_row($Result2))) {
			$wk_pos = strpos($pickordertypes, $Row2[0]);
			if ($wk_pos === false)
			{
				echo("<OPTION value=\"" . $Row2[0] . "\">$Row2[1]\n");
			}
			else
			{
				echo("<OPTION value=\"" . $Row2[0] . "\" selected >$Row2[1]\n");
			}
		}
		echo("</SELECT>\n");
		echo ("</TD>\n");
		echo ("</TR>\n");
	}
	else
	{
		echo ("<TR>\n");
		// want desc for order type
		$Query2 = "select order_type, description from order_type where outwards = 'T' and order_type = '" . $pick_order_type . "' order by order_type";
		if (!($Result2 = ibase_query($Link, $Query2)))
		{
			echo("Unable to Read OrderType!<BR>\n");
			exit();
		}
		echo("<TD>Order Type:</TD>");
		if ( ($Row2 = ibase_fetch_row($Result2)) ) {
			echo("<TD>".$Row2[1]."</TD>");
		}
		echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"\">");
		$pickordertypes = "";
		echo ("</TR>\n");
	}
	echo ("<TR>\n");
	echo("<TD>Order Mode:</TD>");
	echo("<TD>".$Row[8]."</TD>\n");
	echo ("</TR>\n");
	echo ("<TR>\n");
	echo("<TD>Order No:</TD>");
	if ($pick_pordno == "T")
	{
		// enter an order in field to add to list
		echo ("<TD>");
		$pickordernos = "";
		if (isset($_POST['pickordernos']))
		{
			// add to list
			$pickordernos = $_POST['pickordernos'];
		}
		if (isset($_GET['pickordernos']))
		{
			// add to list
			$pickordernos = $_GET['pickordernos'];
		}
		if (isset($_POST['pickorderno']))
		{
			// add to list
			if (strlen($_POST['pickorderno']) > 1)
			{
				$pickordernos = $pickordernos . "|" . $_POST['pickorderno'];
			}
		}
		if (isset($_GET['pickorderno']))
		{
			// add to list
			if (strlen($_GET['pickorderno']) > 1)
			{
				$pickordernos = $pickordernos . "|" . $_GET['pickorderno'];
			}
		}
		echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
		echo("<input name=\"pickorderno\" type=\"text\" >\n");
		// Fetch the results from the database.
		echo ("</TD>\n");
		if (strlen($pickordernos) > 0)
		{
			echo ("<TD>\n");
			echo("<SELECT readonly name=\"chosenorders\"  >\n");
			$wk_pos_offset = 1;
			$wk_pos = strpos($pickordernos, "|", $wk_pos_offset);
			//echo("pos " . $wk_pos); 
			//$wk_cnt = 0;
			
			do {
				if ($wk_pos === false)
				{
					//end of list
					echo("<OPTION value=\"" . substr($pickordernos, $wk_pos_offset ) . "\">" . substr($pickordernos, $wk_pos_offset ) . " \n");
				}
				else
				{
					echo("<OPTION value=\"" . substr($pickordernos, $wk_pos_offset, $wk_pos - $wk_pos_offset ) . "\">" . substr($pickordernos, $wk_pos_offset, $wk_pos - $wk_pos_offset ) . " \n");
					$wk_pos_offset = $wk_pos + 1;
					$wk_pos = strpos($pickordernos, "|", $wk_pos_offset);
					//echo("pos " . $wk_pos); 
					//$wk_cnt = $wk_cnt + 1;
					//if ($wk_cnt == 3)
					//	$wk_pos = -1;
				}
			} while ($wk_pos !== false) ;
			if ($wk_pos === false)
			{
				//end of list
				echo("<OPTION value=\"" . substr($pickordernos, $wk_pos_offset ) . "\">" . substr($pickordernos, $wk_pos_offset ) . " \n");
			}
			echo("</SELECT>\n");
			echo ("</TD>\n");
		}
		echo ("</TR>\n");
	}
	else
	{
		echo("<TD></TD>");
		echo("<input name=\"pickordernos\" type=\"hidden\" value=\"\">");
		$pickordernos = "";
		echo ("</TR>\n");
	}
	echo ("<TR>\n");
	echo ("</TR>\n");
	echo ("<TR>\n");
	echo("<TD>Order Status:</TD>");
	echo ("</TR>\n");
	echo ("<TR>\n");
	echo("<TD>Order Priority:</TD>");
	echo ("</TR>\n");
	echo ("<TR>\n");
	echo("<TD>Input Required IDs:</TD>");
	echo ("</TR>\n");
}

echo ("</TABLE>\n");
if (isset($Result2))
{
	//release memory
	ibase_free_result($Result2);
}
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
	whm2buttons("Accept","ViewType.php","N","Back_50x100.gif","Back","accept.gif");
	// html 4.0 browser
	$backto = "ViewOrders.php";
	$alt2 = "View+Orders";
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=" . $alt2 . ">\n");
	echo("<input name=\"pickmode\" type=\"hidden\" value=\"" . $pickmode . "\">");
	echo("<input name=\"pickordertypes\" type=\"hidden\" value=\"" . $pickordertypes . "\">");
	echo("<input name=\"pickordernos\" type=\"hidden\" value=\"" . $pickordernos . "\">");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt2 . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt2 . '"></INPUT>');
*/
	echo('SRC="/icons/whm/vieworders.gif" alt="' . $alt2 . '"></INPUT>');
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
