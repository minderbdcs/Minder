<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Receive Verify Product Load</title>
<?php
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
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
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
	document.gorefresh.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.gorefresh.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
	document.getcomplete1.retfrom.value = document.getprodqtys.retfrom.value ;
	document.getcomplete1.received_ssn_qty.value = document.getprodqtys.received_ssn_qty.value ;
	document.getcomplete1.location.value = document.getprodqtys.location.value ;
	document.getcomplete1.printer.value = document.getprodqtys.printer.value ;
	if (document.getprodqtys.problem.checked == true)
	{
		document.getcomplete1.problem.value = "T" ;
	}
	else
	{
		document.getcomplete1.problem.value = "" ;
	}
	document.getcomplete1.uom.value = document.getprodqtys.uom.value ;
	document.getcomplete1.product.value = document.getprodqtys.product.value ;
	document.getcomplete1.label_qty1.value = document.getprodqtys.label_qty1.value ;
	document.getcomplete1.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomplete1.label_qty2.value = document.getprodqtys.label_qty2.value ;
	document.getcomplete1.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomplete1.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getcomplete1.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
	document.getcomplete1.other_qty1.value = document.getprodqtys.other_qty1.value ;
	document.getcomplete1.other_qty2.value = document.getprodqtys.other_qty2.value ;
	document.getcomplete1.other_qty3.value = document.getprodqtys.other_qty3.value ;
	if (wk_company_cnt < 2)
	{
		document.getcomplete1.owner.value = document.getprodqtys.owner.value ;
	}
	else
	{
		dowhat = document.getprodqtys.owner.selectedIndex ;
		document.getcomplete1.owner.value = document.getprodqtys.owner.options[dowhat].value ;
	}
	document.getcomplete1.submit();
}

