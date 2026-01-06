<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Verify Product Load</title>
<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
include "viewport.php";
             
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD-netfront.css">');
}
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
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p> ")
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<p>.............................................................. ")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<p>.............................................................. ")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
function processBack() {
	var dowhat;
/*
	dowhat=confirm("Cancel Delivery?");
	if(dowhat)
	{
		document.forms.getssnback.submit();
		return true;
	}
	else
	{
  		document.getprodqtys.message.value="Select the Sent From";
		document.getprodqtys.retfrom.focus();
		return false;
	}
*/
/* treat as if yes is wanted in cancel */
		document.forms.getssnback.submit();
		return true;
}
function doproduct() {
	document.getproduct.product.value=document.getprodqtys.product.value;
	document.gorefresh.retfrom.value = document.getprodqtys.retfrom.value ;
	document.gorefresh.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.gorefresh.location.value = document.getprodqtys.location.value ;
	document.gorefresh.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.gorefresh.problem.value = "T" ;
	}
	else
	{
		document.gorefresh.problem.value = "" ;
	}
	document.gorefresh.uom.value = document.getprodqtys.uom.value ;
	document.gorefresh.product.value = document.getprodqtys.product.value ;
	document.gorefresh.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.gorefresh.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.gorefresh.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.gorefresh.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.gorefresh.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.gorefresh.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.gorefresh.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.gorefresh.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.gorefresh.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.gorefresh.submit();
	return true;
}
function chkNumeric(strString)
{
/* check for valid numerics */
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
function showComplete2() {
	var wk_next_screen;
	if (wk_use_prod_complete == "T")
	{
		/* go to getcomplete1 */
		wk_next_screen = document.getcomplete1;
	}
	else
	{
		/* go to goforw */
		wk_next_screen = document.goforw;
	}
	wk_next_screen.retfrom.value = document.getprodqtys.retfrom.value ;
	wk_next_screen.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	wk_next_screen.location.value = document.getprodqtys.location.value ;
	wk_next_screen.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		wk_next_screen.problem.value = "T" ;
	}
	else
	{
		wk_next_screen.problem.value = "" ;
	}
	wk_next_screen.uom.value = document.getprodqtys.uom.value ;
	wk_next_screen.product.value = document.getprodqtys.product.value ;
	wk_next_screen.label_qty1.value = document.getprodqtys.label_qty1.value ;
	wk_next_screen.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	wk_next_screen.label_qty2.value = document.getprodqtys.label_qty2.value ;
	wk_next_screen.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	wk_next_screen.other_qty1.value = document.getprodqtys.other_qty1.value ;
	wk_next_screen.other_qty2.value = document.getprodqtys.other_qty2.value ;
	wk_next_screen.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		wk_next_screen.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		wk_next_screen.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	wk_next_screen.submit();
}
function processEdit() {
	var dowhat;
  	var csum;
  /* document.getprodqtys.message.value="in process edit"; */
  if ( document.getprodqtys.retfrom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the Sent From";
	document.getprodqtys.retfrom.focus();
  	return false;
  }
  if ( document.getprodqtys.uom.value=="")
  {
  	document.getprodqtys.message.value="Must Select the UoM";
	document.getprodqtys.uom.focus();
  	return false;
  }
  if ( document.getprodqtys.product.value=="")
  {
  	document.getprodqtys.message.value="Must Enter the Product";
	document.getprodqtys.product.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="0")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Received Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.printer.value=="")
  {
  	document.getprodqtys.message.value="Must Select the printer";
	document.getprodqtys.printer.focus();
  	return false;
  }
  if ( document.getprodqtys.label_qty1.value=="")
  {
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st labels */
  }
  if ( document.getprodqtys.ssn_qty1.value=="")
  {
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty2.value=="")
  {
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 2nd labels */
  }
  if ( document.getprodqtys.ssn_qty2.value=="")
  {
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 1st ssn qty */
  }
  if ( chkNumeric(document.getprodqtys.label_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First Label Qty Not Numeric";
	document.getprodqtys.label_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.label_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second Label Qty Not Numeric";
	document.getprodqtys.label_qty2.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty2.focus();
  	return false;
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="ISSN/Label Qtys dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
	/* csum2 = parseInt(document.getprodqtys.other_qty1.value * wk_prod_per_pallet_qty) + 
	       parseInt(document.getprodqtys.other_qty2.value * wk_prod_per_inner_qty) +
	       parseInt(document.getprodqtys.other_qty3.value ) ; */
	csum2 = parseInt(document.getprodqtys.other_qty1.value * wk_prod_per_pallet_qty) + 
	       parseInt(document.getprodqtys.other_qty2.value * wk_prod_per_outer_qty) +
	       parseInt(document.getprodqtys.other_qty3.value ) ;
	if (csum2 != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="PCI Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
  if ( document.getprodqtys.location.value=="")
  {
  	document.getprodqtys.message.value="Must Select the Location";
	document.getprodqtys.location.focus();
  	return false;
  }
  if ((csum==document.getprodqtys.received_ssn_qty.value) &&
      (csum2==document.getprodqtys.received_ssn_qty.value))
  {
	showComplete2();
	return false;
  }
  else
  {
	/* the submit these qtys so far */
	document.getprodqtys.complete.value = " ";
	document.goforw.complete.value = document.getprodqtys.complete.value ;
	document.goforw.retfrom.value = document.getprodqtys.retfrom.value ;
	document.goforw.uom.value = document.getprodqtys.uom.value ;
	document.goforw.product.value = document.getprodqtys.product.value ;
	document.goforw.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.goforw.location.value = document.getprodqtys.location.value ;
	document.goforw.printer.value = document.getprodqtys.printer.value ;
	document.goforw.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.goforw.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.goforw.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.goforw.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.goforw.problem.value = "T" ;
	}
	else
	{
		document.goforw.problem.value = "" ;
	}
	document.goforw.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.goforw.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.goforw.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.goforw.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.goforw.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
/*
	document.goforw.submit();
*/
  }
/*
*/
  return false;
}
function processComment() {
	var dowhat;
	document.getcomment.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomment.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomment.location.value = document.getprodqtys.location.value ;
	document.getcomment.printer.value = document.getprodqtys.printer.value ;
	document.getcomment.problem.value = document.getprodqtys.problem.value ;
	document.getcomment.uom.value = document.getprodqtys.uom.value ;
	document.getcomment.product.value = document.getprodqtys.product.value ;
	document.getcomment.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomment.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getcomment.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomment.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomment.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getcomment.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getcomment.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getcomment.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getcomment.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getcomment.submit();
  return true;
}
function processProdSearch() {
	document.getprodsrch.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getprodsrch.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
/* 	document.getprodsrch.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ; */
	document.getprodsrch.location.value = document.getprodqtys.location.value ;
	document.getprodsrch.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getprodsrch.problem.value = "T" ;
	}
	else
	{
		document.getprodsrch.problem.value = "" ;
	}
	document.getprodsrch.uom.value = document.getprodqtys.uom.value ;
	document.getprodsrch.product.value = document.getprodqtys.product.value ;
	document.getprodsrch.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getprodsrch.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getprodsrch.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getprodsrch.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getprodsrch.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getprodsrch.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getprodsrch.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getprodsrch.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getprodsrch.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getprodsrch.submit();
}
function processProdSupplier() {
	var dowhat;
	/* clicked add supplier */
	/* populate getprodsupplier and submit form to add supplier 
	dowhat = document.getprodqtys.retfrom.selectedIndex ;
	document.getprodsupplier.retfrom.value = document.getprodqtys.retfrom.options[dowhat].value ;
	dowhat = document.getprodqtys.uom.selectedIndex ;
	document.getprodsupplier.uom.value = document.getprodqtys.uom.options[dowhat].value ;
	dowhat = document.getprodqtys.printer.selectedIndex ;
	document.getprodsupplier.printer.value = document.getprodqtys.printer.options[dowhat].value ;
	dowhat = document.getprodqtys.location.selectedIndex ;
	document.getprodsupplier.location.value = document.getprodqtys.location.options[dowhat].value ;
	dowhat = document.getprodqtys.weight_uom.selectedIndex ;
	document.getprodsupplier.weight_uom.value = document.getprodqtys.weight_uom.options[dowhat].value ; */

	document.getprodsupplier.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getprodsupplier.printer.value = document.getprodqtys.printer.value ;
	document.getprodsupplier.uom.value = document.getprodqtys.uom.value ;
	document.getprodsupplier.location.value = document.getprodqtys.location.value ;
	document.getprodsupplier.weight_uom.value = document.getprodqtys.weight_uom.value ;  

	document.getprodsupplier.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	/* document.getprodsupplier.printed_ssn_qty.value = document.getprodqtys.printed_ssn_qty.value ; */
	if (document.getprodqtys.problem.checked == true)
	{
		document.getprodsupplier.problem.value = "T" ;
	}
	else
	{
		document.getprodsupplier.problem.value = "" ;
	}
	document.getprodsupplier.product.value = document.getprodqtys.product.value ;
	document.getprodsupplier.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getprodsupplier.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getprodsupplier.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getprodsupplier.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getprodsupplier.weight_qty1.value = document.getprodqtys.weight_qty1.value ; 
	document.getprodsupplier.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getprodsupplier.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getprodsupplier.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getprodsupplier.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getprodsupplier.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getprodsupplier.submit();
}
function processProduct() {
	var dowhat;
	document.getproduct.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getproduct.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getproduct.location.value = document.getprodqtys.location.value ;
	document.getproduct.printer.value = document.getprodqtys.printer.value ;
	document.getproduct.problem.value = document.getprodqtys.problem.value ;
	document.getproduct.uom.value = document.getprodqtys.uom.value ;
	document.getproduct.product.value = document.getprodqtys.product.value ;
	document.getproduct.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getproduct.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getproduct.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getproduct.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getproduct.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getproduct.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getproduct.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getproduct.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getproduct.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getproduct.submit();
  return true;
}
/* ===================================================================== */

