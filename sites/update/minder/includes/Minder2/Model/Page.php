<?php

/**
 * @property string $menuId
 *
 * @property boolean $SM_SHORTCUTS
 * @property boolean $SM_LIMIT
 * @property boolean $isLeftPannelVisible
 * @property boolean $SM_MENU_DISPLAY

 * @property string $SM_TITLE
 * @property string $SM_SUBMENU_ID
 * @property string $serviceUrl
 */
class Minder2_Model_Page extends Minder2_Model {

    const MESSAGES_NAMESPACE = 'MESSAGES_NAMESPACE';
    const ERRORS_NAMESPACE   = 'ERRORS_NAMESPACE';
    const WARNINGS_NAMESPACE = 'WARNINGS_NAMESPACE';

    protected $_screens   = array();
    /**
     * @var Zend_Controller_Action_Helper_FlashMessenger|null
     */
    protected $_messenger    = null;

    /**
     * @param array $fields
     * @return Minder2_Model_Page
     */
    function _setDbFields($fields)
    {
        parent::_setDbFields($fields);
        $this->isLeftPannelVisible = $this->SM_LIMIT || $this->SM_SHORTCUTS;
        return $this;
    }

    public function getScreenNames() {
        return array_map(function($screen){
            /**
             * @var Minder2_Model_SysScreen $screen
             */
            return $screen->SS_NAME;
        }, $this->getScreens());
    }

    public function getScreens() {
        return $this->_screens;
    }

    /**
     * @param Minder2_Model_Interface $a
     * @param Minder2_Model_Interface $b
     * @return int
     */
    protected function _sortCallback($a, $b) {
        return $a->getOrder() - $b->getOrder();
    }

    public function sortScreens() {
        return usort($this->_screens, array($this, '_sortCallback'));
    }

    /**
     * @param $screenName
     * @return null|Minder2_Model_SysScreen
     */
    public function getScreen($screenName) {
        /**
         * @var Minder2_Model_SysScreen $sysScreen
         */
        foreach ($this->_screens as $sysScreen) {
            if ($sysScreen->SS_NAME == $screenName)
                return $sysScreen;
        }

        return null;
    }

    public function addScreen(Minder2_Model_SysScreen $screen) {
        $this->_screens[$screen->SS_NAME] = $screen;
        return $this;
    }

    public function setScreens(array $screens) {
        /**
         * @var Minder2_Model_Interface $screen
         */
        foreach ($screens as $screen)
            $this->addScreen($screen);

        return $this;
    }

