<?php
$today = getdate();
$day  = $today['mday'];
// use yesterday
$day -= 1;
$month = $today['mon'];
$mtharr = array("January","February","March","April","May","June","July","August","September","October","November","December");
$year = $today['year'];
$lastyear = $year - 1;
?>
<table cellpadding="3" cellspacing="2" border="0">
<tr>
<td valign="top">
<select name="sday">
<?php
for ($i=1;$i<=31;$i++) 
{
	echo "<option value=\"$i\"";
	if ($i == $day)
	{
		echo " selected";
	}
	echo ">$i</option>";
}
?>
</select></td>
<td valign="top">
<select name="smonth">
<?php
for ($i=1;$i<=12;$i++) 
{
	echo "<option value=\"$i\"";
	if ($i == $month)
	{
		echo " selected";
	}
	echo ">" . $mtharr[$i -1] . "</option>";
}
?>
</select></td>
<td valign="top">
<select name="syear">
<?php
	echo "<option value=\"$lastyear\"";
	echo ">$lastyear</option>";
	echo "<option value=\"$year\"";
	echo " selected";
	echo ">$year</option>";
?>
</select></td>
</tr>
</table>


