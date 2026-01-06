<?php

class MinderAcceptanceTestCase extends PHPUnit_Extensions_Selenium2TestCase {
    CONST DB_HOST = 'interbase.local';

    /**
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $_connection;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->setBrowser('chrome');
    }


    /**
     * Asserts that two given tables are equal.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $expected
     * @param PHPUnit_Extensions_Database_DataSet_ITable $actual
     * @param string $message
     */
    public static function assertTablesEqual(PHPUnit_Extensions_Database_DataSet_ITable $expected, PHPUnit_Extensions_Database_DataSet_ITable $actual, $message = '')
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_TableIsEqual($expected);

        PHPUnit_Framework_Assert::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two given datasets are equal.
     *
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet|\PHPUnit_Extensions_Database_DataSet_ITable $expected
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet|\PHPUnit_Extensions_Database_DataSet_ITable $actual
     * @param string $message
     */
    public static function assertDataSetsEqual(PHPUnit_Extensions_Database_DataSet_IDataSet $expected, PHPUnit_Extensions_Database_DataSet_IDataSet $actual, $message = '')
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($expected);

        self::assertThat($actual, $constraint, $message);
    }

    public function login($user, $password) {
        $this->url('/user/login');
        $this->byId('userId')->value($user);
        $this->byId('password')->value($password);
        $this->byId('action')->click();
        $this->timeouts()->implicitWait(1000);
    }

    public function logout() {
        $this->url('/user/logout');
        $this->timeouts()->implicitWait(1000);
    }

    public function logoutAll($user, $password) {
        $this->url('/user/logout-all');
        $this->byId('userId')->value($user);
        $this->byId('password')->value($password);
        $this->byId('action')->click();
        $this->timeouts()->implicitWait(1000);
    }

    /**
     * @deprecated replaced with setupConfigNew
     * @param $dsn
     * @param $licensee
     * @throws Zend_Config_Exception
     */
    public function setupConfig($dsn, $licensee) {
        $config = array(
            'database' => array(
                'dsn' => array(
                    'main' => $dsn
                )
            ),
            'date' => array(
                'timezone' => 'Australia/Sydney',
                'dateformat' => "Y-m-d H:i:s",

            ),
            'logging' => array(
                'level' => 6
            ),
            'contenttype' => array(
                'csv' => 'application/octet-stream',
                'json' => 'text/javascript',
                'pc' => 'text/html',
                'txt' => 'text/plain',
                '12-inch' => 'text/html',
                '3.5-inch' => 'text/html',
            ),
            'soapcli' => array(
                'logupdate' => '/tmp/soap-update.log',
                'logcommit' => '/tmp/soap-commit.log',
                'period_update' => 3,
                'period_commit' => 5
            ),
            'soap' => array(
                'passport' => array(
                    "email" => "fpgminder@barcoding.com.au",
                    "password" => "fpgminder1",
                    "role" => "Administrator",
                    "account" => 823303
                )
            ),
            'company' => array(
                'licensee' => $licensee
            ),
        );

        $writer = new Zend_Config_Writer_Ini();
        $writer->write(ROOT_DIR . '/minder.ini', new Zend_Config($config));
    }

    public function setupConfigNew($licensee, $dbAlias, $host = null) {
        $host = is_null($host) ? static::DB_HOST : $host;
        $this->setupConfig(implode(':', array($host, $dbAlias)), $licensee);
    }

    public function createYamlDataset($filename)
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($filename);
    }

    public function createArrayDataset($data) {
        return new ArrayDataSet($data);
    }

    public function createQueryDataset() {
        return new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
    }

    public function waitForValue($cssSelector, $value, $timeout = null)
    {
        return $this->waitUntil(function ($testCase) use ($cssSelector, $value) {
            /**
             * @var MinderAcceptanceTestCase $testCase
             */
            return $testCase->executeScript('return ($("' . $cssSelector . '").val() == "' . $value . '") ? true : null;');
        }, $timeout);

    }

    protected function _buildConnection() {
        list($dbHost, $dbAlias) = explode(':', $this->_getDsn());

        $dbConfig = new Zend_Config(array(
            'adapter' => 'Firebird',
            'params' => array(
                'host' => $dbHost,
                'dbname' => $dbAlias,
                'username' => 'SYSDBA',
                'password' => 'masterkey',
                'adapterNamespace' => 'ZendX_Db_Adapter',
                'profiler' => true
            )
        ));

        $db = Zend_Db::factory($dbConfig);

        return new Zend_Test_PHPUnit_Db_Connection($db, '');
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if (empty($this->_connection)) {
            $this->_connection = $this->_buildConnection();
        }

        return $this->_connection;
    }

    protected function _getDsn() {
        $config = new Zend_Config_Ini(ROOT_DIR . '/minder.ini');
        return $config->database->dsn->main;
    }

    protected function cleanInsert()
    {
        return new PHPUnit_Extensions_Database_Operation_Composite(array(
            PHPUnit_Extensions_Database_Operation_Factory::DELETE(),
            PHPUnit_Extensions_Database_Operation_Factory::INSERT(),
        ));
    }

    protected function update() {
        return PHPUnit_Extensions_Database_Operation_Factory::UPDATE();
    }

    protected function delete()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::DELETE();
    }

    public function executeScript($script, array $args = array())
    {
        return $this->execute(array('script' => $script, 'args' => $args));
    }

    public function waitForElement($cssSelector, $timeout = null) {
        return $this->waitUntil(function($testCase)use($cssSelector){
            /**
             * @var MinderAcceptanceTestCase $testCase
             */
            return $testCase->executeScript('return $("' . $cssSelector . '").length ? "exists" : null;');
        }, $timeout);

    }

    protected function truncate()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }

    protected function alterTrigger($triggerName, $active)
    {
        $this->getConnection()->getConnection()->query('ALTER TRIGGER ' . $triggerName . ' ' . $active);
    }

    protected function enableTriggers($triggers)
    {
        $triggers = (array)$triggers;
        foreach ($triggers as $triggerName) {
            $this->alterTrigger($triggerName, 'ACTIVE');
        }
    }

    protected function disableTriggers($triggers)
    {
        $triggers = (array)$triggers;
        foreach ($triggers as $triggerName) {
            $this->alterTrigger($triggerName, 'INACTIVE');
        }
    }

    protected function _scanLabel($label) {
        $this->executeScript('$("#barcode").focus().val("' . $label . '").blur();');
    }
}