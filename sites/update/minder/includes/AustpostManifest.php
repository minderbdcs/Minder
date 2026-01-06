<?php

class AustpostManifest {
    
    protected $atDate;
    protected $carriersList = array();
    protected $manifestId   = null;
    
    protected $builtManifests = array();
    
    /**
    * @var AustpostManifest
    */
    private static $instance = null;
    
    private function __construct() {
        
    }
    
    /**
    * Return instance of AustpostManifest class
    * 
    * @return AustpostManifest
    */
    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new AustpostManifest();
            
        return self::$instance;
    }
    
    /**
    * Get instance of configured Database object
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    public static function getDb() {
        return Zend_Registry::get('db');
    }
    
    /**
    * Get instance of Logger object
    * 
    * @return Zend_Log
    */
    public static function getLog() {
        return Zend_Registry::get('logger');
    }
    
    /**
    * Get instance of Getopt object
    * 
    * @return AustpostManifest_Console_Getopt
    * 
    */
    public static function getGetopt() {
        return Zend_Registry::get('getopt');
    }
    
    public function getDefaultControl($controlName) {
        $controls = AustpostManifest_Db_Table_Control::getInstance();
        return $controls[$controlName];
    }
    
    /**
    * Put debug information to log
    * 
    * @param mixed   $expression - expression to debug
    * @param string  $name       - optional name for expression
    * @param boolean $backtrace  - include backtrace 
    * 
    * @return void
    */
    public static function debug($expression, $name = '', $backtrace = false) {
        $expression = (empty($name)) ? array($expression) : array($name => $expression); 
        
        $messageStr = print_r($expression, true);
        
        if ($backtrace) {
            $messageStr .= PHP_EOL . print_r(array('backtrace' => debug_backtrace()));
        }
        
        $logger = self::getLog();
        $logger->log($messageStr, Zend_Log::DEBUG);
    }
    
    /**
    * Put informational message to log
    * 
    * @param string $message
    */
    public static function info($message) {
        self::getLog()->info($message);
    }
    
    /**
    * Put error message to log
    * 
    * @param string $message
    */
    public static function error($message) {
        self::getLog()->err($message);
        
    }
    
    /**
    * Put warning message to log
    * 
    * @param string $message
    */
    public static function warning($message) {
        self::getLog()->warn($message);
        
    }
    
    /**
    * Make initial environment setup
    * 
    * @param Zend_Config $config
    */
    public static function init($config) {
        //parse CLI params
        $getopt = new AustpostManifest_Console_Getopt();
        Zend_Registry::set('getopt', $getopt);
        $getopt->parse();
        
        //set Manifest dir
        $manifestDir = ROOT_DIR . DIRECTORY_SEPARATOR . 'manifests';
        $options = $getopt->getOptions();
        if (in_array('manifest-dir', $options))
            $manifestDir = $getopt->getOption('manifest-dir');
        Zend_Registry::set('manifest_dir', $manifestDir);

        //set timezone
        date_default_timezone_set($config->date->timezone);
        
        //setup log
        $loggingConf = new Zend_Config(array('logging' => array('level' => 7)), true);
        $loggingConf->merge($config);
        
        $writer = new Zend_Log_Writer_Syslog(array('application' => 'eParcel_Manifest_Builder_cli'));
        $writer->addFilter(new Zend_Log_Filter_Priority((int)$loggingConf->logging->level));
        
        Zend_Registry::set('logger', new Zend_Log($writer));
        
        //setup DB access
        $dbInfo = explode(':', $config->database->dsn->main);
        if (count($dbInfo) > 1) {
            list($dbHost, $dbAlias) = $dbInfo;
        } else {
            //assuming no host defined
            $dbAlias = $dbInfo[0];
            $dbHost  = 'localhost';
        }
	if ($config->database->dsn->user) {
            $dbUser = $config->database->dsn->user; 
	} else {
            $dbUser = 'SYSDBA'; 
	}

	if ($config->database->dsn->password) {
            $dbPass = $config->database->dsn->password; 
	} else {
            $dbPass = 'masterkey'; 
	}
        
        $dbConfig = new Zend_Config(array(
            'adapter' => 'Firebird',
            'params'  => array(
                'host'             => $dbHost,
                'dbname'           => $dbAlias,
                'username'         => $dbUser,
                'password'         => $dbPass,
                'adapterNamespace' => 'ZendX_Db_Adapter',
                'profiler'         => true
            )
        ));
        
        $db = Zend_Db::factory($dbConfig);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    public function formatDateTime($timestamp = null) {
        if (is_null($timestamp))
            $timestamp = microtime(true);

        $seconds = $timestamp;
        $milliseconds = '000';

        if (is_float($timestamp)) {
            $seconds = floor($timestamp);
            $milliseconds = strval(floor(($timestamp - $seconds) * 1000));
            $milliseconds .=  str_repeat('0', 3 - strlen($milliseconds));
        }

        return date('Y-m-d\TH:i:s', $seconds) . '.' . $milliseconds;
    }
    
    protected function createManifest() {
        
        self::info('Building manifest for date: ' . date('Y-m-d', $this->atDate) . ' ...');
        
        $manifestBuilder = new AustpostManifest_ManifestBuilder();
        $this->builtManifests = $manifestBuilder->build($this->carriersList, $this->manifestId);
    }
    
    protected function uploadManifest() { 
        self::info('Uploading built manifests... ');

        $manifestUploader = new AustpostManifest_ManifestUploader();
        $manifestUploader->upload($this->builtManifests);
    }
    
    /**
    * Runs main application
    * 
    */
    public function run() {
        $scriptOptions = $this->getGetopt();
        //set date to build manifest
        $this->atDate = time();
        self::info('Script parameters passed: ' . $scriptOptions->toString());

        $tmpCarriersList    = $scriptOptions->getOption('carriers');
        $this->carriersList = (is_string($tmpCarriersList)) ? explode('|', $tmpCarriersList) : array();
        $this->manifestId   = $scriptOptions->getOption('pick-manifest-id');

        if ($scriptOptions->getOption('create-manifest')) {
            $this->createManifest();
        
            if ($scriptOptions->getOption('upload-manifest')) {
                $this->uploadManifest();
            }
            
            $profiler = $this->getDb()->getProfiler();
            if (!empty($profiler)) {
                self::debug($profiler->getTotalNumQueries(), 'Total Queries');
                self::debug($profiler->getTotalElapsedSecs(), 'Total Elapsed');
                foreach ($profiler->getQueryProfiles(null, true) as $queryProfile) {
                    self::debug(
                        array(
                            'Query Text' => $queryProfile->getQuery(), 
                            'Params' => $queryProfile->getQueryParams(), 
                            'Elapsed' => $queryProfile->getElapsedSecs()
                        ), 
                        'Query Profile'
                    );
                }
            }
            
            return;
        }
        
        echo $scriptOptions->getUsageMessage();
    }
}

class AustpostManifest_Exception extends Exception {}
