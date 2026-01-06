<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
?>
<html>
<head>
<title>Get One Location</title>
<?php
include "viewport.php";
include "checkdatajs.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);


// create js for location check
whm2scanvars($Link, 'locn','LOCATION', 'LOCATION');
?>

<script type="text/javascript">
function processEdit() {
/* # check for valid location */
  var mytype;
  mytype = checkLocn(document.getlocn.location.value); 
  if (mytype == "none")
  {
	alert("Not a Location");
  	return false;
  }
  else
  {
	return true;
  }
}
</script>
</head>
<body>
<?php
echo("<FONT size=\"2\">\n");
echo("<FORM action=\"getonelocnso.php\" method=\"post\" name=getlocn ONSUBMIT=\"return processEdit();\">");

//echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\">");
echo("Location: <INPUT type=\"text\" name=\"location\" size=\"10\" ONBLUR=\"return processEdit();\" >");
echo ("<TABLE>\n");
echo ("<TR>\n");
echo("<TH>Scan Location</TH>\n");
echo ("</TR>\n");
echo ("</TABLE>\n");

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</FORM>\n");

/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<FORM action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</FORM>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\"");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
?>
<script type="text/javascript">
<?php
{
	echo("document.getlocn.location.focus();\n");
}
?>
</script>
</body>
</html>
