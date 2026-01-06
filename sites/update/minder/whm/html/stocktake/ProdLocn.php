<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Get Product you are working on</title>
<link rel=stylesheet type="text/css" href="product2.css">
<script type="text/javascript">
var strEmpty = "";
function errorHandler(errorMessage, url, line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>");
	document.write("<b>URL:</b> "+url+"<br>");
	document.write("<b>Line:</b> "+line+"</p>");
	return true;
}
onerror = errorHandler
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
<body BGCOLOR="#F00000">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	//include "checkdatajs.php";
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// create js for location check
	//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
	//whm2scanvars($Link, 'prod13','PROD_13', 'PROD13');
	//whm2scanvars($Link, 'prod','PROD_INTERNAL', 'PRODINTERNAL');
	//whm2scanvars($Link, 'altprod','ALT_PROD_INTERNAL', 'ALTPRODINTERNAL');
	require_once "logme.php";
	//include "checkdata.php";
	require_once "checkdata.php";
	//include "logme.php";
	if (isset($_COOKIE['SaveUser']))
	{
		list($tran_user, $tran_device,$UserType) = explode("|", $_COOKIE['SaveUser']);
	}

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
	}
	if (isset($_POST['message']))
	{
		$message = $_POST['message'];
	}
	$message = "";
	if (isset($_POST['product'])) 
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product'])) 
	{
		$product = $_GET['product'];
	}
	if (isset($_POST['owner'])) 
	{
		$owner = $_POST['owner'];
	}
	if (isset($_GET['owner'])) 
	{
		$owner = $_GET['owner'];
	}
	if (isset($_POST['qty'])) 
	{
		$qty = $_POST['qty'];
	}
	if (isset($_GET['qty'])) 
	{
		$qty = $_GET['qty'];
	}
	if (isset($_POST['prodlocn'])) 
	{
		$prodlocn = $_POST['prodlocn'];
	}
	if (isset($_GET['prodlocn'])) 
	{
		$prodlocn = $_GET['prodlocn'];
	}
	if (isset($_POST['prod'])) 
	{
		$prod = $_POST['prod'];
	}
	if (isset($_GET['prod'])) 
	{
		$prod = $_GET['prod'];
	}
	if (isset($prod))
	{
		if ($prod == "")
		{
			unset ($prod);
			unset ($qty);
		}
	}
	if (isset($prod))
	{
		$prod = strtoupper($prod);
	}
	if (isset($prodlocn))
	{
		$prodlocn = strtoupper($prodlocn);
	}
	if (isset($_POST['audited'])) 
	{
		$wk_audited = $_POST['audited'];
	}
	if (isset($_GET['audited'])) 
	{
		$wk_audited = $_GET['audited'];
	}
	if (!isset($wk_audited))
	{
		$wk_audited = "N";
	}
	//echo("audited received :" . $wk_audited);
/*
	if (isset($_POST['prodcnt'])) 
	{
		$wk_prodcnt = $_POST['prodcnt'];
	}
	if (isset($_GET['prodcnt'])) 
	{
		$wk_prodcnt = $_GET['prodcnt'];
	}
*/
	if (isset($_POST['recordid'])) 
	{
		$wk_record_id = $_POST['recordid'];
	}
	if (isset($_GET['recordid'])) 
	{
		$wk_record_id = $_GET['recordid'];
	}
	if (isset($_POST['x'])) 
	{
		$image_x = $_POST['x'];
	}
	if (isset($_GET['x'])) 
	{
		$image_x = $_GET['x'];
	}
	if (isset($_POST['y'])) 
	{
		$image_y = $_POST['y'];
	}
	if (isset($_GET['y'])) 
	{
		$image_y = $_GET['y'];
	}
// ===================================================================================

