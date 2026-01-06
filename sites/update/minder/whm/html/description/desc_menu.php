<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>Descriptions Menu Page</title>
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

  <h4 ALIGN="LEFT">Description v1.1</h4>

<?php
include "db_access.php";
include "2buttons.php";

	$description_method = "";
	$description_method .= ",NEXTLOCN,";
	$description_method .= ",DROPSSN,";
	$description_method .= ",LABEL,";
	$description_method .= ",LOCATION,";

	// Create a table.
	echo ("<table BORDER=\"0\">");
	echo ("<tr>");
	$wk_label_posn = 0;
	addMenuButton(",BACK,", ",BACK,", "../mainmenu.php", "back", "/icons/whm/Back_50x100.gif", "Back");
	addMenuButton(",NEXTLOCN,", $description_method, "GetLocn.php", "getssns", "/icons/whm/locations.gif", "Next 
Location");
	addMenuButton(",DROPSSN,", $description_method, "dropssn.php", "dropstart", "/icons/whm/SSNS_50x100.gif", "SSN via
Dropdown");
	addMenuButton(",LABEL,", $description_method, "labeltype.php", "typelabel", "/icons/whm/REPRINT_50x100.gif", "Reprint
Labels via
Dropdown");

	echo ("</TR>");
	echo ("</table>");
?>
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<script type=\"text/javascript\">");
	echo("document.getssns.ssns.focus();\n");
	echo("</script>\n");
}
?>
 </body>
</html>
