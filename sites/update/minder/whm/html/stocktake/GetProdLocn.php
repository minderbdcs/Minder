<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Product you are working on</title>
<script type="text/javascript">
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
function processEdit() {
  	var csum;
  document.getproduct.message.value=" ";
  if ( document.getproduct.qty.value=="")
  {
  	document.getproduct.message.value="Must Enter a Qty";
	document.getproduct.qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getproduct.qty.value)==false)
  {
  	document.getproduct.message.value="Must be Numeric";
	document.getproduct.qty.value = "";
	document.getproduct.qty.focus();
  	return false;
  }
  csum = 0
  csum += (document.getproduct.qty.value * 1)
  if (( csum) < 0)
  {
  	document.getproduct.message.value="Product Cannot have its Qty Set Negative";
	document.getproduct.qty.value = "";
	document.getproduct.qty.focus();
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
	include "checkdatajs.php";
/*
scan a location
do an stlo

if scan a new locn
do an stlx and stlo for new locn

	scan a product
	enter a qty
	if dosnt match qty in location
	then
	begin
		 do a recount
		get qty
		if the qty matches last entered
			do a prod adj
		else go to get 1st qty
	end
	else nochange
	go to get next product

on exit do a stlx on last locn
*/
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	// create js for location check
	whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
	whm2scanvars($Link, 'prod','PROD_INTERNAL', 'PRODINTERNAL');
	whm2scanvars($Link, 'altprod','ALT_PROD_INTERNAL', 'ALTPRODINTERNAL');
	whm2scanvars($Link, 'ssn','BARCODE', 'BARCODE');
?>

<script type="text/javascript">
function processEdit2(how) {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getproduct.prodlocn.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
	document.getproduct.prodlocn.focus();
  	return false;
  }
  else
  {
	if (how == 1)
 	{
		document.getproduct.submit();
	}
	return true;
  }
}
function processEdit3(how) {
/* # check for valid alt product */
  var mytype;
  mytype = checkAltprod(document.getproduct.prodlocn.value); 
  if (mytype == "none")
  {
	/* not an alt prod - try a prod */
	mytype = checkProd(document.getproduct.prodlocn.value); 
	if (mytype == "none")
	{
		alert("Not a Product");
		document.getproduct.prodlocn.focus();
	  	return false;
	}
	else
	{
		if (how == 1)
	 	{
			document.getproduct.submit();
		}
		return true;
	}
  }
  else
  {
	if (how == 1)
 	{
		document.getproduct.submit();
	}
	return true;
  }
}
function processEdit4(how) {
/* # check for valid ssn */
  var mytype;
  mytype = checkSsn(document.getproduct.ssn.value); 
  if (mytype == "none")
  {
	alert("Not an SSN");
	document.getproduct.ssn.focus();
  	return false;
  }
  else
  {
	if (how == 1)
 	{
		document.getproduct.qty.focus();
	}
	return false;
  }
}
</script>

<?php
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
	}
	if (isset($_GET['prodlocn'])) 
	{
		$prodlocn = $_GET['prodlocn'];
	}
	if (isset($_POST['ssn'])) 
	{
		$ssn = $_POST['ssn'];
	}
	if (isset($_GET['ssn'])) 
	{
		$ssn = $_GET['ssn'];
	}
	if (isset($ssn))
	{
		if ($ssn == "")
		{
			unset ($ssn);
			unset ($qty);
		}
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
	$wk_docommit = "N";
	if (isset($prodlocn))
	{
		$Query = "select locn_name ";
		$Query .= "from location ";
		$Query .= "where wh_id = '" . substr($prodlocn,0,2) . "' and locn_id='";
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2) . "'";
		
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query Location!<BR>\n");
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
	if (isset($prodlocn))
	{
		if (isset($product))
		{
			//want locns for product
			$Query = "select short_desc, long_desc ";
			$Query .= "from prod_profile ";
			$Query .= "where prod_id = '".$product."' ";
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to query product!<BR>\n");
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
				$message .= "Product Not Found";
				unset ($product);
				if (isset($ssn))
				{
					unset ($ssn);
					unset ($qty);
				}
			}
			ibase_free_result($Result);


		}
	}
	if (isset($ssn))
	{
		$Query = "select ssn.storage_uom ";
		$Query .= "from issn ";
		$Query .= "join ssn on issn.original_ssn = ssn.ssn_id ";
		$Query .= "where issn.ssn_id = '" . $ssn ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query SSN!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_uom = $Row[0];
		}
		if (!isset($wk_uom))
		{
			$wk_uom = "EA";
		}
		ibase_free_result($Result);

			
	}
	if ((isset($prodlocn)) and (!isset($product)) and (!isset($ssn)))
	{
		// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = 0;
	$location = $prodlocn;
	$my_object = "";
	$my_sublocn = "";
	$my_ref = "Stocktake Location" ;

	$my_message = "";
	$tran_tran = "STLO";
	$my_message = dotransaction($tran_tran, "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"N");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$message .= urldecode($my_mess_label) . " ";
	}
	else
	{
		$message .= "New Location " . $prodlocn;
	}
	$wk_docommit = "Y";
	}
	if (isset($product))
	{
		if (isset($qty))
		{
			if ($qty > 0)
			{
				// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = $qty;
	$location = $prodlocn;
	$my_object = $ssn;
	$my_sublocn = "";
	$my_ref = $wk_uom . "|" . $product ;

	$my_message = "";
	$tran_tran = "STOB";
	if ($wk_audited == "R")
	{
		$tran_tran = "STRC";
	}
	$my_message = dotransaction($tran_tran, "A", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, "N");
	if ($my_message > "")
	{
		list($my_mess_field, $my_mess_label) = explode("=", $my_message);
		$message .= urldecode($my_mess_label) . " ";
	}
	else
	{
		$Query = "select audited ";
		$Query .= "from issn ";
		$Query .= "where issn.ssn_id = '" . $ssn ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query ISSN!<BR>\n");
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
			$message .= "Item Seen but Recount Required";
			unset($qty);
		}
		else
		{
			$message .= "Item " . $ssn . " Seen ";
			unset($ssn);
		}
		ibase_free_result($Result);

	}
	$wk_docommit = "Y";
			}
		}
	}

	if (isset($message))
	{

		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
	if (isset($prodlocn))
	{
		if (isset($product))
		{
			if (isset($ssn))
			{
  				echo("<H4 ALIGN=\"LEFT\">Enter Qty</H4>");
			}
			else
			{
  				echo("<H4 ALIGN=\"LEFT\">Enter SSN and Qty</H4>");
			}
		}
		else
		{
  			echo("<H4 ALIGN=\"LEFT\">Enter Product </H4>");
		}
	}
	else
	{
  		echo("<H4 ALIGN=\"LEFT\">Enter Location</H4>");
	}

?>

 <form action="GetSSNProd.php" method="post" name=getproduct ONSUBMIT="return processEdit2(0);">
 <P>
<?php
echo("<INPUT type=\"hidden\" name=\"audited\" value=\"" . $wk_audited . "\">");
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"40\" value=\"");
	if (isset($locn_desc))
	{
		echo($locn_desc);
	}
 echo("\"><br>");
