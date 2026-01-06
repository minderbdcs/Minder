<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Product you are working on</title>
<?php
include "viewport.php";
//<link rel=stylesheet type="text/css" href="product.from.css">
?>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 1em;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
<script type="text/javascript">
function errorHandler(errorMessage, url, line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>");
	document.write("<b>URL:</b> "+url+"<br>");
	document.write("<b>Line:</b> "+line+"</p>");
	return true;
}
/* onerror = errorHandler */
function chkNumeric(strString)
{
//check for valid numerics
	var strValidChars = "-0123456789";
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
function processEdit(currentqty) {
  	var csum;
  document.getproduct.message.value=" ";
  if ( document.getproduct2.qty.value=="")
  {
  	document.getproduct.message.value="Must Enter an Adjustment";
	document.getproduct2.qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getproduct2.qty.value)==false)
  {
  	document.getproduct.message.value="Must be Numeric";
	document.getproduct2.qty.value = "";
	document.getproduct2.qty.focus();
  	return false;
  }
  csum = currentqty * 1
  csum += (document.getproduct2.qty.value * 1)
  if (( csum) < 0)
  {
  	document.getproduct.message.value="Product Cannot have its Qty Set Negative";
	document.getproduct2.qty.value = "";
	document.getproduct2.qty.focus();
  	return false;
  }
  return true;
}
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

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

	require_once "logme.php";
	//include "checkdata.php";
	require_once "checkdata.php";
	//include "logme.php";
	// create js for location check
	//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
	echo("<style type=\"text/css\">\n");
if ($wkMyBW == "IE60")
{
	require_once "./product.from.css";
} elseif ($wkMyBW == "IE65")
{
	require_once "./product.from.css";
} elseif ($wkMyBW == "CHROME")
{
	require_once "./product.from.chrome.css";
} elseif ($wkMyBW == "SAFARI")
{
	require_once "./product.from.chrome.css";
}
	echo("</style>\n");

/*
was in outer level as just javascript
<script type="text/javascript">
function processEdit2() {
-* # check for valid location *-
  var mytype;
  mytype = checkLocn(document.getproduct.prodlocn.value); 
  if (mytype == "none")
  {
	document.getproduct.message.value="Not a Location";
  	return false;
  }
  else
  {
	return true;
  }
}
</script>
*/

function checkadjust($confirmpw )
{
	global $Link;

	// check that the password allowes stock adjusts
	$wk_confirm = 'F';
	$wk_confirm_user = '';
	$Query = "select stock_adjust, user_id from sys_user where pass_word = '" . $confirmpw . "'";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query SYS_USER!<br>\n");
		exit();
	}
	else
	while (($Row = ibase_fetch_row($Result)))
	{
		if ($Row[0] == 'T')
		{
			$wk_confirm = $Row[0];
			$wk_confirm_user = $Row[1];
		}
	}
	ibase_free_result($Result);
	return ($wk_confirm == 'T');
} // end of function

function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'Company',true);
	//$pager = new ADODB_Pager($conn,$Query,'Company',false);
	$pager->Render(3);

}