function changeQty() {
	var dowhat;
  	var csum;
  if ( document.getprodqtys.received_ssn_qty.value=="")
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
  if ( document.getprodqtys.label_qty3.value=="")
  {
	document.getprodqtys.label_qty3.value = "0";
	document.getprodqtys.ssn_qty3.value = "0";
	/* no 3rd labels */
  }
  if ( document.getprodqtys.ssn_qty3.value=="")
  {
	document.getprodqtys.ssn_qty3.value = "0";
	/* no 3rd ssn qty */
  }
  if (doDirect == "T")
  {
	/* have toPickQty and  document.getprodqtys.received_ssn_qty.value */
  	document.getprodqtys.label_qty3.value = 1;
  	document.getprodqtys.ssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
  	document.getprodqtys.label_qty1.value =  Math.floor( ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value ) / issnDirectDefaultQty );  /* qty remaining for issn labels */
  	document.getprodqtys.ssn_qty1.value = issnDirectDefaultQty ;
  	document.getprodqtys.label_qty2.value = 1;
  	document.getprodqtys.ssn_qty2.value = ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value - (document.getprodqtys.ssn_qty1.value * document.getprodqtys.label_qty1.value ) ); /* qty required for issn part of issn qty */ 
  	document.getprodqtys.dlabel_qty3.value = 1;
  	document.getprodqtys.dssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
  	document.getprodqtys.dlabel_qty1.value =  Math.floor ( ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value ) / issnDirectDefaultQty );  /* qty remaining for issn labels */
  	document.getprodqtys.dssn_qty1.value = issnDirectDefaultQty ;
  	document.getprodqtys.dlabel_qty2.value = 1;
  	document.getprodqtys.dssn_qty2.value = ( document.getprodqtys.received_ssn_qty.value - document.getprodqtys.ssn_qty3.value - (document.getprodqtys.ssn_qty1.value * document.getprodqtys.label_qty1.value ) ); /* qty required for picks */ 
  }
	csum = (document.getprodqtys.label_qty1.value * document.getprodqtys.ssn_qty1.value) + 
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value) +
	(document.getprodqtys.label_qty3.value * document.getprodqtys.ssn_qty3.value);
	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.className = "nomatch";
		document.getprodqtys.label_qty2.className = "nomatch";
		document.getprodqtys.ssn_qty1.className = "nomatch";
		document.getprodqtys.ssn_qty2.className = "nomatch";
		/* document.getprodqtys.label_qty1.focus(); */
  		return false;
	}
	else
	{
  		document.getprodqtys.message.value="Qty Adds Up";
		document.getprodqtys.label_qty1.className = "match";
		document.getprodqtys.label_qty2.className = "match";
		document.getprodqtys.ssn_qty1.className = "match";
		document.getprodqtys.ssn_qty2.className = "match";
	}
  	return true;
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
/* =========================================================================== */

  if (doDirect == "T")
  {
  	/* doing direct delivery so calc the sum  */
/* this overwrites the entered qty */
  	document.getprodqtys.label_qty3.value = 1;
  	document.getprodqtys.ssn_qty3.value = Math.min(toPickQty, document.getprodqtys.received_ssn_qty.value); /* qty required for picks */ 
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
	(document.getprodqtys.label_qty2.value * document.getprodqtys.ssn_qty2.value) +
	(document.getprodqtys.label_qty3.value * document.getprodqtys.ssn_qty3.value);

	if (csum != document.getprodqtys.received_ssn_qty.value)
	{
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
  }
  else
  {
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
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.focus();
  		return false;
	}
  }
  if ( document.getprodqtys.location.value=="")
  {
  	document.getprodqtys.message.value="Must Select the Location";
	document.getprodqtys.location.focus();
  	return false;
  }
  if (csum==document.getprodqtys.received_ssn_qty.value)
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
	document.goforw.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.goforw.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.goforw.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
	document.goforw.submit();
  }
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
	document.getcomment.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getcomment.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getcomment.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getcomment.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
	document.getprodsrch.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getprodsrch.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getprodsrch.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
	document.getprodsupplier.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getprodsupplier.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
	document.getproduct.label_qty3.value = document.getprodqtys.label_qty3.value ;
	document.getproduct.ssn_qty1.value = document.getprodqtys.ssn_qty1.value ;
	document.getproduct.ssn_qty2.value = document.getprodqtys.ssn_qty2.value ;
	document.getproduct.ssn_qty3.value = document.getprodqtys.ssn_qty3.value ;
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
  		document.getprodqtys.message.value="Qty dosn't Add Up";
		document.getprodqtys.label_qty1.className = "nomatch";
		document.getprodqtys.label_qty2.className = "nomatch";
		document.getprodqtys.ssn_qty1.className = "nomatch";
		document.getprodqtys.ssn_qty2.className = "nomatch";
		/* document.getprodqtys.label_qty1.focus(); */
  		return false;
	}
	else
	{
  		document.getprodqtys.message.value="Qty Adds Up";
		document.getprodqtys.label_qty1.className = "match";
		document.getprodqtys.label_qty2.className = "match";
		document.getprodqtys.ssn_qty1.className = "match";
		document.getprodqtys.ssn_qty2.className = "match";
	}
  return false;
}
/* ====================================================================== */
onerror = errorHandler
</script>
</head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
//require 'logme.php';
require_once 'logme.php';

//include "checkdata.php";
require_once "checkdata.php";

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

// ==============================================================================================================

/**
 * get the qty of Pick Lines ready to be picked for this product
 *
 * @param ibase_link $Link Connection to database
 * @param string $wkProduct the Product to use
 * @param string $wkCompany the Company to use
 * @param string $wkSaleChannel whether or not to use sale channels T or F
 * @param string $wkOrderSaleChannel SaleChannel for the Purchase Order
 * @param string $wkPRRecord Array of RECORD_ID of the PROD_RESERVATION record involved 
 * @return array

 * @oldparam string $wkPRRecordId old parameter RECORD_ID of the PROD_RESERVATION record involved 
 */
