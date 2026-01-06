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

if (count($_SERVER['argv']) > 1) {
    switch ($_SERVER['argv'][1]) {
        case 'a':
            // technical mail list
            insert('1', 'ta.nu.da@gmail.com');
            insert('2', 'clarifying@gmail.com');
            insert('cking', 'cking.personal@gmail.com');
            insert('support', 'support@barcoding.com.au');

            // customer mail list
            insert('cking', 'cking.personal@gmail.com', 'EMAIL_L_Dl');
            insert('nigeld', 'nigel.davis@freshproduce.net.au', 'EMAIL_L_Dl');
            insert('renato', 'renato.villareal@freshproduce.net.au', 'EMAIL_L_Dl');

            //insert('5', 'nigeld@freshproduce.net.au', 'EMAIL_L_Dl');
            insert('6', 'ta.nu.da@gmail.com', 'EMAIL_L_Dl');
            insert('7', 'clarifying@gmail.com', 'EMAIL_L_Dl');
        break;
        case 'd':
            // clear all mailing list
            $sql = "DELETE FROM OPTIONS WHERE GROUP_CODE LIKE 'EMAIL%'";
            $res = ibase_query($db, $sql);
            if ($res == false) {
                errfetch();
            }
            ibase_close($db);

        break;
        default:
            echo 'unknown option ' . $_SERVER['argv'][0] . PHP_EOL;
        break;
    }
}
$sql = "select * from OPTIONS where GROUP_CODE LIKE 'EMAIL%'";
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

function insert($id, $mail, $code = 'EMAIL_TECH')
{
    $sql = "insert into OPTIONS (GROUP_CODE, CODE, DESCRIPTION) VALUES('{$code}', '{$id}', '{$mail}')";
    $res = ibase_query($sql);
    if ($res == false) {
        errfetch();
    }
}