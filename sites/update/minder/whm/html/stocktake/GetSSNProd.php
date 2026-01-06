<?php
include "../login.inc";
//setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Product you are working on</title>
<?php
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="product.css">
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
function removeMessage(fromstr,fromvalue)
{
	var wkIndex;
	var wkLen;
	var wkFromLen;
	var wkPosnBefore;
	var wkPosnAfter;
	var wkBefore;
	var wkAfter;
	var wkReturn;
	wkIndex = fromstr.indexOf(fromvalue)
	/* alert("str:"+fromstr+"value:"+fromvalue);
	alert("index:"+wkIndex); */
	if (wkIndex > -1)
	{
		/* found */
		wkLen = fromvalue.length;
		wkFromLen = fromstr.length;
		wkPosnAfter = wkLen + wkIndex;
		wkPosnBefore = wkIndex - 1;
		/* wkx = string before + string after */
		if (wkPosnBefore > -1)
		{
			wkBefore = fromstr.substring(0,wkPosnBefore)
		}
		else
		{
			wkBefore = ""
		}
		if (wkPosnAfter < wkFromLen)
		{
			wkAfter = fromstr.substring(wkPosnAfter)
		}
		else
		{
			wkAfter  = ""
		}
		/* alert("before:"+wkBefore+"after:"+wkAfter); */
		wkReturn = wkBefore + " " + wkAfter
	}
	else
	{
		/* not found */
		/* alert("not found"); */
		wkReturn =  fromstr
	}
	return wkReturn
}
function processEdit() {
/* enter in qty submits form */
  	var csum;
  /* document.getproduct.message.value=" "; */
  if ( document.getproduct.qty.value=="")
  {
  	document.getproduct.message.value="Must Enter a Qty";
	document.getproduct.qty.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Must Enter a Qty")
  if ( chkNumeric(document.getproduct.qty.value)==false)
  {
  	document.getproduct.message.value="Must be Numeric";
	document.getproduct.qty.value = "";
	document.getproduct.qty.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Must be Numeric")
  csum = 0
  csum += (document.getproduct.qty.value * 1)
  if (( csum) < 0)
  {
  	document.getproduct.message.value="Product Cannot have its Qty Set Negative";
	document.getproduct.qty.value = "";
	document.getproduct.qty.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Product Cannot have its Qty Set Negative")
  return true;
}
</script>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFF00">

<?php
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	include "transaction.php";
	include "checkdatajs.php";
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
	whm2scanvars($Link, 'prod13','PROD_13', 'PROD13');
	whm2scanvars($Link, 'ssn','BARCODE', 'BARCODE');
	whm2scanvars($Link, 'altssn','ALTBARCODE', 'ALTBARCODE');
?>

<script type="text/javascript">
function processEdit2(how) {
/* # check for valid location */
  var mytype;
  var myvalue;
  var mylen;
  var myinstr;
  /* document.getproduct.message.value=" "; */
  if ( document.getproduct.prodlocn.value=="")
  {
  	document.getproduct.message.value="Must Enter a Location";
	document.getproduct.prodlocn.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Must Enter a Location")
  myvalue = document.getproduct.prodlocn.value;
  document.getproduct.prodlocn.value = myvalue.toUpperCase();
  mytype = checkLocn(document.getproduct.prodlocn.value); 
  if (mytype == "none")
  {
	/* alert("Not a Location"); */
  	document.getproduct.message.value="Not a Location";
	document.getproduct.prodlocn.value = "";
	document.getproduct.prodlocn.focus();
  	return false;
  }
  else
  {
  	document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not a Location")
  	/* alert("a location startposn " + startposn );  */
	myvalue = document.getproduct.prodlocn.value;
	mylen = myvalue.length - startposn;
	myinstr = myvalue.substr(startposn,mylen);
	/* alert(myinstr); */
	document.getproduct.prodlocn.value = myinstr; 
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
  var myvalue;
  var mylen;
  var myinstr;
  /* document.getproduct.message.value=" "; */
  if ( document.getproduct.product.value=="")
  {
  	document.getproduct.message.value="Must Enter a Product";
	document.getproduct.product.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Must Enter a Product")
  myvalue = document.getproduct.product.value;
  document.getproduct.product.value = myvalue.toUpperCase();

  mytype = checkLocn(document.getproduct.product.value); 
  if (mytype == "none")
  {
	  mytype = checkprod13(document.getproduct.product.value); 
	  if (mytype == "none")
	  {
		  mytype = checkAltprod(document.getproduct.product.value); 
		  if (mytype == "none")
		  {
			/* not an alt prod - try a prod */
			mytype = checkProd(document.getproduct.product.value); 
			if (mytype == "none")
			{
				/* alert("Not a Product"); */
		  		document.getproduct.message.value="Not a Product";
				document.getproduct.product.value = "";
				document.getproduct.product.focus();
			  	return false;
			}
			else
			{
		  		document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not a Product")
				myvalue = document.getproduct.product.value;
				mylen = myvalue.length - startposn;
				myinstr = myvalue.substr(startposn,mylen);
				document.getproduct.product.value = myinstr; 
				if (how == 1)
			 	{
					document.getproduct.submit();
				}
				return true;
			}
		  }
		  else
		  {
		  	document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not a Product")
			myvalue = document.getproduct.product.value;
			mylen = myvalue.length - startposn;
			myinstr = myvalue.substr(startposn,mylen);
			document.getproduct.product.value = myinstr; 
			if (how == 1)
		 	{
				document.getproduct.submit();
			}
			return true;
		  }
	  }
	  else
	  {
	  	document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not a Product")
		myvalue = document.getproduct.product.value;
		mylen = myvalue.length - startposn;
		myinstr = myvalue.substr(startposn,mylen);
		document.getproduct.product.value = myinstr; 
		if (how == 1)
	 	{
			document.getproduct.submit();
		}
		return true;
	  }
  }
  else
  {
  	document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not a Product")
	myvalue = document.getproduct.product.value;
	mylen = myvalue.length - startposn;
	myinstr = myvalue.substr(startposn,mylen);
	document.getproduct.prodlocn.value = myinstr; 
	document.getproduct.product.value = ""; 
	if (how == 1)
 	{
		document.getproduct.submit();
	}
	return true;
  }
}
function processEdit4(how) {
/* # check for valid ssn */
/* scanning an ssn forces submit of form !!! */
  var mytype;
  var myvalue;
  var mylen;
  var myinstr;
  if ( document.getproduct.ssn.value=="")
  {
  	document.getproduct.message.value="Must Enter an SSN";
	document.getproduct.ssn.focus();
  	return false;
  }
  document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Must Enter an SSN")
  myvalue = document.getproduct.ssn.value;
  document.getproduct.ssn.value = myvalue.toUpperCase();
  mytype = checkSsn(document.getproduct.ssn.value); 
  if (mytype == "none")
  {
	mytype = checkAltssn(document.getproduct.ssn.value); 
	if (mytype == "none")
	{
  		mytype = checkLocn(document.getproduct.ssn.value); 
 		if (mytype == "none")
  		{
			  mytype = checkprod13(document.getproduct.ssn.value); 
			  if (mytype == "none")
			  {
				  mytype = checkAltprod(document.getproduct.ssn.value); 
				  if (mytype == "none")
				  {
					/* not an alt prod - try a prod */
					mytype = checkProd(document.getproduct.ssn.value); 
					if (mytype == "none")
					{
						/* alert("Not an SSN"); */
		  				document.getproduct.message.value="Not-an-SSN";
						document.getproduct.ssn.value = "";
						document.getproduct.ssn.focus();
	  					return false;
					}
					else
					{
		  				document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
						myvalue = document.getproduct.ssn.value;
						mylen = myvalue.length - startposn;
						myinstr = myvalue.substr(startposn,mylen);
						document.getproduct.product.value = myinstr; 
						document.getproduct.ssn.value = "";
						if (how == 1)
			 			{
							document.getproduct.submit();
						}
						return true;
					}
		  		}
		  		else
		  		{
		  			document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
					myvalue = document.getproduct.ssn.value;
					mylen = myvalue.length - startposn;
					myinstr = myvalue.substr(startposn,mylen);
					document.getproduct.product.value = myinstr; 
					document.getproduct.ssn.value = "";
					if (how == 1)
		 			{
						document.getproduct.submit();
					}
					return true;
		  		}
          		}
	  		else
	  		{
	  			document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
				myvalue = document.getproduct.ssn.value;
				mylen = myvalue.length - startposn;
				myinstr = myvalue.substr(startposn,mylen);
				document.getproduct.product.value = myinstr; 
				document.getproduct.ssn.value = "";
				if (how == 1)
	 			{
					document.getproduct.submit();
				}
				return true;
	  		}
		}
		else
		{
  			document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
			myvalue = document.getproduct.ssn.value;
			mylen = myvalue.length - startposn;
			myinstr = myvalue.substr(startposn,mylen);
			document.getproduct.prodlocn.value = myinstr; 
			document.getproduct.product.value = ""; 
			document.getproduct.ssn.value = ""; 
			if (how == 1)
		 	{
				document.getproduct.submit();
			}
			return true;
		}
	}
	else
	{
  		document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
	  	/* document.getproduct.message.value=" "; */
		myvalue = document.getproduct.ssn.value;
		mylen = myvalue.length - startposn;
		myinstr = myvalue.substr(startposn,mylen);
		document.getproduct.ssn.value = myinstr; 
		if (how == 1)
	 	{
			document.getproduct.qty.focus();
		}
		return false;
  	}
  }
  else
  {
  	document.getproduct.message.value = removeMessage(document.getproduct.message.value,"Not-an-SSN")
  	/* document.getproduct.message.value=" "; */
	myvalue = document.getproduct.ssn.value;
	mylen = myvalue.length - startposn;
	myinstr = myvalue.substr(startposn,mylen);
	document.getproduct.ssn.value = myinstr; 
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
	//$message = '';
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
	if (isset($product))
	{
		if ($product == "")
		{
			unset ($product);
			$ssn = "";
			$qty = 1;
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
	include "checkdata.php";
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
			$message = str_replace( "Not a Valid Location","", $message);
		}
		else
		{
			if ( strpos($message, "Not a Valid Location") === False)
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
			if (isset($message))
			{
				$message_save =  $message;
			}
			$field_type = checkForTypein($product, 'PROD_13' ); 
			if ($field_type == "none")
			{
				// not a prod 13 - perhaps an prod internal
				$field_type = checkForTypein($product, 'PROD_INTERNAL' ); 
				if ($field_type == "none")
				{
					// not a prod internal - perhaps an alt prod internal
					$field_type = checkForTypein($product, 'ALT_PROD_INTERNAL' ); 
					if ($field_type == "none")
					{
						// not a product
						unset($product);
						$message_save = "Not a Product";
					} else {
						// an alt prod internal
						if ($startposn > 0)
						{
							$wk_realdata = substr($product,$startposn);
							$product = $wk_realdata;
						}
					}
				} else {
					// a prod internal
					if ($startposn > 0)
					{
						$wk_realdata = substr($product,$startposn);
						$product = $wk_realdata;
					}
				}
			} else {
				// a prod 13
				if ($startposn > 0)
				{
					$wk_realdata = substr($product,$startposn);
					$product = $wk_realdata;
				}
			}
			if (isset($message_save))
			{
				$message = $message_save ;
			}
		}
		if (isset($product))
		{
			//want locns for product
			$Query = "select short_desc, long_desc ";
			$Query .= "from prod_profile ";
			$Query .= "where prod_id = '".$product."' ";
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to query product!<BR>\n");
				exit();
			}
			if (($Row = ibase_fetch_row($Result)) )
			{
				// have prod in locn
				$wk_prod_short_desc = $Row[0];
				$wk_prod_long_desc = $Row[1];
				$message = str_replace( "Product-Not-Found","", $message);
			}
			else
			{
				if ( strpos($message, "Product-Not-Found") === False)
					$message .= "Product-Not-Found";
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
		if (isset($message))
		{
			$message_save =  $message;
		}
		$field_type = checkForTypein($ssn, 'BARCODE','SSN' ); 
		if ($field_type == "none")
		{
			// not an ssn - perhaps an altbarcode
			$field_type = checkForTypein($ssn, 'ALTBARCODE' ); 
			if ($field_type == "none")
			{
				// not an ssn
				unset($ssn);
				unset($qty);
				$message_save = "Not an SSN";
			} else {
				// an alt ssn 
				if ($startposn > 0)
				{
					$wk_realdata = substr($ssn,$startposn);
					$ssn = $wk_realdata;
				}
			}
		} else {
			// an ssn 
			if ($startposn > 0)
			{
				$wk_realdata = substr($ssn,$startposn);
				$ssn = $wk_realdata;
			}
		}
		if (isset($message_save))
		{
			$message = $message_save ;
		}
	}
	if (isset($ssn))
	{
		$Query = "select ssn.storage_uom, issn.prod_id ";
		$Query .= "from issn ";
		$Query .= "join ssn on issn.original_ssn = ssn.ssn_id ";
		$Query .= "where issn.ssn_id = '" . $ssn ."' ";
			
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query SSN!<BR>\n");
			exit();
		}
		if (($Row = ibase_fetch_row($Result)) )
		{
			$wk_uom = $Row[0];
			$wk_current_prod = $Row[1];
		}
		if (!isset($wk_uom))
		{
			$wk_uom = "EA";
		}
		ibase_free_result($Result);
	}
	if ((isset($product)) and (isset($wk_current_prod))) 
	{
		/*
		if current prod is null then a new prod is allowed
		otherwise the current prod must match the one
		we are scanning for at the moment
		*/
		if ($wk_current_prod <> "") // not null
		{
			if ($wk_current_prod <> $product)
			{
				// check whether can change the prod - 06/08/07
				$Query = "select description ";
				$Query .= "from options ";
				$Query .= "where group_code = 'ChgProduct'  and code = 'STOB' ";
			
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to query Options!<BR>\n");
					exit();
				}
				if (($Row = ibase_fetch_row($Result)) )
				{
					$wk_change_prod = $Row[0];
				}
				if (!isset($wk_change_prod))
				{
					$wk_change_prod = "F";
				}
				ibase_free_result($Result);
                                if ($wk_change_prod == 'F')
				{
					if ( preg_match( "/SSN .* is for Product .* Cannot change this/", $message) ==  0) {
						$message .= "SSN " . $ssn . " is for Product " . $wk_current_prod . " Cannot change this";
					} else {
						$message = preg_replace( "/SSN .* is for Product .* Cannot change this/", 
							"SSN " . $ssn . " is for Product " . $wk_current_prod . " Cannot change this",
							$message);
					}
					unset($qty);
					unset($ssn);
				} else {
					$message = preg_replace( "/SSN .* is for Product .* Cannot change this/", "", $message);
				}
			}
		}
	}
	if ((isset($prodlocn)) and (!isset($product)) and (!isset($ssn)))
	{
		// do transactions for new location
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
			if ( preg_match( "/New Location .* /", $message) >  0) {
				$message = preg_replace( "/New Location .* /", 
				 	"" ,
					$message);
			}
		}
		else
		{
/*
			if ( preg_match( "/New Location .* /", $message) ==  0) {
				$message .= "New Location " . $prodlocn ." ";
			} else {
				$message = preg_replace( "/New Location .* /", 
				 	"New Location " . $prodlocn ." ",
					$message);
			}
*/
			$message = "New Location " . $prodlocn ." ";
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
	//$my_sublocn = "";
	$my_sublocn = $owner;
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
			echo("Unable to query ISSN!<BR>\n");
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
			if ( preg_match( "/Item .* Seen /", $message) >  0) {
				$message = preg_replace( "/Item .* Seen /", 
				 	"" ,
					$message);
			}
			if ( strpos($message, "Item seen but Recount Required") === False)
				$message = "Item seen but Recount Required";
			unset($qty);
		}
		else
		{
			$message = str_replace( "Item seen but Recount Required","", $message);

			//$message .= "Item " . $ssn . " Seen ";
			$message = "Item " . $ssn . " " . $wk_prod_short_desc . " Seen ";
/*
			if ( preg_match( "/Item .* Seen /", $message) ==  0) {
				$message = "Item " . $ssn . $wk_prod_short_desc . " Seen ";
			} else {
				$message = preg_replace( "/Item .* Seen /", 
				 	"Item " . $ssn . $wk_prod_short_desc ." Seen ",
					$message);
			}
*/
			unset($ssn);
		}
		ibase_free_result($Result);

	}
	$wk_docommit = "Y";
			}
		}
	}

/*
	if (isset($message))
	{

		echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	}
*/
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


echo(" <form action=\"GetSSNProd.php\" method=\"post\" name=\"getproduct\" ONSUBMIT=\"return processEdit2(0)");
if (isset($product))
	echo(" && processEdit3(0)");
echo(";\">\n");
echo(" <P>\n");
echo("<INPUT type=\"hidden\" name=\"audited\" value=\"" . $wk_audited . "\">");
echo("<table border=\"0\">");
echo("<tr><td>");
echo("Location:</td><td><INPUT type=\"text\" name=\"prodlocn\"");
if (isset($prodlocn)) 
{
	echo(" value=\"".$prodlocn."\"");
}
echo(" size=\"12\"");
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
// start of company
echo("Owned by:</td><td><SELECT name=\"owner\" class=\"sel3\" >\n");
$wk_get_control_company = "Y";
if (isset($owner))
{
	if ($owner <> "")
	{
		$default_comp = $owner;
		$wk_get_control_company = "N";
	}
}
if ($wk_get_control_company == "Y")
{
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
}

$Query = "select company_id, name from company order by name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Company!<br>\n");
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
echo("</SELECT>\n");
// end of company
	echo("</td></tr>");

//if (isset($prodlocn))
//{
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
		unset($wk_prod_short_desc);
		/* save screen space */
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

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"40\" value=\"");
/*
	if (isset($locn_desc))
	{
		echo($locn_desc);
	}
*/
	if (isset($message))
	{
		echo($message);
	}
 echo("\"><br>");
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
<?php
/*
echo("<form action=\"\" name=getcons >");
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"100\" ");
echo("><br>");
*/
?>
</body>
</html>
