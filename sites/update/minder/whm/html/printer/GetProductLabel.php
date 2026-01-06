<?php
include "login.inc";
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
?>
<html>
<head>
<?php
 include "viewport.php";
?>
<title>Reprint Product Labels</title>
<link rel=stylesheet type="text/css" href="GetProductLabel.css">
<script type="text/javascript">
function processEdit() {
  if ( document.printprod.prod.value=="")
  {
  	document.printprod.message.value="Must Enter the Product";
	document.printprod.prod.focus()
  	return false
  }
  if ( document.printprod.copys.value=="")
  {
  	document.printprod.message.value="Enter the # of copies";
	document.printprod.copys.focus()
  	return false
  }
  if ( document.printprod.labelqty.value=="")
  {
  	document.printprod.message.value="Enter the Qty of Labels";
	document.printprod.labelqty.focus()
  	return false
  }
  if ( document.printprod.perlabelqty.value=="")
  {
  	document.printprod.message.value="Enter the Qty on Each Label";
	document.printprod.perlabelqty.focus()
  	return false
  }
  return true;
}
function changeProd() {
  document.printprod.message.value="Enter Qty of Labels";
  document.printprod.labelqty.value="";
  document.printprod.labelqty.focus();
}
function changeType() {
  if ( document.printprod.labeltype.options[document.printprod.labeltype.selectedIndex].value == "" )
  {
    document.printprod.message.value="Enter Qty of Labels"; 
    document.printprod.labelqty.value="";
    document.printprod.perlabelqty.value="1";
    document.printprod.labelqty.focus();
  }
}
</script>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "transaction.php";
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wk_prod_no = "";
$wk_label_type = "";
$wk_label_qty = "";
$wk_per_label_qty = "";
$wk_copys = 1;
$wk_printer = "";
$wk_image_x = "";
$wk_image_y = "";
if (isset($_POST['prod'])) 
{
	$wk_prod_no = $_POST["prod"];
}
if (isset($_GET['prod'])) 
{
	$wk_prod_no = $_GET["prod"];
}
if (isset($_POST['labeltype'])) 
{
	$wk_label_type = $_POST["labeltype"];
}
if (isset($_GET['labeltype'])) 
{
	$wk_label_type = $_GET["labeltype"];
}
if (isset($_POST['labelqty'])) 
{
	$wk_label_qty = $_POST["labelqty"];
}
if (isset($_GET['labelqty'])) 
{
	$wk_label_qty = $_GET["labelqty"];
}
if (isset($_POST['perlabelqty'])) 
{
	$wk_per_label_qty = $_POST["perlabelqty"];
}
if (isset($_GET['perlabelqty'])) 
{
	$wk_per_label_qty = $_GET["perlabelqty"];
}
if (isset($_POST['copys'])) 
{
	$wk_copys = $_POST["copys"];
}
if (isset($_GET['copys'])) 
{
	$wk_copys = $_GET["copys"];
}
if (isset($_POST['printer'])) 
{
	$wk_printer = $_POST["printer"];
}
if (isset($_GET['printer'])) 
{
	$wk_printer = $_GET["printer"];
}
if (isset($_POST['x'])) 
{
	$wk_image_x = $_POST["x"];
}
if (isset($_GET['x'])) 
{
	$wk_image_x = $_GET["x"];
}
if (isset($_POST['y'])) 
{
	$wk_image_y = $_POST["y"];
}
if (isset($_GET['y'])) 
{
	$wk_image_y = $_GET["y"];
}
$wk_message = "";

if ($wk_label_type == "")
{
	if ($wk_per_label_qty == 0)
	{
		$wk_per_label_qty = 1;
	}
}
	


$wk_prod_exists = "";
//$wk_desc = "";
if (isset($wk_prod_no))
{
	// check that the prod exists and the labeltype list
	$Query4 = "SELECT short_desc FROM prod_profile where prod_id = '" . $wk_prod_no . "'";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read prod!<BR>\n");
		exit();
	}
	if ( ($Row5 = ibase_fetch_row($Result4)) ) 
	{
		$wk_prod_exists = "Y";
		$wk_desc = $Row5[0];
	}
	//release memory
	ibase_free_result($Result4);
}
if (isset($wk_prod_no))
{
	if ($wk_prod_exists == "")
	{
		$wk_prod_no = "";
  		$wk_message = "Product Dosn't Exist ";
	}
}
//if (($wk_prod_no <>"") and ($wk_copys <> "") and ($wk_label_qty <> "") and ($wk_per_label_qty <> "") and ($wk_printer <> "") and ($wk_image_x > 0) and ($wk_image_y > 0))
if (($wk_prod_no <>"") and ($wk_copys <> "") and ($wk_label_qty <> "") and ($wk_per_label_qty <> "") and ($wk_printer <> "") )
{
	$my_source = 'SSBSSKSSS';
	$tran_qty = $wk_copys;
	$my_object = $wk_prod_no;
	$location = $wk_label_type;
	$my_sublocn = "";
	$my_ref = "|" . $wk_label_qty . "|" . $wk_per_label_qty . "|" . "0|0|" . $wk_printer . "|" ;

	$my_message = "";
	$my_message = dotransaction("PLPR", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$wk_message .= urldecode($my_mess_label) . " ";
	}
	//release memory
	//ibase_free_result($Result4);
}

