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
if (isset($_POST['systemundo']))
{
	$systemundo = $_POST['systemundo'];
}
if (isset($_GET['systemundo']))
{
	$systemundo = $_GET['systemundo'];
}
$wk_format = "html";
if (isset($ExportAs))
{
	if ($ExportAs == "RUN")
	{
		//header("Content-type: text/csv");
		$wk_format = "html2";
	}
}
if ($wk_format == "html")
{
	echo('
 <head>');
include "viewport.php";

	echo('
  <title>Slotting</title>
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
	$Query = "select code, description from options where group_code = 'SLOTTING'  ";
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
  	/* document.details.systemundo.value = mytime;  */
	var FormName = "moredetails";
	var FieldName = "systemundo[]";
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
  		document.details.systemundo.value = "false";
	else
	{
  		document.details.systemundo.value = "false";
		for(var i = 0; i < countCheckBoxes; i++)
			if (objCheckBoxes[i].checked) 
  				document.details.systemundo.value = objCheckBoxes[i].value;
	}
  	document.details.outputtype.value = "RUN"; 
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
	//exec($wkCommand['BATCHTIME'],$wkResults, $retval2);
	echo("<br>");
	//</pre>
	//var_dump($wkResults);
	echo("<h4>Run Slotting</h4>\n");
	echo("<form action=\"slotting.php\" method=\"get\" name=\"moredetails\" onsubmit=\"return false;\">\n");
	//echo('<input type="text" name="message" value="" size=\"20\" >');
	echo("<table border=\"1\">\n");
	//foreach ($wkResults as $wk_time) 
	{
		echo('<tr>');
		echo('<td>');
		echo("<label for=\"systemundofalse\"> \n");
		echo('<input type="checkbox" name="systemundo[]" value="false" id="systemundofalse" ></td>');
		//echo('<td>' . $wk_time . '</td>');
		echo('<td>' . "Undo No"  );
		echo("</label>\n");
		echo('</td>');
		echo('<td>');
		echo("<label for=\"systemundotrue\"> \n");
		echo('<input type="checkbox" name="systemundo[]" value="true" id="systemundotrue" ></td>');
		echo('<td>' . "Undo Yes"  );
		echo("</label>\n");
		echo('</td>');
		echo('</tr>');
		echo('<tr>');
		echo('<td>');
		$wk_time = "Run";
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

	echo("<form action=\"slotting.php\" method=\"post\" name=details\n>");
	echo("<INPUT type=\"hidden\" name=\"systemundo\"> ");  
	echo("<INPUT type=\"hidden\" name=\"outputtype\"> ");  
	echo("</form>");

} else {
	$wkResults = array();
	$retval = 0;
	$retval2 = 0;

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
  	/* document.details.systemundo.value = mytime;  */
	var FormName = "moredetails";
	var FieldName = "systemundo[]";
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
  		document.details.systemundo.value = "false";
	else
	{
  		document.details.systemundo.value = "false";
		for(var i = 0; i < countCheckBoxes; i++)
			if (objCheckBoxes[i].checked) 
  				document.details.systemundo.value = objCheckBoxes[i].value;
	}
  	document.details.outputtype.value = "RUN"; 
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
	//exec($wkCommand['BATCHTIME'],$wkResults, $retval2);
	echo("<br>");
	//</pre>
	//var_dump($wkResults);
	echo("<h4>Run Slotting</h4>\n");
	echo("<form action=\"slotting.php\" method=\"get\" name=\"moredetails\" onsubmit=\"return false;\">\n");
	//echo('<input type="text" name="message" value="" size=\"20\" >');
	echo("<table border=\"1\">\n");
	//foreach ($wkResults as $wk_time) 
	{
		echo('<tr>');
		echo('<td>');
		echo("<label for=\"systemundofalse\"> \n");
		echo('<input type="checkbox" name="systemundo[]" value="false" id="systemundofalse" ></td>');
		//echo('<td>' . $wk_time . '</td>');
		echo('<td>' . "Undo No"  );
		echo("</label>\n");
		echo('</td>');
		echo('<td>');
		echo("<label for=\"systemundotrue\"> \n");
		echo('<input type="checkbox" name="systemundo[]" value="true" id="systemundotrue" ></td>');
		echo('<td>' . "Undo Yes"  );
		echo("</label>\n");
		echo('</td>');
		echo('</tr>');
		echo('<tr>');
		echo('<td>');
		$wk_time = "Run";
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

	echo("<form action=\"slotting.php\" method=\"post\" name=details\n>");
	echo("<INPUT type=\"hidden\" name=\"systemundo\"> ");  
	echo("<INPUT type=\"hidden\" name=\"outputtype\"> ");  
	echo("</form>");
	echo("<textarea name=\"results\" title=\"Run Response\" rows=\"6\" cols=\"60\"> ");  
	//$last_line = system($wkCommand['BATCHCSV'],$retval);
	exec($wkCommand['SLOTCOMMAND'] . " " .  $systemundo ,$wkResults, $retval2);
	//echo ("Retval2:" . $retval2);
	foreach ($wkResults as $wk_time) {
		echo( $wk_time );
		echo("\r\n");
	}
	echo("</textarea> ");  
}
//release memory
ibase_free_result($Result);

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

?>