//function getLines4Picks ($Link, $wkProduct, $wkCompany )
function getLines4Picks ($Link, 
                         $wkProduct, 
                         $wkCompany ,
                         $wkSaleChannel, 
                         $wkOrderSaleChannel,
                         $wkPRRecordId)
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	// want to limit by wh of receive 
	$toPickWhId = "";
	$Query = "select  session.description from session where session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' " ; 
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Session!<BR>\n");
	}
	while (($Row5 = ibase_fetch_row($Result))) {
		$toPickWhId = $Row5[0];
	}		

	//release memory
	ibase_free_result($Result);
	if (sizeof($wkPRRecordId) > 0) {
		$wk1stPRRecordId = $wkPRRecordId[0]['PR_RECORD_ID'];
	} else {
		$wk1stPRRecordId = 0;
	} 

		$toPickQty = 0;
		$Query = "select sum(p1.pick_order_qty - coalesce(p1.picked_qty,0) )  "; 
		$Query .= "from pick_item p1 ";
		$Query .= "join pick_order p2 on p1.pick_order = p2.pick_order ";
		//$Query .= "where p1.pick_line_status in ('OP','UP') ";
		$Query .= "where p1.pick_line_status in ('OP','UP','AS') ";
		$Query .= "and   p1.prod_id = '" . $wkProduct . "' ";
		$Query .= "and   p2.pick_status in ('OP','DA') ";
		$Query .= "and   p2.wh_id = '" . $toPickWhId . "' ";
		$Query .= "and   p2.company_id = '" . $wkCompany . "' ";
		//if ($wkPRRecordId <> 0 and $wkOrderSaleChannel <> "NONE" and $wkSaleChannel == "T" ) {
		if ($wk1stPRRecordId <> 0 and $wkOrderSaleChannel <> "NONE" and $wkSaleChannel == "T" ) {
			/* have a prod_reservation so limit the orders to those that use it */
			//$Query .= "and   p2.other2 = '" . $wkOrderSaleChannel . "' ";
			/* have a Sales Channel so can use any order channel or none - but 1st use the orders channel */
			$wkDummy = 1;
		}
		if ( $wkOrderSaleChannel == "NONE" and $wkSaleChannel == "T" ) {
			/* have a  NONE Sales Channel so limit the orders to those that use it */
			//$Query .= "and   coalesce(p2.other2,'') = '' ";
			/* have a  NONE Sales Channel so can use any order channel or none */
			$wkDummy = 1;
		}
/*
		if ( $wkSaleChannel == "F" ) {
			-* have No prod_reservation so no limits  *-
		}
*/
/* that will give 
lines for the product that are unpicked
1 now must ensure that this line or product is the only line or product waiting  for this order */
		$Query .= "and   not exists ( select p3.pick_label_no 
                           from pick_item p3 
                           where p3.pick_order = p1.pick_order 
                           and   p3.pick_line_status in ('OP','UP')
		           and   ( p3.prod_id <> p1.prod_id ) ) ";
/* 2 if this product is a component line for a kit
   then must ensure that there are no AS status lines in the order for the kit
*/
		$Query .= "and   not exists ( select p4.pick_label_no 
                           from pick_item p4 
                           where p4.pick_order = p1.pick_order 
                           and   p4.pick_line_status in ('AS')
		           and   p4.parent_label_no = p1.parent_label_no   
		           and   (p4.prod_id <> p1.prod_id)    
		           and   ( p4.parent_label_no is not null ) ) ";

		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Orders Count!<BR>\n");
			exit();
		}
		while (($Row5 = ibase_fetch_row($Result))) {
			if (!is_null($Row5[0])) {
				$toPickQty = $Row5[0];
			}
		}		

		//release memory
		ibase_free_result($Result);

	setBDCScookie($Link, $tran_device, "pick_qty", $toPickQty );
	//echo('to pick qty:' . print_r($toPickQty,true));
	return $toPickQty;
} /* end of function */

/* =========================================================================================================== */
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
	$label_qty3 = getBDCScookie($Link, $tran_device, "label_qty3" );
	$ssn_qty3 = getBDCScookie($Link, $tran_device, "ssn_qty3" );
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
	$default_wh_id = getBDCScookie($Link, $tran_device, "WH_ID" );
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
if (isset($_POST['message']))
{
	$message = $_POST['message'];
}
if (isset($_GET['message']))
{
	$message = $_GET['message'];
}

if (isset($_POST['noprod']))
{
	$wk_NoProd = $_POST['noprod'];
}
if (isset($_GET['noprod']))
{
	$wk_NoProd = $_GET['noprod'];
}