//release memory
//ibase_free_result($Result);

//echo (" <FORM action=\"GetProductLabel.php\" method=\"post\" ONSUBMIT=\"return processEdit();\" name=printprod>");
echo (" <FORM action=\"GetProductLabel.php\" method=\"post\" name=printprod>");
echo (" <P>");
echo ("<INPUT type=\"text\" name=\"message\" class=\"message\" readonly size=\"40\" ><BR> ");
echo ("Product:<INPUT type=\"text\" name=\"prod\" value = \"".$wk_prod_no."\" size=\"30\" ONCHANGE=\"changeProd();\" ><BR> ");
if (isset($wk_desc))
{
	echo ("<INPUT type=\"text\" name=\"proddesc\" value = \"".$wk_desc."\" size=\"50\" readonly>");
}
echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo ("<TR><TD>");
echo ("Label Type:");
echo ("</TD><TD>");
echo ("<SELECT name=\"labeltype\" ONCHANGE=\"changeType();\" > ");
if ($wk_label_type == "")
{
	echo ("<OPTION value=\"\"");
	echo(" selected >None</OPTION> ");
}
else
{
	echo ("<OPTION value=\"\"");
	echo(">None</OPTION> ");
}
if ($wk_label_type == "INNER")
{
	echo ("<OPTION value=\"INNER\"");
	echo(" selected >Inner</OPTION> ");
}
else
{
	echo ("<OPTION value=\"INNER\"");
	echo(">Inner</OPTION> ");
}
if ($wk_label_type == "OUTER")
{
	echo ("<OPTION value=\"OUTER\"");
	echo(" selected >Outer</OPTION> ");
}
else
{
	echo ("<OPTION value=\"OUTER\"");
	echo(">Outer</OPTION> ");
}
echo ("</SELECT>");
echo ("</TD></TR>");
echo ("<TR><TD>");
echo ("Copies of Each Label:");
echo ("</TD><TD>");
echo ("<INPUT type=\"text\" name=\"copys\" value=\"$wk_copys\" size=\"4\" maxlength=\"4\">");
echo ("</TD></TR>");
echo ("<TR><TD>");
echo ("Qty of Labels:");
echo ("</TD><TD>");
echo ("<INPUT type=\"text\" name=\"labelqty\" value=\"$wk_label_qty\" size=\"4\" maxlength=\"4\">");
echo ("</TD></TR>");
echo ("<TR><TD>");
echo ("Qty on Each Label:");
echo ("</TD><TD>");
echo ("<INPUT type=\"text\" name=\"perlabelqty\" value=\"$wk_per_label_qty\" size=\"4\" maxlength=\"4\">");
echo ("</TD></TR>");
echo ("<TR><TD>");
echo ("Printer:");
echo ("</TD><TD>");
echo ("<SELECT name=\"printer\" > ");
$Query = "SELECT device_id FROM sys_equip WHERE device_type = 'PR' ORDER BY device_id ";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read sys_equip!<BR>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) 
{
	echo ("<OPTION value=\"" . $Row[0] . "\"");
	if ($wk_printer == "")
	{
		$wk_printer = $Row[0];
	}
	if ($wk_printer == $Row[0])
	{
		echo(" selected ");
	}
	echo(">" . $Row[0] . "</OPTION>");
}
//release memory
ibase_free_result($Result);
echo ("</SELECT>");
echo ("</TD></TR>");
echo ("</TABLE>");
echo ("<BR><BR><BR><BR><BR><BR><BR>");
echo ("<BR><BR><BR><BR><BR><BR><BR><BR>");
echo("<TABLE BORDER=\"0\" ALIGN=\"BOTTOM\">\n");
if ($wk_prod_no == "")
{
	whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<script type=\"text/javascript\">\n");
	echo("document.printprod.prod.focus();\n");
	if ($wk_message <> "")
	{
		echo("document.printprod.message.value=\"" . $wk_message . " Enter Product to Print\";\n");
	}
	else
	{
		echo("document.printprod.message.value=\"Enter Product to Print\";\n");
	}
	echo("</script>");
}
else
{
	if ($wk_label_qty == 0)
	{
		whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
		echo("<script type=\"text/javascript\">\n");
		echo("document.printprod.labelqty.focus();\n");
		echo("document.printprod.message.value=\"Enter Qty of Labels to Print\";\n");
		echo("</script>");
	}
	else
	{
		if ($wk_per_label_qty == 0)
		{
			whm2buttons('Accept', 'print_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
			echo("<script type=\"text/javascript\">\n");
			echo("document.printprod.perlabelqty.focus();\n");
			echo("document.printprod.message.value=\"Enter Qty on Each Label\";\n");
			echo("</script>");
		}
		else
		{
			if ($wk_message <> "")
			{
				echo("<script type=\"text/javascript\">\n");
				echo("document.printprod.message.value=\"" . $wk_message . "\";\n");
				echo("</script>");
			}
			whm2buttons('Print', 'print_Menu.php',"Y","Back_50x100.gif","Back","Print_50x100.gif");
		}
	}
}
//commit
//ibase_commit($dbTran);

//close
ibase_close($Link);
?>
</body>
</html>
