<?php

use MinderNG\PageMicrocode\MicrocodeCache;

class Minder_Controller_Action_Helper_Cache extends Zend_Controller_Action_Helper_Abstract {
    /** @var MicrocodeCache */
    private $_microcodeCache;

    public function clearAll() {
        $this->_microcodeCache()->cleanSessionCache();
        Minder_SysScreen_Builder::dropScreenDescriptionsCache();
        Minder2_Environment::getInstance()->dropSystemControlCache();
        Minder_SysScreen_Legacy_ControlManager::flushData();
        Minder_SysScreen_Legacy_OptionManager::flushData();
        Minder_SysScreen_Legacy_OrderManager::flushData();
        Minder_SysScreen_Legacy_SysEquipManager::flushData();
        Minder_SysScreen_Legacy_TableManager::flushData();
        Minder_SysScreen_Legacy_TabManager::flushData();
        Minder_SysScreen_Legacy_VarManager::flushData();
    }

    public function clear($tableName) {
        $tableName = strtoupper($tableName);

        if (in_array($tableName, Minder_SysScreen_Builder::getSysScreenTableList())) {
            Minder_SysScreen_Builder::dropScreenDescriptionsCache();
            $this->_microcodeCache()->cleanSessionCache();
        }

        if ($tableName == 'CONTROL') {
            Minder2_Environment::getInstance()->dropSystemControlCache();
            Minder_SysScreen_Legacy_ControlManager::flushData();
        }

        if ($tableName == 'OPTIONS') {
            Minder_SysScreen_Legacy_OptionManager::flushData();
            Minder_SysScreen_Builder::dropScreenDescriptionsCache();
        }

        if ($tableName == 'SYS_SCREEN_ORDER') {
            Minder_SysScreen_Legacy_OrderManager::flushData();
        }

        if ($tableName == 'SYS_EQUIP') {
            Minder_SysScreen_Legacy_SysEquipManager::flushData();
        }

        if ($tableName == 'SYS_SCREEN_TABLE') {
            Minder_SysScreen_Legacy_TableManager::flushData();
        }

        if ($tableName == 'SYS_SCREEN_TAB') {
            Minder_SysScreen_Legacy_TabManager::flushData();
        }

        if ($tableName == 'SYS_SCREEN_VAR') {
            Minder_SysScreen_Legacy_VarManager::flushData();
        }
    }

    public function dropScreenDescriptionCache() {
        Minder_SysScreen_Builder::dropScreenDescriptionsCache();
        $this->_microcodeCache()->cleanSessionCache();
    }

    /**
     * @return MicrocodeCache
     * @throws Exception
     */
    private function _microcodeCache() {
        if (empty($this->_microcodeCache)) {
            Zend_Db_Table::setDefaultAdapter(Minder::getDefaultDbAdapter());
            $this->_microcodeCache = $this->_diContainer()->create(MicrocodeCache::CLASS_NAME);
        }

        return $this->_microcodeCache;
    }

    /**
     * @return \MinderNG\Di\DiContainer
     * @throws Zend_Exception
     */
    private function _diContainer() {
        return Zend_Registry::get('dice');
    }
}