<?php

//define('BASE_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));
//define('OTC_LOG_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'otc');
define('TMP_DIR', sys_get_temp_dir());
define('OTC_LOG_DIR', TMP_DIR  );

$cookieName = ini_get('session.name');

if (!isset($_COOKIE[$cookieName]))
    return;

$sessionId = strval($_COOKIE[$cookieName]);

if (!isset($_POST['message']) || empty($_POST['message']))
    return;

$message = strval($_POST['message']);
$clientTimestamp = strval($_POST['timestamp']);

if (!file_exists(OTC_LOG_DIR))
    mkdir(OTC_LOG_DIR);

if (!is_dir(OTC_LOG_DIR))
    die("{'error' : 'Log directory not found.'}");

$filename = OTC_LOG_DIR . DIRECTORY_SEPARATOR . $sessionId . '.log';

if (false === ($fp = fopen($filename, 'a')))
    die("{'error' : 'Cannot open log file for writing.'}");

if (false === fwrite($fp, date('Y-m-d H:i:s.u') . ': CLIENT INFO: ' . $clientTimestamp . ': ' . $message . PHP_EOL))
    echo "{'error' : 'Error writing to log file.'}";
else
    echo "{'result' : 'ok'}";

fclose($fp);
