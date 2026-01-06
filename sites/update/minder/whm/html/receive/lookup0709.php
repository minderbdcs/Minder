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


if (false === ($Link = ibase_pconnect($DBName2, $User, $Password)))
{
        echo("Unable to Connect!<BR>\n");
        exit();
}

if (isset($_GET['r']) && isset($_GET['q'])) {
    switch ($_GET['r']) {
      case 'product':
        /*
        $sql = 'SELECT PROD_ID, SHORT_DESC FROM PROD_PROFILE WHERE ';
        $sql = $sql . 'LOWER(PROD_ID) LIKE LOWER(\'%\'+?+\'%\') OR ';
        $sql = $sql . 'LOWER(SHORT_DESC) LIKE LOWER(\'%\'+?+\'%\') OR ';
        $sql = $sql . 'LOWER(LONG_DESC) LIKE LOWER(\'%\'+?+\'%\')';
        */
        $sql = 'SELECT PROD_ID, SHORT_DESC FROM PROD_PROFILE WHERE ';
        $sql = $sql . 'LOWER(PROD_ID) LIKE LOWER(\'%'.$_GET['q'].'%\') OR ';
        $sql = $sql . 'LOWER(ALTERNATE_ID) LIKE LOWER(\'%'.$_GET['q'].'%\') OR ';
        $sql = $sql . 'LOWER(SHORT_DESC) LIKE LOWER(\'%'.$_GET['q'].'%\') OR ';
        $sql = $sql . 'LOWER(LONG_DESC) LIKE LOWER(\'%'.$_GET['q'].'%\')';
        $result = ibase_query($Link, $sql);
        //$query = ibase_prepare($Link, $sql);
        //$result = ibase_execute($query, $_GET['q'], , $_GET['q']);
        break;
      case 'variety':
        /*
        $sql = 'SELECT CODE, DESCRIPTION
                FROM   GENERAL
                WHERE  LOWER(DESCRIPTION) LOWER(\'%\'+?+\'%\') OR
                       LOWER(CODE) LIKE LOWER(\'%\'+?+\'%\')';
        */
        $sql = 'SELECT CODE, DESCRIPTION
                FROM   GENERIC
                WHERE  LOWER(DESCRIPTION) LIKE LOWER(\'%'.$_GET['q'].'%\') OR
                       LOWER(CODE) LIKE LOWER(\'%'.$_GET['q'].'%\')';
        $result = ibase_query($Link, $sql);
        //$query = ibase_prepare($Link, $sql);
        //$result = ibase_execute($query, $_GET['q'], $_GET['q']);
        break;
      case 'brand':
        /*
        $sql = 'SELECT CODE, DESCRIPTION
                FROM   BRAND
                WHERE  LOWER(DESCRIPTION) LIKE LOWER(\'%\'+?+\'%\') OR
                       LOWER(CODE) LIKE LOWER(\'%\'+?+\'%\')';
        */
        $sql = 'SELECT CODE, DESCRIPTION
                FROM   BRAND
                WHERE  LOWER(DESCRIPTION) LIKE LOWER(\'%'.$_GET['q'].'%\') OR
                       LOWER(CODE) LIKE LOWER(\'%'.$_GET['q'].'%\')';
        $result = ibase_query($Link, $sql);
        //$query = ibase_prepare($Link, $sql);
        //$result = ibase_execute($query, $_GET['q'], $_GET['q']);
        break;
    }
    if (false === $result) {
        exit('To run query');
    }
    $r = array();
    while (($row = ibase_fetch_row($result))) {
        $r[] = array ('id' => $row[0], 'desc' => $row[1]);
    }
    ibase_free_result($result);
    //ibase_free_query($query);
    echo json_encode($r);
} else {
    echo json_encode(false);
}

