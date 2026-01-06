<?php
$UserType="PR";
require_once 'DB.php';
require 'db_access.php';
if (isset($_POST['ssn_id'])) 
{
	$ssn_id = $_POST["ssn_id"];
}
if (isset($_GET['ssn_id'])) 
{
	$ssn_id = $_GET["ssn_id"];
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$Query = "select iss.ssn_id, sn.ssn_id ";
$Query .= " from  issn iss join ssn sn on iss.original_ssn = sn.ssn_id";
$Query .= " where iss.ssn_id = '".$ssn_id."'";

if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read SSN and ISSN!<BR>\n");
	exit();
}

$got_ssn = 0;

// Fetch the results from the database.
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_update = "UPDATE ISSN SET PREV_LOCN_ID=LOCN_ID,WH_ID='XX',LOCN_ID='00000000' WHERE  ssn_id = '".$ssn_id."'";
	$wk_ssnid = $Row[1];
	$got_ssn = 1;
	//release memory
	ibase_free_result($Result);
	if (!($Result = ibase_query($Link, $wk_update)))
	{
		echo("Unable to Update ISSN!<BR>\n");
		exit();
	}
	if ($wk_ssnid == $ssn_id)
	{
		$wk_update = "UPDATE SSN SET PREV_WH_ID=WH_ID,PREV_LOCN_ID=LOCN_ID,WH_ID='XX',LOCN_ID='00000000', PO_ORDER=NULL WHERE ssn_id = '".$wk_ssnid."'";
		if (!($Result = ibase_query($Link, $wk_update)))
		{
			echo("Unable to Update SSN!<BR>\n");
			exit();
		}
	}
}

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

if ($got_ssn == 1)
{
	header ("Location: getssn.php?message=Cancelled+" . urlencode($ssn_id));
}
else
{
	header ("Location: getssn.php?message=SSN+" . urlencode($ssn_id) . "+Not+Found");
}
?>
