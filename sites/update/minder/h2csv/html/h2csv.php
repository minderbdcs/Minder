<html>
 <head>
<?php
$init = parse_ini_file('/etc/h2csv/init.ini', True);
//var_dump($init);
$layout_fields = split(",", $init['layout']['fields']);
$screen_fields = array();
foreach($layout_fields as $lkey => $lvalue)
{
	$screen_fields[$init[$lvalue]['screenorder']] = $lvalue;
}
ksort($screen_fields);
?>
  <title><?php echo $init['title'];?></title>
<?php
{
	echo('<link rel=stylesheet type="text/css" href="h2csv.css">');
}
?>
 </head>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
</script>
<?php
$aClass = "";
$message = "";
$scnMessage = "";
$comments = "";
// init received values
foreach ($screen_fields as $skey => $svalue)
{
	$wk_field_id = $svalue;
	eval("\$" . $wk_field_id . "=\"\";");
}
$cnt = 0;
// input fields from post
foreach ($screen_fields as $skey => $svalue)
{
	$wk_field_id = $svalue;
	if (isset( $_POST[$wk_field_id])) {
		$wk_cmd = "\$" . $wk_field_id . "=\"" .  $_POST[$wk_field_id] . "\";";
		//echo $wk_cmd;
		eval($wk_cmd);
	}
}
if (isset( $_POST['cnt'])) {
	$cnt = $_POST['cnt'];
}
$comments = date("D:M:Y H:i:s.u A", time());

//validate

if (isset( $_POST['Submit'])) {
	// check all reqd fields are entered
	$wk_fields_not_ok = False;
	foreach ($screen_fields as $skey => $svalue)
	{
		$wk_field_id = $svalue;
		$wk_field_reqd = $init[$wk_field_id]['reqd'];
		if ($wk_field_reqd == "T") {
			$wk_cmd = "\$wk_fields_not_ok = \$wk_fields_not_ok or  empty(\$" . $wk_field_id . ");";
			//echo $wk_cmd;
			eval($wk_cmd);
		}
	}
	if ($wk_fields_not_ok) { 
		$scnMessage = 'Fill in areas in red!';
		$aClass = 'error';
	} else {

		// what about symbology prefix in  field
		include "checkdata.php";
		$field_type = "none";
		foreach ($layout_fields as $lkey => $lvalue)
		{
			$wk_field_id = $lvalue;
			$wk_field_reqd = $init[$wk_field_id]['reqd'];
			$wk_field_param = $init[$wk_field_id]['param'];
			if (!empty($wk_field_param) ) {
				$wk_real_param = $init[$wk_field_param];
				if ($field_type == "none")
				{
					//$field_type = checkForTypein($fn, $wk_field_param, '', $wk_real_param ); 
					$wk_cmd = "\$field_type = checkForTypein(\$" . $wk_field_id . ", \$wk_field_param, '', \$wk_real_param );";
					//echo $wk_cmd;
					eval($wk_cmd);
					if ($field_type !== "none")
					{
						if ($startposn > 0)
						{
							//$wk_realdata = substr($fn,$startposn);
							$wk_cmd = "\$wk_realdata = substr(\$" . $wk_field_id . ",\$startposn);";
							//echo $wk_cmd;
							eval($wk_cmd);
							//$fn = $wk_realdata;
							$wk_cmd = "\$" . $wk_field_id . "=\$wk_realdata;";
							//echo $wk_cmd;
							eval($wk_cmd);
						}
					}
				}
				if ($field_type == "none")
				{
					$scnMessage = "Not Valid data for type " . $wk_field_param . "!";
					$aClass = 'error';
				}
			}
		}
		if ($aClass != "error") {
			//this is where the creating of the csv takes place
			//$cvsData = $fn . "," . $ln . "," . $comments ."\n";
			$cvsData = "";
			foreach($layout_fields as $lkey => $lvalue)
			{
				$wk_cmd = "\$cvsData .= \$" . $lvalue . ". \",\";";
				//echo $wk_cmd;
				eval($wk_cmd);
			}
			$cvsData .= $comments . "\n";

			$fp = fopen($init['outfile'] ,"a"); // $fp is now the file pointer to csv file 

			if($fp){
				fwrite($fp,$cvsData); // Write information to the file
				fclose($fp); // Close the file
				$cnt = $cnt + 1;
			}
			$aClass = 'standard';
		}
	}
} else {
	$scnMessage = 'Fill in ';
	$aClass = 'standard';

}

