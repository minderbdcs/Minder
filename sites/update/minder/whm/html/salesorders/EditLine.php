<html>
<head>
<title>Modify a Line</title>
</head>
 <body BGCOLOR="#0080FF">
<script type="text/javascript">
var wk_empty = "";
var wk_dome = "dome";
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;
	var i;
	if (strString.length == 0) return false;
	for (i = 0; i<strString.length && blnResult == true; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			blnResult = false;
		}
	}
	return blnResult;
}
function processQty() {
  if ( document.showlocn.pick_label_no.value=="")
  {
  	document.showlocn.message.value="Must Enter Label ID";
	document.showlocn.pick_label_no.focus();
  	return false;
  }
  document.showlocn.message.value="Working";
  return true;
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wk_ssn = "";
$wk_prod = "";
$wk_pickorderqty = "";
$wk_pickorderlineno = "";
$wk_discount = "";
$wk_sale_price = "";
$wk_special_instructions1 = "";
$wk_special_instructions2 = "";
$wk_dome="";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['ssn'])) 
{
	$ssn = $_POST["ssn"];
}
if (isset($_POST['dome'])) 
{
	$wk_dome = $_POST["dome"];
}
if (isset($_POST['prod'])) 
{
	$prod = $_POST["prod"];
}
if (isset($_POST['pickorderqty'])) 
{
	$pickorderqty = $_POST["pickorderqty"];
}
if (isset($_POST['pickorderlineno'])) 
{
	$pickorderlineno = $_POST["pickorderlineno"];
}
if (isset($_POST['discount'])) 
{
	$discount = $_POST["discount"];
}
if (isset($_POST['sale_price'])) 
{
	$sale_price = $_POST["sale_price"];
}
if (isset($_POST['special_instructions1'])) 
{
	$special_instructions1 = $_POST["special_instructions1"];
}
if (isset($_POST['special_instructions2'])) 
{
	$special_instructions2 = $_POST["special_instructions2"];
}
if (isset($_POST['pick_label_no'])) 
{
	$pick_label_no = $_POST["pick_label_no"];
	//echo " pick_label " . $pick_label_no;
}

if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if (isset($pick_label_no))
{
/*
	if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
*/
	{
		$Query = "select pick_label_no, ssn_id, prod_id, pick_order_qty, pick_order_line_no, discount, sale_price, special_instructions2, special_instructions1, pick_line_status from pick_item ";
		$Query .= " where pick_label_no = '" . $pick_label_no . "'";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read pick_label_no!<BR>\n");
			exit();
		}
		$wk_line_found = "F";
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_line_found = "T";
			$wk_line = $Row[0];
			$wk_ssn = $Row[1];
			$wk_prod = $Row[2];
			$wk_pickorderqty = $Row[3];
			$wk_pickorderlineno = $Row[4];
			$wk_discount = $Row[5];
			$wk_sale_price = $Row[6];
			$wk_special_instructions2 = $Row[7];
			$wk_special_instructions1 = $Row[8];
			$wk_status = $Row[9];
		}
	}
}