if (isset($prodlocn) and $prodlocn != "")
{
	if (isset($message))
		$wk_old_message = $message;
	// perhaps a location
	$field_type = checkForTypein($prodlocn, 'LOCATION' ); 
	if ($field_type == "none")
	{
		// not a location  try a device 
		$field_type = checkForTypein($prodlocn, 'DEVICE' ); 
		if ($field_type == "none")
		{
				// unknown data - treat as hand entered
		} else {
			// a device 
			if ($startposn > 0)
			{
				$wk_realdata = substr($prodlocn,$startposn);
				$prodlocn = $wk_realdata;
			}
			setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
		}
	} else {
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($prodlocn,$startposn);
			$prodlocn = $wk_realdata;
		}
		setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
	}
	//echo "location field is " . $field_type;
	if (isset($message))
	{
		if (isset($wk_old_message))
		{
			$message = $wk_old_message ;
		} else {
			unset($message);
		}
	}
}

// ==================================================================================

if (isset($prod) and $prod != "")
{
	if (isset($message))
		$wk_old_message = $message;
	$field_type = checkForTypein($prod, 'LOCATION' ); 
	if ($field_type == "none")
	{
		// perhaps a prod 13
		$field_type = checkForTypein($prod, 'PROD_13' ); 
		if ($field_type == "none")
		{
			// not a prod 13  try prod internal 
			$field_type = checkForTypein($prod, 'PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
				// not a prod internal try alt prod internal
				$field_type = checkForTypein($prod, 'ALT_PROD_INTERNAL' ); 
				if ($field_type == "none")
				{
					// unknown data - treat as hand entered
				} else {
					// an alt prod internal 
					if ($startposn > 0)
					{
						$wk_realdata = substr($prod,$startposn);
						$prod = $wk_realdata;
					}
					setBDCScookie($Link, $tran_device, "prod", $prod );
				}
			} else {
				// a prod internal 
				//echo $startposn;
				if ($startposn > 0)
				{
					$wk_realdata = substr($prod,$startposn);
					$prod = $wk_realdata;
				}
				setBDCScookie($Link, $tran_device, "prod", $prod );
			}
		} else {
			// a prod_13
			if ($startposn > 0)
			{
				$wk_realdata = substr($prod,$startposn);
				$prod = $wk_realdata;
			}
			setBDCScookie($Link, $tran_device, "prod", $prod );
		}
	} else {
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($prod,$startposn);
			$prodlocn = $wk_realdata;
		} else {
			$prodlocn = $prod;
		}
		setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
		unset ($prod);
	}
	//echo "prod field is " . $field_type;
	if (isset($message))
	{
		if (isset($wk_old_message))
		{
			$message = $wk_old_message ;
		} else {
			unset($message);
		}
	}
}

// ==================================================================================


/*
if (isset($qty) and $qty != "")
{
	if (isset($message))
		$wk_old_message = $message;
	$field_type = checkForTypein($qty, 'LOCATION' ); 
	if ($field_type == "none")
	{
		// perhaps a prod 13
		$field_type = checkForTypein($qty, 'PROD_13' ); 
		if ($field_type == "none")
		{
			// not a prod 13  try prod internal 
			$field_type = checkForTypein($qty, 'PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
				// not a qty internal try alt prod internal
				$field_type = checkForTypein($qty, 'ALT_PROD_INTERNAL' ); 
				if ($field_type == "none")
				{
					// unknown data - treat as hand entered - qty
				} else {
					// an alt prod internal 
					if ($startposn > 0)
					{
						$wk_realdata = substr($qty,$startposn);
						$prod = $wk_realdata;
					} else {
						$prod = $qty;
					}
					setBDCScookie($Link, $tran_device, "prod", $qty );
					unset ($qty);
				}
			} else {
				// a prod internal 
				if ($startposn > 0)
				{
					$wk_realdata = substr($qty,$startposn);
					$prod = $wk_realdata;
				} else {
					$prod = $qty;
				}
				setBDCScookie($Link, $tran_device, "prod", $qty );
				unset ($qty);
			}
		} else {
			// a prod_13
			if ($startposn > 0)
			{
				$wk_realdata = substr($qty,$startposn);
				$prod = $wk_realdata;
			} else {
				$prod = $qty;
			}
			setBDCScookie($Link, $tran_device, "prod", $qty );
			unset ($qty);
		}
	} else {
		// a location
		if ($startposn > 0)
		{
			$wk_realdata = substr($qty,$startposn);
			$prodlocn = $wk_realdata;
		} else {
			$prodlocn = $qty;
		}
		setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
		unset ($qty);
		unset ($prod);
	}
	//echo "prod field is " . $field_type;
	if (isset($message))
	{
		if (isset($wk_old_message))
		{
			$message = $wk_old_message ;
		} else {
			unset($message);
		}
	}
}
*/
// ==================================================================================

