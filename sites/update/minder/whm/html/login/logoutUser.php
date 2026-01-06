<html>
<head>
<title>Logout a User</title>
<?php
include "viewport.php";
include "db_access.php";
//<meta name="viewport" content="width=device-width">
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

//include "2buttons.css";
?>
</head>
<body>
<?php
require_once 'DB.php';
//require 'db_access.php';
include "2buttons.php";

//list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
// Set the variables for the database access:

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['devicefrom'])) 
{
	$devicefrom = $_POST["devicefrom"];
}
$wk_sysuser = "F";
$wk_done = "F";
// check user is capable
/*
{
	$Query = "SELECT sys_admin from sys_user";
	$Query .= " where user_id = '" . $tran_user . "'";
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	if (($Row = ibase_fetch_row($Result)) )
	{
		$wk_sysuser = $Row[0];
	}
	//release memory
	ibase_free_result($Result);
}
*/

echo (" <FORM action=\"logoutUser.php\" method=\"post\" name=logoutuser>");
if (isset($devicefrom))
{
		//if ($tran_user <> $devicefrom)
		{
			//ok do update
			/* here do the transaction */

			$Query = "UPDATE sys_user set device_id=NULL, login_date=NULL ";
			$Query .= " WHERE user_id = '" . $devicefrom . "' ";
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to update sys_user!<BR>\n");
				exit();
			}
			
			//release memory
			//ibase_free_result($Result);
		
			$Query = "UPDATE sys_equip set current_person=NULL, current_logged_on=NULL ";
			$Query .= " WHERE current_person = '" . $devicefrom . "' ";
			
			if (!($Result = ibase_query($Link, $Query)))
			{
				print("Unable to update sys_equip!<BR>\n");
				exit();
			}
			
		        {
		            $wk_buffer = "LOGIN:WHM Logout User: " . $devicefrom  ;
			    $wk_buffer .= "Remote Addr " . $_SERVER['REMOTE_ADDR'] . " ";
			    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			    {
				$wk_buffer  .= " forwarded for " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " ";
			    }
		            $query = "INSERT INTO
		                      LOG(DESCRIPTION )
		                      VALUES('" . $wk_buffer . "' || CAST(CAST('NOW' AS TIMESTAMP) AS CHAR(24)))"; 
		            $result = ibase_query($Link, $query);
		            if (false === $result) {
		                echo $query ;
		                echo "Unable to Update log!\n";
		            }
			    //$LogFile = "/tmp/login.log";
			    $LogFile = "/data/tmp/login.log";
			    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			    {
			    	file_put_contents($LogFile, "LOGOUT All-User:" . $devicefrom .  " http_x_forwarded_for: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . " remote_addr: " . $_SERVER['REMOTE_ADDR'] . " Device " . $DBDevice .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
			    } else {
			    	file_put_contents($LogFile, "LOGOUT All-User:" . $devicefrom .  " remote_addr: " . $_SERVER['REMOTE_ADDR'] . " Device " . $DBDevice .  date("M, d-M-Y H:i:s.u") . "\n", FILE_APPEND );
			    }
		        }
			$wk_done = "T";

		}
}
else
{
	echo("<H4>Select the User</H4>\n");
}
echo("<br><br>User:<SELECT name=\"devicefrom\" size=\"1\" class=\"sel2\"");
echo(" onchange=\"document.logoutuser.submit()\" ><br><br>\n");
{
	$Query = "SELECT current_person, count(*) from sys_equip";
	$Query .= " where device_type in ('HH','PC') and (current_person is not null) group by current_person";
	// Create a table.
	
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query table!<BR>\n");
		exit();
	}
	
	// echo headers
	// Fetch the results from the database.
	while (($Row = ibase_fetch_row($Result)) )
	{
		echo( "<OPTION value=\"" . $Row[0] . "\"");
		echo(">" . $Row[0] . " " . $Row[1] . "\n");
	}
	
	//release memory
	ibase_free_result($Result);
}
echo("</SELECT><br><br>");
	
{
	//commit
	ibase_commit($dbTran);
	
	//close
	ibase_close($Link);
	echo("<table border=\"0\" ALIGN=\"LEFT\">\n");
}
	$wk_menu_output = "IMAGE";
/*
if ($wk_sysuser != "T")
{
	whm2buttons('Accept', 'login.php?message=Not+Available+to+You',"Y","Back_50x100.gif","Back","accept.gif");
	echo("<SCRIPT>document.Back.submit();</SCRIPT>");
}
else
*/
{
	whm2buttons('Accept', 'login.php',"Y","Back_50x100.gif","Back","accept.gif");
}
if ($wk_done == "T")
{
	echo("<script type=\"text/javascript\">\n");
	echo("document.Back.submit();</script>");
}
?>
</body>
</html>

