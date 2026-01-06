<?php

if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';

header('text/javascript');


if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
        echo("Unable to Connect!<BR>\n");
        exit();
}

$sql = 'SELECT PROD_ID, SHORT_DESC FROM PROD_PROFILE WHERE ';
$sql = $sql . 'LOWER(PROD_ID) LIKE \'%' . $_GET['q'] . '%\' OR ';
$sql = $sql . 'LOWER(SHORT_DESC) LIKE \'%' . $_GET['q'] . '%\' OR ';
$sql = $sql . 'LOWER(LONG_DESC) LIKE \'%' . $_GET['q'] . '%\'';
$sql = $sql . ' ORDER BY PROD_ID';

if (!($result = ibase_query($Link, $sql))) {
    exit('To run query');
}
$r = array();
while (($row = ibase_fetch_row($result))) {
    $r[] = array ('id' => $row[0], 'desc' => $row[1]);
}
ibase_free_result($result);

echo json_encode($r);
