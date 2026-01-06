<?php
include "../login.inc";
?>
<html>
<head>
<title>Product Profile Dimensions</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
?>
<link rel=stylesheet type="text/css" href="proddim.css">
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
    border-collapse:separate;
    border-spacing:1px 0px;
}
</style>
<script type="text/javascript">
function regetproduct() {
	document.reget.product.value=document.alterproduct.product.value;
	document.reget.location.value=document.alterproduct.location.value;
	document.reget.company.value=document.alterproduct.company.value;
	document.reget.submit();
	return true;
}
function regetlocation() {
	document.reget.location.value=document.alterproduct.location.value;
	document.reget.submit();
	return true;
}
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
function clearDimx()
{
	document.alterproduct.dimx.value = "";
}
function clearDimy()
{
	document.alterproduct.dimy.value = "";
}
function clearDimz()
{
	document.alterproduct.dimz.value = "";
}
function clearNetWeight()
{
	document.alterproduct.net_weight.value = "";
}
function revokeDimx(wk_value)
{
	if (document.alterproduct.dimx.value == "")
	{
		document.alterproduct.dimx.value = wk_value;
	}
}
function revokeDimy(wk_value)
{
	if (document.alterproduct.dimy.value == "")
	{
		document.alterproduct.dimy.value = wk_value;
	}
}
function revokeDimz(wk_value)
{
	if (document.alterproduct.dimz.value == "")
	{
		document.alterproduct.dimz.value = wk_value;
	}
}
function revokeNetWeight(wk_value)
{
	if (document.alterproduct.net_weight.value == "")
	{
		document.alterproduct.net_weight.value = wk_value;
	}
}
</script>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
/*
Jan 27th 2010
Since cannot find products
when they enter the product - the screen refreshes
so first try the product code used
	if not found in prod profile
	then try looking up using the product code without / - spaces \ and matching on the 1st that matches

	if not found in prod_profile
	then add this product but set the product_stock to 'U'
*/
function getstockdesc($stock)
{
	$stockdesc = "";
	if ($stock == "")
	{
		$stockdesc = "Unknown";
	}
	else
	{
		$stockdesc = $stock;
	}
	return $stockdesc;
}
$location = "";
$product = "";
$company = "";
if (isset($_POST['product'])) 
{
	$product = $_POST["product"];
}
if (isset($_GET['product'])) 
{
	$product = $_GET["product"];
}
if (isset($_POST['location'])) 
{
	$location = $_POST["location"];
}
if (isset($_GET['location'])) 
{
	$location = $_GET["location"];
}
if (isset($_POST['company'])) 
{
	$company = $_POST["company"];
}
if (isset($_GET['company'])) 
{
	$company = $_GET["company"];
}
if (isset($_POST['from'])) 
{
	$from = $_POST["from"];
}
if (isset($_GET['from'])) 
{
	$from = $_GET["from"];
}
if (isset($_POST['grn'])) 
{
	$grn = $_POST["grn"];
}
if (isset($_GET['grn'])) 
{
	$grn = $_GET["grn"];
}
if (isset($_POST['dimx'])) 
{
	$dimx = $_POST["dimx"];
}
if (isset($_GET['dimx'])) 
{
	$dimx = $_GET["dimx"];
}
if (isset($_POST['dimy'])) 
{
	$dimy = $_POST["dimy"];
}
if (isset($_GET['dimy'])) 
{
	$dimy = $_GET["dimy"];
}
if (isset($_POST['dimz'])) 
{
	$dimz = $_POST["dimz"];
}
if (isset($_GET['dimz'])) 
{
	$dimz = $_GET["dimz"];
}
if (isset($_POST['net_weight'])) 
{
	$net_weight = $_POST["net_weight"];
}
if (isset($_GET['net_weight'])) 
{
	$net_weight = $_GET["net_weight"];
}
$product = strtoupper($product);
$location = strtoupper($location);
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$wk_ok_reason = "";
// allow for param entry for location and product
include "checkdata.php";
// check location for a location
if ($location <> "")
{
	$field_type = checkForTypein($location, 'LOCATION' ); 
	//echo 'test location ' . $field_type;
	if ($field_type != "none")
	{
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($location,$startposn);
			$location = $wk_realdata;
		}
	} else {
		$location = "";
		$wk_ok_reason .= "Invalid Location";
	}
}


