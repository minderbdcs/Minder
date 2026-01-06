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
function processEdit(currentqty) {
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
  csum = currentqty * 1
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
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	// create js for location check
	whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');

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
	if (isset($product))
	{
		if (isset($prodlocn)) 
		{
			echo("<script type=\"text/javascript\">\n");
			echo "function processEdit2() {\n";
			echo "  var mytype;\n";
			echo "  mytype = checkLocn(document.getproduct.prodlocn.value); \n";
			echo '  if (mytype == "none")';
			echo "  {\n";
			echo '	alert("Not a Location");';
			echo "  	return false;\n";
			echo "  }\n";
			echo "  else\n";
			echo "  {\n";
			echo "	return true;\n";
			echo "  }\n";
			echo "}\n";
			echo "</script>";
		}
	}

	if (isset($product))
	{
		// check product kit exists
		$Query = "select kit_id ";
		$Query .= "from kit ";
		$Query .= "where kit_id = '".$product."' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query Kit!<BR>\n");
			exit();
		}
		// echo headers
		
		// Fetch the results from the database.
		if (($Row = ibase_fetch_row($Result)) )
		{
			$kitid = $Row[0];
		}
		else
		{
			unset ($product);
			echo ("<B><FONT COLOR=RED>Not a Kitted Product</FONT></B>\n");
		}
		//release memory
		//$Result->free();
		ibase_free_result($Result);
	}
	if (isset($prodlocn))
	{
		// check location exists
		$Query = "select wh_id, locn_id ";
		$Query .= "from location ";
		$Query .= "where wh_id = '";
		$Query .= substr($prodlocn,0,2). "' ";
		$Query .= "and locn_id = '";
		$Query .= substr($prodlocn,2,strlen($prodlocn) - 2)."' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			print("Unable to query Location!<BR>\n");
			exit();
		}
		// echo headers
		
		// Fetch the results from the database.
		if (($Row = ibase_fetch_row($Result)) )
		{
			$kitwhid = $Row[0];
		}
		else
		{
			unset ($prodlocn);
			echo ("<B><FONT COLOR=RED>Location does Not Exist</FONT></B>\n");
		}
		//release memory
		//$Result->free();
		ibase_free_result($Result);
	}
	if (isset($product))
	{
		if (isset($qty))
		{
			if ($qty > 0)
			{
				/*
				if (isset($image_x) and isset($image_y))
				{
					if ($image_x > 0 and $image_y > 0)
					{
				*/
						// do transactions
	$my_source = 'SSBSSKSSS';
	$tran_qty = $qty;
	$location = $prodlocn;
	$my_object = $product;
	$my_sublocn = "";
	$my_ref = "Kit Creation" ;

	$my_message = "";
	$my_message = dotransaction("STKA", "P", $my_object, $location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
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
/*
					}
				}
*/
			}
		}
	}
	if (isset($product))
	{
		if (isset($prodlocn))
		{
  			echo("<H4 ALIGN=\"LEFT\">Enter Qty to Create</H4>");
		}
		else
		{
  			echo("<H4 ALIGN=\"LEFT\">Enter Location to Create in</H4>");
		}
	}
	else
	{
  		echo("<H4 ALIGN=\"LEFT\">Enter Product Kit</H4>");
	}


 	echo '<FORM action="AddProductKit.php" method="post" name=getproduct ';
	if (isset($product))
	{
		echo 'ONSUBMIT="return processEdit2();"';
	}
	echo ">\n";
?>
 <P>
<?php
echo("<INPUT type=\"text\" name=\"message\" readonly size=\"40\" ><br>");
echo("Product: <INPUT type=\"text\" name=\"product\"");
if (isset($product)) 
{
	echo(" value=\"".$product."\"");
}
echo(" size=\"30\"");
echo(" maxlength=\"30\" onchange=\"document.getproduct.submit()\"><BR>\n");
if (isset($product))
{
	echo("Location: <INPUT type=\"text\" name=\"prodlocn\"");
	if (isset($prodlocn)) 
	{
		echo(" value=\"".$prodlocn."\"");
	}
	echo(" size=\"10\"");
	echo(" maxlength=\"10\" onchange=\"document.getproduct.submit()\"><BR>\n");
	if (isset($prodlocn))
	{
		echo("Qty to Create: <INPUT type=\"text\" name=\"qty\"");
		if (isset($qty)) 
		{
			echo(" value=\"".$qty."\"");
		}
		echo(" size=\"7\"");
		echo(" maxlength=\"7\" onchange=\"return processEdit(0)\"><BR>\n");
	}
}

{
	// html 4.0 browser
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	if (isset($product) and isset($prodlocn) ) 
	{
		whm2buttons('Create', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","addkit.gif");
	}
	else
	{
		whm2buttons('Accept', 'Stocktake_menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	}
}
//commit
//ibase_commit($dbTran);
?>
</P>
<script type="text/javascript">
<?php
	if (isset($product))
	{
		if (isset($prodlocn))
		{
			echo("document.getproduct.qty.focus();");
		}
		else
		{
			echo("document.getproduct.prodlocn.focus();");
		}
	}
	else
	{
		echo("document.getproduct.product.focus();");
	}
?>
</script>
</body>
</html>
