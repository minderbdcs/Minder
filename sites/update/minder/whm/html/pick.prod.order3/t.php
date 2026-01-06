<?php

$tran_type = isset($_POST['tran_type']) ? $_POST['tran_type'] : '';
$tran_class = isset($_POST['tran_class']) ? $_POST['tran_class'] : '';
$my_object = isset($_POST['my_object']) ? $_POST['my_object'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';
$my_sublocn = isset($_POST['my_sublocn']) ? $_POST['my_sublocn'] : '';
$my_ref = isset($_POST['my_ref']) ? $_POST['my_ref'] : '';
$tran_qty = isset($_POST['tran_qty']) ? $_POST['tran_qty'] : '';
$my_source = isset($_POST['my_source']) ? $_POST['my_source'] : '';
$tran_user = isset($_POST['tran_user']) ? $_POST['tran_user'] : '';
$tran_device = isset($_POST['tran_device']) ? $_POST['tran_device'] : '';

?>
<html>
    <head>
        <title></title>
    </head>
    <body>
        <form action="t.php" method="post">
            <table>
                <tr><th>Type:</th><td><input type="text" name="tran_type" value="<?php echo $tran_type; ?>" autocomplete="off" /></td></tr>
                <tr><th>Class:</th><td><input type="text" name="tran_class" value="<?php echo $tran_class; ?>" autocomplete="off" /></td></tr>
                <tr><th>Object:</th><td><input type="text" name="my_object" value="<?php echo $my_object; ?>" autocomplete="off" /></td></tr>
                <tr><th>Location:</th><td><input type="text" name="location" value="<?php echo $location; ?>" autocomplete="off" /></td></tr>
                <tr><th>Sub Location:</th><td><input type="text" name="my_sublocn" value="<?php echo $my_sublocn; ?>" autocomplete="off" /></td></tr>
                <tr><th>Reference</th><td><input type="text" name="my_ref" value="<?php echo $my_ref; ?>" autocomplete="off" /></td></tr>
                <tr><th>Qty</th><td><input type="text" name="tran_qty" value="<?php echo $tran_qty; ?>" autocomplete="off" /></td></tr>
                <tr><th>Source</th><td><input type="text" name="my_source" value="<?php echo $my_source; ?>" autocomplete="off" /></td></tr>
                <tr><th>User ID</th><td><input type="text" name="tran_user" value="<?php echo $tran_user; ?>" autocomplete="off" /></td></tr>
                <tr><th>Device ID</th><td><input type="text" name="tran_device" value="<?php echo $tran_device; ?>" autocomplete="off" /></td></tr>
            <table>
            <input type="submit" value="Submit" />
        </form>
        <pre>
<?php
    if (!empty($_POST)) {
	$Query = "EXECUTE PROCEDURE ADD_TRAN('";
	$Query .= substr($location,0,2)."','";
	$Query .= substr($location,2,strlen($location) - 2)."','";
	$Query .= $my_object."','";
	$Query .= $tran_type."','";
	$Query .= $tran_class."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	$Query .= $my_ref."','";
	$Query .= $tran_qty."','F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";
        echo $Query . "\n";

	$Query = "SELECT RESPONSE_TEXT FROM ADD_TRAN_RESPONSE('";
	$Query .= substr($location,0,2)."','";
	$Query .= substr($location,2,strlen($location) - 2)."','";
	$Query .= $my_object."','";
	$Query .= $tran_type."','";
	$Query .= $tran_class."','";
	$tran_trandate = date("Y-M-d H:i:s");
	$Query .= $tran_trandate."','";
	$Query .= $my_ref."','";
	$Query .= $tran_qty."','F','','MASTER',0,'";
	$Query .= $my_sublocn."','";
	$Query .= $my_source."','";
	$Query .= $tran_user."','";
	$Query .= $tran_device."')";
        echo $Query . "\n";
    }
?>
        </pre>
    </body>
</html>
