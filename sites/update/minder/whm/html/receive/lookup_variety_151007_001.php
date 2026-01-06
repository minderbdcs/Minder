<?php
if (!isset($_COOKIE['LoginUser'])) {
    header('Location: /whm/');
    exit(0);
}

include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include 'transaction.php';
//include "logme.php";

header('text/javascript');

if (false === ($Link = ibase_pconnect($DBName2, $User, $Password)))
{
        echo("Unable to Connect!<BR>\n");
        exit();
}
$r = array();
//logme($Link, "", "XX", "lookup_variety q " . "[" . $_GET['q'] . "]");
//logme($Link, "", "XX", "lookup_variety r " . "[" . $_GET['r'] . "]");
if (isset($_GET['q']) && isset($_GET['r'])) {
    if ($_GET['r'] == 'variety') {
        /* $sql = 'SELECT CODE, DESCRIPTION
                   FROM   GENERIC
                   WHERE SSN_TYPE = ?';
	*/
        $sql = 'SELECT GENERIC.CODE, GENERIC.DESCRIPTION
                   FROM   PROD_PROFILE
                   JOIN   GENERIC ON GENERIC.SSN_TYPE = PROD_PROFILE.SSN_TYPE
                   WHERE PROD_PROFILE.PROD_ID = ?';
        $query  = ibase_prepare($Link, $sql);
        if (false !== $query)
        {
            //$result = ibase_execute($query, substr($_GET['q'],0,3));
            $result = ibase_execute($query, $_GET['q']);
            if (false !== $result) {
                $i = 0;
                $r[''] = '';
                while (false !== ($row = ibase_fetch_row($result))) {
                    $r[$row[0]] = $row[1];
                    $i++;
                }
                echo json_encode($r);
            } else {
                exit('Failed query');
            }
        } else {
            exit('Failed prepare query');
        }
    } elseif ($_GET['r'] == 'subtype') {
        $sql = 'SELECT SSN_SUB_TYPE.CODE, SSN_SUB_TYPE.DESCRIPTION
                   FROM   PROD_PROFILE
                   JOIN   GENERIC ON GENERIC.SSN_TYPE = PROD_PROFILE.SSN_TYPE
                   JOIN   SSN_SUB_TYPE ON SSN_SUB_TYPE.SSN_TYPE = PROD_PROFILE.SSN_TYPE AND SSN_SUB_TYPE.GENERIC = GENERIC.CODE
                   WHERE PROD_PROFILE.PROD_ID = ?
                   AND GENERIC.CODE = ?';
        $query  = ibase_prepare($Link, $sql);
        if (false !== $query)
        {
            if (isset( $_GET['q']))
            {
                $wk_q = $_GET['q'];
            }
            else
            {
                $wk_q = "";
            }
            if (isset( $_GET['s']))
            {
                $wk_s = $_GET['s'];
            }
            else
            {
                $wk_s = "";
            }
            //$result = ibase_execute($query, $_GET['q'], $_GET['s']);
            $result = ibase_execute($query, $wk_q, $wk_s);
            if (false !== $result) {
                $i = 0;
                $r[''] = '';
                while (false !== ($row = ibase_fetch_row($result))) {
                    $r[$row[0]] = $row[1];
                    $i++;
                }
                echo json_encode($r);
            } else {
                exit('Failed query');
            }
        } else {
            exit('Failed prepare query');
        }
    } else {
        exit('Incorrect params');
    }
} else {
    exit('No params');
}