if ($image_x == 0 and $image_y == 0 and $wk_message == "Working")
{
	/* if pick_label_no changed then not working
	   else image_x = 1 and image y = 1
	*/
	if ($wk_dome == "dome")
	{
		// pick_label_no changed
		$wk_message = "";
	}
	else
	{
		$image_x = 1;
		$image_y = 1;
	}

}
if ($image_x > 0 and $image_y > 0 and $wk_message == "Working")
{
	//echo('got image_x');
	//echo($image_x);
	//echo('got image_y');
	//echo($image_y);
	if (isset($wk_line) and $wk_line <> "")
	{
		$wk_cnt = 0;
		$wk_update = "";
		/*
		if ($ssn <> $wk_ssn)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "ssn_id = '" . $ssn . "' ";
			$wk_ssn = $ssn;
		}
		*/
		/*
		if ($prod <> $wk_prod)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "prod_id = '" . $prod . "' ";
			$wk_prod = $prod;
		}
		*/
		if ($pickorderqty <> $wk_pickorderqty)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "pick_order_qty = '" . $pickorderqty . "' ";
			$wk_pickorderqty = $pickorderqty;
		}
		if ($pickorderlineno <> $wk_pickorderlineno)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "pick_order_line_no = '" . $pickorderlineno . "' ";
			$wk_pickorderlineno = $pickorderlineno;
		}
		if ($discount <> $wk_discount)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "discount = '" . $discount . "' ";
			$wk_discount = $discount;
		}
		if ($sale_price <> $wk_sale_price)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "sale_price = '" . $sale_price . "' ";
			$wk_sale_price = $sale_price;
		}
		if ($special_instructions2 <> $wk_special_instructions2)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "special_instructions2 = '" . $special_instructions2 . "' ";
			$wk_special_instructions2 = $special_instructions2;
		}
		if ($special_instructions1 <> $wk_special_instructions1)
		{
			if ($wk_update != "")
			{
				$wk_update .= ",";
			}
			$wk_update .= "special_instructions1 = '" . $special_instructions1 . "' ";
			$wk_special_instructions1 = $special_instructions1;
		}
		if ($wk_update <> "")
		{
			$wk_cnt = $wk_cnt + 1;
			$Query2 = "update pick_item set " . $wk_update . " where pick_label_no = '" . $wk_line . "'";
			//echo($Query2);
			if (!($Result2 = ibase_query($Link, $Query2)))
			{
				echo("Unable to Update pick_label_no!<BR>\n");
				exit();
			}
		}
		$message = "Updated Line";
	}
	//release memory
	ibase_free_result($Result);

	//commit
	ibase_commit($dbTran);

	//close
	//ibase_close($Link);

}
//else
{
	echo("<H4>Enter Values to Update</H4>\n");
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo (" <FORM action=\"" .  basename($_SERVER["PHP_SELF"])   . "\" method=\"post\" name=showlocn onsubmit=\"return processQty()\" >");
	echo ("<INPUT type=\"hidden\" name=\"dome\" value=\"\" >\n");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly >\n");
	echo("<tr>");
	echo("<td>");
	echo ("Label");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT type=\"text\" name=\"pick_label_no\" readonly ");
	echo(" size=\"10\" maxlength=\"10\" value=\"$pick_label_no\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Status");
	echo("</td>");
	echo("<td>$wk_status");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>SSN");
	echo("</td>");
	echo("<td>");
	echo ("<INPUT readonly type=\"text\" name=\"ssn\"  ");
	echo(" size=\"20\" maxlength=\"20\" value=\"$wk_ssn\" onchange=\"return processQty()\" >\n");
	echo("</td>");
	echo("</tr>");

	echo("<tr>");
	echo("<td>Product");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"prod\" readonly  ");
	echo(" size=\"30\" maxlength=\"30\" value=\"$wk_prod\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>Line No");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"pickorderlineno\" ");
	echo(" size=\"4\" maxlength=\"4\" value=\"$wk_pickorderlineno\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Order Qty");
	echo("</td>");
	echo("<td colspan=\"2\">");

	echo ("<INPUT type=\"text\" name=\"pickorderqty\"  ");
	echo(" size=\"6\" value=\"$wk_pickorderqty\" >\n");

	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Discount");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"discount\"  ");
	echo(" size=\"8\" maxlength=\"10\" value=\"$wk_discount\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Sale Price");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"sale_price\"  ");
	echo(" size=\"8\" maxlength=\"10\" value=\"$wk_sale_price\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo ("Comments");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"special_instructions1\"  ");
	echo(" size=\"70\" maxlength=\"255\" value=\"$wk_special_instructions1\" >\n");
	echo("</td>");
	echo("</tr>");
	echo("<tr>");
	echo("<td>");
	echo("</td>");
	echo("<td colspan=\"2\">");
	echo ("<INPUT type=\"text\" name=\"special_instructions2\"  ");
	echo(" size=\"70\" maxlength=\"255\" value=\"$wk_special_instructions2\" >\n");
	echo("</td>");
	echo("</tr>");
	whm2buttons('Accept', 'ShowLine.php',"Y","Back_50x100.gif","Back","accept.gif");
	if (!isset($pick_label_no))
	{
		echo("<script type=\"text/javascript\">\n");
		echo("document.showlocn.pick_label_no.focus();</script>");
	}
	//release memory
	ibase_free_result($Result);
}
?>
</body>
</html>

