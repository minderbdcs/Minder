<style type="text/css">
.buttons, input.buttons{
height: 4.2em;
width: 7em;
white-space: normal;
padding: 0;
margin: 1%;
border-width: medium;
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: .8em;
font-weight: bold;
line-height: 0.96em ;
text-align: center;
color: black;
border-color: blue black grey blue;
border-width: 2px;
background-color: yellow;
-webkit-border-radius:5px;
-o-border-radius:5px;
border-radius:5px;
}
#back, #Back, input.back{
color: black;
border-color: blue black grey blue;
background-color: bisque;
}
#accept, #Accept, input.accept, #send, #Send, input.send{
color: black;
border-color: blue black grey blue;
background-color: PaleGreen;
}
#getout,#logout, input.logout,#logoutdevice,#logoutall,#logoutuser{
color: black;
border-color: blue black grey blue;
background-color: red;
}
</style>
<?php
function whm2buttons($alt , $backto = "../mainmenu.php", $endtable = "Y", $image2 = "Back_50x100.gif", $alt2 = "Back", $image1="",$endline = "Y")
{
	global $rimg_width;
	global $wk_menu_output;
	$wk_use_output_type = isset($wk_menu_output) ? $wk_menu_output : "BUTTON";
	// Create a table.
	//echo ("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	echo ("<tr>");
	echo ("<td>");
	$menuName = $alt;
	if ($wk_use_output_type == "BUTTON") {
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"button\" />\n");
	}
	if ($wk_use_output_type == "IMAGE") {
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		if ($image1 == "")
		{
			echo('SRC="/icons/whm/button.php?text=' . $alt . '&fromimage=');
			echo('Blank_Button_50x100.gif" alt="' . $alt . '">');
		} else {
			echo('SRC="/icons/whm/' . $image1 . '" alt="' . $alt . '">');
		}
	}
	echo("</form>");
	echo ("</td>");
	echo ("<td>");
	echo("<form action=\"" . $backto . "\" method=\"post\" name=" . $alt2 . ">\n");
	$menuName = $alt2;
	if ($wk_use_output_type == "BUTTON") {
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt2 . "\" id=\"" . $menuName . "\" title=\"" . $alt2 . "\" class=\"button\" />\n");
	}
	if ($wk_use_output_type == "IMAGE") {
		echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
		echo('SRC="/icons/whm/' . $image2 . '" alt="' . $alt2 . '">');
	}
	echo("</form>");
	echo ("</td>");
	if ($endtable == "Y")
	{
		$endline = "Y";
	}
	if ($endline == "Y")
	{
		echo ("</tr>");
	}
	if ($endtable == "Y")
	{
		echo ("</table>");
	}
}
function addMenuButton($menuCode, $menuMethod, $menuAction, $menuName, $menuImage, $menuAlt)
{
	global $wk_label_posn;
	global $wk_menu_output;
	$wk_use_output_type = isset($wk_menu_output) ? $wk_menu_output : "BUTTON";
	if (strpos($menuMethod, $menuCode) === FALSE)
	{
		$wk_dummy = $menuCode;
	}
	else
	{
		echo ("<td>");
		echo("<form action=\"" . $menuAction ."\" method=\"post\" name=\"". $menuName . "\">\n");
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"button\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" ");  
			echo('src="' . $menuImage . '" alt="' . $menuAlt . '">');
		}
		echo ("</form>");
		echo ("</td>");
		$wk_label_posn = $wk_label_posn + 1;
		if ($wk_label_posn > 1)
		{
			echo ("</tr>");
			echo ("<tr>");
			$wk_label_posn = 0;
		}
	}
} // end of function

function addScreenButton( $menuName, $menuImage, $menuAlt)
{
	global $wk_label_posn;
	global $wk_menu_output;
	$wk_use_output_type = isset($wk_menu_output) ? $wk_menu_output : "BUTTON";
	if ($wk_use_output_type == "BUTTON") {
		echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"button\" />\n");
	}
	if ($wk_use_output_type == "IMAGE") {
		echo("<input type=\"IMAGE\" ");  
		echo('src="' . $menuImage . '" alt="' . $menuAlt . '">');
	}
} // end of function
?>