// check product for a location
if ($product  <> "")
{
	// perhaps a prod 13
	$field_type = checkForTypein($product, 'PROD_13' ); 
	if ($field_type !== "none")
	{
		// a prod 13 
		if ($startposn > 0)
		{
			$wk_realdata = substr($product,$startposn);
			$product = $wk_realdata;
		}
	} else {
		// not a prod 13 - perhaps a prod internal
		$field_type = checkForTypein($product, 'PROD_INTERNAL' ); 
		if ($field_type !== "none")
		{
			// a prod internal
			if ($startposn > 0)
			{
				$wk_realdata = substr($product,$startposn);
				$product = $wk_realdata;
			}
		} else {
			$product = "";
			$wk_ok_reason .= " Invalid Product";
		}
	}
}
$rimg_width = 60;
echo("<h4>Product:Dimensions</h4>\n");
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:
$rcount = 0;
if (isset($from))
	echo("<FORM action=\"proddim.php\" method=\"post\" name=alterproduct>\n");
else
	echo("<FORM action=\"proddim.php\" method=\"post\" name=alterproduct>\n");

/* ok
if location product dimx dimy dimz populated
then update/insert into prod profile
*/
$wk_save_reason = $wk_ok_reason;
$wk_ok_reason = "";
if ($location <> "") 
{
	if ($company <> "")
	{
		if ($product <> "")
		{
			if (isset($dimx))
			{
				if ($dimx <> "")
				{
				} else {
					$wk_ok_reason = "  X Dimension";
				}
			}
			if (isset($dimy))
			{
				if ($dimy <> "")
				{
				} else {
					$wk_ok_reason .= "  Y Dimension";
				}
			}
			if (isset($dimz))
			{
				if ($dimz <> "")
				{
				} else {
					$wk_ok_reason .= "  Z Dimension";
				}
			}
		} else {
			$wk_ok_reason .= " Product";
		}
	} else {
		$wk_ok_reason .= " Company";
	}
} else {
	$wk_ok_reason .= " Location";
}
//if ($wk_ok_reason == "")
if ($product <> "")
{
	$wk_product_found = 0;
	//ok to update
	$Query = "select 1  ";
	$Query .= " from prod_profile pp";
	$Query .= " where pp.prod_id = '".$product."'";
	$Query .= " and  pp.company_id = '".$company."'";

	//echo("[$Query]\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Product!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result))) {
		$wk_product_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	if ($wk_product_found == 0) 
	{
		// calc new alt prod
		$wk_altprod = str_replace(array('*','-','/','\\'),'',$product);
		$Query = "select 1, prod_id  ";
		$Query .= " from prod_profile pp";
		$Query .= " where pp.alternate_id = '".$wk_altprod."'";

		//echo("[$Query]\n");
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query Product Alternate!<BR>\n");
			exit();
		}
		// Fetch the results from the database.
		while (($Row = ibase_fetch_row($Result))) {
			$wk_product_found = $Row[0];
			$wk_alt_orig_product = $product;
			$product = $Row[1];
		}
		//release memory
		ibase_free_result($Result);
	}
}