/*
=================================================================================================================
if (isset( $_POST['Submit'])) {
	if(empty($fn)  ){//show the form
		$scnMessage = 'Fill in areas in red!';
		$aClass = 'error';
	} else {

		// what about symbology prefix in fn field
		include "checkdata.php";
		$field_type = "none";
		if ($field_type == "none")
		{
			$field_type = checkForTypein($fn, 'LOCATION' ); 
			if ($field_type !== "none")
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($fn,$startposn);
					$fn = $wk_realdata;
				}
			}
		}
		if ($field_type == "none")
		{
			$field_type = checkForTypein($fn, 'BARCODE','SSN' ); 
			if ($field_type !== "none")
			{
				if ($startposn > 0)
				{
					$wk_realdata = substr($fn,$startposn);
					$fn = $wk_realdata;
				}
			}
		}

		//this is where the creating of the csv takes place
		$cvsData = $fn . "," . $ln . "," . $comments ."\n";


		//$fp = fopen("/tmp/hhform.csv","a"); // $fp is now the file pointer to file $filename
		$fp = fopen($init['outfile'] ,"a"); // $fp is now the file pointer to file $filename


		if($fp){
			fwrite($fp,$cvsData); // Write information to the file
			fclose($fp); // Close the file
			$cnt = $cnt + 1;
		}
		$aClass = 'standard';
	}
} else {
	$scnMessage = 'Fill in ';
	$aClass = 'standard';

}
==================================================================================================
*/

if ($cnt == 0) {
	/* calc length of csv */
	//if ($fh = fopen('/tmp/hhform.csv' ,'r')) {
	if (file_exists($init['outfile'])) {
		if ($fh = fopen($init['outfile'] ,'r')) {
  			while (! feof($fh)) {
    				if (fgets($fh,1048576)) {
	      				$cnt++;
    				}
  			}
			fclose($fh); // Close the file
		}
	}
}

?>
<body>
<h3><?php echo $init['title'];?></h3>
<form id="form1" name="form1" method="post" action="h2csv.php">
<table class="formatTblClass">
<tr>
<th colspan="6"><?php echo $scnMessage;?></th>
</tr>
<tr>
<td width="62"><span>Lines</span></td>
<td width="62"><span><?php echo $cnt;?></span></td>
</tr>
<!--
<tr>
<td width="62"><span>Code</span></td>
<td colspan="3"><input class="<?php echo "standard";?>" name="ln" type="text" id="ln" size="4" /></td>
</tr>
<tr>
<td width="68"><span>Barcode</span></td>
<td width="215"><input class="<?php echo $aClass;?>" type="text" name="fn" id="fn" /></td>
</tr>
 -->
<?php 
foreach ($screen_fields as $skey => $svalue)
{
	$wk_field_id = $svalue;
	$wk_field_title = $init[$wk_field_id]['title'];
	$wk_field_reqd = $init[$wk_field_id]['reqd'];
	$wk_field_param = $init[$wk_field_id]['param'];
	$wk_field_titlewidth = $init[$wk_field_id]['titlewidth'];
	$wk_field_width = $init[$wk_field_id]['width'];
	$wk_field_size = $init[$wk_field_id]['size'];
	echo "<tr>";
	// title line
	if ($wk_field_titlewidth != "") {
		echo '<td width="'. $wk_field_titlewidth . '"><span>' . $wk_field_title . "</span></td>";
	} else {
		echo '<td><span>' . $wk_field_title . "</span></td>";
	}
	// input  line
	if ($wk_field_width != "") {
		echo '<td width="'. $wk_field_width . '">' ;
	} else {
		echo "<td>";
	}
	if ($wk_field_reqd == "T") {
		echo '<input class="' . $aClass . '" type="text" name="' .  $wk_field_id . '" id="' . $wk_field_id . '" ';
	} else {
		echo '<input class="standard" type="text" name="' . $wk_field_id . '" id="' . $wk_field_id . '" ';
	}
	if ($wk_field_size != "") {
		echo ' size="' . $wk_field_size . '" ';
	}
	echo ' />';
	echo "</td>";
	echo "</tr>";
}
?>
<input type="hidden" name="cnt" id="cnt" value="<?php echo $cnt;?>" />
<tr>
<td colspan="3">
<div align="center">
<input type="submit" name="Submit" id="Submit" value="Submit" class="button" />
<input type="reset" name="Reset" id="button" value="Reset" class="button" />
</form>
</td>
<td>
<form id="form2" name="form2" method="post" action="h2page.php">
<input type="submit" name="View" id="View" value="View" class="button" />
</td>
</tr>
</table>
</div>

<script type="text/javascript">
/*	document.form1.fn.focus(); */
<?php
// want the first reqd field or the 1st field otherwise
$wk_1st_field = "";
$wk_1st_reqd_field = "";
foreach ($screen_fields as $skey => $svalue)
{
	$wk_field_id = $svalue;
	$wk_field_title = $init[$wk_field_id]['title'];
	$wk_field_reqd = $init[$wk_field_id]['reqd'];
	$wk_field_param = $init[$wk_field_id]['param'];
	$wk_field_titlewidth = $init[$wk_field_id]['titlewidth'];
	$wk_field_width = $init[$wk_field_id]['width'];
	$wk_field_size = $init[$wk_field_id]['size'];
	if ($wk_field_reqd == "T" and $wk_1st_reqd_field == "") {
		$wk_1st_reqd_field = $wk_field_id;
		$wk_1st_field = $wk_field_id;
	}
	if ( $wk_1st_field == "") {
		$wk_1st_field = $wk_field_id;
	}
}
if ($wk_1st_field  != "") {
	echo "document.form1." . $wk_1st_field . ".focus();";
}
?>
</script>
</body>
</html>

