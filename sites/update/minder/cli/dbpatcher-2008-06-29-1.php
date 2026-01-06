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

$sql = "UPDATE PICK_ITEM_DETAIL SET PICK_DETAIL_STATUS ='Dl' WHERE PICK_DETAIL_STATUS = 'D|'";
$res = ibase_query($db, $sql);
if ($res == false) {
    errfetch();
}
ibase_close($db);

function errfetch()
{
    echo ibase_errcode() . ' : ' . ibase_errmsg() . PHP_EOL;
}

