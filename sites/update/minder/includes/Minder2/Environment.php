<?php

class Minder2_Environment {

    const SESSION_NAMESPACE = 'MINDER_ENVIRONMENT';
    const CURRENT_USER      = 'CURRENT_USER';
    const CURRENT_DEVICE    = 'CURRENT_DEVICE';
    const CURRENT_WAREHOUSE = 'CURRENT_WAREHOUSE';
    const WAREHOUSE_LIMIT   = 'WAREHOUSE_LIMIT';
    const CURRENT_COMPANY   = 'CURRENT_COMPANY';
    const COMPANY_LIMIT     = 'COMPANY_LIMIT';
    const CURRENT_PRINTER   = 'CURRENT_PRINTER';
    const COMPANY_LIST      = 'COMPANY_LIST';
    const WAREHOUSE_LIST    = 'WAREHOUSE_LIST';
    const PRINTER_LIST      = 'PRINTER_LIST';

    const SYSTEM_CONTROLS   = 'SYSTEM_CONTROLS';

    const PRICE_NUMBER_FORMAT = 'PRICE_NUMBER_FORMAT';

    /**
     * @var null|Zend_Session_Namespace
     */
    protected static $_session = null;

    /**
     * @var Minder2_Environment
     */
    protected static $_instance = null;

    private function __construct() {

    }

    /**
     * @static
     * @return Minder2_Environment
     */
    public static function getInstance() {
        if (is_null(self::$_instance))
            self::$_instance = new Minder2_Environment();

        return self::$_instance;
    }

    /**
     * @static
     * @return void
     */
    public static function destroy() {
        self::$_instance = null;
    }

    /**
     * @static
     * @return Zend_Session_Namespace
     */
    protected static function _getSession() {
        if (is_null(self::$_session))
            self::$_session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);

