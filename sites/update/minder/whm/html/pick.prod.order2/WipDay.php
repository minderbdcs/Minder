<html>
 <head>
  <title>Days Picks</title>
<?php
include "viewport.php";
?>
 </head>
<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
 <body BGCOLOR="#FFFFFF">

<?php
require_once "DB.php";
require "db_access.php";
if (isset($_POST['workday']))
{
	$workday = $_POST['workday'];
}
if (isset($_GET['workday']))
{
	$workday = $_GET['workday'];
}
if (isset($workday))
{
	list($work_p1,$work_p2) = explode(" ",$workday);
	list($work_dd, $work_mm, $work_yy) = explode("-", $work_p1);
}
else
{
	$work_p1 = strftime("%d-%m-%Y");
	list($work_dd, $work_mm, $work_yy) = explode("-", $work_p1);
}

//=============================================================
//$wk_query1 = "select person_id,mer_hour(trn_date),count(distinct object) from transactions_archive where trn_type='PKOL' and trn_code = 'P' group by person_id,mer_hour(trn_date) ";  
$wk_query1 = "select person_id,mer_hour(trn_date),count(*) from transactions_archive where trn_type='PKOL' and trn_code='P' and trn_date > 'TODAY' group by person_id,mer_hour(trn_date) ";  
$wk_query2 = "select person_id,mer_hour(trn_date),count(distinct object) from transactions_archive where trn_type='PKIL' and trn_code='M' and trn_date > 'TODAY' group by person_id,mer_hour(trn_date) ";  
$wk_query1 = "select person_id,mer_hour(trn_date),count(*) from transactions_archive where trn_type='PKOL' and trn_code='P' and trn_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and trn_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) group by person_id,mer_hour(trn_date) ";  
$wk_query2 = "select person_id,mer_hour(trn_date),count(distinct object) from transactions_archive where trn_type='PKIL' and trn_code='M' and trn_date >= cast('$work_mm/$work_dd/$work_yy' as timestamp) and trn_date <= cast('$work_mm/$work_dd/$work_yy 23:59:59' as timestamp) group by person_id,mer_hour(trn_date) ";  
//echo $wk_query1;
{
	echo("<html>\n");
	echo("<head>\n");
	echo("<title>Picked Products</title>\n");
	echo("<script language=\"JavaScript\" src=\"/includes/ts_picker.js\">\n");
	echo("</script>\n");
	echo("</head>\n");
	echo("<body>\n");
	echo("<h2>Picked Products</h2>\n");
	include "2buttons.php";
echo("<FORM action=\"WipDay.php\" method=\"post\" name=getdetails>\n");
echo("<input type=\"hidden\" name=\"workdaychange\" value=\"0\">");
echo ("<table border=\"1\">\n");
echo ("<tr><th>Request Details</th></tr>\n");
echo("<tr><td>");
echo("Day");
echo(":</td><td><input type=\"text\" name=\"workday\" size=\"20\" maxlength=\"20\" value=\"");
if (isset($workday))
{
	echo("$workday");
}
echo("\" onchange=\"document.forms.getdetails.submit()\">");
echo("<a href=\"Javascript:show_calendar('document.getdetails.workday', document.getdetails.workday.value);\"><img src=\"cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Click here to POPup the date\"></a>");
echo ("</td></tr>");
echo ("</table>\n");
}
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:

{
	if (!($Result = ibase_query($Link, $wk_query1)))
	{
		echo("Unable to query tables!<BR>\n");
		exit();
	}

	$wk_results = Array();
	$wk_min_hour = 25;
	$wk_max_hour = -1;
	while ( ($Row = ibase_fetch_row($Result)))
	{
		$wk_person = $Row[0];
		$wk_hour = $Row[1];
		$wk_prods = $Row[2];
		$wk_min_hour = min($wk_min_hour, $wk_hour);
		$wk_max_hour = max($wk_max_hour, $wk_hour);
		if (array_key_exists($wk_person, $wk_results))
		{
			$person = $wk_results[$wk_person];
			$person[$wk_hour] =  $wk_prods;
			$wk_results[$wk_person] = $person;
		}
		else
		{
			// person dosnt exist
			$person = array();
			$person[$wk_hour] = $wk_prods;
			$wk_results[$wk_person] =  $person;
		}
/*
		if hour not in the arrar
		then add array for hour
			if person not in array
			then add person array for hours
			else update person for hour
		else update array for that hour
			if person not in array
			then add person array for hours
			else update person for hour
*/

	}
	//release memory
	//$Result->free();
	ibase_free_result($Result);
	//print_r($wk_results);
	if (!($Result = ibase_query($Link, $wk_query2)))
	{
		echo("Unable to query tables!<BR>\n");
		exit();
	}

	$wk_results2 = Array();
	while ( ($Row = ibase_fetch_row($Result)))
	{
		$wk_person = $Row[0];
		$wk_hour = $Row[1];
		$wk_prods = $Row[2];
		$wk_min_hour = min($wk_min_hour, $wk_hour);
		$wk_max_hour = max($wk_max_hour, $wk_hour);
		if (array_key_exists($wk_person, $wk_results2))
		{
			$person = $wk_results2[$wk_person];
			$person[$wk_hour] =  $wk_prods;
			$wk_results2[$wk_person] = $person;
		}
		else
		{
			// person dosnt exist
			$person = array();
			$person[$wk_hour] = $wk_prods;
			$wk_results2[$wk_person] =  $person;
		}
/*
		if hour not in the arrar
		then add array for hour
			if person not in array
			then add person array for hours
			else update person for hour
		else update array for that hour
			if person not in array
			then add person array for hours
			else update person for hour
*/

	}
	//print_r($wk_results);
	{
		echo '<table align="center" cellpadding="2" cellspacing="2" border="1">';
	
		echo '<tr>';
		$wk_hours = array();
		echo("<th></th>\n"); /* the person */
		//echo "MIN $wk_min_hour MAX $wk_max_hour\n";
		for ($wk_hour = $wk_min_hour; $wk_hour <= $wk_max_hour; $wk_hour++)
		{
			echo("<th>$wk_hour</th>\n");
			$wk_hours[] = $wk_hour;
		}
		echo '</tr>';
	}
	//echo "1111111111111111\n";
	//print_r($wk_hours);
	foreach ($wk_results as $Key_results => $Value_results) 
	{
		$wk_person = $Key_results;
		//echo "2222222222222222\n";
		//print_r($Value_results);
		{
	 		echo '<tr>';
			echo "<td>$wk_person</td>";
			foreach ($wk_hours as $Key_hours => $Value_hours)
			{
				//echo $Value_hours;
				echo "<td>{$Value_results[$Value_hours]}</td>";
			}
			echo '</tr>';
		}
	}
	echo '<tr></tr>';
	foreach ($wk_results2 as $Key_results => $Value_results) 
	{
		$wk_person = $Key_results;
		//echo "2222222222222222\n";
		//print_r($Value_results);
		{
	 		echo '<tr>';
			echo "<td>$wk_person</td>";
			foreach ($wk_hours as $Key_hours => $Value_hours)
			{
				//echo $Value_hours;
				echo "<td>{$Value_results[$Value_hours]}</td>";
			}
			echo '</tr>';
		}
	}
	{
		echo '</table><BR /><HR />';
	}
	
	//release memory
	//$Result->free();
	ibase_free_result($Result);
	
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
}

//close
//$Link->disconnect();
ibase_close($Link);

//close
//$Link->disconnect();


{
	//<input type="submit" name="submit" value="Submit">
	echo '<form action="pick_Menu.php" method="post" name="Export"> ';
	
	echo '<table align="left"  border="0">';
	whm2buttons('Accept', 'pick_Menu.php',"Y","Back_50x100.gif","Back","accept.gif");
	echo '</body>';
	echo '</html>';
}
?>