if (isset($grn))
{
	$Query = "select  g1.wh_id from  grn g1  where g1.grn = '$grn' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GRN!<br>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		//$default_comp = $Row[2];
		//$default_supplier = $Row[3];
		$default_wh_id = $Row[0];
                if ($default_wh_id == "")
		{
			$default_wh_id = $wk_current_wh_id ;
		}

		//$received_qty = getBDCScookie($Link, $tran_device, "received_qty" );

		setBDCScookie($Link, $tran_device, "WH_ID", $default_wh_id);

	}
	//release memory
	ibase_free_result($Result);
}

$Query = "select receive_direct_delivery, receive_issn_original_qty, use_sale_channel  from control  "; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Control!<BR>\n");
	exit();
}
$wkDirectDelivery = "F";
$wkDirectDeliveryDefaultIssnQty = null;
$wkSaleChannel = "F";
while ( ($Row = ibase_fetch_row($Result)) ) {
	$wkDirectDelivery = $Row[0];
	$wkDirectDeliveryDefIssnQty = $Row[1];
	$wkSaleChannel = $Row[2];
}
//release memory
ibase_free_result($Result);
if (is_null($wkDirectDelivery )) {
	$wkDirectDelivery = "F";
}
if (is_null($wkSaleChannel )) {
	$wkSaleChannel = "F";
}

$wkOrderSaleChannel = "NONE";
$wkPRRecordId = 0;
$wkPRAvailableQty = 0;
$wkPRReserveQty = 0;
$wkPRRecord = array();
if ($wkSaleChannel == "T" and $wkOrderSaleChannel <> "NONE") {
	$Query = "select record_id,available_qty,reserved_qty  from prod_reservation where prod_id='" . $product . "' and sale_channel_code='" . $wkOrderSaleChannel . "' "; 
	$Query = "select record_id,pr_available_qty,pr_reserved_qty  from prod_reservation where pr_prod_id='" . $product . "' and pr_sale_channel_code='" . $wkOrderSaleChannel . "' and pr_reservation_status = 'OP' "; 
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read PO Order Reservation!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wkPRRecordId = $Row[0];
		$wkPRAvailableQty = $Row[1];
		$wkPRReserveQty = $Row[2];
	}
	//release memory
	ibase_free_result($Result);
	if (is_null($wkPRRecordID )) {
		$wkPRRecordID = 0 ;
	}
	if (is_null($wkPRAvailableQty )) {
		$wkPRAvailableQty = 0 ;
	}
	if (is_null($wkPRReserveQty )) {
		$wkPRReserveQty = 0 ;
	}
	$wkIssn = array();
	$wkIssn['PR_RECORD_ID']  = $wkPRRecordId;
	$wkIssn['PR_AVAILABLE_QTY']  = $wkPRAvailableQty;
	$wkIssn['PR_RESERVED_QTY']  = $wkPRReserveQty;
	/* $wkPRRecord[] = array(
		$wkPRRecordId  ,
		$wkPRAvailableQty  ,
		$wkPRReserveQty ); */
	$wkPRRecord[] = array($wkIssn);
} 
$useVehicleDesc1  = getVehicleOption($Link,  "OTHER_QTY1" );
$useVehicleDesc2  = getVehicleOption($Link,  "OTHER_QTY2" );
$useVehicleDesc3  = getVehicleOption($Link,  "OTHER_QTY3" );

/* ===================================================================== */

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

/* =================================================================== */

//echo("<div id=\"search1\">");
//	prod search
	$alt = "Search";
	echo("<form action=\"prodsrch.php\"  method=\"post\" name=getprodsrch>\n");