if (isset($qty) and $qty != "")
{
	if (!is_numeric($qty))
	{
		if (isset($message))
		{
			$message .= " Qty Must be Numeric";
		} else {
			$message = " Qty Must be Numeric";
		}
		unset($qty);
	}
}
// ==================================================================================
	$wk_docommit = "N";
	if (isset($prodlocn))
	{
		$Query = "select locn_name ";
		$Query .= "from location ";
		$Query .= "where wh_id = '" . substr($prodlocn,0,2) . "' and locn_id='";
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "'";
		
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query Location!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$locn_desc = $Row[0];
		}
		else
		{
			$message .= "Not a Valid Location";
			unset($prodlocn);
		}
		//commit
		ibase_free_result($Result);
		ibase_commit($dbTran);
	}
/* =============================================================================================================
21/06/10
have to stop when product not found - yes
allow use of alternate_id to find the prod_id - yes

   ============================================================================================================= */
	if (isset($prod))
	{
		if ($prod <> "")
		{
			$wk_product_found = 0;
			//ok to update
			$Query = "select 1  ";
			$Query .= " from prod_profile pp";
			$Query .= " where pp.prod_id = '".$prod."'";
		
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
				//$wk_altprod = str_replace(array('*','-','/','\\'),'',$prod);
				$wk_altprod = $prod;
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
					$wk_alt_orig_product = $prod;
					$prod = $Row[1];
				}
				//release memory
				ibase_free_result($Result);
			}
			if ($wk_product_found == 0) 
			{
				//$message .= "Not a Valid Product";
				$message .= "Not a Valid Product " . $prod;
				unset($prod);
			}
		}
	}
	if (isset($prod))
	{
		$Query = "select first 1 prod_profile.uom, prod_profile.prod_id, issn.audited ";
		$Query .= "from prod_profile ";
		$Query .= "left outer join issn on prod_profile.prod_id = issn.prod_id  ";
		$Query .= "and issn.wh_id = '" . substr($prodlocn,0,2) ."' ";
		$Query .= "and issn.locn_id = '" . substr($prodlocn,2,10) ."' ";
		$Query .= "where prod_profile.prod_id = '" . $prod ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query Product!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_uom = $Row[0];
			$wk_current_prod = $Row[1];
			$wk_audited = $Row[2];
		}
		if (!isset($wk_uom))
		{
			$wk_uom = "EA";
		}
		ibase_free_result($Result);
	}

	if ((isset($prodlocn)) and (!isset($qty)) and (!isset($prod)))
	{
		$wk_last_location = getBDCScookie($Link, $tran_device, "stocktakelocation");
		if ($wk_last_location <> $prodlocn)
		{
			// check whether a STLX is required
			// do the STLX - no - this is done in the STLO
			// do transactions for new location
			$my_source = 'SSBSSKSSS';
			$tran_qty = 0;
			$location = $prodlocn;
			$my_object = "";
			$my_sublocn = "";
			$my_ref = "Stocktake Location" ;
		
			$my_message = "";
			$tran_tran = "STLX";
			$my_message = dotransaction_response($tran_tran, "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			//echo $my_message;
			$my_message = "";
			$tran_tran = "STLO";
			$my_message = dotransaction_response($tran_tran, "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
			//echo $my_message;
			if ($my_message > "")
			{
				list($my_mess_field, $my_mess_label) = explode("=", $my_message);
				$my_message_response = urldecode($my_mess_label) . " || ";
				list($my_mess, $wk_prodcnt) = explode("|", $my_message_response);
				if (!isset($message))
				{
					$message = "";
				}
				if ($my_mess == "Processed successfully")
				{
					$my_mess = "OK";
				}
				$message .= "STLO " . $my_mess ;
			}
			else
			{
				$message .= "New Location " . $prodlocn;
			}
			setBDCScookie($Link, $tran_device, "stocktakelocation", $prodlocn);
			echo $prodlocn;
		}
		$wk_docommit = "Y";
	}
	if (isset($prod))
	{
		if (isset($qty))
		{
			if ($qty > 0)
			{
				// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = $qty;
	$location = $prodlocn;
	$my_object = $prod;
	$my_sublocn = "";
	if (!isset($wk_record_id))
	{
		$wk_record_id = "";
	}
	$my_ref = $qty . "|" . $wk_record_id . "|"   ;

	$my_message = "";
	$tran_tran = "STIS";
	//echo("audit" . $wk_audited);
	if ($wk_audited == "R")
	{
		$wk_tran_code = "Q";
		$my_sublocn = ""; // want the record id here
		//$my_ref = $wk_record_id . "||" ;
	}
	else
	{
		$wk_tran_code = "P";
	}
	$my_message = dotransaction_response($tran_tran, $wk_tran_code, $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		//$message .= urldecode($my_mess_label) . " ";
		$my_message_response = urldecode($my_mess_label) . " ||||||| ";
		list($my_mess, $wk_record_id, $wk_action, $wk_variance, $wk_status) = explode("|", $my_message_response);
		if ($my_mess == "Processed successfully")
		{
			$my_mess = "OK";
		}
		$message .= "STIS " . $my_mess . " " . $prod ;
		//$message .= $my_mess ;
	}
	{
		$Query = "select first 1 audited ";
		$Query .= "from issn ";
		//$Query .= "where issn.ssn_id = '" . $ssn ."' ";
		$Query .= "where issn.prod_id = '" . $prod ."' ";
		$Query .= "and issn.wh_id = '" . substr($prodlocn,0,2) ."' ";
		$Query .= "and issn.locn_id = '" . substr($prodlocn,2,10) ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query ISSN for Product!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_audited = $Row[0];
		}
		if (!isset($wk_audited))
		{
			$wk_audited = "N";
		}
		if ($wk_audited == 'R')
		{
			$message = "Item Seen but Recount Required";
			unset($qty);
		}
		else
		{
			$message .= "Item " . $prod . " Seen ";
			unset($prod);
			$wk_issn_seen = "T";
/*
			if (isset($wk_prodcnt))
			{
				if ($wk_prodcnt > 0)
				{
					$wk_prodcnt = $wk_prodcnt - 1;
				}
			}
*/
		}
		ibase_free_result($Result);

	}
	$wk_docommit = "Y";
			}
		}
	}
	if (isset($prodlocn))
	{
		$Query = "select count(*) ";
		$Query .= "from issn ";
		$Query .= "where issn.wh_id = '"  ;
		$Query .= substr($prodlocn,0,2)."' ";
		$Query .= "and issn.locn_id = '" ;
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2)."'  ";
		$Query .= "and issn.audited = 'M' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query ISSN count!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_prodcnt = $Row[0];
		}
	}

 	//echo("<H3 ALIGN=\"LEFT\">Stocktake ISSN</H3>");
	if (isset($prodlocn))
	{
		if (isset($prod))
		{
  			echo("<h4 align=\"LEFT\">");
			if ($wk_audited == 'R')
			{
				echo("Recount Required - ");
			}
  			//echo("<h4 align=\"LEFT\">Enter Qty</h4>");
  			//echo("<h4 align=\"LEFT\">Enter Qty");
  			echo("Enter Qty");
			if (isset ($wk_prodcnt))
			{
				echo(" (".$wk_prodcnt . " ToGo)");
			}
  			echo("</h4>");
		}
		else
		{
			if (isset($wk_issn_seen))
			{
  				echo("<h4 align=\"LEFT\">Scan next Product");
			}
			else
			{
  				//echo("<h4 align=\"LEFT\">Enter SSN and Qty</h4>");
  				echo("<h4 align=\"LEFT\">Enter Product and Qty");
			}
			if (isset ($wk_prodcnt))
			{
				echo(" (".$wk_prodcnt . " ToGo)");
			}
  			echo("</h4>");
		}
	}
	else
	{
  		echo("<h4 align=\"LEFT\">Enter Location</h4>");
	}