$wk_add_pager_javascript= 'F';
$wk_pick_method = "PL2";
	$doconfirm = "F";
	// check that the user can do this
	$Query = "select stock_adjust_confirm from control";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query CONTROL!<br>\n");
		exit();
	}
	else
	if (($Row = ibase_fetch_row($Result)))
	{
		$doconfirm = $Row[0];
	}
	if ($doconfirm == "")
	{
		$doconfirm = 'F';
	}



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
	if (isset($message))
	{
		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	} else {
		echo ("<B></B><br>\n");
	}
	if (isset($_POST['product'])) 
	{
		$product = $_POST['product'];
	}
	if (isset($_GET['product'])) 
	{
		$product = $_GET['product'];
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
		setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
	}
	if (isset($_GET['prodlocn'])) 
	{
		$prodlocn = $_GET['prodlocn'];
		setBDCScookie($Link, $tran_device, "prodlocn", $prodlocn );
	}
	if (isset($_GET['Company_next_page'])) 
	{
		$prodlocn = getBDCScookie($Link, $tran_device, "prodlocn" );
		$product = getBDCScookie($Link, $tran_device, "product" );
		$owner = getBDCScookie($Link, $tran_device, "company" );
		if (empty($product))
		{
			unset ($product);
		}
		if (empty($owner))
		{
			unset ($owner);
		}
		//echo $prodlocn;
	}
	$prodlocn2 = "";
	if (isset($_POST['prodlocn2'])) 
	{
		$prodlocn2 = $_POST['prodlocn2'];
	}
	if (isset($_GET['prodlocn2'])) 
	{
		$prodlocn2 = $_GET['prodlocn2'];
	}
	if (isset($_POST['confirmadjust'])) 
	{
		$confirmadjust = $_POST['confirmadjust'];
	}
	if (isset($_GET['confirmadjust'])) 
	{
		$confirmadjust = $_GET['confirmadjust'];
	}

	if (isset($_POST['reason'])) 
	{
		$reason = $_POST['reason'];
	}
	if (isset($_GET['reason'])) 
	{
		$reason = $_GET['reason'];
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
	$wk_orig_company = getBDCScookie($Link, $tran_device, "company" );
	if (isset($_POST['owner'])) 
	{
		$owner = $_POST['owner'];
		setBDCScookie($Link, $tran_device, "company", $owner );
	}
	if (isset($_GET['owner'])) 
	{
		$owner = $_GET['owner'];
		setBDCScookie($Link, $tran_device, "company", $owner );
	}
	if (isset($owner))
	{
		if ($wk_orig_company <> $owner)
		{
			$prodlocn2 = "";
		}
	}
	$wk_orig_product = getBDCScookie($Link, $tran_device, "product" );
	if (isset($product))
	{
		if (empty($product))
		{
			unset ($product);
		}
	}
	if (isset($owner))
	{
		if (empty($owner))
		{
			unset ($owner);
		}
	}

if (isset($product) and $product != "")
{
	if (isset($message))
		$wk_old_message = $message;
	// perhaps a product 13
	$field_type = checkForTypein($product, 'PROD_13' ); 
	if ($field_type == "none")
	{
		// not a prod 13  try prod internal 
		$field_type = checkForTypein($product, 'PROD_INTERNAL' ); 
		if ($field_type == "none")
		{
			// not a product internal try alt prod internal
			$field_type = checkForTypein($product, 'ALT_PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
				// unknown data - treat as hand entered
			} else {
				// an alt prod internal 
				if ($startposn > 0)
				{
					$wk_realdata = substr($product,$startposn);
					$product = $wk_realdata;
				}
				setBDCScookie($Link, $tran_device, "product", $product );
			}
		} else {
			// a prod internal 
			if ($startposn > 0)
			{
				$wk_realdata = substr($product,$startposn);
				$product = $wk_realdata;
			}
			setBDCScookie($Link, $tran_device, "product", $product );
		}
	} else {
		// a prod_13
		if ($startposn > 0)
		{
			$wk_realdata = substr($product,$startposn);
			$product = $wk_realdata;
		}
		setBDCScookie($Link, $tran_device, "product", $product );
	}
	if (isset($message))
	{
		if (isset($wk_old_message))
		{
			$message = $wk_old_message ;
		} else {
			unset($message);
		}
	}
	if ($product <> "")
	{
		//$_SESSION['product'] = $product;
		// not sure the setBDCScookie is working
		setBDCScookie($Link, $tran_device, "product", $product );
	}
	if ($wk_orig_product <> $product)
	{
		$prodlocn2 = "";
	}
}


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

	if (isset($prodlocn))
	{
		$Query = "select wh_id, locn_id  ";
		$Query .= "from location ";
		$Query .= "where location.wh_id = '" ; 
		$Query .= substr($prodlocn,0,2)."' AND location.locn_id = '";
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query product!<br>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			// have prod in locn
			$wk_ln_wh_id = $Row[0];
			$wk_ln_locn_id = $Row[1];
		}
		else
		{
			$message = "Location Not Found";
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
			unset ($prodlocn);
			if (isset($product))
			{
				unset ($product);
			}
		}
		//release memory
		ibase_free_result($Result);
	}
	if (isset($prodlocn))
	{
		if (isset($product))
		{
			//want locns for product
			$Query = "select short_desc, long_desc ";
			$Query .= "from prod_profile ";
			$Query .= "where prod_id = '".$product."' ";
			$Query .= "and company_id = '".$owner."' ";
			
			//echo $Query;
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query product!<br>\n");
				exit();
			}
			if (($Row = ibase_fetch_row($Result)) )
			{
				// have prod in locn
				$wk_prod_short_desc = $Row[0];
				$wk_prod_long_desc = $Row[1];
			}
			else
			{
				$message = "Product Not Found";
				echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
				//unset ($product);
			}
			//release memory
			ibase_free_result($Result);

			if ($prodlocn2 <> "")
			{
				list($currentstatus, $currentqty) = explode("|", $prodlocn2);
			}
			else
			{
				//check prod exists in locn
				//want locns for product
				$Query = "select first 1 issn.wh_id, issn.locn_id ,issn.issn_status ";
				$Query .= "from issn ";
				$Query .= "where issn.prod_id = '".$product."' ";
				$Query .= "and issn.wh_id = '" ; 
				$Query .= substr($prodlocn,0,2)."' AND issn.locn_id = '";
				$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "' ";
				$Query .= "and issn.company_id = '".$owner."' ";
				$Query .= " and (issn.issn_status < 'X' or issn.issn_status > 'X~') ";
				//echo $Query;			
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to query product!<br>\n");
					exit();
				}
				if (($Row = ibase_fetch_row($Result)) )
				{
					// have prod in locn
					$wk_dummy = 1;
				}
				else
				{
					// not in locn
					$message = "Product Not in Location";
					echo ("<B><FONT COLOR=ORANGE>$message</FONT></B>\n");
					//unset ($product);
				}
				//release memory
				ibase_free_result($Result);
			}
		}
	}
	if (isset($product))
	{
		if ($product <> "")
		{
			if (isset($qty))
			{
				if ($qty <> 0)
				{
					if (($qty + $currentqty ) >= 0)
					{
						if ($doconfirm == 'F' or checkadjust($confirmadjust))
						{

							// do transactions
		$my_source = 'SSBSSKSSS';
		$tran_qty = $qty;
		$location = $prodlocn;
		$my_object = $product;
		$my_sublocn = $currentstatus;
		$my_ref = "Product Adjustment" ;
		$my_ref = "Prod Adj " ;
		if (isset($reason))
		{
			$my_ref .= $reason;
		}
		if (strlen($my_ref) > 40)
		{
			$my_ref = substr($my_ref,0,40);
		}

		$my_message = "";
		$my_message = dotransaction("STPA", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,'Y',$product, $owner);
		if ($my_message > "")
		{
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$message .= urldecode($my_mess_label) . " ";
			echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
		}
		else
		{
			unset($product);
		}
						}
						else
						{
							$message .= "Not a Stock Adjust Allowed Password";
							echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
							$wk_get_confirm = 'T';
						}

					}

				}
			}
		}
	}
  	
	if (isset($prodlocn))
	{
		if (isset($product))
		{
			if ($product <> "")
			{
				if ($prodlocn2 <> "")
				{

					if (isset($wk_get_confirm))
					{
  						echo("<H4 ALIGN=\"LEFT\">Enter Adjustment Password</H4>");
					}
					else
					{
	
  						echo("<H4 ALIGN=\"LEFT\">Enter Adjustment Qty</H4>");
					}			

				}
				else
				{
  					echo("<H4 ALIGN=\"LEFT\">Select Status to Adjust</H4>");
				}

			}
			else
			{
  				echo("<H4 ALIGN=\"LEFT\">Enter Product to Adjust</H4>");
			}
			
		}
		else
		{
  			echo("<H4 ALIGN=\"LEFT\">Enter Product to Adjust</H4>");
		}
	}
	else
	{
  		echo("<H4 ALIGN=\"LEFT\">Enter Location</H4>");
	}

 //echo("<form action=\"GetProductAdj.php\" method=\"post\" name=getproduct ONSUBMIT=\"return processEdit2();\">\n");
