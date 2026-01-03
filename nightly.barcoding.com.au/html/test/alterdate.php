<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Adjust Date with Date Picker or NTP</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=yes, target-densityDpi=device-dpi" />
        <style type="text/css">
            body {
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <h2>Date</h2>
        <form action="alterdate.php" method="post" name="alterdate">
            <input type="hidden" id="runntp" name="runntp" value="FALSE">
                Date: <input type="date" name="sday" id="sday">
                <br>
                <br>
                Time: <input type="time" name="stime" id="stime" step="1">
                <br>
                <br>
            <input type="submit" value="Adjust Date">
        </form>
        <form>
        <form action="alterdate.php" method="post" name="runntpdate">
            <input type="hidden" id="runntp" name="runntp" value="TRUE">
            <input type="submit" value="Run NTP Date">
        </form>
        <form action="start-page.php" method="post" name="back">
            <input type="submit" value="Back">
        </form>
    </body>
</html>
<?php
// now do we have the request to change the device
if (isset($_POST['runntp'])) {       
	$doRunNtp = $_POST['runntp'];
	if ($doRunNtp == "TRUE") {
		// run ntpdate
		$LogFile = '/data/tmp/ntpdate.trg';
		file_put_contents($LogFile,  date("M, d-M-Y H:i:s.u") . "\n", LOCK_EX );
	}
	if ($doRunNtp == "FALSE") {
		// run date set
		$wk_sday = $_POST['sday'];
		$wk_stime = $_POST['stime'];
		$LogFile = '/data/tmp/setdate.trg';
		file_put_contents($LogFile,  $wk_sday . " " . $wk_stime . "\n", LOCK_EX );
	}
}
?>