//	echo("<input type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
	echo("<input type=\"hidden\" name=\"from2\" value=\"".$_SERVER['PHP_SELF']."\" >");
	echo("<input type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<input type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
//echo("</div>\n");
//echo("<div id=\"addsupplier1\">");
//	add a prod supplier
	$alt = "Add Supplier";
	echo("<form action=\"supplier.php\"  method=\"post\" name=getprodsupplier>\n");
//	echo("<input type=\"IMAGE\" ");  
//	echo('SRC="/icons/unknown.gif"');
//	echo(' alt="' . $alt . '">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
	echo("<input type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<input type=\"hidden\" name=\"weight_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo("<form action=\"prodcomplete.php\"  method=\"post\" name=getcomplete1>\n");
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"printed_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
//echo("</div>\n");

echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
echo("<form method=\"post\" name=getprodqtys onsubmit=\"return processEdit();\">");
if (isset($type))
{
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
}
echo("<input type=\"hidden\" name=\"complete\" >");

echo("<input type=\"text\" name=\"message\" readonly size=\"30\" class=\"message\"><br>\n");
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
echo("<input type=\"text\" name=\"order\" maxlength=\"10\" size=\"10\" readonly value=\"$order\" class=\"noread\">\n");
echo("<input type=\"text\" name=\"line\" maxlength=\"4\" size=\"4\" readonly value=\"$line\" class=\"noread\"><br>\n");
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
	$owner = $default_comp ;
	echo("<input type=\"hidden\" name=\"owner\"  value=\"$owner\" >\n");
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
echo("<input type=\"button\" name=\"addsupplier\" value=\"A\" ");  
echo(' alt="Add Supplier" onclick="processProdSupplier();" >');
echo("<br>\n"); 
echo("Problem:<input type=\"checkbox\" name=\"problem\" value=\"T\"");
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
/*
if (isset($product))
	echo("Product ID:<br><input type=\"text\" name=\"product\" value=\"" . $product . "\" class=\"default\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
else
	echo("Product ID:<br><input type=\"text\" name=\"product\" class=\"default\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
*/
if (isset($product)) {
	if (isset($wk_NoProd)) {
		echo("Product ID:<br><input type=\"text\" name=\"product\" value=\"" . $product . "\" class=\"nomatch\" maxlength=\"33\" size=\"20\" onchange=\"doproduct();");
	} else {
		echo("Product ID:<br><input type=\"text\" name=\"product\" value=\"" . $product . "\" class=\"default\" maxlength=\"30\" size=\"20\" onchange=\"doproduct();");
	}
} else {
	echo("Product ID:<br><input type=\"text\" name=\"product\" class=\"default\" maxlength=\"33\" size=\"20\" onchange=\"doproduct();");
}
//echo("processEdit();\" ><br>\n");
//echo("processEdit();\" class=\"product\" >\n");
echo("\"  >\n");
echo("<input type=\"button\" name=\"search\" value=\"Search\" ");  
echo(' alt="Search" onclick="processProdSearch();" >');
echo("<br>\n"); 
echo("<table border=\"0\">\n");
echo("<tr><td>");
//echo("Recd :<input type=\"text\" name=\"received_ssn_qty\" size=\"5\" maxlength=\"5\" class=\"default\" value=\"$received_ssn_qty\">\n");
echo("Recd :<input type=\"text\" name=\"received_ssn_qty\" size=\"4\" maxlength=\"4\" value=\"$received_ssn_qty\" class=\"default\"");
echo(" onfocus=\"document.getprodqtys.received_ssn_qty.value=strEmpty\" ");
if ($wkDirectDelivery == "T") 
{
	echo(" onchange=\"changeQty()\" ");
}
echo(">\n");
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
	echo("<label for=\"other_qty1\">$useVehicleDesc1[1]:</label>");
	echo("<input type=\"text\" name=\"other_qty1\" size=\"3\" maxlength=\"6\" class=\"default\" value=\"$other_qty1\">\n");
} else {
	echo("<input type=\"hidden\" name=\"other_qty1\"  value=\"$other_qty1\">\n");
}
if ($useVehicleDesc2[0] == "T" )
{
	echo("<label for=\"other_qty2\">$useVehicleDesc2[1]:</label>");
	echo("<input type=\"text\" name=\"other_qty2\" size=\"3\" maxlength=\"6\" class=\"default\" value=\"$other_qty2\">\n");
} else {
	echo("<input type=\"hidden\" name=\"other_qty2\"  value=\"$other_qty2\">\n");
}
if ($useVehicleDesc3[0] == "T" )
{
	echo("<label for=\"other_qty3\">$useVehicleDesc3[1]:</label>");
	echo("<input type=\"text\" name=\"other_qty3\" size=\"3\" maxlength=\"6\" class=\"default\" value=\"$other_qty3\">\n");
} else {
	echo("<input type=\"hidden\" name=\"other_qty3\"  value=\"$other_qty3\">\n");
}
echo("<input type=\"hidden\" name=\"other_qty4\"   value=\"$other_qty4\">\n");
if (($useVehicleDesc1[0] == "T" ) or
    ($useVehicleDesc2[0] == "T" ) or
    ($useVehicleDesc3[0] == "T" ))
{
	echo ("<br>");
}
// ============= end of grn totals ================

