<?php
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	//header ("Location: ./login/login.php?Message=LoggedOut");
}
?>
<HTML>
 <HEAD>
  <TITLE>Main Menu Page</TITLE>
<?php
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="core-style.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
?>
 </HEAD>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <BODY BGCOLOR="#FFFFFF">

<?php
//print("<H4 ALIGN=\"LEFT\">Inventory v1.");
print("<H4>Inventory v1.");
if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
if ($UserType == "PR")
{
	print("Prod");
}
else
{
	print("Test");
}
if (isset($DBDevice))
{
	print(".".$DBDevice);
}
print("</H4>\n");
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	print("<FORM action=\"./description/GetLocn.php\" method=\"post\" name=getdescription>\n");
	print("<INPUT type=\"submit\" name=\"description\" value=\"Description\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./transfer/Transfer_Menu.php\" method=\"post\" name=gettransfer>\n");
	print("<INPUT type=\"submit\" name=\"transfer\" value=\"tRansfer\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./test/GetSSNFrom.php\" method=\"post\" name=gettest>\n");
	print("<INPUT type=\"submit\" name=\"test\" value=\"tEst\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./pick/pick_Menu.php\" method=\"post\" name=getpick>\n");
	print("<INPUT type=\"submit\" name=\"pick\" value=\"Pick\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./query/query.php\" method=\"post\" name=getquery>\n");
	print("<INPUT type=\"submit\" name=\"query\" value=\"Query\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./despatch/despatch_menu.php\" method=\"post\" name=getdespatch>\n");
	print("<INPUT type=\"submit\" name=\"despatch\" value=\"Despatch\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./stocktake/GetSTKLocn.php\" method=\"post\" name=getstocktake>\n");
	print("<INPUT type=\"submit\" name=\"stocktake\" value=\"Stocktake\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./receive/receive_menu.php\" method=\"post\" name=getreceive>\n");
	print("<INPUT type=\"submit\" name=\"receive\" value=\"Receive\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./putaway/getfromlocn.php\" method=\"post\" name=getputaway>\n");
	print("<INPUT type=\"submit\" name=\"putaway\" value=\"Putaway\">\n");
	print("</FORM>\n");
	print("<FORM action=\"./login/logout.php\" method=\"post\" name=getlogout>\n");
	print("<INPUT type=\"submit\" name=\"logout\" value=\"log Out\">\n");
	print("</FORM>\n");
}
else
{
	// html 4.0 browser
	print("<div id=\"col1\">");
	print("<BUTTON type=\"button\" accesskey=\"d\" name=\"description\" value=\"Description\" onfocus=\"location.href='./description/GetLocn.php';\">\n");
	print("Description<IMG SRC=\"/icons/quill.gif\" alt=\"description\" ></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"r\" name=\"transfer\" value=\"tRansfer\" onfocus=\"location.href='./transfer/Transfer_Menu.php';\">\n");
	print("tRansfer<IMG SRC=\"/icons/transfer.gif\" alt=\"transfer\" ></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"e\" name=\"test\" value=\"tEst\" onfocus=\"location.href='./test/GetSSNFrom.php';\">\n");
	print("tEst<IMG SRC=\"/icons/unknown.gif\" alt=\"test\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"p\" name=\"pick\" value=\"Pick\" onfocus=\"location.href='./pick/pick_Menu.php';\">\n");
	print("Pick<IMG SRC=\"/icons/continued.gif\" alt=\"pick\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"o\" name=\"logout\" value=\"log Out\" onfocus=\"location.href='./login/logout.php';\">\n");
	print("logOut<IMG SRC=\"/icons/back.gif\" alt=\"logout\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"c\" name=\"cancel ssn\" value=\"Cancel SSN\" onfocus=\"location.href='./cancel/getssn.php';\">\n");
	print("Cancel SSN<IMG SRC=\"/icons/alert.red.gif\" alt=\"cancel ssn\"></BUTTON>\n");
	print("</div>");
	print("<div id=\"col2\">");
	print("<BUTTON type=\"button\" accesskey=\"h\" name=\"despatch\" value=\"despatcH\" onfocus=\"location.href='./despatch/despatch_menu.php';\">\n");
	print("despatcH<IMG SRC=\"/icons/sphere1.gif\" alt=\"despatch\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"s\" name=\"stocktake\" value=\"Stocktake\" onfocus=\"location.href='./stocktake/GetSTKLocn.php';\">\n");
	print("Stocktake<IMG SRC=\"/icons/sphere2.gif\" alt=\"stocktake\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"w\" name=\"putaway\" value=\"Putaway\" onfocus=\"location.href='./putaway/getfromlocn.php';\">\n");
	print("putaWay<IMG SRC=\"/icons/layout.gif\" alt=\"putaway\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"q\" name=\"query\" value=\"Query\" onfocus=\"location.href='./query/query.php';\">\n");
	print("Query<IMG SRC=\"/icons/comp.blue.gif\" alt=\"query\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"v\" name=\"receive\" value=\"receiVe\" onfocus=\"location.href='./receive/receive_menu.php';\">\n");
	print("receiVe<IMG SRC=\"/icons/sphere1.gif\" alt=\"receive\"></BUTTON>\n");
	print("<BUTTON type=\"button\" accesskey=\"l\" name=\"label\" value=\"Label reprint\" onfocus=\"location.href='./printer/dir.php';\">\n");
	print("Label reprint<IMG SRC=\"/icons/movie.gif\" alt=\"label\"></BUTTON>\n");
	print("</div>");
}
?>
 </BODY>
<?php
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	print("<SCRIPT>\n");
	print("document.getdescription.description.focus();\n");
	print("</SCRIPT>\n");
}
?>

</HTML>