?>

<?php
// echo("<form action="ProdLocn.php" method="post" name=getproduct ONSUBMIT="return processEdit2(0);">\n");
echo("<form action=\"ProdLocn.php\" method=\"post\" name=getproduct >\n");
echo("<INPUT type=\"hidden\" name=\"audited\" value=\"" . $wk_audited . "\">");
//echo("<INPUT type=\"text\" name=\"audited\" value=\"" . $wk_audited . "\">");
if (isset ($wk_prodcnt))
{
	echo("<INPUT type=\"hidden\" name=\"prodcnt\" value=\"" . $wk_prodcnt . "\">");
}
if (isset ($wk_record_ids))
{
	echo("<INPUT type=\"hidden\" name=\"recordid\" value=\"" . $wk_record_id . "\">");
}
echo("<p><label for=\"prodlocn\">Location</label><input type=\"text\"  id=\"prodlocn\" name=\"prodlocn\" class=\"locationform\"");
if (isset($prodlocn)) 
{
	echo(" value=\"".$prodlocn."\"");
}
echo(" size=\"12\"");
//echo(" maxlength=\"13\" onfocus=\"document.getproduct.prodlocn.value=strEmpty\" onchange=\"return processEdit2(1)");
echo(" maxlength=\"13\" onfocus=\"document.getproduct.prodlocn.value=strEmpty\" onchange=\"document.getproduct.submit()");
if (isset($prod))
{
	echo(";document.getproduct.prod.value=strEmpty");
}
echo("\" />\n");
echo("</p>\n");

