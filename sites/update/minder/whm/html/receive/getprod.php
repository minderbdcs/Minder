<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Get Product to Adjust Weight</title>
<?php
require_once 'DB.php';
require 'db_access.php';
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
include "checkdatajs.php";
// create js for product check
whm2scanvars($Link, 'prod13','PROD_13', 'PROD13');
whm2scanvars($Link, 'prodint','PROD_INTERNAL', 'PRODINTERNAL');
?>

<script type="text/javascript">
var mydummy="";
var wkdome="dome";
function processEdit() {
  var mytype;
  mytype = checkProd13(document.getprod.product.value); 
  if (mytype == "PROD13")
  {
	return true;
  }
  mytype = checkProdint(document.getprod.product.value); 
  if (mytype == "PRODINTERNAL")
  {
	return true;
  }
/*
  alert("Not a Product");
  return false;
*/
  return true;
}
</script>

 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FCCFFF">


 <form 
<?php
	echo("action=\"".basename($_SERVER['PHP_SELF'])."\" ");
?>
  method="post" name=getprod ONSUBMIT="return processEdit();">

 <P>
<?php
$product = "";
$wk_dome="";
$image_x = 0;
$image_y = 0;
$message = "";
$wk_message = "";
if (isset($_POST['message'])) 
{
	$wk_message = $_POST["message"];
}
if (isset($_POST['dome'])) 
{
	$wk_dome = $_POST["dome"];
}
if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}
if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}
if ($product <> "")
{
	$Query = "select prod_id, short_desc, net_weight, net_weight_uom  from prod_profile ";
	$Query .= " where prod_id = '" . $product . "'";
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read user_id!<BR>\n");
		exit();
	}
	$wk_prod_found = "F";
	while ( ($Row = ibase_fetch_row($Result)) ) 
	{
		$wk_prod_found = "T";
		$wk_prod = $Row[0];
		$wk_prod_desc = $Row[1];
		$wk_weight = $Row[2];
		$wk_uom = $Row[3];
	}
}
if ($wk_prod_found == "T")
{
	// prod found
	if ($wk_dome == "")
	{
		$wk_message = "Working";
	}
}


if ($image_x == 0 and $image_y == 0 and $wk_message == "Working")
{
	/* if prod_id changed then not working
	   else image_x = 1 and image y = 1
	*/
	if ($wk_dome == "dome")
	{
		// prod_id changed
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
	if ($product <> "")
	{
		//calc the printer to use
	  	$Query = "select export_so_held_printer from control ";
		//echo $Query;
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Control!<BR>\n");
			exit();
		}
		$wk_cont_found = "F";
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_cont_found = "T";
			$wk_printer = $Row[0];
		}
		// create file with prod
		$tran_qty = 0;
		$my_object = $product;
		$location = $wk_printer . " ";
		$my_sublocn = "";
		$my_ref = "" ;
		$my_source = "SSSSSSSSS";
	
		$my_message = "";
		include("transaction.php");
		$my_message = dotransaction_response("PKWT", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
			//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else
		{
			$message = "Requested Weight Recalc";
		}
	}
	else
	{
		if ($wk_prod_found == "F")
		{
			// prod not found
			$product = "";
			$message = "Product Not Found";
		}
	}
	//release memory
	ibase_free_result($Result);

}
if ($wk_prod_found == "F")
{
	// prod not found
	$product = "";
	$message = "Product Not Found";
}
if ($product == "")
{
	echo("<H2 ALIGN=\"LEFT\">Enter Product to Adjust Orders Weight</H2>\n");
}
else
{
	echo("<H2 ALIGN=\"LEFT\">Press Accept to Request the Update</H2>\n");
}
echo ("<INPUT type=\"hidden\" name=\"dome\" value=\"\" >\n");
echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
echo(" size=\"70\" readonly ><br>\n");
echo("<table BORDER=\"0\" ALIGN=\"LEFT\">\n");
echo("<tr><td>\n");
echo('Product:</td><td><input type="text" name="product" size="30" onblur="return processEdit();"');
if (isset($product))
{
	echo(" value=\"" . $product . "\"");
}
echo(" onchange=\"document.getprod.dome.value=wkdome;document.getprod.submit();\" ");
echo(" onfocus=\"document.getprod.product.value=mydummy;\"");
echo(" ></td></tr>\n");
if ($product <> "")
{
	echo("<tr><td>\n");
	echo("</td><td><input type=\"text\" name=\"proddesc\" size=\"50\" readonly value=\"" . $wk_prod_desc . "\"");
	echo(" ></td></tr>\n");
	echo("<tr><td>\n");
	echo("Net Wt</td><td><input type=\"text\" name=\"netwt\" size=\"10\" readonly value=\"" . $wk_weight . "\"");
	echo(" ></td></tr>\n");
	echo("<tr><td>\n");
	echo("Uom</td><td><input type=\"text\" name=\"neuom\" size=\"2\" readonly value=\"" . $wk_uom . "\"\n");
	echo(" ></td></tr>\n");
}

include "2buttons.php";
{
	// html 4.0 browser
	whm2buttons('Accept',"receive_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"send\" value=\"Send!\" type=\"submit\">\n");
echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
	echo("</form>\n");
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"product.href='../query/query.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

echo("<script type=\"text/javascript\">");
if ($product == "")
{
	echo("document.getprod.product.focus();\n");
}
?>
</script>
</body>
</html>
