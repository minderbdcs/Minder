<?php
session_start();
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	// header ("Location: ../login/login.php?Message=LoggedOut");
	//$demomode = 1;
	if (isset($demomode))
	{
		setcookie("LoginUser","BDCS|MA",time()+86400,"/");
		//setcookie("SaveUser","BDCS|MA|TR",time()+1111000,"/");
		setcookie("SaveUser","BDCS|MA|PR",time()+1111000,"/");
	}
	else
	{
		if (isset($_SESSION['LoginUser'])) 
		{
			setcookie("LoginUser",$_SESSION['LoginUser'],0,"/");
		} else {
			header ("Location: ../login/login.php?Message=LoggedOut");
			exit();
		}
	}
}
if (isset($_SESSION["LoginTime"]))
{
	if (($_SESSION['LoginTime'] + 86400 ) < time())
	{
		// last login over a day old
		header ("Location: ../login/login.php?Message=LoggedOut");
		exit();
	}
}
?>