if ($wk_ok_reason == "")
{
	// calc new alt prod
	$wk_altprod = str_replace(array('*','-','/','\\'),'',$product);

	if ($wk_product_found == 1)
	{
		//$Query = "insert into prod_profile(prod_id, short_desc, home_locn_id, dimension_x, dimension_y, dimension_z, net_weight)  values ('" . $product . "','" . $product . "','" . $location . "','" . $dimx . "','" . $dimy . "','" . $dimz . "','" . $net_weight . "')";
		$Query = "update prod_profile set home_locn_id = '" . $location . "'," .
		" dimension_x  =  '" . $dimx . "'," . 
		" dimension_y  =  '" . $dimy . "'," . 
		" dimension_z  =  '" . $dimz . "'," . 
		" dimension_x_uom  =  'MM'," . 
		" dimension_y_uom  =  'MM'," . 
		" dimension_z_uom  =  'MM'," . 
		" pack_weight   =  '" . $net_weight  . "'," .
		" net_weight_uom   =  'KG',"  . 
		" last_update_by   =  '" . $tran_user . "',"  . 
		" last_update_date  =  'NOW',"  . 
		" alternate_id  =  '" . $wk_altprod . "'"  . 
		" where prod_id = '" . $product . "'" .
		" and company_id = '" . $company . "'";
		//echo("[$Query]\n");
		//if (!($Result = ibase_query($Link, $Query)))
		//{
		//	echo("Unable to Update Product!<BR>\n");
		//	exit();
		//}
		$wk_ok_reason .= " Product  " . $product . " Updated ";
	} else {
		//$wk_ok_reason .= " Product  " . $product . " Cannot be Added ";
		$wk_ok_reason .= " Product  " . $product . " Added ";
		$Query = "insert into prod_profile(";
		$Query .= "prod_id, ";
		$Query .= "short_desc, ";
		$Query .= "company_id, ";
		$Query .= "home_locn_id, ";
		$Query .= "dimension_x, ";
		$Query .= "dimension_y, ";
		$Query .= "dimension_z, ";
		$Query .= "dimension_x_uom, ";
		$Query .= "dimension_y_uom, ";
		$Query .= "dimension_z_uom, ";
		$Query .= "pack_weight, ";
		$Query .= "net_weight_uom,";
		$Query .= "last_update_by,";
		$Query .= "last_update_date,";
		$Query .= "alternate_id,";
		$Query .= "stock) ";
		$Query .= "  values ('" . $product . "','";
		$Query .= $product . "','" ;
		$Query .= $company . "','" ;
		$Query .= $location . "','" ;
		$Query .= $dimx . "','" ;
		$Query .= $dimy . "','" ;
		$Query .= $dimz . "','" ;
		$Query .= "MM','" ; 
		$Query .= "MM','" ; 
		$Query .= "MM','" ; 
		$Query .= $net_weight . "','";
		$Query .= "KG','"  ; 
		$Query .= $tran_user . "','"  ; 
		$Query .= "NOW','"  ; 
		$Query .= $wk_altprod . "','"  ; 
		$Query .= "U')";
	}
	//echo("[$Query]\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Update Product!<BR>\n");
		exit();
	}
	$product = '';
	// update the location
	$wk_location_found = 0;
	//ok to update
	$Query = "select 1  ";
	$Query .= " from location";
	$Query .= " where wh_id = '" ; 
	$Query .= substr($location,0,2)."' and locn_id = '";
	$Query .= substr($location,2,strlen($location) - 2)."'";

	//echo("[$Query]\n");
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query Location!<BR>\n");
		exit();
	}
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result))) {
		$wk_location_found = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if ($wk_location_found == 0)
	{
		$Query = "insert into location(wh_id, locn_id, locn_name, locn_stat, move_stat, last_update_date, last_update_by)  values ('" . substr($location,0,2) . "','" . substr($location,2,strlen($location) - 2)  . "','" . $location . "','OK','ST','NOW','" . $tran_user . "')";
		//echo("[$Query]\n");
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Add Location!<BR>\n");
			exit();
		}
		$wk_ok_reason .= " Location  " . $location . " Added ";
	}
} else {
	$wk_ok_reason = $wk_save_reason . " Enter " . $wk_ok_reason;
}
// put buttons here =============================================================================================

echo ("<div id=\"col2\">\n");

echo("Home Location:<input type=\"text\" name=\"location\" value=\"$location\" size=\"12\" onchange=\"return regetlocation()\"><br>");