    function __get($name)
    {
        switch ($name) {
            case 'SM_SHORTCUTS':
            case 'SM_LIMIT':
            case 'SM_MENU_DISPLAY':
            case 'isLeftPannelVisible':
                return $this->_getBooleanFieldsValue($name);
            default:
                return $this->_getFieldValue($name);
        }
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'SM_SHORTCUTS':
            case 'SM_LIMIT':
            case 'SM_MENU_DISPLAY':
            case 'isLeftPannelVisible':
                return $this->_setBooleanFieldValue($name, $value);
        }
        return parent::__set($name, $value);
    }


    /**
     * @param bool $show
     * @return Minder2_Model_Page
     */
    function showShortcuts($show = true) {
        $this->SM_SHORTCUTS = $show;
        return $this;
    }

    /**
     * @return Minder2_Model_Page
     */
    function hideShortcuts() {
        $this->showShortcuts(false);
        return $this;
    }

    /**
     * @param bool $show
     * @return Minder2_Model_Page
     */
    function showLimits($show = true) {
        $this->SM_LIMIT = $show;
        return $this;
    }

    /**
     * @return Minder2_Model_Page
     */
    function hideLimits() {
        $this->showLimits(false);
        return $this;
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'pageModel_' . $this->SM_SUBMENU_ID;
    }

    /**
     * @return string
     */
    public function getStateId()
    {
        return 'MINDER_PAGE-' . $this->SM_SUBMENU_ID;
    }

    public function savePageState($fields) {
        $this->_restoreState();
        $this->setFields($fields);
        $this->_saveState();
    }

    /**
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    protected function _getMessenger() {
        if (is_null($this->_messenger))
            $this->_messenger = new Zend_Controller_Action_Helper_FlashMessenger();

        return $this->_messenger;
    }

    /**
     * @param string|array $messages
     * @param string $type
     * @return Minder2_Model_Page
     */
    public function addMessages($messages, $type = self::MESSAGES_NAMESPACE) {
        $messages = is_array($messages) ? $messages : array($messages);
        $this->_getMessenger()->setNamespace($type);

        foreach ($messages as $message)
            $this->_getMessenger()->addMessage($message);

        return $this;
    }

    /**
     * @param string|array $message
     * @return Minder2_Model_Page
     */
    public function addWarnings($message) {
        return $this->addMessages($message, self::WARNINGS_NAMESPACE);
    }

    /**
     * @param string|array $message
     * @return Minder2_Model_Page
     */
    public function addErrors($message) {
        return $this->addMessages($message, self::ERRORS_NAMESPACE);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getMessages($type = self::MESSAGES_NAMESPACE) {
        return $this->_getMessenger()->setNamespace($type)->getCurrentMessages();
    }

    /**
     * @return array
     */
    public function getWarnings() {
        return $this->getMessages(self::WARNINGS_NAMESPACE);
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->getMessages(self::ERRORS_NAMESPACE);
    }

    /**
     * @return array
     */
    public function getAllMessages() {
        return array(
            'messages' => $this->getMessages(),
            'warnings' => $this->getWarnings(),
            'errors'   => $this->getWarnings()
        );
    }

    /**
     * @param string $type
     * @return Minder2_Model_Page
     */
    public function clearMessages($type = self::MESSAGES_NAMESPACE) {
        $this->_getMessenger()->setNamespace($type)->clearMessages();
        return $this;
    }

    /**
     * @return Minder2_Model_Page
     */
    public function clearWarnings() {
        return $this->clearMessages(self::WARNINGS_NAMESPACE);
    }

    /**
     * @return Minder2_Model_Page
     */
    public function clearErrors() {
        return $this->clearMessages(self::ERRORS_NAMESPACE);
    }

    /**
     * @return Minder2_Model_Page
     */
    public function clearAllMessages() {
        return $this->clearMessages()->clearWarnings()->clearErrors();
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasMessages($type = self::MESSAGES_NAMESPACE) {
        return $this->_getMessenger()->setNamespace($type)->hasCurrentMessages();
    }

    /**
     * @return bool
     */
    public function hasWarnings() {
        return $this->hasMessages(self::WARNINGS_NAMESPACE);
    }

    /**
     * @return bool
     */
    public function hasErrors() {
        return $this->hasMessages(self::ERRORS_NAMESPACE);
    }

    /**
     * @return int
     */
    function getOrder()
    {
        return 0;
    }

    /**
     * @return Minder2_Model_SysUser
     */
    function getCurrentUser() {
        try {
            return Minder2_Environment::getCurrentUser();
        } catch (Exception $e) {
            $this->addErrors($e->getMessage());
        }

        return new Minder2_Model_SysUser();
    }

    /**
     * @return Minder2_Model_Warehouse
     */
    function getCurrentWarehouse() {
        try {
            return Minder2_Environment::getCurrentWarehouse();
        } catch (Exception $e) {
            $this->addErrors($e->getMessage());
        }

        return new Minder2_Model_Warehouse();
    }

    /**
     * @return Minder2_Model_SysEquip
     */
    function getCurrentDevice() {
        try {
            return Minder2_Environment::getCurrentDevice();
        } catch (Exception $e) {
            $this->addErrors($e->getMessage());
        }

        return new Minder2_Model_SysEquip();
    }

    /**
     * @return Minder2_Model_Company
     */
    function getCompanyLimit() {
        return Minder2_Environment::getCompanyLimit();
    }

    /**
     * @param Minder2_Model_Company|string $company
     * @return boolean
     */
    function setCompanyLimit($company) {
        Minder2_Environment::setCompanyLimit($company);
        return true;
    }

    /**
     * @return array(Minder2_Model_Company)
     */
    function getCompanyList() {
        return Minder2_Environment::getInstance()->getCompanyList();
    }

    /**
     * @return array(Minder2_Model_Warehouse)
     */
    function getWarehouseList() {
        return Minder2_Environment::getWarehouseList();
    }

    /**
     * @return Minder2_Model_Warehouse
     */
    function getWarehouseLimit() {
        return Minder2_Environment::getWarehouseLimit();
    }

    /**
     * @param Minder2_Model_Warehouse|string $warehouse
     * @return boolean
     */
    function setWarehouseLimit($warehouse) {
        Minder2_Environment::setWarehouseLimit($warehouse);
        return true;
    }

    /**
     * @return array
     */
    function getPrinterList() {
        return Minder2_Environment::getPrinterList();
    }

    /**
     * @return Minder2_Model_SysEquip
     */
    function getCurrentPrinter() {
        return Minder2_Environment::getCurrentPrinter();
    }

    /**
     * @param Minder2_Model_SysEquip | string $device
     * @return boolean
     */
    function setCurrentPrinter($device) {
        Minder2_Environment::setCurrentPrinter($device);
        return true;
    }
}