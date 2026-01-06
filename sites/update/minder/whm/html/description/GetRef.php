<?php
include "../login.inc";
?>
<HTML>
 <HEAD>
  <TITLE>Get Reference for Transactions</TITLE>
 </HEAD>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <BODY BGCOLOR="#FFFFFF">

  <H2 ALIGN="LEFT">Enter Reference</H2>

 <FORM action="GetLocn.php" method="post" name=getref>
 <P>
<?php
print("Reference: <INPUT type=\"text\" name=\"reference\"");
if (isset($_POST['reference'])) 
{
	print(" value=\"".$_POST['reference']."\"");
}
if (isset($_GET['reference'])) 
{
	print(" value=\"".$_GET['reference']."\"");
}
print(" size=\"40\"");
print(" maxlength=\"40\"><BR>\n");
?>
<INPUT type="submit" value="send">
<INPUT type="reset">
</FORM>
</P>
</BODY>
<SCRIPT>
document.getref.reference.focus();
</SCRIPT>
</HTML>