$Query = "select pp.short_desc, pp.long_desc, pp.company_id, pp.pack_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, cy.name, pp.stock, o1.description ,k1.kit_id, pp.dimension_x, pp.dimension_y, pp.dimension_z";
$Query .= " from prod_profile pp";
$Query .= " left outer join company cy on cy.company_id = pp.company_id";
$Query .= " left outer join options o1 on o1.group_code = 'PROD_STOCK' and o1.code = pp.stock";
$Query .= " left outer join kit k1 on k1.kit_id = pp.prod_id";
$Query .= " where pp.prod_id = '".$product."'";
$Query .= " and   pp.company_id = '".$company."'";

//echo("[$Query]\n");
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to query issn!<BR>\n");
	exit();
}
$short_desc = "";
$long_desc = "";
//$company = "";
$net_weight = "";
$order_qty = "";
$inner_qty = "";
$order_weight = "";
$net_weight_uom = "";
$order_uom = "";
$inner_uom = "";
$order_weight_uom = "";
$company_name = "";
$prod_stock = "";
$stock_desc = "";
$kit_id = "";
$dimx="";
$dimy="";
$dimz="";
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result))) {
	$short_desc = $Row[0];
	$long_desc = $Row[1];
	//$company = $Row[2];
	$net_weight = $Row[3];
	$order_qty = $Row[4];
	$per_inner_qty = $Row[5];
	if ($per_inner_qty == "")
		$per_inner_qty = 1;
	$inner_qty = $order_qty / $per_inner_qty;
	$order_weight = $Row[6];
	$net_weight_uom = $Row[7];
	$order_uom = $Row[8];
	$inner_uom = $Row[9];
	$order_weight_uom = $Row[10];
	$company_name = $Row[11];
	$prod_stock = $Row[12];
	$stock_desc = $Row[13];
	$kit_id = $Row[14];
	$dimx = $Row[15];
	$dimy = $Row[16];
	$dimz = $Row[17];
}

//release memory
ibase_free_result($Result);

/*
echo("<input type=\"hidden\" name=\"stock\" value=\"$prod_stock\" readonly >");
echo("<input type=\"hidden\" name=\"company\" value=\"$company\" readonly >");
echo("Product Owner:<input type=\"text\" name=\"stock_desc\" value=\"" . getstockdesc($stock_desc) . "\" readonly size=\"11\">");
if ($kit_id <> "")
{
	echo("Kitted<br>");
	echo("<input type=\"hidden\" name=\"kitid\" value=\"" . $kit_id . "\" >");
}
else
{
	echo("Not Kitted<br>");
}
echo("<input type=\"text\" name=\"companyname\" value=\"$company_name\" readonly size=\"50\"><br>");
*/
echo("Company ID:<input type=\"text\" name=\"company\" value=\"$company\" size=\"10\" onchange=\"return regetproduct()\">");
echo("<br>\n");
echo("Product ID:<input type=\"text\" name=\"product\" value=\"$product\" size=\"30\" onchange=\"return regetproduct()\">");
echo("<br>\n");
//echo("<input type=\"text\" name=\"shortdesc\" readonly size=\"50\" value=\"$short_desc\" >");
echo("<input type=\"text\" name=\"shortdesc\" readonly size=\"50\" value=\"" . htmlspecialchars($short_desc) . "\" >");
/*
echo("<table border=\"0\" align=\"left\">");
echo("<tr><td>");
echo("<textarea name=\"longdesc\" readonly rows=\"2\" cols=\"50\">$long_desc</textarea>");
echo("</td></tr>");
echo ("</table>\n");
*/
//echo("<br><br><br><br>");
echo("<table border=\"0\" align=\"left\"  class=\"data\" cellspacing=\"1\"  >");
echo("<tr><td colspan=\"6\">Dimensions (mm):</td></tr>");

