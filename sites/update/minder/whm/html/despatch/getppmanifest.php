<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
//include "2buttons.php";

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

/*
get command to run from the options table

then use system command to run it
eg /data/asset.rf/script/doppmailb7.sh prod     eparcel     True     23
                                       test or prod
                                                carrier
                                                            display pdf
                                                                     manifestid
*/

{
	$wk_command = "";
	$wk_system_type = "";
	$wk_carrier = "";
	// check that the order exists and the supplier list
	$Query4 = "SELECT options.description,options.code,control.system_type from options join control on control.record_id=1 where options.group_code='MANIFESTSH'  ";
	if (!($Result4 = ibase_query($Link, $Query4)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	if ( ($Row4 = ibase_fetch_row($Result4)) ) 
	{
		$wk_command = $Row4[0];
		$wk_carrier = $Row4[1];
		$wk_system_type = $Row4[2];
	}
	//release memory
	ibase_free_result($Result4);
}
//echo("command:".$wk_command." carrier:".$wk_carrier." system:".$wk_system_type.":");
$wk_retval = 0;
$wk_response = "";
$wk_retlines = array();
if ($wk_command <> "")
{
	// need the parameters
	//1 id system type
	//2 is carrier
	$wk_cmd =  $wk_command . " " . $wk_system_type . " " . $wk_carrier . " True 2>&1 ";
	//echo("cmd:".$wk_cmd);
	$wk_response = "";
	$wk_response  = exec   ($wk_cmd, $wk_retlines, $wk_retval);
}
if ($wk_response <> "")
{
  	//echo($wk_response);
	// the response is the file name that has the report in it
 	header ( "Content-type: application/pdf" );
	$wk_data = file_get_contents($wk_response);
	echo ($wk_data);
} else {
	echo "No response" . ":" . $wk_retval;
}

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>