echo("<table border=\"0\">");
echo("<tr><td>");
echo("Location:</td><td><INPUT type=\"text\" name=\"prodlocn\"");
if (isset($prodlocn)) 
{
	echo(" value=\"".$prodlocn."\"");
}
echo(" size=\"10\"");
echo(" maxlength=\"10\" onfocus=\"document.getproduct.prodlocn.value=''\" onchange=\"return processEdit2(1)");
if (isset($product))
{
	echo(";document.getproduct.product.value=''");
}
echo("\">\n");
echo("</td>");
if (isset($prodlocn))
{
	echo("<tr><td>");
	echo("Product:</td><td> <INPUT type=\"text\" name=\"product\"");
	if (isset($product)) 
	{
		echo(" value=\"".$product."\"");
	}
	echo(" size=\"30\"");
	echo(" maxlength=\"30\" onfocus=\"document.getproduct.product.value=''\" onchange=\"return processEdit3(1)");
	if (isset($product) and isset($ssn))
	{
		echo(";document.getproduct.ssn.value=''");
		echo(";document.getproduct.qty.value=''");
	}
	echo("\"></td></tr>\n");
	if (isset($product))
	{
		if (isset($wk_prod_short_desc))
		{
			echo("<tr><td colspan=\"3\"><INPUT type=\"text\" readonly name=\"prodshortdesc\" value=\"$wk_prod_short_desc\" size=\"50\"></td></tr>");
		}
		{
			echo("<tr><td>SSN:</td><td> <INPUT type=\"text\" name=\"ssn\"");
			if (isset($ssn)) 
			{
				echo(" value=\"".$ssn."\"");
			}
			echo(" size=\"20\"");
			echo(" maxlength=\"20\" onchange=\"document.getproduct.qty.value='';return processEdit4(1)\"></td></tr>\n");
			//echo("<INPUT type=\"text\" name=\"ssn\" value=\"". $ssn . "\" readonly >");
			echo("<tr><td>Qty</td><td> <INPUT type=\"text\" name=\"qty\"");
			if (isset($qty)) 
			{
				echo(" value=\"".$qty."\"");
			}
			echo(" size=\"7\"");
			echo(" maxlength=\"7\" onchange=\"return processEdit()\"></td></tr>\n");
		}
	}
}

{
	// html 4.0 browser
 	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if (isset($qty))
	{
		whm2buttons('Accept', 'EndSSNProd.php',"Y","Back_50x100.gif","Back","adjustprod.gif");
	}
	else
	{
		whm2buttons('Accept', 'EndSSNProd.php',"Y","Back_50x100.gif","Back","accept.gif");
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
		if (isset($product) and ($product <> ""))
		{
			if (isset($ssn) and ($ssn <> ""))
			{
				echo("document.getproduct.qty.focus();");
			}
			else
			{
				echo("document.getproduct.ssn.focus();");
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
if ($wk_docommit == "Y")
{
	ibase_commit($Link);
}
ibase_close($Link);
?>
</script>
</body>
</html>
