<html>
<head>
  <title>Label Generation via Drop Downs</title>
<?php
{
	echo('<link rel=stylesheet type="text/css" href="labeltype.css">');
}
?>
 </head>
<script>

function processEdit() {
  var mytype;
  /* document.gettype.message.value="in process edit"; */
  if ( document.gettype.sntype.value=="")
  {
  	document.gettype.message.value="Must Select the Type";
	document.gettype.sntype.focus()
  	return false
  }
  if (!(( document.gettype.genbrand[0].checked) ||
       ( document.gettype.genbrand[1].checked)))
  {
  	document.gettype.message.value="Choose Brand or Generic";
	document.gettype.genbrand.focus()
  	return false
  }
  return true;
}
function dogeneric() {
  var mytype;
  divbrand.style.visibility='hidden';
  divgeneric.style.visibility='visible';
  if ( document.gettype.generic1.value=="")
  {
    document.gettype.message.value="Select 1st Generic";
    document.gettype.generic1.focus(); 
  }
  else
  if ( document.gettype.generic2.value=="")
  {
    document.gettype.message.value="Select 2nd Generic";
    document.gettype.generic2.focus(); 
  }
  else
  if ( document.gettype.generic3.value=="")
  {
    document.gettype.message.value="Select 3rd Generic";
    document.gettype.generic3.focus(); 
  }
  else
  if ( document.gettype.generic4.value=="")
  {
    document.gettype.message.value="Select 4th Generic";
    document.gettype.generic4.focus(); 
  }
}
function dobrand() {
  var mytype;
  divgeneric.style.visibility='hidden';
  divbrand.style.visibility='visible';
  if ( document.gettype.brand1.value=="")
  {
    document.gettype.message.value="Select 1st Brand";
    document.gettype.brand1.focus(); 
  }
  else
  if ( document.gettype.brand2.value=="")
  {
    document.gettype.message.value="Select 2nd Brand";
    document.gettype.brand2.focus(); 
  }
  else
  if ( document.gettype.brand3.value=="")
  {
    document.gettype.message.value="Select 3rd Brand";
    document.gettype.brand3.focus(); 
  }
  else
  if ( document.gettype.brand4.value=="")
  {
    document.gettype.message.value="Select 4th Brand";
    document.gettype.brand4.focus(); 
  }
}
function loadgenbrand() {
  var mylen;
  var i;
  {
     myopt = new Array (document.gettype.generic1.options)  
     /* document.gettype.generic2.options = myopt   */
     mylen = document.gettype.generic1.length;
  }
}
</script>
<body onload="loadgenbrand();">
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$message = "";
if (isset($_POST['sntype']))
{
	$sntype = $_POST['sntype'];
}
if (isset($_GET['sntype']))
{
	$sntype = $_GET['sntype'];
}
if (isset($_POST['typechange']))
{
	$typechange = $_POST['typechange'];
}
if (isset($_GET['typechange']))
{
	$typechange = $_GET['typechange'];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//release memory
//ibase_free_result($Result);

echo("<FORM action=\"labeltype.php\" method=\"post\" name=gettype onsubmit=\"return processEdit();\" >");
echo("<INPUT type=\"hidden\" name=\"typechange\" value=\"0\" >");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"50\" class=\"message\"><br>\n");
echo("<div id=\"col2\">");
echo("Type:<br>");
echo("<br>");
echo("</div>\n");
echo("<div id=\"col3\">");
//echo('<table border="0">');
//echo("<tr><td>");
$Query = "SELECT code, description FROM ssn_type ORDER BY description ";
//echo("Type:</td><td><SELECT name=\"sntype\" size=\"1\" class=\"sel50\" onchange=\"document.gettype.typechange.value=1\">\n");
echo("<SELECT name=\"sntype\" size=\"1\" class=\"sel50\" onchange=\"document.gettype.typechange.value=1\" >\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Type!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	{
		echo( "<OPTION value=\"" . $Row[0] .  "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
//echo ("</SELECT></td></tr>");
echo ("</SELECT>");
echo "<br><input type=\"radio\" name=\"genbrand\" value=\"GENERIC\" onclick=\"dogeneric();\">Generic\n";
echo "<input type=\"radio\" name=\"genbrand\" value=\"BRAND\" onclick=\"dobrand();\">Brand\n";
//echo ("</table>");
echo("</div>\n");
echo("<div id=\"divgeneric\" style=\"visibility:hidden\">");
echo("<label for=\"generic1\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic1\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"generic2\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic2\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"generic3\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic3\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"generic4\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic4\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("</div>\n");
echo("<div id=\"divbrand\" style=\"visibility:hidden\">");
echo("<label for=\"brand1\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand1\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"brand2\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand2\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"brand3\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand3\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("<label for=\"brand4\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand4\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	{
		echo( "<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
	}
}
if ($wk_selected == "N")
{
	//if (isset($current_type))
	{
		//if ($current_type == "")
		{
			echo( "<OPTION value=\"\" selected>NO VALUE\n");
		}
	}
}
echo ("</select><br>");
echo("</div>\n");
echo("<div id=\"col4\">");
echo('<table border="0">');
//release memory
ibase_free_result($Result);
//commit
ibase_commit($dbTran);
echo ("<BR>");
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./desc_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
}
echo("</div>\n");
echo("<script>");
if (isset($message))
{
	echo("document.gettype.message.value=\"" . $message . " Select Type" . "\";");
	echo('document.gettype.sntype.focus();');
}
else
{
	echo('document.gettype.message.value="Select Type";');
	echo('document.gettype.sntype.focus();');
}
?>
</script>
</body>
</html>