// echo("<form action=\"GetProductAdj.php\" method=\"post\" name=getproduct  >\n");
 echo("<form action=\"GetProductAdj.php\" method=\"post\" name=getproduct  >\n");
 echo("<p>\n");
echo("<input type=\"text\" name=\"message\" id=\"message\" readonly size=\"40\" ><br>");
echo("<br>");
echo("Location: <input type=\"text\" name=\"prodlocn\"");
if (isset($prodlocn)) 
{
	echo(" value=\"".$prodlocn."\"");
}
echo(" size=\"10\" id=\"prodlocn\" ");
echo(" maxlength=\"13\" onchange=\"document.getproduct.submit()\"><br>\n");
if (isset($prodlocn))
{
// ====================================================================
// product owner

	$wk_company_cnt = 0;
	$default_comp = "";
	// get the default company
	$Query = "select company_id from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$default_comp = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	$Query = "select count(*)  from company "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_company_cnt = $Row[0];
	}
	//release memory
	ibase_free_result($Result);

	if (isset($owner))
	{
		if ($owner != "")
		{
			$default_comp = $owner;
		}
	}
	if ($wk_company_cnt > 1)
	{
		//echo("<label for=\"currentcompany\">Owner</label>");
		echo("Owner");
		//echo("<label for=\"owner\">Owner:</label>");
		// //echo("Owner:<select name=\"owner\" class=\"sel8\"");
		// echo("<select name=\"owner\" class=\"sel8\" id=\owner\"");
		// echo(" size=\"4\" onchange=\"document.getproduct.submit()\">\n");
		//$Query = "select company_id, name from company order by name "; 
		$Query = "select company_id, name from company order by company_id "; 
		$Query = "select company_id, substr(name, 1,18) from company order by company_id "; 
		//echo($Query);
/*
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Company!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($default_comp == $Row[0])
			{
				echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
			}
			else
			{
				echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
			}
		}
		//release memory
		ibase_free_result($Result);
*/
		if (isset($owner))
		{
			//echo("<input type=\"text\" name=\"currentcompany\" value=\"$owner\" readonly  >\n");
			//echo("<input type=\"text\" name=\"currentcompany\" size=\"13\" value=\"$owner\" readonly  >\n");
			echo("<input type=\"text\" name=\"currentcompany\" size=\"20\" value=\"$owner\" readonly  id=\"owner\"  >\n");
		}
		dopager($Query);
		$wk_add_pager_javascript= 'T';
		//echo("</select><br>\n");
		//echo("<br>\n");
		//echo("<br>\n");
		//echo("<br>\n");
		//echo("<br>\n");
		echo("<input type=\"hidden\" name=\"owner\" value=\"");
		if (isset($owner))
		{
			echo($owner);
		}
		echo("\"  >\n");
		echo("<br>\n");
	} else {
		echo("<input type=\"hidden\" name=\"owner\"  value=\"$owner\" >\n");
		//echo("<input type=\"hidden\" name=\"owner\"  value=\"$default_comp\" >\n");
	}
