<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Transfer Product From Location</title>
<?php
include "viewport.php";
?>
<link rel=stylesheet type="text/css" href="product.css">
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
}
</style>
<script type="text/javascript">
function takeAllQty() {
	document.gettakeall.locationfrom.value = document.getproduct.locationfrom.value;
	document.gettakeall.productfrom.value = document.getproduct.productfrom.value;
	document.gettakeall.qtyfrom.value = document.getproduct.qtyfrom.value;
	document.gettakeall.reasonfrom.value = document.getproduct.reasonfrom.value;
	document.gettakeall.getallqtys.value = "Y";
	return false;
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
function processQty() {
  if ( document.getproduct.qtyfrom.value=="")
  {
  	document.getproduct.message.value="Must Enter Qty";
	document.getproduct.qtyfrom.focus();
  	return false;
  }
  if ( document.getproduct.qtyfrom.value=="0")
  {
  	document.getproduct.message.value="Must Enter Qty";
	document.getproduct.qtyfrom.focus();
  	return false;
  }
  if ( chkNumeric(document.getproduct.qtyfrom.value)==false)
  {
  	document.getproduct.message.value="Qty Not Numeric";
	document.getproduct.qtyfrom.focus();
  	return false;
  }
  document.gettakeall.qtyfrom.value = document.getproduct.qtyfrom.value;
  document.gettakeall.getallqtys.value = "N";
  return true;
}
</script>
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">
<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
require_once "logme.php";
//include "checkdatajs.php";
//include "checkdata.php";
require_once "checkdata.php";
//include "logme.php";

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
	$message = trim($message);
	if ($message <> "Processed successfully") {
		echo ("<b><font color=RED>$message</font></b>\n");
		if ($wkMyBW == "IE60")
		{
			echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
		} elseif ($wkMyBW == "IE65")
		{
			echo ("<bgsound src=\"../includes/notok.wav\" loop=\"" .$rsound_notok_repeats . "\" >\n"); 
		}
	} else {
		echo ("<b><font color=GREEN>$message</font></b>\n");
		if ($wkMyBW == "IE60")
		{
			echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
		} elseif ($wkMyBW == "IE65")
		{
			echo ("<bgsound src=\"../includes/ok.wav\" loop=\"" .$rsound_ok_repeats . "\" >\n"); 
		}
	}
}
if (isset($_POST['locationfrom']))
{
	$locationfrom = $_POST['locationfrom'];
}
if (isset($_GET['locationfrom']))
{
	$locationfrom = $_GET['locationfrom'];
}
if (isset($_POST['productfrom']))
{
	$productfrom = $_POST['productfrom'];
}
if (isset($_GET['productfrom']))
{
	$productfrom = $_GET['productfrom'];
}
if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($productfrom))
{
	if ($productfrom == "")
	{
		unset($productfrom);
	}
}
if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}

if (isset($_GET['havedata']))
{
	$havedata = "Y";
}
if (isset($_POST['havedata']))
{
	$havedata = "Y";
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($owner))
{
	setBDCScookie($Link, $tran_device, "company", $owner);
} else {
	$owner = getBDCScookie($Link, $tran_device, "company" );
}
// create js for location check
//whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<?php
/* 
*/

function form_header()
{
	global $locationfrom, $productfrom;
	echo("</form>");
	if (isset($locationfrom) and isset($productfrom))
	{
		echo("<form action=\"PostFrom.php\" method=\"post\" name=\"getproduct\" onsubmit=\"return processQty()\">\n");
		echo("<input type=\"hidden\" name=\"transaction_type\" value=\"TROL\">");
		echo("<input type=\"hidden\" name=\"tran_type\" value=\"PRODUCT\">");
	}
	else
	{
		//echo("<form action=\"GetProductFrom.php\" method=\"get\" name=getproduct ONSUBMIT=\"return processEdit();\">\n");
		echo("<form action=\"GetProductFrom.php\" method=\"get\" name=getproduct >\n");
	}
} /* end function */
/* ====================================================== */