function recalcCol() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.label_qty1.value=="")
  {
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st labels */
  }
  if ( document.getprodqtys.ssn_qty1.value=="")
  {
	document.getprodqtys.ssn_qty1.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.label_qty2.value=="")
  {
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 2nd labels */
  }
  if ( document.getprodqtys.ssn_qty2.value=="")
  {
	document.getprodqtys.ssn_qty2.value = "0";
	/* no 1st ssn qty */
  }
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
	document.getprodqtys.received_ssn_qty.value = "0";
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( document.getprodqtys.received_ssn_qty.value=="0")
  {
  	document.getprodqtys.message.value="Must Enter Received Qty";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
  	document.getprodqtys.message.value="Received Qty Not Numeric";
	document.getprodqtys.received_ssn_qty.value = "0";
	document.getprodqtys.received_ssn_qty.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.label_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First Label Qty Not Numeric";
	document.getprodqtys.label_qty1.value = "0";
	document.getprodqtys.label_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty1.value)==false)
  {
  	document.getprodqtys.message.value="First SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty1.value = "0";
	document.getprodqtys.ssn_qty1.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.label_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second Label Qty Not Numeric";
	document.getprodqtys.label_qty2.value = "0";
	document.getprodqtys.label_qty2.focus();
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.ssn_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Second SSN Qty Not Numeric";
	document.getprodqtys.ssn_qty2.value = "0";
	document.getprodqtys.ssn_qty2.focus();
  	return false;
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="ISSN Labels/Qty dosn't Add Up";
		document.getprodqtys.label_qty1.className = "nomatch";
		document.getprodqtys.label_qty2.className = "nomatch";
		document.getprodqtys.ssn_qty1.className = "nomatch";
		document.getprodqtys.ssn_qty2.className = "nomatch";
		/* document.getprodqtys.label_qty1.focus(); */
  		return false;
	}
	else
	{
  		document.getprodqtys.message.value="ISSN Labels/Qty Adds Up";
		document.getprodqtys.label_qty1.className = "match";
		document.getprodqtys.label_qty2.className = "match";
		document.getprodqtys.ssn_qty1.className = "match";
		document.getprodqtys.ssn_qty2.className = "match";
	}
  return false;
}
function recalcPCICol() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.other_qty1.value=="")
  {
	document.getprodqtys.other_qty1.value = "0";
  }
  if ( document.getprodqtys.other_qty2.value=="")
  {
	document.getprodqtys.other_qty2.value = "0";
  }
  if ( document.getprodqtys.other_qty3.value=="")
  {
	document.getprodqtys.other_qty3.value = "0";
  }
  if ( chkNumeric(document.getprodqtys.other_qty1.value)==false)
  {
  	document.getprodqtys.message.value="Pallet Qty Not Numeric";
	/* document.getprodqtys.other_qty1.focus(); */
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.other_qty2.value)==false)
  {
  	document.getprodqtys.message.value="Carton Qty Not Numeric";
	/* document.getprodqtys.other_qty2.focus(); */
  	return false;
  }
  if ( chkNumeric(document.getprodqtys.other_qty3.value)==false)
  {
  	document.getprodqtys.message.value="Individual Qty Not Numeric";
	/* document.getprodqtys.other_qty3.focus(); */
  	return false;
  }
	/* csum = parseInt(document.getprodqtys.other_qty1.value * wk_prod_per_pallet_qty) + 
	       parseInt(document.getprodqtys.other_qty2.value * wk_prod_per_inner_qty) +
	       parseInt(document.getprodqtys.other_qty3.value ) ; */
	csum = parseInt(document.getprodqtys.other_qty1.value * wk_prod_per_pallet_qty) + 
	       parseInt(document.getprodqtys.other_qty2.value * wk_prod_per_outer_qty) +
	       parseInt(document.getprodqtys.other_qty3.value ) ;
	document.getprodqtys.other_sum.value  = parseInt(csum );
