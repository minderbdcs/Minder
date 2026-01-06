<?php session_start(); ?>
<html>
<head>
<title>
<?php
echo ("$PageTitle");
?>
</title>
<?php
include "viewport.php";
include "db_access.php";
/*
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	//echo('<link rel=stylesheet type="text/css" href="core-style.css">');
*/
/*
	if ((strpos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 4.01") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 6.0") === false) and
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "WebKit") === false))
	{
		echo('<link rel=stylesheet type="text/css" href="core-style.css">');
	} else {
		echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
	}
*/
/*
	if ((strpos($_SERVER['HTTP_USER_AGENT'] , "MSIEMobile 6.0") !== false) or 
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "MSIE 4.01") !== false) or
	    (strpos($_SERVER['HTTP_USER_AGENT'] , "Gecko") !== false))
	{
		echo('<link rel=stylesheet type="text/css" href="core-style.css">');
	} else {
		echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
	}
}
else
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
*/
if ($wkMyBW == "IE60")
{
	echo('<link rel=stylesheet type="text/css" href="core-style.css">');
} elseif ($wkMyBW == "IE65")
{
	echo('<link rel=stylesheet type="text/css" href="core-ie7.css">');
} elseif ($wkMyBW == "CHROME")
{
	echo('<link rel=stylesheet type="text/css" href="core-chrome.css">');
} elseif ($wkMyBW == "SAFARI")
{
	echo('<link rel=stylesheet type="text/css" href="core-chrome.css">');
} elseif ($wkMyBW == "NETFRONT")
{
	echo('<link rel=stylesheet type="text/css" href="core-netfront.css">');
}
echo('<link rel=stylesheet type="text/css" href="nopad.css">');
//include "2buttons.css";
include "2buttons.php";
?>
</head>
<body>
<?php
//<table align="left" border=0>
//<tr><td align="left"><b>Welcome</b></td></tr>
//<tr><td align="left"><p>
?>