function form_company($Link, $owner)
{
// ====================================================================
// product owner

	echo("<br>\n");
	echo("<div id=\"company\" dir=\"rtl\">\n");
	$wk_company_cnt = 0;
	$default_comp = "";
	// get the default company
	$Query = "select company_id from control "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
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
		echo("Unable to Read Control!<BR>\n");
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
		//echo("<label for=\"owner\">Owner</label>");
		//echo("Owner:<select name=\"owner\" class=\"sel8\"");
		echo("<select name=\"owner\" id=\"owner\" class=\"sel8\"");
		echo(" size=\"3\" onchange=\"document.getproduct.submit()\">\n");
		$Query = "select company_id, name from company order by name "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Company!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($default_comp == $Row[0])
			{
				//echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
				echo( "<OPTION value=\"$Row[0]\" selected >"); 
				echo( substr($Row[1],0,23));
				echo("\n");
			}
			else
			{
				//echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
				echo( "<OPTION value=\"$Row[0]\">"); 
				echo( substr($Row[1],0,23));
				echo("\n");
			}
		}
		//release memory
		ibase_free_result($Result);
		echo("</select><br>\n");
	} else {
		//echo("<INPUT type=\"hidden\" name=\"owner\"  value=\"$owner\" >\n");
		echo("<INPUT type=\"hidden\" name=\"owner\"  value=\"$default_comp\" >\n");
	}
	echo("</div>");
} /* end function */
/* ====================================================== */
$gotmore = 0;
$Query = "SELECT COUNT(DISTINCT PROD_ID) FROM ISSN WHERE LOCN_ID = '$DBDevice'" ;
// echo($Query); 
if (($Result = ibase_query($Link, $Query)))
{
	if (($Row = ibase_fetch_row($Result)))
	{
		$gotmore =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}
}
$Query = "SELECT TRANSFER_PROD_TO_METHOD FROM CONTROL" ;
// echo($Query); 
if (($Result = ibase_query($Link, $Query)))
{
	if (($Row = ibase_fetch_row($Result)))
	{
		$wk_tranprod_2_meth =  $Row[0];
		ibase_free_result($Result); 
		unset($Result); 
	}
}
//echo("<FONT size=\"2\">\n");