/*
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="PCI Qty dosn't Add Up";
		document.getprodqtys.other_qty1.className = "nomatch";
		document.getprodqtys.other_qty2.className = "nomatch";
		document.getprodqtys.other_qty3.className = "nomatch";
		-* document.getprodqtys.other_qty1.focus(); *-
  		return false;
	}
	else
*/
	{
  		document.getprodqtys.message.value="PCI Qty Adds Up";
		document.getprodqtys.other_qty1.className = "match";
		document.getprodqtys.other_qty2.className = "match";
		document.getprodqtys.other_qty3.className = "match";
	}
  return false;
}
function defaultPCICols() {
	var wk_pallets;
  	var wk_cartons;
  	var wk_individuals;
  	var wk_qty_to_use;
  if ( document.getprodqtys.received_ssn_qty.value=="")
  {
	document.getprodqtys.received_ssn_qty.value = "0";
  }
  if ( document.getprodqtys.other_qty1.value=="")
  {
	document.getprodqtys.other_qty1.value = "0";
  }
  if ( document.getprodqtys.other_qty2.value=="")
  {
	document.getprodqtys.other_qty2.value = "0";
  }
  if ( document.getprodqtys.other_qty3.value=="")
  {
	document.getprodqtys.other_qty3.value = "0";
  }
  if ( chkNumeric(document.getprodqtys.other_qty1.value)==false)
  {
	document.getprodqtys.other_qty1.value = "0";
  }
  if ( chkNumeric(document.getprodqtys.other_qty2.value)==false)
  {
	document.getprodqtys.other_qty2.value = "0";
  }
  if ( chkNumeric(document.getprodqtys.other_qty3.value)==false)
  {
	document.getprodqtys.other_qty3.value = "0";
  }
  if ( chkNumeric(document.getprodqtys.received_ssn_qty.value)==false)
  {
	document.getprodqtys.received_ssn_qty.value = "0";
  }
  wk_qty_to_use = parseInt(document.getprodqtys.received_ssn_qty.value);
  wk_pallets = 0;
  wk_cartons = 0;
  wk_individuals = 0;
  if (wk_use_pallet_qty == "T")
  {
	if (parseInt(wk_prod_per_pallet_qty) > 1)
	{
  		wk_pallets  =  parseInt(wk_qty_to_use / wk_prod_per_pallet_qty);
		wk_qty_to_use = wk_qty_to_use - parseInt(wk_pallets * wk_prod_per_pallet_qty);
	}
  }
  if (wk_use_outer_qty == "T")
  {
	if (parseInt(wk_prod_per_outer_qty) > 1)
	{
  		wk_cartons  =  parseInt(wk_qty_to_use / wk_prod_per_outer_qty);
		wk_qty_to_use = wk_qty_to_use - parseInt(wk_cartons * wk_prod_per_outer_qty);
	}
  }
  wk_individuals  =  wk_qty_to_use ;
  document.getprodqtys.other_qty1.value =  wk_pallets;
  document.getprodqtys.other_qty2.value =  wk_cartons;
  document.getprodqtys.other_qty3.value =  wk_individuals;
  document.getprodqtys.label_qty1.value =  1;
  document.getprodqtys.ssn_qty1.value =  document.getprodqtys.received_ssn_qty.value;

  return false;
}
/* ====================================================================== */
onerror = errorHandler
</script>
</head>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
//require 'logme.php';
require_once 'logme.php';

//include "checkdata.php";
require_once "checkdata.php";

$_SESSION['token'] = md5(session_id() . time());
/* want to stop double submits
now this form submits transLPNV as well as prodcomplete
*/

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$received_ssn_qty = 0;
if (isset($_COOKIE['BDCSData']))
{
	list($received_qty, $type, $order, $line, $carrier, $vehicle, $container, $pallet_type, $pallet_qty, $consignment, $grn, $problem) = explode("|", $_COOKIE["BDCSData"]);
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<br>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

function getVehicleOption($Link, $code)
{
	{
		$Query = "select description, description2 from options where group_code='RECEIVE'  and code = 'OTHER|" . $code . "' "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Options!<BR>\n");
			$log = fopen('/tmp/getdelivery.log' , 'a');
			fwrite($log, $Query);
			fclose($log);
			//exit();
		}
		$wk_data = "";
		$wk_data2 = "";
		while ( ($Row = ibase_fetch_row($Result)) ) {
			if ($Row[0] > "")
			{
				$wk_data = $Row[0];
			}
			if ($Row[1] > "")
			{
				$wk_data2 = $Row[1];
			}
		}
		//release memory
		ibase_free_result($Result);
	} 
	return array( $wk_data, $wk_data2);
} // end of function