        return self::$_session;
    }

    /**
     * @static
     * @param string $name
     * @return mixed
     */
    protected static function _getSavedProperty($name) {
        $session = self::_getSession();

        if (isset($session->$name))
            return $session->$name;

        return null;
    }

    /**
     * @static
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected static function _saveProperty($name, $value) {
        $session = self::_getSession();
        $session->$name = $value;
    }

    /**
     * @static
     * @throws Minder_Exception
     * @param string $userId
     * @return Minder2_Model_SysUser
     */
    protected static function _findUser($userId) {
        $userMapper = new Minder2_Model_Mapper_SysUser();
        return $userMapper->find($userId);
    }

    /**
     * @static
     * @throws Minder_Exception
     * @return Minder2_Model_SysUser
     */
    public static function getCurrentUser() {
        $user = self::_getSavedProperty(self::CURRENT_USER);

        if (is_null($user)) {
            if (empty(Minder::getInstance()->userId)) {
                $user = new Minder2_Model_SysUser();
            } else {
                $user = self::_findUser(Minder::getInstance()->userId);
                $user->isLogged = true;

                if (!$user->existed)
                    throw new Minder_Exception('Not existed Current User');
            }

            self::setCurrentUser($user);
        }

        return $user;
    }

    /**
     * @static
     * @param Minder2_Model_SysUser $user
     * @return void
     */
    public static function setCurrentUser(Minder2_Model_SysUser $user = null) {
        self::_saveProperty(self::CURRENT_USER, $user);
        self::_setCurrentDevice(null); //cleanup CURRENT_DEVICE
        self::_setCurrentWarehouse(null); //cleanup CURRENT_WAREHOUSE
        self::_setWarehouseList(null); //cleanup WAREHOUSE_LIST
        self::_setCompanyList(null); //cleanup COMPANY_LIST
        self::_setPrinterList(null); //cleanup PRINTER_LIST
    }

    /**
     * @static
     * @throws Minder_Exception
     * @return Minder2_Model_SysEquip
     */
    public static function getCurrentDevice() {
        $device = self::_getSavedProperty(self::CURRENT_DEVICE);

        if (is_null($device)) {
            $device = self::getCurrentUser()->getDevice();
            self::_setCurrentDevice($device);
        }

        return $device;
    }

    /**
     * @param Minder2_Model_SysEquip | null $device
     * @return void
     */
    protected static function _setCurrentDevice(Minder2_Model_SysEquip $device = null) {
        self::_saveProperty(self::CURRENT_DEVICE, $device);
    }

    /**
     * @static
     * @return Minder2_Model_Warehouse
     */
    public static function getCurrentWarehouse() {
        $warehouse = self::_getSavedProperty(self::CURRENT_WAREHOUSE);

        if (is_null($warehouse)) {
            $warehouse = self::getCurrentUser()->getWarehouse();
            self::_setCurrentWarehouse($warehouse);
        }

        return $warehouse;
    }

    /**
     * @static
     * @param Minder2_Model_Warehouse|null $warehouse
     * @return void
     */
    protected static function _setCurrentWarehouse(Minder2_Model_Warehouse $warehouse = null) {
        self::_saveProperty(self::CURRENT_WAREHOUSE, $warehouse);
        self::_setPrinterList(null); //cleanup printer list as it depends on current warehouse
    }

    /**
     * @return array
     */
    public function getCompanyList() {
        $companyList = self::_getSavedProperty(self::COMPANY_LIST);

        if (is_null($companyList)) {
            $companyList = self::getCurrentUser()->getAccessCompanyList();
            $this->_setCompanyList($companyList);
        }

        return $companyList;
    }

    /**
     * @param array|null $companyList
     * @return void
     */
    protected function _setCompanyList(array $companyList = null) {
        self::_saveProperty(self::COMPANY_LIST, $companyList);
        self::setCompanyLimit(self::getCompanyLimit()); //reset COMPANY_LIMIT as it depends on accessible company list
    }

    /**
     * @static
     * @return Minder2_Model_Company
     */
    public static function getCompanyLimit() {
        $company = self::_getSavedProperty(self::COMPANY_LIMIT);

        if (is_null($company))
            return new Minder2_Model_Company();
        
        return $company;
    }

    /**
     * @static
     * @param string $companyId
     * @return Minder2_Model_Company
     */
    protected static function _findCompany($companyId) {
        if (strtolower($companyId) == 'all')
            return new Minder2_Model_Company(); //legacy code support

        $companyMapper = new Minder2_Model_Mapper_Company();
        return $companyMapper->find($companyId);
    }

    /**
     * @static
     * @param Minder2_Model_Company | string $company
     * @return void
     */
    public static function setCompanyLimit($company) {
        $company = ($company instanceof Minder2_Model_Company) ? $company : self::_findCompany($company);

        $companyList = self::getInstance()->getCompanyList();

        $session = new Zend_Session_Namespace();
        if (isset($companyList[$company->COMPANY_ID])) {
            self::_saveProperty(self::COMPANY_LIMIT, $company);

            //legacy code support
            Minder::getInstance()->limitCompany = $company->COMPANY_ID;
            $session->limitCompany              = $company->COMPANY_ID;
        } else {
            self::_saveProperty(self::COMPANY_LIMIT, null);

            //legacy code support
            Minder::getInstance()->limitCompany = 'all';
            $session->limitCompany              = 'all';
        }

        self::_saveProperty(self::CURRENT_COMPANY, null); //cleanup CURRENT_COMPANY as it depends on COMPANY_LIMIT
        Minder_SysScreen_Builder::dropScreenDescriptionsCache();
    }

    protected function _getDefaultCompany() {
        $userCompany = $this->_findCompany($this->getCurrentUser()->COMPANY_ID);

        if (!$userCompany->existed)
            return $this->_findCompany($this->getSystemControls()->COMPANY_ID);

        return $userCompany;
    }

    public function getCurrentCompany() {
        /**
         * @var Minder2_Model_Company $company
         */
        $company = self::_getSavedProperty(self::CURRENT_COMPANY);

        if (is_null($company) || !$company->existed) {
            $company = $this->_getDefaultCompany();
            $this->_setCurrentCompany($company);
        }

        return $company;
    }

    protected function _setCurrentCompany(Minder2_Model_Company $company = null) {
        self::_saveProperty(self::CURRENT_COMPANY, $company);
    }

    /**
     * @static
     * @return array(Minder2_Model_Warehouse)
     */
    public static function getWarehouseList() {
        $warehouseList = self::_getSavedProperty(self::WAREHOUSE_LIST);

        if (is_null($warehouseList)) {
            $warehouseList = self::getCurrentUser()->getAccessWarehouseList();
            self::_setWarehouseList($warehouseList);
        }

        return $warehouseList;
    }

    /**
     * @static
     * @param array|null $warehouseList
     * @return void
     */
    protected static function _setWarehouseList(array $warehouseList = null) {
        self::_saveProperty(self::WAREHOUSE_LIST, $warehouseList);
        self::setWarehouseLimit(self::getWarehouseLimit()); //reset WAREHOUSE_LIMIT as it depends on accessible warehouse list
    }

    /**
     * @static
     * @return Minder2_Model_Warehouse
     */
    public static function getWarehouseLimit() {
        $warehouse = self::_getSavedProperty(self::WAREHOUSE_LIMIT);

        if (is_null($warehouse))
            return new Minder2_Model_Warehouse();

        return $warehouse;
    }

    /**
     * @static
     * @param $whId
     * @return Minder2_Model_Warehouse
     */
    protected static function _findWarehouse($whId) {
        if (strtolower($whId) == 'all')
            return new Minder2_Model_Warehouse(); //legacy code support

        $warehouseMapper = new Minder2_Model_Mapper_Warehouse();
        return $warehouseMapper->find($whId);
    }

    /**
     * @static
     * @param Minder2_Model_Warehouse|string $warehouse
     * @return void
     */
    public static function setWarehouseLimit($warehouse) {
        $warehouse = ($warehouse instanceof Minder2_Model_Warehouse) ? $warehouse : self::_findWarehouse($warehouse);

        $warehouseList = self::getWarehouseList();

        $session = new Zend_Session_Namespace();
        if (isset($warehouseList[$warehouse->WH_ID])) {
            self::_saveProperty(self::WAREHOUSE_LIMIT, $warehouse);

            //legacy code support
            Minder::getInstance()->limitWarehouse = $warehouse->WH_ID;
            $session->limitWarehouse              = $warehouse->WH_ID;
        } else {
            self::_saveProperty(self::WAREHOUSE_LIMIT, null);

            //legacy code support
            Minder::getInstance()->limitWarehouse = 'all';
            $session->limitWarehouse              = 'all';
        }

        Minder_SysScreen_Builder::dropScreenDescriptionsCache();
    }

    /**
     * @static
     * @return array
     */
    public static function getPrinterList() {
        $printerList = self::_getSavedProperty(self::PRINTER_LIST);

        if (is_null($printerList)) {
            $printerList = array_merge(self::getCurrentUser()->getAccessPrinterList(), self::getCurrentWarehouse()->getPrinterList());
            self::_setPrinterList($printerList);
        }

        return $printerList;
    }

    /**
     * @static
     * @param array|null $printerList
     * @return void
     */
    protected static function _setPrinterList(array $printerList = null) {
        self::_saveProperty(self::PRINTER_LIST, $printerList);
        self::setCurrentPrinter(self::getCurrentPrinter()); //reset CURRENT_PRINTER as it depends on accessible printer list
    }

    /**
     * @static
     * @return Minder2_Model_SysEquip
     */
    public static function getCurrentPrinter() {
        $printer = self::_getSavedProperty(self::CURRENT_PRINTER);

        if (is_null($printer)) {
            $printerList = self::getPrinterList();
            reset($printerList);
            $printer = current($printerList);
        }

        if ($printer === false)
            return new Minder2_Model_SysEquip();

        return $printer;
    }

    /**
     * @static
     * @param string $deviceId
     * @return Minder2_Model_SysEquip
     */
    protected static function _findPrinter($deviceId) {
        $sysEquipMapper = new Minder2_Model_Mapper_SysEquip();
        return $sysEquipMapper->findPrinter($deviceId);
    }

    /**
     * @static
     * @param Minder2_Model_SysEquip | string $device
     * @return void
     */
    public static function setCurrentPrinter($device) {
        $device = ($device instanceof Minder2_Model_SysEquip) ? $device : self::_findPrinter($device);

        $session = new Zend_Session_Namespace();
        $printerList = self::getPrinterList();
        if (isset($printerList[$device->DEVICE_ID])) {
            self::_saveProperty(self::CURRENT_PRINTER, $device);
            Minder::getInstance()->limitPrinter = $device->DEVICE_ID;
            $session->limitPrinter              = $device->DEVICE_ID;
        } else {
            reset($printerList);
            self::_saveProperty(self::CURRENT_PRINTER, (current($printerList) === false) ? null : current($printerList));
            Minder::getInstance()->limitPrinter = key($printerList);
            $session->limitPrinter              = key($printerList);
        }

    }

    /**
     * @return Minder2_Model_Control
     */
    protected function _findSystemControls() {
        $mapper = new Minder2_Model_Mapper_Control();
        return $mapper->getControls();
    }

    /**
     * @return Minder2_Model_Control
     */
    public function getSystemControls() {
        $controls = self::_getSavedProperty(self::SYSTEM_CONTROLS);

        if (null === $controls) {
            $controls = $this->_findSystemControls();
            $this->_saveSystemControls($controls);
        }

        return $controls;
    }

    /**
     * @return Minder2_Environment
     */
    public function dropSystemControlCache() {
        $this->_saveSystemControls(null);
        return $this;
    }

    /**
     * @param Minder2_Model_Control|null $controls
     * @return Minder2_Environment
     */
    protected function _saveSystemControls(Minder2_Model_Control $controls = null) {
        self::_saveProperty(self::SYSTEM_CONTROLS, $controls);
        return $this;
    }

    public function getPriceNumberFormat() {
        $numberFormat = $this->_getSavedProperty(self::PRICE_NUMBER_FORMAT);

        if (is_null($numberFormat)) {
            $numberFormat = '$#,###.00';
            $this->savePriceNumberFormat($numberFormat);
        }

        return $numberFormat;
    }

    public function savePriceNumberFormat($value = null) {
        $this->_saveProperty(self::PRICE_NUMBER_FORMAT, strval($value));
        return $this;
    }
}