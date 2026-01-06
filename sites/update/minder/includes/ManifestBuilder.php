<?php

class ManifestBuilder implements ManifestBuilder_LoggerAwareInterface {

    /**
     * @var ManifestBuilder
     */
    static private $_instance = null;

    /**
     * @var Zend_Console_Getopt
     */
    protected $_getopt;
    /**
     * @var ManifestBuilder_Options
     */
    protected $_options;

    protected $_databaseAdapter;

    /**
     * @var ManifestBuilder_BuilderInterface
     */
    protected $_concreteBuilder;

    /**
     * @var ManifestBuilder_Factory
     */
    protected $_builderFactory;

    /**
     * @var ManifestBuilder_Logger
     */
    protected $_logger;

    /**
     * @var ManifestBuilder_Provider_Carrier
     */
    protected $_carriersProvider;

    public static function getInstance() {
        if (is_null(static::$_instance)) {
            static::$_instance = new ManifestBuilder();
        }

        return static::$_instance;
    }

    protected function _initDatabase($config) {
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
        return $db;
    }

    protected function initConcreteBuilder(ManifestBuilder_BuilderInterface $builder) {
        if ($builder instanceof ManifestBuilder_LoggerAwareInterface) {
            $builder->setLogger($this->getLogger());
        }

        $timestamp = microtime(true);
        $time = floor($timestamp);
        $micro = floor(($timestamp - $time) * 1000);
        $date = new ManifestBuilder_Date($time);
        $date->setMilliSecond($micro, 3);

        $builder->setCurrentDate($date);

        return $builder;
    }

    protected function _initLogger($config) {
        $logger = new ManifestBuilder_Logger();

        //setup log
        $loggingConf = new Zend_Config(array('logging' => array('level' => 7)), true);
        $loggingConf->merge($config);

        $sysLogWriter = new Zend_Log_Writer_Syslog(array('application' => 'Manifest_Builder_cli'));
        $sysLogWriter->addFilter(new Zend_Log_Filter_Priority((int)$loggingConf->logging->level));

        $stderrWriter = new Zend_Log_Writer_Stream('php://stderr');
        $stderrWriter->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN));

        $stdoutWriter = new Zend_Log_Writer_Stream('php://stdout');
        $stdoutWriter->addFilter(new Zend_Log_Filter_Priority((int)$loggingConf->logging->level));
        $stdoutWriter->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN, '>'));

        $logger->addWriter($sysLogWriter);
        $logger->addWriter($stderrWriter);
        $logger->addWriter($stdoutWriter);

        return $logger;
    }

    public function bootstrap($config) {
        $this->setLogger($this->_initLogger($config));

        $this->setGetopt(new ManifestBuilder_Console_Getopt());
        $this->setOptions(new ManifestBuilder_Options());
        $this->getOptions()->fillCommandLineOptions($this->getGetopt());

        $this->setDatabaseAdapter($this->_initDatabase($config));
        $this->setBuilderFactory(new ManifestBuilder_Factory());
        $carrierProvider = new ManifestBuilder_Provider_Carrier();
        $carrierProvider->setDbAdapter($this->getDatabaseAdapter());
        $this->setCarriersProvider($carrierProvider);
        return $this;
    }

    public function run() {
        $this->getLogger()->info('Call time params: ' . $this->getGetopt()->toString());

        if ($this->getOptions()->showHelp()) {
            return $this->getUsageMessage();
        }

        if ($this->getOptions()->createManifest()) {
            $options = $this->getOptions();
            foreach ($this->getCarrierOutputFormats($options->getCarriersList(), $options->getManifestId()) as $carrierOutputFormat) {
                try {
                    $builder = $this->getConcreteBuilder($carrierOutputFormat->CONNOTE_EXPORT_METHOD);
                    $builtManifests = $builder->build($carrierOutputFormat->CARRIER_ID, $this->getOptions());

                    if ($this->getOptions()->uploadManifest()) {
                        $builder->upload($builtManifests);
                    }
                } catch (Exception $e) {
                    $this->getLogger()->err($e->getMessage() . ' Skipping manifest build.');
                    $this->getLogger()->trace($e->getTrace());
                }
            }
        }

        return '';
    }

    /**
     * @param $carriersList
     * @param $manifestId
     * @return ManifestBuilder_Model_CarrierOutputFormat[]
     */
    protected function getCarrierOutputFormats($carriersList, $manifestId) {
        return $this->getCarriersProvider()->getCarrierOutputFormats($carriersList, $manifestId);
    }

    public function getGetopt()
    {
        return $this->_getopt;
    }

    /**
     * @param Zend_Console_Getopt $getOpt
     */
    public function setGetopt(Zend_Console_Getopt $getOpt)
    {
        $this->_getopt = $getOpt;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param ManifestBuilder_Options $options
     */
    public function setOptions(ManifestBuilder_Options $options)
    {
        $this->_options = $options;
    }

    public function getDatabaseAdapter()
    {
        return $this->_databaseAdapter;
    }

    public function setDatabaseAdapter($database)
    {
        $this->_databaseAdapter = $database;
    }

    public function getConcreteBuilder($format)
    {
        $builder = $this->getBuilderFactory()->doBuild($format, $this->getDatabaseAdapter());
        return $this->initConcreteBuilder($builder);
    }

    /**
     * @param ManifestBuilder_BuilderInterface $concreteBuilder
     * @return ManifestBuilder
     */
    public function setConcreteBuilder(ManifestBuilder_BuilderInterface $concreteBuilder)
    {
        $this->_concreteBuilder = $concreteBuilder;
        return $this;
    }

    public function getBuilderFactory()
    {
        return $this->_builderFactory;
    }

    /**
     * @param ManifestBuilder_Factory $builderFactory
     * @return ManifestBuilder
     */
    public function setBuilderFactory(ManifestBuilder_Factory $builderFactory)
    {
        $this->_builderFactory = $builderFactory;
        return $this;
    }

    public function getUsageMessage() {
        return $this->getGetopt()->getUsageMessage();
    }

    /**
     * @param ManifestBuilder_Logger $logger
     * @return ManifestBuilder_LoggerAwareInterface
     */
    public function setLogger(ManifestBuilder_Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * @return ManifestBuilder_Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return ManifestBuilder_Provider_Carrier
     */
    public function getCarriersProvider()
    {
        return $this->_carriersProvider;
    }

    /**
     * @param ManifestBuilder_Provider_Carrier $carriersProvider
     * @return ManifestBuilder
     */
    public function setCarriersProvider(ManifestBuilder_Provider_Carrier $carriersProvider)
    {
        $this->_carriersProvider = $carriersProvider;
        return $this;
    }
}
