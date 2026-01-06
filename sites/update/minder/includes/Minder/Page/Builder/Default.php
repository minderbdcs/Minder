<?php

class Minder_Page_Builder_Default {
    protected $_menuId = '';

    function __construct($_menuId)
    {
        $this->_menuId = $_menuId;
    }

    protected function _getMenuId() {
        if (empty($this->_menuId))
            throw new Exception('_menuId is empty.');

        return $this->_menuId;
    }

    protected function _getSysMenuMapper() {
        return new Minder_Page_Mapper_SysMenu();
    }

    protected function _getEnvironment() {
        return Minder2_Environment::getInstance();
    }

    /**
     * @return null|Zend_Db_Table_Row
     */
    protected function _getMenuEntry() {
        return $this->_getSysMenuMapper()->find($this->_getMenuId(), $this->_getEnvironment());
    }

    /**
     * @param Zend_Db_Table_Row $menuEntry
     * @return Minder_Page_Config
     */
    protected function _build($menuEntry) {
        $result = new Minder_Page_Config(array('options' => $menuEntry->toArray()));

        return $result;
    }

    public function build() {
        $menuEntry = $this->_getMenuEntry();
        if (is_null($menuEntry))
            throw new Exception('Page not found: ' . $this->_getMenuId());

        return $this->_build($menuEntry);
    }
}