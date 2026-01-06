<?php

class Minder2_SysMenu_Manager {

    const MENU_TYPE_TOP      = 'TOP';
    const MENU_TYPE_LEFT     = 'LEFT';

    const _SUB_MENU_KEY      = '_SUB_MENU';

    public function getRenderLimitsFlag($activeMenuId) {
        foreach ($this->_getMenuTree() as $menuDescription) {

            if (!isset($menuDescription['SM_SUBMENU_ID']))
                continue;

            if ($menuDescription['SM_SUBMENU_ID'] !== $activeMenuId)
                continue;

            if (empty($menuDescription['SM_LIMIT']))
                return true;

            return strtoupper($menuDescription['SM_LIMIT']) == 'T';
        }

        return true;
    }

    /**
     * @param string $menuType
     * @param string $activeMenuId
     * @return Zend_Navigation
     */
    public function getNavigation($menuType, $activeMenuId) {
        switch ($menuType) {
            case self::MENU_TYPE_TOP:
                return $this->_getTopMenuNavigation($activeMenuId);
            case self::MENU_TYPE_LEFT:
                return $this->_getLeftMenuNavigation($activeMenuId);
        }

        return new Zend_Navigation();
    }

    /**
     * @param string $activeMenuId
     * @return Zend_Navigation
     */
    protected function _getTopMenuNavigation($activeMenuId) {
        //todo: add caching support
        return $this->_buildTopMenuNavigation($activeMenuId);
    }

    /**
     * @param string $activeMenuId
     * @return Zend_Navigation
     */
    protected function _buildTopMenuNavigation($activeMenuId) {
        $navigationContainer = new Zend_Navigation();
        foreach ($this->_getMenuTree() as $menuDescription) {
            if ($menuDescription['SM_MENU_TYPE'] != self::MENU_TYPE_TOP)
                continue;

            $menuDescription['uri'] = $menuDescription['SM_MENU_ACTION'];
            $menuDescription['label'] = $menuDescription['SM_TITLE'];
            $menuDescription['order'] = $menuDescription['SM_SEQUENCE'];
            $navigationContainer->addPage($menuDescription);
        }

        $activeTopMenuId = $this->_getActiveTopMenu($activeMenuId);

        $activePage = $navigationContainer->findBy('SM_SUBMENU_ID', $activeTopMenuId);

        if (!is_null($activePage))
            $activePage->setActive();

        if ($navigationContainer->count() < 1) {
            if ($this->_getEnvironment()->getCurrentUser()->isAdmin()) {
                $navigationContainer->addPage(array(
                    'uri' => 'admin',
                    'label' => 'Administration',
                    'order' => 999
                ));
            }
        }

        return $navigationContainer;
    }

    /**
     * @param string $activeMenuId
     * @return Zend_Navigation
     */
    protected function _getLeftMenuNavigation($activeMenuId) {
        //todo: add caching support
        return $this->_buildLeftMenuNavigation($activeMenuId);
    }

    /**
     * @param Zend_Navigation $container
     * @param array $menuTree
     * @param string $rootMenuId
     * @return Zend_Navigation
     */
    protected function _fillNavigationContainer($container, &$menuTree, $rootMenuId) {
        foreach ($menuTree[$rootMenuId][self::_SUB_MENU_KEY] as $menuId) {
            if (!isset($menuTree[$menuId]))
                continue;

            $menuDesc = $menuTree[$menuId];
            if (!isset($menuDesc['RECORD_ID']))
                continue;

            $menuDesc['uri']   = $menuDesc['SM_MENU_ACTION'];
            $menuDesc['label'] = $menuDesc['SM_TITLE'];
            $menuDesc['order'] = $menuDesc['SM_SEQUENCE'];
            $page = new Zend_Navigation_Page_Uri($menuDesc);

            if (isset($menuDesc[self::_SUB_MENU_KEY]) && !empty($menuDesc[self::_SUB_MENU_KEY]))
                $page = $this->_fillNavigationContainer($page, $menuTree, $menuId);

            $container->addPage($page);
        }

        return $container;
    }

    /**
     * @param string $activeMenuId
     * @return Zend_Navigation
     */
    protected function _buildLeftMenuNavigation($activeMenuId) {
        $activeTopMenuId = $this->_getActiveTopMenu($activeMenuId);

        $container = new Zend_Navigation();
        $menuTree  = $this->_getMenuTree();

        if (!isset($menuTree[$activeTopMenuId]))
            return $container;

        if (!isset($menuTree[$activeTopMenuId][self::_SUB_MENU_KEY]))
            return $container;

        if (empty($menuTree[$activeTopMenuId][self::_SUB_MENU_KEY]))
            return $container;

        return $this->_fillNavigationContainer($container, $this->_getMenuTree(), $activeTopMenuId);
    }

    

    protected function _getActiveTopMenu($activeMenuId) {
        $menuTree = $this->_getMenuTree();

        $currentMenuId = $activeMenuId;
        while (isset($menuTree[$currentMenuId])) {
            if ($menuTree[$currentMenuId]['SM_MENU_TYPE'] == self::MENU_TYPE_TOP)
                return $currentMenuId;

            $currentMenuId = $menuTree[$currentMenuId]['SM_MENU_ID'];
        }

        return null;
    }

    protected  function _getMenuTree() {
        $menuTree = $this->_getCachedTree();

        if (is_null($menuTree)) {
            $menuTree = $this->_buildMenuTree();
        }

        return $menuTree;
    }

    public function findMenu($menuId) {
        $menuTree = $this->_getMenuTree();

        if (isset($menuTree[$menuId])) return $menuTree[$menuId];

        return null;
    }

    /**
     * @return array
     */
    protected function _buildMenuTree() {
        $sysMenu = $this->_getSysMenu();
        $menuTree = array();

        foreach ($sysMenu as $menuDescription) {
            $tmpMenuId    = $menuDescription['SM_SUBMENU_ID'];
            $parentMenuId = $menuDescription['SM_MENU_ID'];

            if (isset($menuTree[$tmpMenuId])) {
                $menuTree[$tmpMenuId] = array_merge($menuTree[$tmpMenuId], $menuDescription);
            } else {
                $menuTree[$tmpMenuId] = $menuDescription;
                $menuTree[$tmpMenuId][self::_SUB_MENU_KEY] = array();
            }


            if (!empty($parentMenuId)) {
                if (!isset($menuTree[$parentMenuId])) {
                    $menuTree[$parentMenuId] = array(self::_SUB_MENU_KEY => array());
                }
                $menuTree[$parentMenuId][self::_SUB_MENU_KEY][] = $tmpMenuId;
            }
        }

        return $menuTree;
    }

    protected function _getSysMenu() {
        $menuPartBuilder = new Minder_SysScreen_PartBuilder_SysMenu('SYS_MENU');
        return $menuPartBuilder->build();
    }
    
    /**
     * @return null | array
     */
    protected function _getCachedTree() {
        //todo: implement tree caching
        return null;
    }

    private function _getEnvironment()
    {
        return Minder2_Environment::getInstance();
    }
}