// =============================================== end of owner
	echo("Product: <input type=\"text\" name=\"product\"");
	if (isset($product)) 
	{
		if (!empty($product))
		{
			echo(" value=\"".$product."\"");
		}
	}
	echo(" size=\"30\"");
	//echo(" maxlength=\"33\" onchange=\"document.getproduct.submit()\"><BR>\n");
	echo(" maxlength=\"33\" onchange=\"document.getproduct.submit();\">\n");
	if (!isset($product))
	{
		echo("<br>");
 		echo("<table BORDER=\"0\" align=\"LEFT\">");
		whm2buttons('Accept', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
		$wk_have_buttons = True;
		//echo("</form>\n");
	} else {
		echo("</form>\n");
	}
 	echo("<form action=\"GetProductAdj.php\" method=\"post\" name=getproduct2  >\n");
	if (isset($product))
	{
		echo("<input type=\"hidden\" name=\"product\" value=\"" . $product . "\">\n");
	}
	if (isset($owner))
	{
		echo("<input type=\"hidden\" name=\"owner\" value=\"" . $owner . "\">\n");
	}
	if (isset($prodlocn))
	{
		echo("<input type=\"hidden\" name=\"prodlocn\" value=\"" . $prodlocn . "\">\n");
	}

	if (isset($product))
	{
		if (isset($wk_prod_short_desc))
		{
			echo("<input type=\"text\" name=\"prodshortdesc\" readonly value=\"$wk_prod_short_desc\" size=\"50\"><br>");
		}
/*
		if (isset($wk_prod_long_desc))
		{
			//echo("<textarea name=\"longdesc\" readonly rows=\"2\" cols=\"50\">$wk_prod_long_desc</textarea>");
			echo("<textarea name=\"longdesc\" readonly rows=\"1\" cols=\"50\">$wk_prod_long_desc</textarea>");
		}
*/
		if ($prodlocn2 <> "")
		{
			echo("<input type=\"hidden\" name=\"prodlocn2\" value=\"". $prodlocn2 . "\">");
			//echo("<input type=\"text\" name=\"prodlocn2\" value=\"". $prodlocn2 . "\" readonly >");
			echo("Current Qty <input type=\"text\" name=\"currentqty\"");
			if (isset($currentqty)) 
			{
				echo(" value=\"".$currentqty."\"");
			}
			echo(" size=\"6\"");
			//echo(" readonly><BR>\n");
			echo(" readonly>\n");
			echo("Status <input type=\"text\" name=\"currentstatus\"");
			if (isset($currentstatus)) 
			{
				echo(" value=\"".$currentstatus."\"");
			}
			echo(" size=\"2\"");
			echo(" readonly><br>\n");
			echo("Qty to Adjust: <input type=\"text\" name=\"qty\"");
			if (isset($qty)) 
			{
				echo(" value=\"".$qty."\"");
			}
			echo(" size=\"7\"");
			echo(" maxlength=\"7\" onchange=\"return processEdit(" . $currentqty . ")\"><br>\n");
			if ($doconfirm == 'T')
			{
				echo("Confirm Adjust Password: <input type=\"password\" name=\"confirmadjust\"");
				echo(" size=\"8\"");
				echo(" ><br>\n");
		
			}
			echo("Reason: <input type=\"text\" name=\"reason\"");
			if (isset($reason)) 
			{
				echo(" value=\"".$reason."\"");
			}
			echo(" size=\"40\"");
			echo(" maxlength=\"40\" ><br>\n");
		}
		else
		{
			//want locns for product
			//$Query = "select location.locn_name, coalesce(issn.issn_status,'ST'), sum(coalesce(issn.current_qty,0)) ";
			$Query = "select substr(location.locn_name,1,25) as locns_name, coalesce(issn.issn_status,'ST') as issns_status, sum(coalesce(issn.current_qty,0)) as prods_qty ";
			$Query .= "from location ";
			$Query .= "left outer join issn on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
			$Query .= "and    issn.prod_id = '".$product."' ";
			$Query .= "and issn.company_id = '".$owner."' ";
			$Query .= "and (issn.issn_status < 'X' or issn.issn_status > 'X~') ";
			$Query .= "where location.wh_id = '" ; 
			$Query .= substr($prodlocn,0,2)."' AND location.locn_id = '";
			$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "' ";
			//$Query .= "group by location.locn_name, issn.issn_status ";
			$Query .= "group by locns_name, issns_status ";
			
			// Create a table.
			echo ("<table border=\"1\">\n");
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query product!<br>\n");
				exit();
			}
			// echo headers
			echo ("<tr>\n");
			echo("<th>Name</th>\n");
			echo("<th>Status</th>\n");
			echo("<th>Qty</th>\n");
			echo ("</tr>\n");
			
			// Fetch the results from the database.
			while (($Row = ibase_fetch_row($Result)) )
			{
			 	echo ("<tr>\n");
				for ($i=0; $i<3; $i++)
				{
					if ($i == 1)
					{
						echo("<td>");
						echo("<form action=\"GetProductAdj.php\" method=\"post\" name=getlocn>\n");
						echo("<input type=\"hidden\" name=\"prodlocn2\" value=\"" . $Row[1] . '|' . $Row[2] . "\">\n");
						echo("<input type=\"hidden\" name=\"product\" value=\"" . $product . "\">\n");
						echo("<input type=\"hidden\" name=\"owner\" value=\"" . $owner . "\">\n");
						echo("<input type=\"hidden\" name=\"prodlocn\" value=\"" . $prodlocn . "\">\n");
						echo("<input type=\"submit\" name=\"locn_id\" value=\"$Row[$i]\">\n");
						echo("</form>\n");
						echo("</td>");
					}
					else
					{
			 			echo ("<td>$Row[$i]</td>\n");
					}
				}
			 	echo ("</tr>\n");
			}
			echo ("</table>\n");
			
			//release memory
			//$Result->free();
			ibase_free_result($Result);

		}
	}
}

