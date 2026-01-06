<?php
include "../login.inc";
setcookie("BDCSData","");
?>
<html>
 <head>
  <title>Get Transfer Type</title>
<?php
include "viewport.php";
?>
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
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	include "2buttons.php";
	
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{	
	  echo("Can't connect to DATABASE!");
	  //exit();
	}
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	$Query = "select transfer_method from control ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Control!<BR>\n");
		//exit();
	}
	$transfer_method = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$transfer_method = $Row[0];
		}
	}

	//release memory
	ibase_free_result($Result);

	if (isset($_GET['message']))
	{
		$message = $_GET['message'];
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
	if (isset($_POST['message']))
	{
		$message = $_POST['message'];
		$message = trim($message);
		//echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
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
	// Set the variables for the database access:
	include "logme.php";
	setBDCScookie($Link, $tran_device, "transfer", "");
	setBDCScookie($Link, $tran_device, "BDCSData", "");
	setBDCScookie($Link, $tran_device, "ssnfrom", "") ;
	setBDCScookie($Link, $tran_device, "locationfrom", "") ;
	setBDCScookie($Link, $tran_device, "qtyto","")  ;
	setBDCScookie($Link, $tran_device, "locationto", "") ;
	// get number of products on device for display
	$gotmoreprod = 0;
	$Query = "SELECT COUNT(DISTINCT PROD_ID) FROM ISSN WHERE LOCN_ID = '$DBDevice' and (wh_id <'XA' or wh_id > 'XZ')" ;
	// echo($Query); 
	if (($Result = ibase_query($Link, $Query)))
	{
			if (($Row = ibase_fetch_row($Result)))
		{
			$gotmoreprod =  $Row[0];
			ibase_free_result($Result); 
			unset($Result); 
		}
	}
	$gotmoressn = 0;
	$Query = "SELECT COUNT(DISTINCT ORIGINAL_SSN) FROM ISSN WHERE LOCN_ID = '$DBDevice' and (wh_id <'XA' or wh_id > 'XZ')" ;
	// echo($Query); 
	if (($Result = ibase_query($Link, $Query)))
	{
			if (($Row = ibase_fetch_row($Result)))
		{
			$gotmoressn =  $Row[0];
			ibase_free_result($Result); 
			unset($Result); 
		}
	}
	ibase_commit($dbTran);
?>
<?php
  //<H4 ALIGN="LEFT">Enter Transfer Type</H4>
echo('<h4 ALIGN="LEFT">Select Transfer Type');
if (isset($tran_device))
{
	echo(" ");
	echo($tran_device );
}
if ($gotmoreprod > 0)
{
	echo(" ");
	echo($gotmoreprod );
	echo(" Products on Device");
}
if ($gotmoressn > 0)
{
	echo(" ");
	echo($gotmoressn );
	echo(" SSNs on Device");
}
echo('</h4>');


{
	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",ONESSN,", $transfer_method, "Get1SSN.php", "onessn", "/icons/whm/button.php?text=One+SSN&fromimage=Blank_Button_50x100.gif", "OneSSN");
	addMenuButton(",SSN,", $transfer_method, "GetSSNFrom.php", "ssn", "/icons/whm/SSN_50x100.gif", "SSN");
	addMenuButton(",PROD,", $transfer_method, "GetProductFrom.php", "product", "/icons/whm/product.gif", "Product");
	addMenuButton(",LOCATION,", $transfer_method, "GetLocnFrom.php", "location", "/icons/whm/location.gif", "Location");
	addMenuButton(",SPLIT,", $transfer_method, "GetSSNSplitFrom.php", "split", "/icons/whm/split.gif", "Split");
	addMenuButton(",MOBILE,", $transfer_method, "GetMobile.php", "mobilelocation", "/icons/whm/mobilelocation.gif", "Mobile
Location");
	addMenuButton(",PICK,", $transfer_method, "GetPick.php", "pick", "/icons/whm/Pick_50x100.gif", "Pick");
	//addMenuButton(",PICKORDER,", $transfer_method, "GetPickOrder.php", "pickorder", "/icons/whm/button.php?text=++Order&fromimage=Pick_50x100.gif", "PickOrder");
	addMenuButton(",PICKORDER,", $transfer_method, "GetPickOrder.php", "pickorder", "/icons/whm/button.php?text=Pick+Order&fromimage=Blank_Button_50x100.gif", "Pick Order");
	addMenuButton(",PICKSPLIT,", $transfer_method, "GetPickLine.php", "picksplit", "/icons/whm/button.php?text=Pick+Split+Order&fromimage=Blank_Button_50x100.gif", "Pick Split");
	addMenuButton(",REPLENISH,", $transfer_method, "GetReplenish.php", "replenish", "/icons/whm/button.php?text=Replenish&fromimage=Blank_Button_50x100.gif", "Replenish");
	addMenuButton(",PRODHOME,", $transfer_method, "proddim.php", "prodhome", "/icons/whm/button.php?text=++Home&fromimage=prodprofile.gif", "Prod Profile
Home");
}
?>
    </tr>
  </table>
 </body>
</html>
