<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Query Menu Page</title>
<?php
include "viewport.php";
//<meta name="viewport" content="width=device-width">
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
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
//require_once "Mdr/Minder/Version.php";
require_once "Minder/Version.php";

list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//  <h4 ALIGN="LEFT">Query v1.1</h4>
//echo('<h4 ALIGN="LEFT">Query v1.5 ');
echo('<h4 ALIGN="LEFT">Query ');
$myVersion = new Minder_Version;
$thisVersion = $myVersion->getFull();
$thisVersionPart = explode(".", $thisVersion);
echo($thisVersionPart[3] );
if (isset($tran_device))
{
	echo(" " . $tran_device );
}
echo('</h4>');


	$query_method = ",";
	$query_method .= "SSNS,SSNS2,"; /* ssns  */
	$query_method .= "SOORDER,"; /* SO order */
	$query_method .= "PRODDIMS,"; /* prod profiles dimensions */
{
	$Query = "SELECT description from options where group_code='QUERY_OPT'";

	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Options table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		$query_method .=  $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
	
	//echo("options:" . $query_method);

	// Create a table.
	echo ("<table border=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",SSNS,", $query_method, "getlocn.php", "getssns", "/icons/whm/SSNS_50x100.gif", "SSNS");
	//addMenuButton(",SSNS2,", $query_method, "manyissn.php", "getssns2", "/icons/whm/SSN_Profile_50X65.gif", "SSNS2");
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",SOORDER,", $query_method, "getorder.php", "getorder", "/icons/whm/nextorder.gif", "Shipping Orders");
	addMenuButton(",PRODDIMS,", $query_method, "proddim.php", "productdims", "/icons/whm/button.php?text=++Dims&fromimage=prodprofile.gif", "Product
Dims"); 

/*
	// Create a table.
	echo ("<TABLE BORDER=\"0\">");
	echo ("<TR>");
	echo ("<TD>");
*/
/*
	echo("<FORM action=\"./getlocn.php\" method=\"post\" name=getssns>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/SSNS_50x100.gif" alt="SSNS">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"./manyissn.php\" method=\"post\" name=getssns2>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/SSN_Profile_50X65.gif" alt="SSNS2">');
	echo("</FORM>");
	echo ("</TD>");
*/
/*
	echo ("<TD>");
	//echo("<FORM action=\"../location/locations.php\" method=\"post\" name=locations>\n");
	echo("<FORM action=\"./locations.php\" method=\"post\" name=locations>\n");
	echo("<INPUT type=\"IMAGE\" ");  
-*
	echo('SRC="/icons/whm/button.php?text=Locations&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Logout">');
*-
	echo('SRC="/icons/whm/locations.gif" alt="Locations">');
	echo("</FORM>");
	echo ("</TD>");
*/
/*
	echo ("</TR>");
	echo ("<TR>");
*/
/*
	echo ("<TD>");
	//echo("<FORM action=\"../person/persons.php\" method=\"post\" name=persons>\n");
	echo("<FORM action=\"./persons.php\" method=\"post\" name=persons>\n");
	echo("<INPUT type=\"IMAGE\" ");  
-*
	echo('SRC="/icons/whm/button.php?text=Persons&fromimage=');
	echo('Blank_Button_50x100.gif" alt="Persons">');
*-
	echo('SRC="/icons/whm/persons.gif" alt="Persons">');
	echo("</FORM>");
	echo ("</TD>");
*/
/*
	echo ("<TD>");
	echo("<FORM action=\"../mainmenu.php\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("<TD>");
	echo("<FORM action=\"getorder.php\" method=\"post\" name=getorder>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/nextorder.gif" alt="SOOrder">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
	echo ("<TR>");
	echo ("<TD>");
	echo("<FORM action=\"proddim.php\" method=\"post\" name=productdims>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/button.php?text=++Dims&fromimage=');
	echo('prodprofile.gif" alt="ProdDims2">');
	echo("</FORM>");
	echo ("</TD>");
	echo ("</TR>");
*/
/*
	print("<BUTTON type=\"button\" accesskey=\"s\" name=\"ssns\" value=\"Ssns\" onfocus=\"location.href='../ssn/getlocn.php';\">\n");
	print("Ssns<IMG SRC=\"/icons/hand.up.gif\" alt=\"back\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"l\" name=\"locations\" value=\"Locations\" onfocus=\"location.href='../location/locations.php';\">\n");
	print("Locations<IMG SRC=\"/icons/dir.gif\" alt=\"back\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"p\" name=\"persons\" value=\"Person detail\" onfocus=\"location.href='../person/persons.php';\">\n");
	print("Persons<IMG SRC=\"/icons/pie4.gif\" alt=\"back\"></BUTTON>\n");
	print("<BUTTON name=\"back\" type=\"button\" onClick=\"location.href='../mainmenu.php';\">\n");
	print("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
}
*/
?>
</tr>
</table>
 </body>
</html>
