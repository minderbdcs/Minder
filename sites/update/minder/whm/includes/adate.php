<?php
// Include DateMath class
require_once ('DateTime/DateMath.php');
// Include the TimeUnitValidator
require_once ('DateTime/TimeUnitValidator.php');

// Include Calendar and subclasses
require_once ('DateTime/Calendar.php');
require_once ('DateTime/Month.php');
require_once ('DateTime/Day.php');

// Set up initial variables
if ( !isset ( $_GET['savedata'] )) $_GET['savedata']="";
if ( !isset ( $_GET['from'] )) $_GET['from']=$_SERVER['PHP_SELF'];
if ( !isset ( $_GET['y'] ) ) $_GET['y']=date('Y');
if ( !isset ( $_GET['m'] ) ) $_GET['m']=date('m');
if ( !isset ( $_GET['d'] ) ) $_GET['d']=date('d');

// Instantiate the Month class
$month=new Month($_GET['y'],$_GET['m']);

// Get the details of the months as timestamps
$last=$month->lastMonth(true);
$next=$month->nextMonth(true);
$thisMonth=$month->thisMonth(true);

// Start building the calendar
$calendar ="<table class=\"cal\" width=\"200\">\n<tr>\n";
$calendar.="<td colspan=\"2\"> ".
           "<a class=\"cal_nav\" href=\"".$_SERVER['PHP_SELF'].
           "?y=".date('Y',$last).
           "&m=".date('m',$last).
	   "&d=1".
	   "&from=".  urlencode($_GET['from']).
	   "&savedata=".  urlencode($_GET['savedata'])."\">".
           date('F',$last)."</a></td>";
//$calendar.="<td class=\"cal_now\" colspan=\"3\" align=\"center\">".
$calendar.="<td class=\"cal_now\" colspan=\"3\" >".
           date('F',$thisMonth)." ".date('Y',$thisMonth)."</td>";
//$calendar.="<td colspan=\"2\" align=\"right\">".
$calendar.="<td colspan=\"2\" >".
           "<a class=\"cal_nav\" href=\"".$_SERVER['PHP_SELF'].
           "?y=".date('Y',$next).
           "&m=".date('m',$next).
	   "&d=1".
	   "&from=".  urlencode($_GET['from']).
	   "&savedata=".  urlencode($_GET['savedata'])."\">".
           date('F',$next)."</a>".
           " </td></tr>";

// Array for selected days
$sDays = array (
    new Day($_GET['y'],$_GET['m'],$_GET['d'])
    );

// Build the days of the month
$month->buildWeekDays($sDays);

// Define the days of the week for column headings
$daysOfWeek= array('Mon','Tue','Wed',
    'Thu','Fri','Sat','Sun');

// Build the column headings
$calendar.="<tr class=\"cal_top\">\n";
// Display the days of the week
foreach ( $daysOfWeek as $dayOfWeek ) {
    $calendar.="<th class=\"cal_week\">".$dayOfWeek."</th>";
}
$calendar.="\n</tr>\n";

$alt='';

// Loop through the day entries
while ( $day = $month->fetch() ) {

    // For display alternate rows
    $alt= $alt=="cal_row" ? "cal_row_alt" : "cal_row";

    // If its the start of a week, start a new row
    if ( $day->isFirst() ) {
        $calendar.="<tr class=\"".$alt."\">\n";
    }

    // Check to see if day is an "empty" day
    if ( ! $day->isEmpty() ) {

        // If it's the current day, highlight it
        if ( !$day->isSelected() )
            $calendar.="<td>";
        else
            $calendar.="<td class=\"cal_current\">";

        // Display the day inside a link
        $calendar.="<a class=\"cal_entry\" href=\"".
                   urldecode($_GET['from'])."?y1=".
                   $day->thisYear().
                   "&m=".$day->thisMonth().
                   "&d=".$day->thisDay().
	   	   "&savedata=".  urlencode($_GET['savedata'])."\">".
                   $day->thisDay()."</a></td>";

    // Display an empty cell for empty days
    } else {
        $calendar.="<td class=\"cal_entry\">&nbsp;</td>";
    }

    // If its the end of a week, close the row
    if ( $day->isLast() ) {
        $calendar.="\n</tr>\n";
    }
}
$calendar.="</table>\n";
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> A Calendar </title>
<style type="text/css">
.cal {
    background-color: gray;
    font-family: verdana;
    font-size: 12px;
}
.cal_nav {
    color: #000000;
}
.cal_now {
    font-size: 15px;
    font-weight: bold;
    color: #000000;
}
.cal_top {
    background-color: #000000;
}
.cal_week {
    color: #ffffff;
}
.cal_row {
    background-color: #f5f6f7;
}
.cal_row_alt {
    background-color: #f1f2f3;
}
.cal_entry {
    color: #000066;
}
.cal_current {
    background-color: yellow;
}
</style>
</head>

<body>

<?php echo ( $calendar ); ?>

</body>
</html>