function getProdCompleteOption($Link, $code )
{
	$Query = "select description from options where group_code='RECEIVE'  and code = 'COMPLETE|" . $code . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		$log = fopen('/tmp/getdelivery.log' , 'a');
		fwrite($log, $Query);
		fclose($log);
		//exit();
	}
	$wk_data = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$wk_data = $Row[0];
		}
	}
	//release memory
	ibase_free_result($Result);
	if ($wk_data == "")
	{
		$wk_data = "T";
	}
	return $wk_data ;
} // end of function

function getReceiveOption($Link, $code )
{
	$Query = "select description from options where group_code='RECEIVE'  and code = '" . $code . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		$log = fopen('/tmp/getdelivery.log' , 'a');
		fwrite($log, $Query);
		fclose($log);
		//exit();
	}
	$wk_data = "";
	while ( ($Row = ibase_fetch_row($Result)) ) {
		if ($Row[0] > "")
		{
			$wk_data = $Row[0];
		}
	}
	//release memory
	ibase_free_result($Result);
	if ($wk_data == "")
	{
		$wk_data = "T";
	}
	return $wk_data ;
} // end of function

$other_qty1 = 0;
$other_qty2 = 0;
$other_qty3 = 0;
$other_qty4 = 0;
{
	$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );
	$type = getBDCScookie($Link, $tran_device, "type" );
	$order = getBDCScookie($Link, $tran_device, "order" );
	$line = getBDCScookie($Link, $tran_device, "line" );
	$carrier = getBDCScookie($Link, $tran_device, "carrier" );
	$vehicle = getBDCScookie($Link, $tran_device, "vehicle" );
	$container = getBDCScookie($Link, $tran_device, "container" );
	$pallet_type = getBDCScookie($Link, $tran_device, "pallet_type" );
	$pallet_qty = getBDCScookie($Link, $tran_device, "pallet_qty" );
	$consignment = getBDCScookie($Link, $tran_device, "consignment" );
	$grn = getBDCScookie($Link, $tran_device, "grn" );
	$problem = getBDCScookie($Link, $tran_device, "problem" );

	$retfrom = getBDCScookie($Link, $tran_device, "retfrom" );
	$product = getBDCScookie($Link, $tran_device, "product" );
	$label_qty1 = getBDCScookie($Link, $tran_device, "label_qty1" );
	$ssn_qty1 = getBDCScookie($Link, $tran_device, "ssn_qty1" );
	$label_qty2 = getBDCScookie($Link, $tran_device, "label_qty2" );
	$ssn_qty2 = getBDCScookie($Link, $tran_device, "ssn_qty2" );
	$weight_qty1 = getBDCScookie($Link, $tran_device, "weight_qty1" );
	$weight_uom = getBDCScookie($Link, $tran_device, "weight_uom" );
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	$received_ssn_qty = getBDCScookie($Link, $tran_device, "received_ssn_qty" );
	$location = getBDCScookie($Link, $tran_device, "location" );
	$uom = getBDCScookie($Link, $tran_device, "uom" );
	$printed_ssn_qty = getBDCScookie($Link, $tran_device, "printed_ssn_qty" );
	$owner = getBDCScookie($Link, $tran_device, "owner" );
	$other_qty1 = getBDCScookie($Link, $tran_device, "other_qty1" );
	$other_qty3 = getBDCScookie($Link, $tran_device, "other_qty3" );
	$other_qty2 = getBDCScookie($Link, $tran_device, "other_qty2" );
	$other_qty4 = getBDCScookie($Link, $tran_device, "other_qty4" );
}
if (isset($_POST['type']))
{
	$type = $_POST['type'];
}
if (isset($_GET['type']))
{
	$type = $_GET['type'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['line']))
{
	$line = $_POST['line'];
}
if (isset($_GET['line']))
{
	$line = $_GET['line'];
}
if (isset($_POST['carrier']))
{
	$carrier = $_POST['carrier'];
}
if (isset($_GET['carrier']))
{
	$carrier = $_GET['carrier'];
}
if (isset($_POST['vehicle']))
{
	$vehicle = $_POST['vehicle'];
}
if (isset($_GET['vehicle']))
{
	$vehicle = $_GET['vehicle'];
}
if (isset($_POST['container']))
{
	$container = $_POST['container'];
}
if (isset($_GET['container']))
{
	$container = $_GET['container'];
}
if (isset($_POST['pallet_type']))
{
	$pallet_type = $_POST['pallet_type'];
}
if (isset($_GET['pallet_type']))
{
	$pallet_type = $_GET['pallet_type'];
}
if (isset($_POST['pallet_qty']))
{
	$pallet_qty = $_POST['pallet_qty'];
}
if (isset($_GET['pallet_qty']))
{
	$pallet_qty = $_GET['pallet_qty'];
}
if (isset($_POST['received_qty']))
{
	$received_qty = $_POST['received_qty'];
}
if (isset($_GET['received_qty']))
{
	$received_qty = $_GET['received_qty'];
}
if (isset($_POST['consignment']))
{
	$consignment = $_POST['consignment'];
}
if (isset($_GET['consignment']))
{
	$consignment = $_GET['consignment'];
}
if (isset($_POST['grn']))
{
	$grn = $_POST['grn'];
}
if (isset($_GET['grn']))
{
	$grn = $_GET['grn'];
}
if (isset($_POST['printer']))
{
	$printer = $_POST['printer'];
}
if (isset($_GET['printer']))
{
	$printer = $_GET['printer'];
}
// if printer not specified then try one saved in the session table
if (!isset($printer))
{
	$printer = getBDCScookie($Link, $tran_device, "printer" );
	if ($printer == "")
	{
		unset($printer);
	}
}
// if none then use PA
if (!isset($printer))
{
	$printer = "";
	$wk_current_wh_id  = getBDCScookie($Link, $tran_device, "CURRENT_WH_ID" );
	// use warehouse.default_receive_printer using the current wh_id
	$Query = "select default_receive_printer  from warehouse where wh_id = '" . $wk_current_wh_id . "' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Warehouse!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$printer  = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
	// if null use control.default_receive_printer
	if (is_null($printer )) {
		$Query = "select default_receive_printer  from control  "; 
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Control!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$printer  = $Row[0];
		}
		//release memory
		ibase_free_result($Result);
	}
	// if null use PA
	if (is_null($printer )) {
		$printer = "PA";
	}
}