echo("<tr><td>");
echo("X:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimx\" value=\"$dimx\" size=\"6\" maxlength=\"6\" onfocus=\"clearDimx()\" onblur=\"revokeDimx($dimx)\">");
echo("</td><td>");
echo("Y:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimy\" value=\"$dimy\" size=\"6\" maxlength=\"6\" onfocus=\"clearDimy()\" onblur=\"revokeDimy($dimy)\">");
echo("</td><td>");
echo("Z:");
echo("</td><td>");
echo("<input type=\"text\" name=\"dimz\" value=\"$dimz\" size=\"6\" maxlength=\"6\" onfocus=\"clearDimz()\" onblur=\"revokeDimz($dimz)\">");
echo("</td>");

echo("</tr>");
echo("<tr><td colspan=\"3\">");
echo("Weight (kgs):");
echo("</td><td>");
echo("<input type=\"text\" name=\"net_weight\" value=\"$net_weight\" size=\"6\" maxlength=\"6\" onfocus=\"clearNetWeight()\" onblur=\"revokeNetWeight($net_weight)\">");
echo("</td>");
echo("</tr>");
echo ("</table>\n");
echo("<table border=\"0\" align=\"left\"  class=\"message\"  >");
//echo("<tr><td colspan=\"6\">");
echo("<tr><td>");
echo("<input type=\"text\" name=\"message\"  value=\"$wk_ok_reason\" size=\"40\" maxlength=\"50\" >");
echo("</td>");
echo("</tr>");

echo ("</table>\n");
echo ("</div>\n");
echo ("<div id=\"col5\">\n");
//release memory
//$Result->free();
//ibase_free_result($Result);

//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
//ibase_close($Link);

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"getlocn.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
{
	// html 4.0 browser
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='getlocn.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
if (isset($from))
{
	$backto = $from;
}
else
{
	$backto = 'query.php';
}
$endtable = "Y";
{
	//echo("<br><br><br><br><br><br><br><br><br>");
	echo ("<div id=\"col1\">\n");
	// Create a table.
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"bottom\">");
	echo ("<TABLE BORDER=\"0\" >");
	echo ("<TR>");
	echo ("<TD>");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($from))
	{
		echo("<input type=\"hidden\" name=\"from\" value=\"$from\">");
	}
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	if (isset($from))
	{
		$alt = "Accept";
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	}
	else
	{
		//$alt = "View Locations";
		//echo('SRC="/icons/whm/locations.gif" alt="' . $alt . '">');
		$alt = "Accept";
		echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	}
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"" . $backto . "\" method=\"post\" name=back>\n");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\">");
	}
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	if ($endtable == "Y")
	{
		echo ("</TABLE>");
	}
	echo("<FORM action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"post\" name=reget>\n");
	if (isset($grn))
	{
		echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\">");
	}
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"$product\">");
	}
	if (isset($company))
	{
		echo("<input type=\"hidden\" name=\"company\" value=\"$company\">");
	}
	if (isset($location))
	{
		echo("<input type=\"hidden\" name=\"location\" value=\"$location\">");
	}
	if (isset($from))
	{
		echo("<input type=\"hidden\" name=\"from\" value=\"$from\">");
	}
	echo("</FORM>");
	echo ("</div>\n");
	echo("<script type=\"text/javascript\">");
	if ($location == "")
	{
		echo("document.alterproduct.location.focus();");
	} else {
		if ($company == "")
		{
			echo("document.alterproduct.company.focus();");
		} else {
			if ($product == "")
			{
				echo("document.alterproduct.product.focus();");
			} else {
				if ($dimx == "") 
				{
					echo("document.alterproduct.dimx.focus();");
				} else {
					if ($dimy == "") 
					{
						echo("document.alterproduct.dimy.focus();");
					} else {
						if ($dimz == "") 
						{
							echo("document.alterproduct.dimz.focus();");
						} else {
							//echo("document.alterproduct.message.focus();");
							echo("document.alterproduct.dimx.focus();");
						}
					}
				}
			}
		}
	}
	echo ("</script>\n");
}

?>
</body>
</html>
