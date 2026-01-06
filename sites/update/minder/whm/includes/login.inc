<?php
if (!isset($_COOKIE["LoginUser"]))
{
	// login user has timed out go to login again
	// header ("Location: ../login/login.php?Message=LoggedOut");
	if (isset($demomode))
	{
		setcookie("LoginUser","BDCS|MA",time()+86400,"/");
		setcookie("SaveUser","BDCS|MA|TR",time()+1111000,"/");
	}
	else
	{
		header ("Location: ../login/logout.php?Message=LoggedOut");
		exit();
	}
}
?>