if (isset($_POST['product']))
{
	$product = $_POST['product'];
}
if (isset($_GET['product']))
{
	$product = $_GET['product'];
}
if (isset($_POST['problem']))
{
	$problem = $_POST['problem'];
}
if (isset($_GET['problem']))
{
	$problem = $_GET['problem'];
}


if (isset($_POST['received_ssn_qty']))
{
	$received_ssn_qty = $_POST['received_ssn_qty'];
}
if (isset($_GET['received_ssn_qty']))
{
	$received_ssn_qty = $_GET['received_ssn_qty'];
}
if (isset($_POST['printed_ssn_qty']))
{
	$printed_ssn_qty = $_POST['printed_ssn_qty'];
}
if (isset($_GET['printed_ssn_qty']))
{
	$printed_ssn_qty = $_GET['printed_ssn_qty'];
}
if (isset($_POST['retfrom']))
{
	$retfrom = $_POST['retfrom'];
}
if (isset($_GET['retfrom']))
{
	$retfrom = $_GET['retfrom'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

if (isset($_POST['owner']))
{
	$owner = $_POST['owner'];
}
if (isset($_GET['owner']))
{
	$owner = $_GET['owner'];
}
if (isset($_POST['other_qty1']))
{
	$other_qty1 = $_POST['other_qty1'];
}
if (isset($_GET['other_qty1']))
{
	$other_qty1 = $_GET['other_qty1'];
}
if (isset($_POST['other_qty2']))
{
	$other_qty2 = $_POST['other_qty2'];
}
if (isset($_GET['other_qty2']))
{
	$other_qty2 = $_GET['other_qty2'];
}
if (isset($_POST['other_qty3']))
{
	$other_qty3 = $_POST['other_qty3'];
}
if (isset($_GET['other_qty3']))
{
	$other_qty3 = $_GET['other_qty3'];
}
if (isset($_POST['other_qty4']))
{
	$other_qty4 = $_POST['other_qty4'];
}
if (isset($_GET['other_qty4']))
{
	$other_qty4 = $_GET['other_qty4'];
}
if (isset($_POST['noprod']))
{
	$wk_NoProd = $_POST['noprod'];
}
if (isset($_GET['noprod']))
{
	$wk_NoProd = $_GET['noprod'];
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}

$useVehicleDesc1  = getVehicleOption($Link,  "OTHER_QTY1" );
$useVehicleDesc2  = getVehicleOption($Link,  "OTHER_QTY2" );
$useVehicleDesc3  = getVehicleOption($Link,  "OTHER_QTY3" );
$useProdComplete  = getProdCompleteOption($Link,  "PRODUCT" );
$usePalletQty  = getReceiveOption($Link,  $owner . "|OTHER_QTY1" );
$useOuterQty  = getReceiveOption($Link,  $owner ."|OTHER_QTY2" );

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
}
/* ====================================================================================================== */

if (isset($product) and $product != "")
{
	// get the product details
	$QueryProd = "select pp.short_desc, pp.long_desc, pp.company_id, pp.net_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, pp.stock, pp.uom, pp.issue, pp.issue_per_pallet, pp.prod_id, pp.issue_per_outer_carton, pp.alternate_id";
	$QueryProd .= " from prod_profile pp";
	$QueryProd .= " where (pp.prod_id = '".$product."' or pp.alternate_id = '".$product."')";
	$QueryProd .= " and   pp.company_id ='".$owner."' ";

	//echo("[$QueryProd]\n");
	if (!($ResultProd = ibase_query($Link, $QueryProd)))
	{
		echo("Unable to query Product!<BR>\n");
		exit();
	}
	$prod_short_desc = "";
	$prod_long_desc = "";
	$prod_company = "";
	$prod_net_weight = "";
	$prod_order_qty = "";
	$prod_per_inner_qty = "";
	$prod_inner_qty = "";
	$prod_order_weight = "";
	$prod_net_weight_uom = "";
	$prod_order_uom = "";
	$prod_inner_uom = "";
	$prod_order_weight_uom = "";
	$prod_stock = "";
	$prod_uom = "";
	$prod_issue = "";
	$prod_prod_id = "";
	$prod_per_pallet_qty = "";
	$prod_per_outer_qty = "";
	$prod_alternate_id = "";
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($ResultProd))) {
		$prod_short_desc = $Row[0];
		$prod_long_desc = $Row[1];
		$prod_company = $Row[2];
		$prod_per_inner_qty = $Row[5];
		if ($prod_per_inner_qty == "")
			$prod_per_inner_qty = 1;
		//$prod_inner_qty = $prod_order_qty / $prod_per_inner_qty;
		$prod_inner_uom = $Row[9];
		if ($prod_inner_uom == "")
			$prod_inner_uom = "EA"; 
		$prod_stock = $Row[11];
		if ($prod_stock == "")
			$prod_stock = "C"; 
		$prod_uom = $Row[12];
		if ($prod_uom == "")
			$prod_uom = "EA"; 
		$prod_issue = $Row[13];
		if ($prod_issue == "")
			$prod_issue = 1;
		$prod_per_pallet_qty = $Row[14];
		if ($prod_per_pallet_qty == "")
			$prod_per_pallet_qty = 1;
		$prod_prod_id = $Row[15];
		$prod_per_outer_qty = $Row[16];
		if ($prod_per_outer_qty == "")
			$prod_per_outer_qty = 1;
		$prod_alternate_id = $Row[17];
	}

	//release memory
	ibase_free_result($ResultProd);
	if ($prod_prod_id == "") {
		//echo "not found for current company so use ALL";
		// not found for that company so try the ALL company
		$QueryProd = "select pp.short_desc, pp.long_desc, pp.company_id, pp.net_weight, pp.issue_per_order_unit, pp.issue_per_inner_unit, pp.order_weight, pp.net_weight_uom, pp.order_uom, pp.inner_uom, pp.order_weight_uom, pp.stock, pp.uom, pp.issue, pp.issue_per_pallet, pp.prod_id, pp.issue_per_outer_carton, pp.alternate_id";
		$QueryProd .= " from prod_profile pp";
		//$QueryProd .= " where pp.prod_id = '".$product."'";
		$QueryProd .= " where (pp.prod_id = '".$product."' or pp.alternate_id = '".$product."')";
		$QueryProd .= " and   pp.company_id = 'ALL'";

		//echo("[$QueryProd]\n");
		if (!($ResultProd = ibase_query($Link, $QueryProd)))
		{
			echo("Unable to query Product!<BR>\n");
			exit();
		}
		// Fetch the results from the database.
		while (($Row = ibase_fetch_row($ResultProd))) {
			$prod_short_desc = $Row[0];
			$prod_long_desc = $Row[1];
			$prod_company = $Row[2];
			$prod_per_inner_qty = $Row[5];
			if ($prod_per_inner_qty == "")
				$prod_per_inner_qty = 1;
			//$prod_inner_qty = $prod_order_qty / $prod_per_inner_qty;
			$prod_inner_uom = $Row[9];
			if ($prod_inner_uom == "")
				$prod_inner_uom = "EA"; 
			$prod_stock = $Row[11];
			if ($prod_stock == "")
				$prod_stock = "C"; 
			$prod_uom = $Row[12];
			if ($prod_uom == "")
				$prod_uom = "EA"; 
			$prod_issue = $Row[13];
			if ($prod_issue == "")
				$prod_issue = 1;
			$prod_per_pallet_qty = $Row[14];
			if ($prod_per_pallet_qty == "")
				$prod_per_pallet_qty = 1;
			$prod_prod_id = $Row[15];
			$prod_per_outer_qty = $Row[16];
			if ($prod_per_outer_qty == "")
				$prod_per_outer_qty = 1;
			$prod_alternate_id = $Row[17];
		}
		//release memory
		ibase_free_result($ResultProd);
	}
}
if (isset($product) and $product != "")
{
	if ($prod_prod_id == "")
	{
		// product not found for these 2 companys
		$wk_NoProd = "Not Found Here";
	}
	if ($prod_prod_id != $product)
	{
		if ($prod_prod_id != "")
		{
			$product = $prod_prod_id;
		}
	}
}