{
	// html 4.0 browser
 	echo("<table BORDER=\"0\" align=\"LEFT\">");
	if (isset($qty))
	{
		if (!isset($wk_have_buttons))
		{
			whm2buttons('Adjust', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","adjustprod.gif");
		}
	}
	else
	{
		if (!isset($wk_have_buttons))
		{
			whm2buttons('Accept', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
			//echo("</form>\n");
		}
		//whm2buttons('Accept', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
//commit
//ibase_commit($dbTran);
?>
</P>
<script type="text/javascript">
<?php
	if ($wk_add_pager_javascript== 'T')
	{
	echo("
function saveMe(mycomp) {
/* # save my company */
  var mytype;
  	document.getproduct.owner.value = mycomp; 
  	document.getproduct.submit(); 
	return true;
}
");

	}
	if (isset($prodlocn))
	{
		if (isset($product))
		{
			if ($product <> "")
			{
				if ($prodlocn2 <> "")
				{
					if (isset($wk_get_confirm))
					{
						echo("document.getproduct2.confirmadjust.focus();");
					}
					else
					{

						echo("document.getproduct2.qty.focus();");
					}
				}
			}
			else
			{
				echo("document.getproduct.product.focus();");
			}

		}
		else
		{
			echo("document.getproduct.product.focus();");
		}
	}
	else
	{
		echo("document.getproduct.prodlocn.focus();");
	}
?>
</script>
</body>
</html>