echo("<form action=\"GetProductFrom.php\" method=\"post\" name=dummyproduct>\n");
if ($gotmore > 0)
{
	$havedata = "Y";
	echo('CNT<input type="text" name="ssncnt" readonly size="4" maxlength="10" value="' . $gotmore . '">');
	echo("<BR>");
}
else
{
	if (isset($havedata))
	{
		unset ($havedata);
	}
}
// check whether prodfrom is a product
if ((isset($productfrom)) and  (!isset($locationfrom)))
{
	// trim it
	$productfrom = trim($productfrom);
	// perhaps a product internal
	$field_type = checkForTypein($productfrom, 'PROD_INTERNAL' ); 
	if ($field_type == "none")
	{
		// perhaps a product 13
		$field_type = checkForTypein($productfrom, 'PROD_13' ); 
		if ($field_type == "none")
		{
			// perhaps an alt product internal
			$field_type = checkForTypein($productfrom, 'ALT_PROD_INTERNAL' ); 
			if ($field_type == "none")
			{
				// a dont know
				unset($productfrom);
				if (!isset($message))
				{
					$message = '';
				}
				$message .= "Not a Prod 13 or Prod Internal";
			}
			else
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($productfrom,$startposn);
					$productfrom = $wk_realdata;
				}
			}
		}
		else
		{
			if ($startposn > 0)
			{
				$wk_realdata = substr($productfrom,$startposn);
				$productfrom = $wk_realdata;
			}
		}
	}
	else
	{
		if ($startposn > 0)
		{
			$wk_realdata = substr($productfrom,$startposn);
			$productfrom = $wk_realdata;
		}
	}
}
if (isset($productfrom))
{
	// ===================== company
	//form_company($Link, $owner);
	echo("Owner <input type=\"text\" name=\"owner\" size=\"10\" maxlength=\"20\" readonly  value=\"$owner\" >");
	echo("Product <input type=\"text\" name=\"productfrom\" size=\"20\" maxlength=\"34\" readonly value=\"$productfrom\" ><br>");
	if (isset($locationfrom))
	{
		echo("Location <input type=\"text\" name=\"locationfrom\" size=\"10\" maxlength=\"10\" readonly value=\"".$locationfrom."\" >");
		{
			form_header();
			echo("<input type=\"text\" name=\"message\" size=\"20\" maxlength=\"40\" readonly class=\"message\" ><br>");
			echo("<input type=\"hidden\" name=\"locationfrom\" value=\"$locationfrom\">");
			echo("<input type=\"hidden\" name=\"productfrom\" value=\"$productfrom\" >");
			echo("Qty <input type=\"text\" name=\"qtyfrom\" size=\"6\" maxlength=\"10\" onblur=\"return processQty()\"><br>");
			echo("Reason <input type=\"text\" name=\"reasonfrom\" size=\"20\" maxlength=\"40\" onblur=\"return processQty()\"><br>");
		}
	}
	else
	{
		form_header();
		//echo("<input type=\"hidden\" name=\"productfrom\" value=\"$productfrom\" >");
		// show list of locations
			//want locns for product
			$Query = "select location.locn_name, issn.issn_status, sum(issn.current_qty), issn.wh_id || issn.locn_id ";
			$Query .= "from issn ";
			$Query .= "left outer join location on location.wh_id = issn.wh_id and location.locn_id = issn.locn_id ";
			$Query .= "where issn.prod_id = '".$productfrom."' ";
			$Query .= "and   issn.company_id = '".$owner."' ";
			$Query .= "and (issn.issn_status < 'X' or issn.issn_status > 'X~') ";
			$Query .= "and (issn.wh_id < 'X' or issn.wh_id > 'X~') ";
			$Query .= "and (issn.current_qty > 0 ) ";
			$Query .= "group by location.locn_name, issn.issn_status, issn.wh_id, issn.locn_id ";
			
			// Create a table.
			echo ("<div id=\"locns3\">\n");
			echo ("<table border=\"1\">\n");
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to query product!<BR>\n");
				exit();
			}
			// echo headers
			echo ("<tr>\n");
			echo("<th>Name</th>\n");
			echo("<th>Status</th>\n");
			echo("<th>Qty</th>\n");
			echo ("</tr>\n");
			
			// Fetch the results from the database.
			// Fetch the results from the database.
			while (($Row = ibase_fetch_row($Result)) )
			{
			 	echo ("<tr>\n");
				for ($i=0; $i<3; $i++)
				{
					if ($i == 0)
					{
						if ( $Row[$i] == "")
						{
							$Row[$i] = $Row[3];
						}
						echo("<td>");
						form_header();
						//echo("<form action=\"GetProductFrom.php\" method=\"post\" name=getlocn>\n");
						echo("<input type=\"hidden\" name=\"productfrom\" value=\"$productfrom\" >");
						echo("<input type=\"hidden\" name=\"locationfrom\" value=\"".$Row[3]."\" >");
						echo("<input type=\"submit\" name=\"locationname\" value=\"".$Row[$i]."\">\n");
						//echo("</form>\n");
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
			echo ("</div >\n");
		ibase_free_result($Result); 
		unset($Result); 
		// accept location
		//echo("Location <input type=\"text\" name=\"locationfrom\"  size=\"10\" maxlength=\"10\"  ><BR>");
	}
}
else
{
	form_header();
	// ======================================= company
	if (isset($owner))
	{
		form_company($Link, $owner);
	} else {
		form_company($Link, Null);
	}
	echo ("<div id=\"prods3\">\n");
	//echo("<BR>");
	//echo("<BR>");
	//echo("<BR>");
	echo("Product <input type=\"text\" name=\"productfrom\" size=\"20\" maxlength=\"34\" ><br>");
	echo ("</div>\n");
}
/*
if (isset($locationfrom) and isset($productfrom))
{
	echo('<input type="hidden" name="getallqtys" VALUE="N" >');
}
*/
echo ("<div id=\"col1\">\n");
echo ("<table>\n");
echo ("<tr>\n");
if (isset($owner))
{
	if (isset($productfrom))
	{
		if (isset($locationfrom))
		{
			echo("<td colspan=\"2\">Enter Qty to Transfer</td>\n");
		} else {
			echo("<td colspan=\"2\">Click Location to Take From</td>\n");
		}
	} else {
		echo("<td colspan=\"2\">Enter Product to Transfer</td>\n");
	}
} else {
	echo("<td colspan=\"2\">Enter Company to Transfer</td>\n");
}
echo ("</tr>\n");
//echo ("</table>\n");
// Create a table.
//echo ("<table border=\"0\" align=\"left\">");
if (isset($havedata))
{
	/* whm2buttons('Accept', 'GetLocnTo.php?havedata=$havedata',"N", 'button.php?text=To+Location&fromimage=Blank_Button_50x100.gif' ,"ToLocn","accept.gif"); */
	if ($wk_tranprod_2_meth == "LOCNPROD")
	{
		$wk_to_site =  "GetLocnTo.php?havedata=" . $havedata;
		//whm2buttons('Accept', "GetLocnTo.php?havedata=$havedata","N", 'tolocation.gif' ,"ToLocn","accept.gif"); 
		whm2buttons('Accept', $wk_to_site ,"N", 'tolocation.gif' ,"ToLocn","accept.gif"); 
	}
	else
	{
		$wk_to_site =  "GetProdLocn1To.php?havedata=" . $havedata;
		//whm2buttons('Accept', "GetProdLocn1To.php?havedata=$havedata","N", 'product.gif' ,"ToProduct","accept.gif");
		//whm2buttons('Accept', "GetProdLocn1To.php?havedata=$havedata","N", 'button.php?text=Place+Product&fromimage=Blank_Button_50x100.gif' ,"ToProduct","accept.gif");
		whm2buttons('Accept', $wk_to_site ,"N", 'button.php?text=Place+Product&fromimage=Blank_Button_50x100.gif' ,"ToProduct","accept.gif");
	}
}
else
{
	whm2buttons("Accept","Transfer_Menu.php", "N","Back_50x100.gif","Back","accept.gif"); 
}
if (isset($locationfrom) and isset($productfrom))
{
	$alt = "Take All";
	echo ("<tr>");
	echo ("<td>");
	echo("<form action=\"PostFrom.php\" method=\"post\" name=gettakeall>\n");
	echo("<input type=\"hidden\" name=\"transaction_type\" value=\"TROL\">");
	echo("<input type=\"hidden\" name=\"tran_type\" value=\"PRODUCT\">");
	echo("<input type=\"hidden\" name=\"locationfrom\" value=\"$locationfrom\">");
	echo("<input type=\"hidden\" name=\"productfrom\" value=\"$productfrom\" readonly>");
	echo("<input type=\"hidden\" name=\"qtyfrom\" >");
	echo("<input type=\"hidden\" name=\"reasonfrom\" >");
	echo('<input type="hidden" name="getallqtys" VALUE="Y" >');
	echo("<input type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '" onClick="takeAllQty()"' . '"');
*/
	echo('SRC="/icons/whm/takeall.gif" alt="' . $alt . '" onClick="takeAllQty()">');
	echo("</form>");
	echo ("</td>");
	echo ("</tr>");
}
echo ("</table>");
echo ("</div >\n");

//release memory
//ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>
<script type="text/javascript">
<?php
{
	if (isset($productfrom))
	{
		if (isset($locationfrom))
		{
			echo("document.getproduct.qtyfrom.focus();\n");
		}
		else
		{
			//echo("document.getproduct.locationfrom.focus();\n");
		}
	}
	else
	{
		echo("document.getproduct.productfrom.focus();\n");
	}
}
?>
</script>
</body>
</html>
