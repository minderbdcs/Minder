<?php
echo "<pre>";
$last_line = system("telnet-cwd 192.168.61.80",$retval);
echo '
</pre>
<hr /> Last line: ' . $last_line . '
<hr />Return value: ' . $retval;
	echo("<FORM action=\"./util_Menu.php\" method=\"post\" name=back>\n");
	echo("<INPUT type=\"IMAGE\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back"></INPUT>');
	echo("</FORM>");
?>

