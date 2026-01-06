<?php
require_once 'DB.php';
require 'db_access.php';
if (isset($_POST['outputtype']))
{
	$ExportAs = $_POST['outputtype'];
}
if (isset($_GET['outputtype']))
{
	$ExportAs = $_GET['outputtype'];
}
if (isset($_POST['batchno']))
{
	$BatchNo = $_POST['batchno'];
}
if (isset($_GET['batchno']))
{
	$BatchNo = $_GET['batchno'];
}
$wk_format = "html";
if (isset($ExportAs))
{
	if ($ExportAs == "CSV")
	{
		header("Content-type: text/csv");
		$wk_format = "csv";
	}
}
if ($wk_format == "html")
{
	echo('
 <head>');
include "viewport.php";
	echo('
  <title>Ftp Batches</title>
<link rel=stylesheet type="text/css" href="delivery.css">
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
</style>
 </head>');
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

$wkCommand = array();
{
	$Query = "select code, description2 from options where group_code = 'FTPIN'  ";
	//echo $Query;
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read Options!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) 
	{
		$wk_code = $Row[0];
		$wk_cmd  = $Row[1];
		$wkCommand[$wk_code] = $wk_cmd;
	}
}
if ($wk_format == "html")
{
	echo('
<script type="text/javascript">
function SetAllCheckBoxes(FormName, FieldName, CheckValue)
{
	if(!document.forms[FormName])
		return;
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
		objCheckBoxes.checked = CheckValue;
	else
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			objCheckBoxes[i].checked = CheckValue;
}

function saveMe(mytime) {

/* # save my batch */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(mytime); */
  	document.details.batchno.value = mytime; 
  	document.details.outputtype.value = "CSV"; 
  	document.details.submit(); 
	 SetAllCheckBoxes("moredetails", "reserveme[]", false); 
  	/* document.back.submit(); */
  	/* document.details.message.focus();  */
	return false;
}
</script>');
	echo("<FONT size=\"2\">\n");
	//echo "<pre>";

	$wkResults = array();
	$retval = 0;
	$retval2 = 0;
	//echo $wkCommand['BATCHTIME'];
	//var_dump($wkCommand);
	//$last_line = system($wkCommand['BATCHTIME'],$retval);
	exec($wkCommand['BATCHTIME'],$wkResults, $retval2);
	//echo ("Retval:" . $retval);
	//echo ("Last Line:" . $last_line);
	//echo ("Retval2:" . $retval2);
	echo("<br>");
	//</pre>
	//var_dump($wkResults);
	echo("<h4>FTP Batches In</h4>\n");
	echo("<form action=\"ftpbatch.php\" method=\"get\" name=\"moredetails\" onsubmit=\"return false;\">\n");
	//echo('<input type="text" name="message" value="" size=\"20\" >');
	echo("<table border=\"1\">\n");
	foreach ($wkResults as $wk_time) {
		echo('<tr>');
		echo('<td>');
		echo("<label for=\"" . $wk_time . "\"> \n");
		//echo('<td><input type="checkbox" name="reserveme[]" value="' . $wk_time . '" onchange="saveMe(' . "'" . $wk_time . "'" . ');"></td>');
		echo('<input type="checkbox" name="reserveme[]" value="' . $wk_time . '" id="' . $wk_time . '" onchange="saveMe(' . "'" . $wk_time . "'" . ');"></td>');
		//echo('<td>' . $wk_time . '</td>');
		echo('<td>' . $wk_time  );
		echo("</label>\n");
		echo('</td>');
		echo('</tr>');
	}
	echo("</table>\n");
	echo("</form>");
	echo("<form action=\"./util_Menu.php\" method=\"post\" name=back>\n");
	echo("<input type=\"IMAGE\" ");  
	echo('src="/icons/whm/Back_50x100.gif" alt="Back">');
	echo("</form>");

	echo("<form action=\"ftpbatch.php\" method=\"post\" name=details\n>");
	echo("<INPUT type=\"hidden\" name=\"batchno\"> ");  
	echo("<INPUT type=\"hidden\" name=\"outputtype\"> ");  
	echo("</form>");

} else {
	$wkResults = array();
	$retval = 0;
	$retval2 = 0;
	//echo $wkCommand['BATCHCSV'];
	//var_dump($wkCommand);
	//$last_line = system($wkCommand['BATCHCSV'],$retval);
	exec($wkCommand['BATCHCSV'] . " " .  $BatchNo ,$wkResults, $retval2);
	//echo ("Retval2:" . $retval2);
	foreach ($wkResults as $wk_time) {
		echo( $wk_time );
		echo("\r\n");
	}
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>

