<?php


class Reportman_Wrapper
{

    private $_minder;

    /**
     * Allow console output
     *
     * @var boolean
     */
    private $_mode;

    private $_userid;
    private $_deviceid;
    private $_ip;

    public $lastError;
    public $lastErrorCode;
    public static $client;
    private $webservices_uri;
    private $namespace;
    private $reportmanUserId;
    private $reportmanPassword;
    private $reportmanLoggedIn;

    public function __construct( $userid, $deviceid, $ip = '127.0.0.1', $silentmode = false)
    {
        $this->_minder      = Minder::getInstance();
        $this->_mode        = $silentmode;

        $this->_userid      = $this->_minder->userId   = $userid;
        $this->_deviceid    = $this->_minder->deviceId = $deviceid;
        $this->_ip          = $this->_minder->ip       = $ip;
        $this->_minder->silent  = $silentmode;

	$this->jasperLoggedIn = False;

    }

    private function msg($output)
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        if (!$this->_mode) {
            echo date('Y-m-d H:i:s', time()) . ': ' . $output . PHP_EOL;
        }
    }

    public function __destruct()
    {
    }

    /**
     * convert date from Netsuite to Minder
     *
     * @param string $date
     * @return string|false
     */
    private function __dateConvert($date, $format = 'Y-m-d H:i:s') {
        return ($date ? date($format, strtotime($date)) : false);
    }


/* ============================================= Execute Report ========================================================== */

	// run a report  with paramets passed via an array
public	function reportmanExecuteReport( $reportParams = array())
	{
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
                $output = "";
                     $log->info('reprts params :' . print_r($reportParams,true));
                $this->lastError = '';
		// pass the reports main uri
		// the list of parameters and values in an array
		// the format for output
		// all these come from the reports table for the report
		 
		 // 1 Get the ReoportUnit ResourceDescriptor...
		 $currentUri = "/";
		 
                 if (isset($reportParams['uri'])) {
		 	if ($reportParams['uri'] != '')
		 	{
		 		$currentUri = urldecode($reportParams['uri']);
		 	}
		 }
		 
		 $report_params = array();
		 
                 $report_params_list = "";
                 $report_params_list_txt = "";
		 
		 foreach (array_keys($reportParams) AS $param_name)
		 {
		 	if (strncmp("Param", $param_name,5) == 0)
		 	{
		 		$report_params[$param_name] = $reportParams[$param_name];
                                $report_params_list .= "&" . $param_name . "=" . $reportParams[$param_name];
				$wk_param_name = substr($param_name,5);
                                //$report_params_list_txt .= " -param" . $param_name . "=" . $reportParams[$param_name];
                                $report_params_list_txt .= " -param" . $wk_param_name . "=" . $reportParams[$param_name];
		 		
		 	}
		 	
		 }
		 $report_params['username'] = $reportParams['username'];
                 $report_params_list .= "&username=" . $reportParams['username'];
		 $report_params['password'] = $reportParams['password'];
                 $report_params_list .= "&password=" . $reportParams['password'];
		 
		 // 3. Execute the report
	 	 if ($reportParams['format'] == 'PDF')
		 {
		     $site =  urldecode($reportParams['url']);
                     $site .=  urldecode($reportParams['uri']);
                     $site .= $report_params_list;
                     $log->info('site:' . $site);

                     try {
                         $output = file_get_contents($site);
                     } catch (Exception $e) {
                          //echo 'Caught exception: ',  $e->getMessage(), "\n";
                          $this->lastError = $e->getMessage();
		          return False;
                     }

                     return($output);
		 }
	 	 if ($reportParams['format'] == 'CSV')
		 {
                     $site =  "/usr/local/bin/printrep2csv  ";
                     $site .= $report_params_list_txt . ' ';
                     $site .=  urldecode($reportParams['uri']);
                     $site .=  ' ';
                     $log->info('site:' . $site);

                     /* "printreptopdf -q -csv /var/reports/stockvariance.rep /tmp/$$.csv;cat /tmp/$$.csv " */
                     /* "printreptopdf -q -csv -paramPLOCNFROM=01010101 -paramPLOCNTO=99999999 /var/reports/testlocation2.rep /tmp/$$.csv;cat /tmp/$$.csv " */


                     try {
                         $wkFile = system($site);
                         $log->info('file:' . $wkFile);
                         if (file_exists($wkFile)) {
                         	$output = file_get_contents($wkFile);
                         	$log->info('file: exists');
                         } else {
				$wkFileLog = substr($wkFile,0,-3) . "log";
                         	$log->info('logfile:' . $wkFileLog);
                         	$errorLog = file_get_contents($wkFileLog);
                         	$log->info('log:' . $errorLog);
                    		$this->lastError =  $errorLog;
		          	return False;
			}
                         
                     } catch (Exception $e) {
                          //echo 'Caught exception: ',  $e->getMessage(), "\n";
                          $this->lastError = $e->getMessage();
		          return False;
                     }

                     return($output);
		 }
		 
		 $errorMessage = "Unknown Format!";
                 //$this->addError($errorMessage);
                 $this->lastError = $errorMessage;
		 return False;

	} 
/* ============================================= End of Execute Report =================================================== */


}
