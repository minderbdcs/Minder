<?php
error_reporting(E_ALL);

$dbLiveDsn = 'localhost:minder';
$dbUser = 'sysdba';
$dbPass = 'masterkey';

$db = ibase_connect($dbLiveDsn, $dbUser, $dbPass);
if ($db == false) {
    echo 'Can\'t connect to db via DSN = ' . $dbLiveDsn . PHP_EOL;
    die();
}

$sql = "SELECT PURCHASE_ORDER FROM PURCHASE_ORDER WHERE PURCHASE_ORDER = 'PO00684'" ;
$res = ibase_query($db, $sql);
if ($res !== false ) {
    while ($row = ibase_fetch_assoc($res)) {
        var_dump($row);
    }
} else {
    echo 'Can\'t query DB' . PHP_EOL;
}
die();

function errfetch()
{
    echo ibase_errcode() . ' : ' . ibase_errmsg() . PHP_EOL;
}
