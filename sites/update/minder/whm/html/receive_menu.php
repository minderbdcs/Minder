<?php
include "../login.inc";
setcookie("BDCSData","", time()+11186400, "/");
?>
<html>
 <head>
  <TITLE>Get Receive Type</TITLE>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="receive.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="receive-netfront.css">');
}
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FCCFFF">

<?php
echo("<H4>Receive - ");
echo("Select Receive Type");
echo("</H4>\n");
require_once 'DB.php';
require 'db_access.php';

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Can't connect to DATABASE!");
	//exit();
}
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
$Query = "select sys_admin, editable from sys_user where user_id = '" . $tran_user . "'"; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read User!<BR>\n");
	//exit();
}
$sysadmin = "";
$editable = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$sysadmin = $Row[0];
	}
	if ($Row[1] > "")
	{
		$editable = $Row[1];
	}
}

//release memory
ibase_free_result($Result);
$Query = "select pick_order_weight_calc from control "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	//exit();
}
$do_recalc_weight = "";
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$do_recalc_weight = $Row[0];
	}
}

//release memory
ibase_free_result($Result);
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=getpurchase>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"PO\">\n");
	echo("<INPUT type=\"submit\" name=\"purchase\" value=\"Purchase\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=getreturn>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"RA\">\n");
	echo("<INPUT type=\"submit\" name=\"return\" value=\"Returns\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=gettransfer>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"TR\">\n");
	echo("<INPUT type=\"submit\" name=\"transfer\" value=\"Transfer\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=getwo>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"WO\">\n");
	echo("<INPUT type=\"submit\" name=\"work\" value=\"Work Order\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=getload>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"LD\">\n");
	echo("<INPUT type=\"submit\" name=\"load\" value=\"Load\">\n");
	echo("</FORM>\n");
	echo("<FORM action=\"./newreceive.php\" method=\"post\" name=getproduct>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"LP\">\n");
	echo("<INPUT type=\"submit\" name=\"product\" value=\"Load Product\">\n");
	echo("</FORM>\n");
}
else
{
*/
	// html 4.0 browser
	//echo("<div id=\"col1\">");
	// Create a table.
	echo ("<TABLE BORDER=\"0\">");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=PO\" method=\"post\" name=purchase>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Purchase&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Purchase"></INPUT>');
*/
	echo('SRC="/icons/whm/purchaseorder.gif" alt="Purchase"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=LD\" method=\"post\" name=load>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Load_50x100.gif" alt="Load"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=RA\" method=\"post\" name=return>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Returns&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Returns"></INPUT>');
*/
	echo('SRC="/icons/whm/returns.gif" alt="Returns"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=LP\" method=\"post\" name=load>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Load_Product_50x100.gif" alt="LoadProduct"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=TR\" method=\"post\" name=transfer>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/TRANSFER_50x100.gif" alt="Transfer"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	//echo("<BUTTON type=\"button\" accesskey=\"o\" name=\"delivery\" value=\"DeliveryOnly\" onClick=\"location.href='./newreceive.php?type=LD';\">\n");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive2.php?type=LD\" method=\"post\" name=delivery>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=Delivery+Only&fromimage=');
	echo('Blank_Button_50x100.gif" alt="DeliverOnly"></INPUT>');
/*
	echo('SRC="/icons/whm/purchaseorder.gif" alt="Purchase"></INPUT>');
*/
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	//echo("<BUTTON type=\"button\" accesskey=\"v\" name=\"verify\" value=\"VerifyOnly\" onClick=\"location.href='./getgrnorder.php?type=LD';\">\n");
	echo ("<TD>");
	echo("<FORM action=\"./getgrnorder.php?type=LD\" method=\"post\" name=VerifyOnly>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=Verify+Only&fromimage=');
	echo('Blank_Button_50x100.gif" alt="VerifyOnly"></INPUT>');
/*
	echo('SRC="/icons/whm/purchaseorder.gif" alt="Purchase"></INPUT>');
*/
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"./newreceive.php?type=WO\" method=\"post\" name=WO>\n");
	echo("<INPUT type=\"IMAGE\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=Work+Order&fromimage=');
	echo('Blank_Button_50x100.gif" alt="WorkOrder"></INPUT>');
*/
	echo('SRC="/icons/whm/workorder.gif" alt="WorkOrder"></INPUT>');
	echo("</FORM>");
	echo ("</TD>");
	$wk_label_posn = 0;
	if (($sysadmin == 'T') and ($do_recalc_weight == 'T'))
	{
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</TR>");
			echo ("<TR>");
			$wk_label_posn = 0;
		}
		echo ("<TD>");
		echo("<FORM action=\"./getprod.php\" method=\"post\" name=recalcorders>\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=Recalculate+Orders+Weight');
		echo('" alt="RecalcWeight">');
		echo("</FORM>");
		echo ("</TD>");
	}
	echo ("</TR>");
/*
	echo("<BUTTON type=\"button\" accesskey=\"p\" name=\"purchase\" value=\"Purchase\" onClick=\"location.href='./newreceive.php?type=PO';\">\n");
	echo("Purchase<IMG SRC=\"/icons/small/compressed.gif\" alt=\"purchase\" ></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"r\" name=\"returns\" value=\"Returns\" onClick=\"location.href='./newreceive.php?type=RA';\">\n");
	echo("Returns<IMG SRC=\"/icons/small/transfer.gif\" alt=\"return\" ></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"t\" name=\"transfer\" value=\"Transfer\" onClick=\"location.href='./newreceive.php?type=TR';\">\n");
	echo("Transfer<IMG SRC=\"/icons/small/unknown.gif\" alt=\"transfer\"></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"w\" name=\"work\" value=\"Work\" onClick=\"location.href='./newreceive.php?type=WO';\">\n");
	echo("Work order<IMG SRC=\"/icons/small/continued.gif\" alt=\"work\"></BUTTON>\n");
	//echo("</div>");
	if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 4.01") === false)
	{
		// not the hhp browser
		echo("<div id=\"col2\">");
	}
	else
	{
		echo("<div id=\"col3\">");
	}
	echo("<BUTTON type=\"button\" accesskey=\"l\" name=\"load\" value=\"load\" onClick=\"location.href='./newreceive.php?type=LD';\">\n");
	echo("Load<IMG SRC=\"/icons/small/sound.gif\" alt=\"load\"></BUTTON>\n");
	echo("<BUTTON type=\"button\" accesskey=\"d\" name=\"loadproduct\" value=\"loaDproduct\" onClick=\"location.href='./newreceive.php?type=LP';\">\n");
	echo("loaD product<IMG SRC=\"/icons/small/sound2.gif\" alt=\"product\"></BUTTON>\n");
}
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
{
/*
	echo("<BUTTON name=\"back\" accesskey=\"b\" type=\"button\" onClick=\"location.href='../mainmenu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/small/back.gif\" alt=\"back\"></BUTTON>\n");
	//echo("</div>");
}
*/
echo("</table>");
//commit
//$Link->commit();
ibase_commit($dbTran);

//close
//$Link->disconnect();
ibase_close($Link);

?>
 </body>
</html>