if ($wkDirectDelivery == "T") 
{
	if (isset($product))
	{
                //                              $product_company ,
		// must use the owner as the company to look for
		$wk_ToPickQty = getLines4Picks ($Link, 
                                                $product, 
                                                $owner ,
                                                $wkSaleChannel, 
                                                $wkOrderSaleChannel,
                                                $wkPRRecord);
	} else {
		$wk_ToPickQty = 0;
	}
	echo("Qty Labels X Qty/ISSN Label:<BR><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\"  >\n");
	echo("X<input type=\"text\" name=\"ssn_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" >\n");
	echo("<input type=\"hidden\" name=\"dssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"dlabel_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"dssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"dlabel_qty1\" >\n");
	echo("+PK<input type=\"text\" name=\"dlabel_qty3\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly value=\"1\">\n");
	echo("X<input type=\"text\" name=\"dssn_qty3\" maxlength=\"4\" size=\"3\" class=\"sel15\" readonly ><br>\n");

	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\"  >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" value=\"1\">\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<input type=\"hidden\" name=\"weight_qty1\" >\n");
} else {
	$wk_ToPickQty = 0;
	echo("Qty Labels X Qty/ISSN Label:<br><input type=\"text\" name=\"label_qty1\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty1\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("+<input type=\"text\" name=\"label_qty2\" maxlength=\"4\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\">\n");
	echo("X<input type=\"text\" name=\"ssn_qty2\" maxlength=\"5\" size=\"3\" class=\"sel5\" onchange=\"recalcCol()\"><br>\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"weight_uom\" >\n");
	echo("<input type=\"hidden\" name=\"weight_qty1\" >\n");
}
echo("WH<input name=\"WH_ID\" readonly value=\"" . $default_wh_id . "\" size=\"2\">\n");
echo("Location:<select name=\"location\" class=\"sel8\">\n");
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln join session on session.device_id = '" . $tran_device . "' and session.code='CURRENT_WH_ID' where ln.wh_id = session.description and ln.store_area = 'RC' order by ln.locn_name "; 
$Query = "select ln.wh_id, ln.locn_id, ln.locn_name from location ln  where ln.wh_id = '" . $default_wh_id . "' and ln.store_area = 'RC' order by ln.locn_id,ln.locn_name " ; 
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Locations!<br>\n");
	exit();
}
while ( ($Row = ibase_fetch_row($Result)) ) {
	echo( "<OPTION value=\"$Row[0]$Row[1]\">$Row[2]\n");
}
//release memory
ibase_free_result($Result);
echo("</select><br>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<input type=\"submit\" name=\"accept\" value=\"Accept\">\n");
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
	echo("<input type=\"submit\" name=\"back\" value=\"Back\">\n");
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
	//echo("<input type=\"IMAGE\" ");  
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
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" onClick="return processBack();" alt="Back">');
	echo("</form>");
*/
	echo("<form action=\"receive_menu.php\"  method=\"post\"  name=getssnback>\n");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
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
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	if (isset($product))
		echo("<input type=\"hidden\" name=\"product\" value=\"" . $product . "\" >");
	else
		echo("<input type=\"hidden\" name=\"product\" >");

	echo("<input type=\"hidden\" name=\"from\" value=\"".$_SERVER['PHP_SELF']."\" >");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . urlencode($alt) . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/prodprofile.gif" alt="' . $alt . '">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	$alt = "Comments";
	echo("<form action=\"comments.php\"  method=\"post\" onsubmit=\"return processComment();\" name=getcomment>\n");
	//echo("<input type=\"IMAGE\" ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