if (isset($prodlocn))
{

	/* save screen space */
	{
		echo("<p><label for=\"prod\">Product</label><input type=\"text\"  id=\"prod\" name=\"prod\" class=\"locationform\"");
		if (isset($prod)) 
		{
			echo(" value=\"".$prod."\"");
		}
		echo(" size=\"30\"");
		//echo(" maxlength=\"33\" onchange=\"document.getproduct.qty.value=strEmpty;return processEdit4(1)\" /></p>\n");
		echo(" maxlength=\"33\" onchange=\"document.getproduct.qty.value=strEmpty;document.getproduct.submit()\" /></p>\n");
		echo("<p><label for=\"qty\">Qty</label><input type=\"text\"  id=\"qty\" name=\"qty\" class=\"locationform\"");
		if (isset($qty)) 
		{
			echo(" value=\"".$qty."\"");
		}
		echo(" size=\"7\"");
		//echo(" maxlength=\"33\" onchange=\"return processEdit()\" ");
		//echo("  onfocus=\"return processEdit4(0)\" /></p>\n");
		echo(" maxlength=\"33\" onchange=\"document.getproduct.submit()\" ");
		echo("   /></p>\n");
	}
}
/*
	if (isset($message))
	{
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
*/
	echo("<div  id=\"message\" name=\"message\" > ");
	if (isset($message)) 
	{
		echo("<input type=\"text\"  id=\"message\" name=\"message\" readonly size=\"60\" class=\"message\" ");
		if ($message <> "")
		{
			echo(" value=\"".$message."\"");
		}
	}
	else
	{
		echo("<input type=\"text\"  id=\"message\" name=\"message\" readonly size=\"60\" class=\"message\" ");
	}
	echo("   /></p>\n");
	echo("</div>\n");

{
	// html 4.0 browser
 	echo("<table border=\"0\" align=\"LEFT\">");
	if (isset($qty))
	{
		whm2buttons('Accept', 'EndProdLocn.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
	else
	{
		whm2buttons('Accept', 'EndProdLocn.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
//commit
//ibase_commit($dbTran);
?>
</P>
<script type="text/javascript">
<?php
	if (isset($prodlocn))
	{
		if (isset($prod) and ($prod <> ""))
		{
			echo("document.getproduct.qty.focus();");
		}
		else
		{
			echo("document.getproduct.prod.focus();");
		}
	}
	else
	{
		echo("document.getproduct.prodlocn.focus();");
	}
if ($wk_docommit == "Y")
{
	ibase_commit($Link);
}
ibase_close($Link);
?>
</script>
</body>
</html>
