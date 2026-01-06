<?php
$PageTitle = "Login Page";
require ("header.php");
//include "2buttons.php";
include "db_access.php";
//$rimg_width = 90;
//echo("<input type=\"submit\" name=\"" . $menuName . "\" value=\"" . $alt . "\" id=\"" . $menuName . "\" title=\"" . $alt . "\" class=\"buttons\" />\n");
        echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");
        //echo('SRC="/icons/whm/button.php?text=Logout+User&fromimage=');
        ////echo('Blank_Button_50x100.gif" alt="LogoutUser">');
        echo('SRC="/icons/whm/button.php?text=Logout+User');
        echo(' alt="LogoutUser">');
        //echo('LogOut_50x100.gif" alt="LogoutUser">');


require ("footer.php");
?>