/*
	echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
	echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
*/
	echo('SRC="/icons/whm/comment.gif" alt="' . $alt . '">');
	echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
	echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
	echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
	echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
	echo("<input type=\"hidden\" name=\"retfrom\" >\n");
	echo("<input type=\"hidden\" name=\"owner\" >\n");
	echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
	echo("<input type=\"hidden\" name=\"location\" >\n");
	echo("<input type=\"hidden\" name=\"printer\" >\n");
	echo("<input type=\"hidden\" name=\"problem\" >\n");
	echo("<input type=\"hidden\" name=\"uom\" >\n");
	echo("<input type=\"hidden\" name=\"product\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
	echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
	echo("</form>");
	echo ("</td>");
//	echo ("</tr>");
//	echo ("<tr>");
	//echo ("<td COLSPAN=\"2\">");
	$alt = "Receive with Weights";
	//echo("<form action=\"verifyLPweight.php\"  method=\"post\" name=getweight>\n");
/*
	//echo("<input type=\"IMAGE\" ");  
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
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("</form>");
*/
echo("<form action=\"transLPNV.php\" method=\"post\" name=goforw >");
echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<input type=\"hidden\" name=\"complete\" >");
echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"retfrom\" >\n");
echo("<input type=\"hidden\" name=\"uom\" >\n");
echo("<input type=\"hidden\" name=\"product\" >\n");
echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<input type=\"hidden\" name=\"location\" >\n");
echo("<input type=\"hidden\" name=\"printer\" >\n");
echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
echo("<input type=\"hidden\" name=\"problem\" >\n");
echo("<input type=\"hidden\" name=\"owner\" >\n");
echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
echo("</form>");
echo("<form action=\"verifyLP.php\" method=\"post\" name=\"gorefresh\">");
echo("<input type=\"hidden\" name=\"type\" value=\"$type\" >");
echo("<input type=\"hidden\" name=\"grn\" value=\"$grn\" >");
echo("<input type=\"hidden\" name=\"complete\" >");
echo("<input type=\"hidden\" name=\"order\" value=\"$order\">\n");
echo("<input type=\"hidden\" name=\"line\" value=\"$line\">\n");
echo("<input type=\"hidden\" name=\"retfrom\" >\n");
echo("<input type=\"hidden\" name=\"uom\" >\n");
echo("<input type=\"hidden\" name=\"product\" >\n");
echo("<input type=\"hidden\" name=\"received_ssn_qty\" >\n");
echo("<input type=\"hidden\" name=\"location\" >\n");
echo("<input type=\"hidden\" name=\"printer\" >\n");
echo("<input type=\"hidden\" name=\"label_qty1\" >\n");
echo("<input type=\"hidden\" name=\"label_qty2\" >\n");
echo("<input type=\"hidden\" name=\"label_qty3\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty1\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty2\" >\n");
echo("<input type=\"hidden\" name=\"ssn_qty3\" >\n");
echo("<input type=\"hidden\" name=\"problem\" >\n");
echo("<input type=\"hidden\" name=\"owner\" >\n");
echo("<input type=\"hidden\" name=\"other_qty1\" >\n");
echo("<input type=\"hidden\" name=\"other_qty2\" >\n");
echo("<input type=\"hidden\" name=\"other_qty3\" >\n");
echo("<input type=\"hidden\" name=\"other_qty4\" >\n");
echo("<input type=\"hidden\" name=\"from\" value=\"verifyLP.php\" >\n");
echo("</form>");
?>
<script type="text/javascript">
<?php
{
	echo("var wk_company_cnt=" . $wk_company_cnt. ";"); 
	echo("var strEmpty = \"\";\n");
	echo("var doDirect = " . json_encode($wkDirectDelivery) . ";\n");
	echo("var issnDirectDefaultQty = " . json_encode($wkDirectDeliveryDefIssnQty) . ";\n");
	echo("var toPickQty = " . json_encode($wk_ToPickQty) . ";\n");
	echo("var processDirection = \"\" ;\n");
	if ($wkDirectDelivery == "T") 
	{
		echo("changeQty();\n");
	}
	if (isset($message))
	{
		if (strlen($message) > 200)
		{
			$message = substr($message,1,200);
		}
		echo("document.getprodqtys.message.value=\"" . $message . "\";\n");
	} else {
		echo("document.getprodqtys.message.value=\"Select Sent From\";\n");
	}
	
	echo("document.getprodqtys.retfrom.focus();\n");
}
?>
</script>
</body>
</html>
