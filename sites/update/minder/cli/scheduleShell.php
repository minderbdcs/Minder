<?php
// set no bufferred output
@ob_end_flush();
ob_implicit_flush(TRUE);
// set no time limit
set_time_limit(0);
// =================================================================================================

/**
 * CLI module to work with Manifests
 */

function __autoload($className)
{
    include implode('/', explode('_', $className)) . '.php';
}

// Setup the environment and includes path
define('ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    );

//echo 'Start the session' . PHP_EOL;


try {
// Start the session
//Zend_Session::start(array('strict' => true, 'remember_me_seconds' => 86400));
$session = new Zend_Session_Namespace();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

// Set up the log writer

echo 'Load the config' . PHP_EOL;
// Load the config
$defaultConfig = new Zend_Config(array('logging' => array('level' => 7, 'path' => sys_get_temp_dir())), true);
//$defaultConfig->merge(new Zend_Config_Ini(ROOT_DIR . '/minder.ini', null));
// expect in document root something like "/var/sites/sitename/html"
// so the 4th entry is the sitename
//$mdrDocRoot = explode("/" , $_SERVER['DOCUMENT_ROOT']);
$mdrDocRoot = explode("/" , ROOT_DIR );
$mdrSitename = $mdrDocRoot[3];
$mdrSitename = 'minder';
$config_file = "/etc/minder/" . $mdrSitename . "/minder.ini";
if(file_exists($config_file) ) {
    $mdrExists = True;
    //echo "$config_file found";
} else {
    // use previous location for config
    $config_file = ROOT_DIR . '/minder.ini';
}
$defaultConfig->merge(new Zend_Config_Ini($config_file, null));
Zend_Registry::set('config', $defaultConfig);
date_default_timezone_set($defaultConfig->date->timezone);


// ====================================================================================================

set_error_handler('errHandler');

// Setup the environment and includes path
// ROOT_DIR should be a Minder dir. e.g. /var/sites/nightly.barcoding.com.au/minder

//define('ROOT_DIR', realpath('..'));
//define('ROOT_DIR', '/var/sites/nightly.barcoding.com.au/minder'); 
//define('ROOT_DIR', '/var/sites/fpg.barcoding.com.au/minder'); 

//define('LOG_DIR', '/tmp'); 
define('LOG_DIR', DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR .  'tmp'); 

function errHandler($errno, $errstr, $errfile, $errline) {
    echo date('Y-m-d H:i:s', time()) . ': ' . $errno . ' ' . $errstr . PHP_EOL . ' ' . $errfile . ' ' . $errline . PHP_EOL;
    return true;
}


echo 'Set up the log writer' . PHP_EOL;
$format = '%timestamp% %priorityName% (%priority%) %user_id% %device_id% %ip_addr%: %message%' . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);
if (is_writable(LOG_DIR  )) {
    //$writer = new Zend_Log_Writer_Stream(LOG_DIR . '/soap' . date('Ymd') . '.log');
    $writer = new Zend_Log_Writer_Stream(LOG_DIR . DIRECTORY_SEPARATOR . 'schedule' . date('Ymd') . '.log');
    $writer->setFormatter($formatter);
    $writer->addFilter(new Zend_Log_Filter_Priority((int)$defaultConfig->logging->level));
} else {
    $writer = new Zend_Log_Writer_Null();
}
//$logger = new Zend_Log();
$logger = new Minder_Log();
$logger->addWriter($writer);
Zend_Registry::set('logger', $logger);

echo 'configure DSN' . PHP_EOL;
// configure DSN
if ($defaultConfig->database->dsn->main) {
    Minder::$dbLiveDsn = $defaultConfig->database->dsn->main;
}
if ($defaultConfig->database->dsn->test) {
    Minder::$dbTrainingDsn = $defaultConfig->database->dsn->test;
}
if ($defaultConfig->database->dsn->user) {
    Minder::$dbUser = $defaultConfig->database->dsn->user; 
}
if ($defaultConfig->database->dsn->password) {
    Minder::$dbPass = $defaultConfig->database->dsn->password; 
}

echo 'Initialise our Minder instance' . PHP_EOL;
// Initialise our Minder instance
    $minder = Minder::getInstance();
/*
    $logger->setEventItem('user_id', $minder->userId);
    $logger->setEventItem('device_id', $minder->deviceId);
    $logger->setEventItem('ip_addr', $minder->ip);
*/
$deviceId = "XY";
    $session->isInventoryOperator= true;
    $session->limitCompany = 'all';
    $session->limitWarehouse = 'all';
    $session->deviceId = $deviceId;
    //$minder->deviceId = $deviceId;

    $minder->isInventoryOperator = isset($session->isInventoryOperator) ? $session->isInventoryOperator: true;
    $minder->limitCompany = isset($session->limitCompany) ? $session->limitCompany : 'all';
    $minder->limitWarehouse = isset($session->limitWarehouse) ? $session->limitWarehouse : 'all';
    $minder->limitPrinter = isset($session->limitPrinter) ? $session->limitPrinter : $minder->limitPrinter;
    $minder->whId = $minder->defaultControlValues['DEFAULT_WH_ID'];
    
$userId = "BDCS";
$deviceId = "XY";
    echo date('Y-m-d H:i:s', time()) . 'Got Date' . PHP_EOL;
$wk_current_hour =  date('H', time()) ;
$wk_current_day  =  date('d', time()) ;
$wk_current_date  =  date('Y-m-d.H-i', time()) ;
//echo 'Hour ' . $wk_current_hour . ' Day ' . $wk_current_day  . PHP_EOL;
// set minders timezone
// initialize Synchronizer
echo 'initialize Synchronizer' . PHP_EOL;

  $reportmanConfig =  parse_ini_file('/etc/reportmanserver');

/*
at startup dont wait for event just process waiting 'WS' and 'WK' status WEB_REQUESTS
*/
{
    echo date('Y-m-d H:i:s', time()) . 'Startup Process Waiting transactions' . PHP_EOL;
    /* update the records in web_requests that are status 'WS' to 'WK'  */
    //$minder->updateWebRequestsStatus( 'WS', 'WK' );
    $minder->setSessionRow($deviceId, 'CLIENT_TIME_ZONE', $defaultConfig->date->timezone);
    $tasks = $minder->getScheduleList( 'OK' );
//var_dump($tasks);
}
/* 
register and wait for db event 'MESSAGE_READY_TOGO'
*/

$doMore = True;
foreach ($tasks as $task_id => $task_result) {
    echo date('Y-m-d H:i:s', time()) . 'Got Task' . PHP_EOL;

    // have to test that the time is due for this
    // split out the reps_run_time field into 5 fields
    // 1 is  minute - ignore this
    // 2 is  hour - upto two values comma seperated - do this as several records
    // 3 is  day of month - expect * or a day number
    // 4 is  month - ignore this
    // 5 is  day of week  - only for weekly reports - ignore this
    // so add fields reps_run_day and reps_run_hour which hold a single value either null = '*' or a digit value
//var_dump($task_result);
    //$wk_run_time = $task_result->items['REPS_RUN_TIME'];
    $wk_run_hour = $task_result->items['REPS_RUN_HOUR'];
    $wk_run_day = $task_result->items['REPS_RUN_DAY'];
    $wk_run_day = $task_result->items['REPS_RUN_DAY'];
//var_dump($wk_run_time);
//var_dump($wk_run_hour);
//var_dump($wk_run_day);
    if (empty($wk_run_day)) {
        $wk_run_day = "ALL";
    }
    if (empty($wk_run_hour)) {
        $wk_run_hour = "ALL";
    }
    if (($wk_run_day == $wk_current_day) or ($wk_run_day == "ALL")) {
        // ok to run this day
        echo "Ok to run this day " . $wk_current_day . PHP_EOL;
        if (($wk_run_hour == $wk_current_hour) or ($wk_run_hour == "ALL")) {
            // ok to run this hour
            echo "Ok to run this hour " . $wk_current_hour . PHP_EOL;
            /* doit here */
            // get the report_id for the name
            $wk_report_name = $task_result->items['REPS_NAME'];
            $wk_report_id = $minder->getReportName($wk_report_name);
            // need the script to run
            // have to get reports .rep file and reportman param names rather than minder reportnames
            // have in the reports uri the reportname and alias
            // have the minder param names so   subtract the leading 'Param' to get the reportman param name
            $report = $minder->getReport( $wk_report_id ); 
            $wk_report_uri = $report->items['REPORT_URI'];
            $wk_report_fields = array();
            $wk_report_x = explode('?', $wk_report_uri);
//var_dump($wk_report_x);
            $wk_report_y = explode('&', $wk_report_x[1]);
//var_dump($wk_report_y);
            foreach($wk_report_y as $wk_report_y_id  => $wk_report_y_value) {
                $wk_report_z = explode('=', $wk_report_y_value);
//var_dump($wk_report_z);
                $wk_report_fields [$wk_report_z[0]] = $wk_report_z[1];
            }
//var_dump($wk_report_fields);
//echo $wk_report_fields['aliasname'];
            // now report field 'aliasname' lookup in reportman config to get folder name
            $wk_reportman_dir = $reportmanConfig[$wk_report_fields['aliasname']];
            $wk_reportman_rep = $wk_report_fields['reportname'];
//echo $wk_reportman_dir;
//echo $wk_reportman_rep;
            $wk_reportman_rep = urldecode($wk_reportman_rep) . ".rep";

//echo $wk_reportman_rep;
            // we need /data/minder/script in our path or perhaps /opt/reportman
            $wk_report_cmd = "printreptopdf  "; 
            if (!empty($task_result->items['REPS_PARAMETER_1_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_1_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_WH_ID'] ;
            if (!empty($task_result->items['REPS_PARAMETER_2_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_2_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_PARAMETER_2'] ;
            if (!empty($task_result->items['REPS_PARAMETER_3_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_3_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_PARAMETER_3'] ;
            if (!empty($task_result->items['REPS_PARAMETER_4_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_4_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_PARAMETER_4'] ;
            if (!empty($task_result->items['REPS_PARAMETER_5_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_5_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_PARAMETER_5'] ;
            if (!empty($task_result->items['REPS_PARAMETER_6_QUERY_FIELD'])) 
                $wk_report_cmd .= " -p" . substr($task_result->items['REPS_PARAMETER_6_QUERY_FIELD'],1) . "=" .  $task_result->items['REPS_PARAMETER_6'] ;
            // add report to run
            $wk_report_cmd .= " " . $wk_reportman_dir .  $wk_reportman_rep;
            // calc folder to put into
            $wk_file_name = $task_result->items['REPS_OUT_FOLDER'] . 
                            $task_result->items['REPS_WH_ID'] . '_' .
                            $task_result->items['REPS_OUT_FILE_NAME'] . 
                            $wk_current_date . "." . $task_result->items['REPS_OUT_FILE_EXT'] ; 
            $wk_report_cmd .= " " . $wk_file_name ;
echo $wk_report_cmd;
            // save file
            exec($wk_report_cmd, $wk_response, $wk_status);
            if (0 === $wk_status) {
                //var_dump($wk_response);
                // success write of file
                // so now if the reps_email_group is not null
                // then email the file to each person in the group
    		$wk_run_subject = $task_result->items['REPS_SUBJECT'];
    		$wk_run_name = $task_result->items['REPS_NAME'];
    		$wk_run_email_group = $task_result->items['REPS_EMAIL_GROUP'];
    		if (!empty($wk_run_email_group)) {
			// get members of email group
    			$whomfor = $minder->getEmailGroup( $wk_run_email_group );
			foreach ($whomfor as $whom_id => $whom_result) {
    				$wk_email_to = $whom_result->items['SET_EMAIL_NAME'];
    				echo date('Y-m-d H:i:s', time()) . 'Got Whom for ' . $wk_email_to . PHP_EOL;
				// now construct email
				// using to name
				// from user
				// reply to
				// subject
				// attachment
		                $fromEmail = 'bdcs@localhost';
		                $ccEmail = '';
		                $toEmail = $wk_email_to ;
		
		                // get files in /etc/minder/mail for  body parts
		                $imageExt = array("jpg", "gif", "jpeg", "svg", "bmp", "JPG"); // valid extensions
		                $htmlExt = array("html" ); // valid extensions
		                $txtExt = array("txt" ); // valid extensions
		                $mailDir = "/etc/minder/mail";
		                $mailer           = new PHPMailer(True); // defaults to using php "mail()"
		
		                $mailer->IsSMTP(); // telling the class to use SMTP
		                $mailer->Host       = "127.0.0.1"; // SMTP server
		                $mailer->SMTPDebug  = 2;  // enables SMTP debug information (for testing)
		                $mailer->AltBody = 'Reports attached.';
		                $wk_body =  'Report attached. ';
		                $wk_body_html =  '';
		                // for the txt  in /etc/minder/mail append into body
		                foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
		                    if (in_array($fileInfo->getExtension(), $txtExt) ) { // in $txtExt
		                        $wk_body .= PHP_EOL . file_get_contents($fileInfo->getFilename()));
		                    }
		                }
		                // for the html in /etc/minder/mail
		                //$mailer->MsgHTML(file_get_contents('contents.html'));
		                foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
		                    if (in_array($fileInfo->getExtension(), $htmlExt) ) { // in $htmlExt
		                        //echo $fileInfo->getFilename() . "<br>\n"; // do something here
		                        $wk_body_html .=  file_get_contents($fileInfo->getFilename()));
		                    }
		                }
		                if (empty($wk_body_html)) {
		                    $mailer->Body = $wk_body;
		                    $mailer->IsHTML ( False);
		                } else {
		                    $mailer->IsHTML ( True);
		                    $mailer->MsgHTML($wk_body_html);
		                    $mailer->AltBody = $wk_body;
		                    $mailer->Body = $wk_body_html;
		                }
		                if (empty($fromEmail) or empty($toEmail)) {
		                    // no from email or to email so no send
		                } else {
		
		                    $mailer->SetFrom($fromEmail); // return Email = company.email
		
		                    $mailer->AddAddress($toEmail ); // to address
		
		                    $mailer->AddReplyTo($fromEmail); // reply to and from set to company.email
		                    // get copy to 
		                    // if not null add to email
		                    if (!empty($ccEmail)) {
		                        $mailer->AddCC($ccEmail);
		                    }
		                    $mailer->Subject    = $wk_run_subject ;
		                    // add subject  order no before  and customer_po_wo after desc  perhaps invoice no 
		                    // add invoice pdf
		                    $mailer->AddAttachment($wk_file_name , $name = $wk_run_name,  $encoding = 'base64', $type = 'application/pdf');
		                    // for the jpg or JPG in /etc/minder/mail
		                    foreach (new DirectoryIterator($mailDir) as $fileInfo) { // interator
		                        if (in_array($fileInfo->getExtension(), $imageExt ) ) { // in $imageExt
		                            //echo $fileInfo->getFilename()  ; 
		                            $mailer->AddAttachment($fileInfo->getFilename());      // attachment
		                        }
		                    }
				    // send it
		                    if(!$mailer->Send()) {
		                        echo "Mailer Error: " . $mailer->ErrorInfo;
		                        $mailResult = "Mailer Error:" . $mailer->ErrorInfo;
		                    } else {
		                        echo "Message sent!";
		                        $mailResult = "Message Sent!" ;
		                    }
		
		                } // of of populated email
		                $mailer->ClearAddresses();
		                $mailer->ClearAttachments();

			}
    		}
            } else {
                //echo "Command failed with status: $wk_status";
                echo "Save of File: $wk_file_name failed with status: $wk_status";
            }

        }
    }
}

// to get here must have finished running the tasks

echo " " . PHP_EOL;
echo date('Y-m-d H:i:s', time()) . 'END CLI' . PHP_EOL;