/* ====================================================================================================== */
//echo("<div id=\"search1\">");
//	prod search
	$alt = "Search";
	echo("<form action=\"prodsrch.php\"  method=\"post\" name=getprodsrch>\n");
//	echo("<INPUT type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from2\" value=\"".$_SERVER['PHP_SELF']."\" >");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"addsupplier1\">");
//	add a prod supplier
	$alt = "Add Supplier";
	echo("<form action=\"supplier.php\"  method=\"post\" name=getprodsupplier>\n");
//	echo("<INPUT type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo("<form action=\"prodcomplete.php\"  method=\"post\" name=getcomplete1>\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
//echo("</div>\n");

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form method=\"post\" name=getprodqtys onsubmit=\"return processEdit();\">");
if (isset($type))
{
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
}
echo("<INPUT type=\"hidden\" name=\"complete\" >");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
switch ($type )
{
	case "PO":
		echo($type . ".No.:");
		break;
	case "RA":
		echo("Return No.:");
		break;
	case "TR":
		echo("TR No.:");
		break;
	case "WO":
		echo($type . ".No.:");
		break;
	case "LD":
		echo("Non-Product:");
		break;
	case "LP":
		echo("Load No.:");
		break;
}
echo("<INPUT type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
echo("<INPUT type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\"><br>\n");
// ================================================== do owner
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
	echo("Owner:<select name=\"owner\" class=\"sel8\">\n");
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
			echo( "<OPTION value=\"$Row[0]\" selected >$Row[1]\n");
		}
		else
		{
			echo( "<OPTION value=\"$Row[0]\">$Row[1]\n");
		}
	}
	//release memory
	ibase_free_result($Result);
	echo("</select><br>\n");
} else {
	echo("<INPUT type=\"hidden\" name=\"owner\"  value=\"$owner\" >\n");
	//echo("<INPUT type=\"hidden\" name=\"owner\"  value=\"$default_comp\" >\n");
}
// =============================================== end of owner
//echo("Load Sent From:<br>");
echo("Sent From:");
//echo("<select name=\"retfrom\" class=\"sel3\">\n");
echo("<select name=\"retfrom\" class=\"sel9\">\n");
$Query = "select person_id, first_name, last_name from person where person_type in ('RP','CO','CS','CV','CR','IS')  order by first_name, last_name "; 
$Query = "select person_id, first_name, last_name from person where person_type in ('RP','CO','CS','CV','CR','IS') and status = 'CU'  order by first_name, last_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Returner!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $retfrom)
	{
		echo( "<OPTION value=\"$Row[0]\" selected>$Row[1] $Row[2]\n");
	} else {
		echo( "<OPTION value=\"$Row[0]\">$Row[1] $Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
//echo("</select><br>\n");
echo("</select>\n");
echo("<INPUT type=\"button\" name=\"addsupplier\" value=\"A\" ");  
echo(' alt="Add Supplier" onclick="processProdSupplier();" >');
echo("<br>\n"); 
echo("Problem:<INPUT type=\"checkbox\" name=\"problem\" value=\"T\"");
echo(" onchange=\"processEdit();\"");
if ($problem > "")
{
	echo(" checked ");
}
echo(" ><br>");
echo("</div>");
echo("<div id=\"col6\">");
echo("UOM:<select name=\"uom\" class=\"sel6\">\n");
$Query = "select code, description from uom order by description "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Uom!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == "EA")
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
echo("</select><br>\n");
echo("</div>");
echo("<div id=\"col7\">");
// need multiply css classes for product input
if (isset($product)) {
	if (isset($wk_NoProd)) {
		echo("Product ID:<br><INPUT type=\"text\" name=\"product\" value=\"" . $product . "\" class=\"nomatch\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
	} else {
		echo("Product ID:<br><INPUT type=\"text\" name=\"product\" value=\"" . $product . "\" class=\"default\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
	}
} else {
	echo("Product ID:<br><INPUT type=\"text\" name=\"product\" class=\"default\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
}
//echo("processEdit();\" ><br>\n");
//echo("processEdit();\" class=\"product\" >\n");
echo("\"  >\n");
echo("<INPUT type=\"button\" name=\"search\" value=\"Search\" ");  
echo(' alt="Search" onclick="processProdSearch();" >');
echo("<br>\n"); 
echo("<table border=\"0\">\n");
echo("<tr><td>");
echo("Recd :<INPUT type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"5\" class=\"default\" value=\"$received_ssn_qty\" onchange=\"defaultPCICols(); recalcPCICol(); recalcCol();\">\n");
//echo("Recd :<INPUT type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"5\" class=\"default\" value=\"$received_ssn_qty\" >\n");
echo("<div ID=\"col5\">\n");
echo("</td><td>");
echo("Ptr:<select name=\"printer\" class=\"sel4\">\n");
$Query = "select device_id from sys_equip where device_type = 'PR' order by device_id "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Devices!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] == $printer)
	{
		echo( "<OPTION value=\"$Row[0]\" selected >$Row[0]\n");
	}
	else
	{
		echo( "<OPTION value=\"$Row[0]\">$Row[0]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
//echo("</td></tr><table>");
echo("</td></tr><tr><table>");
echo("</div>\n");
echo("<div id=\"col8\">");
// ============  grn totals ====================
if ($useVehicleDesc1[0] == "T" )
{
	echo("<label for=\"other_qty1\">$useVehicleDesc1[1]</label>");
	echo("<INPUT type=\"text\" name=\"other_qty1\" size=\"2\" maxlength=\"2\" class=\"sel16\" onchange=\"recalcPCICol()\"   value=\"$other_qty1\">\n");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty1\"  value=\"$other_qty1\">\n");
}
if ($useVehicleDesc2[0] == "T" )
{
	echo("<label for=\"other_qty2\">$useVehicleDesc2[1]</label>");
	echo("<INPUT type=\"text\" name=\"other_qty2\" size=\"3\" maxlength=\"3\" class=\"sel16\" onchange=\"recalcPCICol()\"   value=\"$other_qty2\">\n");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty2\"  value=\"$other_qty2\">\n");
}
if ($useVehicleDesc3[0] == "T" )
{
	echo("<label for=\"other_qty3\">$useVehicleDesc3[1]</label>");
	echo("<INPUT type=\"text\" name=\"other_qty3\" size=\"3\" maxlength=\"4\" class=\"sel16\" onchange=\"recalcPCICol()\"   value=\"$other_qty3\">\n");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_qty3\"  value=\"$other_qty3\">\n");
}
echo("<INPUT type=\"hidden\" name=\"other_qty4\"   value=\"$other_qty4\">\n");
if (($useVehicleDesc1[0] == "T" ) or
    ($useVehicleDesc2[0] == "T" ) or
    ($useVehicleDesc3[0] == "T" ))
{
	//echo("<label for=\"other_sum\">=</label>");
	//echo("<INPUT type=\"text\" name=\"other_sum\" size=\"3\" maxlength=\"4\" class=\"sel16\" readonly  >\n");
	echo("<INPUT type=\"hidden\" name=\"other_sum\" >\n");
	echo ("<br>");
} else {
	echo("<INPUT type=\"hidden\" name=\"other_sum\" >\n");
}
// ============= end of grn totals ================
echo("Qty Labels X Qty/ISSN Label:<br><INPUT type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\" value=\"$label_qty1\">\n");
echo("X<INPUT type=\"text\" name=\"ssn_qty1\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\" value=\"$ssn_qty1\">\n");
echo("+<INPUT type=\"text\" name=\"label_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\" value=\"$label_qty2\">\n");
echo("X<INPUT type=\"text\" name=\"ssn_qty2\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\" value=\"$ssn_qty2\"><br>\n");
	echo("<INPUT type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"weight_qty1\" >\n");
echo("Location:<select name=\"location\" class=\"sel8\">\n");
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($location == ($Row[0] . $Row[1]))
	{
		echo( "<OPTION value=\"$Row[0]$Row[1]\" selected>$Row[2]\n");
	} else {
		echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
	}
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\" id=\"col9\" >\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</form>");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"./receive_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	$alt = "Accept";
	// Create a table.
	echo ("<table BORDER=\"0\" ALIGN=\"LEFT\" id=\"col9\">");
	echo ("<tr>");
	echo ("<td>");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/accept.gif" alt="' . $alt . '">');
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
/*
	echo("<form action=\"transNX.php\"  method=\"post\" onsubmit=\"return processBack();\" name=getssnback>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" onClick="return processBack();" alt="Back">');
	echo("</form>");
*/
	echo("<form action=\"receive_menu.php\"  method=\"post\"  name=getssnback>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif"  alt="Back">');
	echo("</form>");
	echo ("</td>");
//	echo ("</tr>");
	$alt = "Product Profile";
//	echo ("<tr>");
	echo ("<td>");
	//echo("<form action=\"../ssn/product.php\"  method=\"post\" name=getproduct>\n");
	//echo("<form action=\"../query/product.php\"  method=\"post\" name=getproduct>\n");
	echo("<form action=\"../query/product.php\"  method=\"post\" onsubmit=\"return processProduct();\" name=getproduct>\n");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	if (isset($product))
		echo("<INPUT type=\"hidden\" name=\"product\" value=\"" . $product . "\" >");
	else
		echo("<INPUT type=\"hidden\" name=\"product\" >");

	echo("<INPUT type=\"hidden\" name=\"from\" value=\"".$_SERVER['PHP_SELF']."\" >");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/prodprofile.gif" alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	$alt = "Comments";
	echo("<form action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment>\n");
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/comment.gif" alt="' . $alt . '">');
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
	echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<INPUT type=\"hidden\" name=\"location\" >\n");
	echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
	echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
	echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
	echo("<INPUT type=\"hidden\" name=\"product\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo ("</td>");
//	echo ("</tr>");
//	echo ("<tr>");
	//echo ("<td COLSPAN=\"2\">");
	$alt = "Receive with Weights";
	//echo("<form action=\"verifyLPweight.php\"  method=\"post\" name=getweight>\n");
/*
	//echo("<INPUT type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/buttonflat.php?text=' . urlencode($alt) );
	echo('" alt="' . $alt . '">');
*/
	//echo('</form>');
	//echo ("</td>");
	echo ("</tr>");
	echo ("</table>");

/*
	//echo("<BUTTON name=\"back\" type=\"button\" id=\"col2\" onfocus=\"location.href='./receive_menu.php';\">\n");
	echo("<BUTTON name=\"back\" type=\"button\" id=\"col10\" onClick=\"return processBack();\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}

echo("</div>\n");
	echo ("</td>");
	echo ("</tr>");
	echo ("</table>");
/*
echo("<form action=\"transNX.php\" method=\"post\" name=getssnback >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("</form>");
*/
$_SESSION['token'] = md5(session_id() . time());
echo("<form action=\"transLPNV.php\" method=\"post\" name=goforw >");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<INPUT type=\"hidden\" name=\"complete\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
echo("<INPUT type=\"hidden\" name=\"product\" >\n");
echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<INPUT type=\"hidden\" name=\"location\" >\n");
echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
echo("<INPUT type=\"hidden\" name=\"printed_ssn_qty\" >\n");
//echo("<INPUT type=\"hidden\" name=\"token\" value=\"". $_SESSION['token'] . "\">\n");
echo("</form>");
echo("<form action=\"verifyLPOtherQty.php\" method=\"post\" name=\"gorefresh\">");
echo("<INPUT type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<INPUT type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<INPUT type=\"hidden\" name=\"complete\" >");
echo("<INPUT type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<INPUT type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<INPUT type=\"hidden\" name=\"retfrom\" >\n");
echo("<INPUT type=\"hidden\" name=\"uom\" >\n");
echo("<INPUT type=\"hidden\" name=\"product\" >\n");
echo("<INPUT type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<INPUT type=\"hidden\" name=\"location\" >\n");
echo("<INPUT type=\"hidden\" name=\"printer\" >\n");
echo("<INPUT type=\"hidden\" name=\"label_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"label_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"problem\" >\n");
echo("<INPUT type=\"hidden\" name=\"owner\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty1\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty2\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty3\" >\n");
echo("<INPUT type=\"hidden\" name=\"other_qty4\" >\n");
echo("<INPUT type=\"hidden\" name=\"from\" value=\"verifyLPOtherQty.php\" >\n");
echo("</form>");
?>
<script type="text/javascript">
<?php
{
	echo("var wk_company_cnt=" . $wk_company_cnt. ";\n"); 
	if (isset($prod_per_inner_qty)) 
	{
		if ($prod_per_inner_qty == "")
			$prod_per_inner_qty = 1;
	}
	else
		$prod_per_inner_qty = 1;
	if (isset($prod_per_pallet_qty)) 
	{
		if ($prod_per_pallet_qty == "")
			$prod_per_pallet_qty = 1;
	} 
	else
		$prod_per_pallet_qty = 1;
	if (isset($prod_issue)) 
	{
		if (isset($prod_issue))
			if ($prod_issue == "")
				$prod_issue = 1;
	}
	else
		$prod_issue = 1;
	if (isset($prod_inner_uom)) 
	{
		if ($prod_inner_uom == "")
			$prod_inner_uom = "EA"; 
	}
	else
		$prod_inner_uom = "EA"; 
	if (isset($prod_uom)) 
	{
		if ($prod_uom == "")
			$prod_uom = "EA"; 
	}
	else
		$prod_uom = "EA"; 
	if (isset($prod_per_outer_qty )) 
	{
		if ($prod_per_outer_qty == "")
				$prod_per_outer_qty = 1;
	}
	else
		$prod_per_outer_qty = 1;
	echo("var wk_prod_per_inner_qty =" . 	$prod_per_inner_qty . ";\n"); 
	//echo("var wk_prod_inner_qty =" . 	$prod_inner_qty . ";\n"); 
	echo("var wk_prod_inner_uom =\"" . 	$prod_inner_uom . "\";\n"); 
	echo("var wk_prod_uom =\"" . 	$prod_uom . "\";\n"); 
	echo("var wk_prod_issue =" . 	$prod_issue . ";\n"); 
	echo("var wk_prod_per_pallet_qty =" . 	$prod_per_pallet_qty . ";\n"); 
	echo("var wk_prod_per_outer_qty =" .	$prod_per_outer_qty . ";\n"); 
	echo("var wk_use_prod_complete =\"" .	$useProdComplete . "\";\n"); 
	echo("var wk_use_pallet_qty =\"" .	$usePalletQty . "\";\n"); 
	echo("var wk_use_outer_qty =\"" .	$useOuterQty . "\";\n"); 
	// individual = prod_issue = 1
	// per carton  qty =  per inner qty
	// per pallet qty = 
	// so pallet qty = order_qty / issue per pallet
	// so carton qty = (order qty - (pallet_qty * issue per pallet)) / per inner qty
	// so individual qty = (order qty - (pallet_qty * issue per pallet) - (per inner qty * carton qty))
	// for per pallet qty = 40 per inner = 6  per individual = 1
	// order qty = 102
	// then pallet qty = 102 / 40 = 2
	// carton qty = (102 - (2 * 40)) / 6 = 22 / 6 = 3
	// individual = 102 - (40 * 2) - (6 * 3) = 22 - 18 = 4
	// so must check that order qty = (pallet qty * issue per pallet) + (carton qty * issue per inner) + indivdual qty
	// if already have the qtys from last entry set the colurs of the fields
	echo("recalcPCICol();\n");
	echo("recalcCol();\n");
	// check which the next field to enter
	if (isset($message))
	{
		echo("document.getprodqtys.message.value=\"" . $message . "\";\n");
		echo("document.getprodqtys.product.focus();\n");
	} else {
		if ((!isset($retfrom)) or  ($retfrom == ""))
		{
			echo("document.getprodqtys.message.value=\"Select Sent From\";\n");
			echo("document.getprodqtys.retfrom.focus();\n");
		} else {
			if ((!isset($product)) or  ($product == ""))
			{
				echo("document.getprodqtys.message.value=\"Scan or Enter Product\";\n");
				echo("document.getprodqtys.product.focus();\n");
			} else {
				if ((!isset($received_ssn_qty)) or  ($received_ssn_qty == "") or ($received_ssn_qty == "0"))
  				{
					echo("document.getprodqtys.message.value=\"Enter Received Qty\";\n");
					echo("document.getprodqtys.received_ssn_qty.focus();\n");
  				}
			}
		}
	}
}
?>
</script>
</body>
</html>
