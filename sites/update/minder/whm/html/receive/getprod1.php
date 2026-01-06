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
 <body BGCOLOR="#FCCF00">

  <H2 ALIGN="LEFT">Enter Product to Adjust the Weight</H2>

 <TABLE BORDER="0" ALIGN="LEFT">
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
		$wk_update = "";
		// create file with prod
		if ($wk_update <> "")
		{
			$Query2 = "update sys_user set " . $wk_update . " where user_id = '" . $wk_prod . "'";
			echo($Query2);
			if (!($Result2 = ibase_query($Link, $Query2)))
			{
				echo("Unable to Update user_id!<BR>\n");
				exit();
			}
		}
		$message = "Requested Weight Recalc";
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
	echo ("<INPUT type=\"hidden\" name=\"dome\" value=\"\" >\n");
	echo ("<INPUT type=\"text\" name=\"message\" value=\"$message\"  ");
	echo(" size=\"70\" readonly >\n");
?>
Product: <input type="text" name="product" size="30" onblur="return processEdit();"
<?php
if (isset($product))
{
	echo(" value=\"" . $product . "\"");
}
echo(" onchange=\"document.getprod.dome.value=wkdome;document.getprod.submit();\" ");
echo(" onfocus=\"document.getprod.product.value=mydummy;\"");
?>
 ><br>
<?php
if ($product <> "")
{
echo("Desc     <input type=\"text\" name=\"proddesc\" size=\"50\" readonly value=\"" . $wk_prod_desc . "\"><br>\n");
echo("Net Wt   <input type=\"text\" name=\"netwt\" size=\"10\" readonly value=\"" . $wk_weight . "\"><br>\n");
}
include "2buttons.php";
/*
*/
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
