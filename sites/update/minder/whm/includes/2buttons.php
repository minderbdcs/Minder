<?php
/*
<style type="text/css">
.buttons, input.buttons{
height: 4.2em;
width: 7em;
white-space: normal;
padding: 0;
margin: 1%;
border-width: medium;
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: 1.2em;
font-weight: bold;
line-height: 1.4em ;
text-align: center;
color: black;
border-color: blue black grey blue;
border-width: 2px;
background-color: yellow;
-webkit-border-radius:5px;
-o-border-radius:5px;
border-radius:5px;
}
.menubuttons, input.menubuttons{
height: 1.8em;
width: 3em;
white-space: normal;
padding: 0;
margin: 1%;
border-width: medium;
font-family: Verdana, Helvetica,  Arial, sans-serif;
font-size: 2.4em;
font-weight: bold;
line-height: 2.88em ;
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
*/
?>
<?php
	$wk_menu_output = "IMAGE";
/* 
if
MSIEMobile 6.0 or
MSIE 6.0 or
MSIE 4.01 
	use 2buttons.css
if WebKit
	use 2buttons-ie7.css
if Gecko
	use 2buttons-ie7.css
*/
/*
	if ((strpos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 4.01") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 6.0") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "WebKit") === false))
	{
		include "2buttons.css";

	} else {
		include "2buttons-ie7.css";
	}
*/
	// eg "/whm/f1/f2/f3/file.php"
	// length = 5
	$wkMyInc = "http://";
  	$wkMySelf = $_SERVER['PHP_SELF'];
  	$wkMyInc .= $_SERVER['HTTP_HOST'];
  	$wkMyPort = $_SERVER['SERVER_PORT'];
	if ($wkMyPort != "80")
	{
		$wkMyInc = $wkMyInc . ":" . $wkMyPort;
	}
	$wkMyInc = $wkMyInc . "/whm/";
/*
//echo "me:" . $wkMySelf;
  	$wkMyDoc = explode("/", $_SERVER['PHP_SELF']);
	$wkMyLen = count($wkMyDoc);
//echo "len:" . $wkMyLen;
	for ($wkMyIdx = $wkMyLen - 1;$wkMyIdx> 0; $wkMyIdx--)
	{
		if ($wkMyDoc[$wkMyIdx] == "whm")
		{
			break;
		}
		$wkMyInc = $wkMyInc . "../";
	}
//echo "inc:" . $wkMyInc;
	if (strlen($wkMyInc) <= 3)
	{
		$wkMyInc = "../";
	} else {
		$wkMyInc = substr($wkMyInc, 3);
	}
*/
	$wkMyInc = $wkMyInc . "includes/";
//echo "inc2:" . $wkMyInc;

/*
	if ((strpos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6.0") !== false) or 
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 4.01") !== false) or
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "Gecko") !== false)) 
	{
		include "2buttons.css";
		//echo('<link rel=stylesheet type="text/css" href="/whm/includes/2buttons.css">');
		//echo('<link rel=stylesheet type="text/css" href="' . $wkMyInc . '2buttons.css">');
	} else {
		include "2buttons-ie7.css";
		//echo('<link rel=stylesheet type="text/css" href="/whm/includes/2buttons-ie7.css">');
		//echo('<link rel=stylesheet type="text/css" href="' . $wkMyInc . '2buttons-ie7.css">');
	}
*/
	if ($wkMyBW == "IE60")
	{
		include "2buttons.css";
	}
	if ($wkMyBW == "IE65")
	{
		include "2buttons-ie7.css";
	}
	if ($wkMyBW == "CHROME")
	{
		include "2buttons-ie7.css";
	}
	if ($wkMyBW == "SAFARI")
	{
		include "2buttons-ie7.css";
	}
	if ($wkMyBW == "NETFRONT")
	{
		include "2buttons.css";
	}
$wkMyTest = "whm2buttons";
if (!is_callable($wkMyTest))
{
	function whm2buttons($alt , $backto = "../mainmenu.php", $endtable = "Y", $image2 = "Back_50x100.gif", $alt2 = "Back", $image1="",$endline = "Y", $imagewidth="N")
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
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo("<input type=\"IMAGE\"  ");  
			if ($imagewidth <> "N") {
				echo(" width=\"$rimg_width\" ");  
			}
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
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt2 . "\" id=\"" . $menuName . "\" title=\"" . $alt2 . "\" class=\"buttons\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
			echo("<input type=\"IMAGE\"  ");  
			if ($imagewidth <> "N") {
				echo(" width=\"$rimg_width\" ");  
			}
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
}
$wkMyTest = "addMenuButton";
if (!is_callable($wkMyTest))
{
	function addMenuButton($menuCode, $menuMethod, $menuAction, $menuName, $menuImage, $menuAlt)
	{
		global $wk_label_posn;
		global $wk_menu_output;
		global $rimg_width;
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
				echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"buttons\" />\n");
			}
			if ($wk_use_output_type == "IMAGE") {
				//echo("<input type=\"IMAGE\" ");  
				//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
				//echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
				echo("<input type=\"IMAGE\"  ");  
				echo("class=\"menubuttons\" ");  
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
}

$wkMyTest = "addScreenButton";
if (!is_callable($wkMyTest))
{
	function addScreenButton( $menuName, $menuImage, $menuAlt)
	{
		global $wk_label_posn;
		global $wk_menu_output;
		$wk_use_output_type = isset($wk_menu_output) ? $wk_menu_output : "BUTTON";
		if ($wk_use_output_type == "BUTTON") {
			echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $menuAlt . "\" id=\"" . $menuName . "\" title=\"" . $menuAlt . "\" class=\"buttons\" />\n");
		}
		if ($wk_use_output_type == "IMAGE") {
			echo("<input type=\"IMAGE\" ");  
			echo('src="' . $menuImage . '" alt="' . $menuAlt . '">');
		}
	} // end of function
}
?>

