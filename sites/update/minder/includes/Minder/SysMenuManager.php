<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 01.12.11
 * Time: 15:56
 * To change this template use File | Settings | File Templates.
 */

class Minder_SysMenuManager {

    const MENU_TYPE_TOP      = 'TOP';
    const MENU_TYPE_LEFT     = 'LEFT';

    const _SUB_MENU_KEY      = '_SUB_MENU';

    protected $_baseUrl = null;

    public function getNavigation($menuType, $activeMenuId)
    {
        switch ($menuType) {
            case self::MENU_TYPE_TOP:
                return $this->_getTopMenuNavigation($activeMenuId);
            case self::MENU_TYPE_LEFT:
                return $this->_getLeftMenuNavigation($activeMenuId);
        }

        return array();
    }

    protected function _getTopMenuNavigation($activeMenuId)
    {
        return $this->_buildTopMenuNavigation($activeMenuId);
    }

    protected function _getBaseUrl() {
        if (is_null($this->_baseUrl)) {
            $view = new Zend_View();
            $this->_baseUrl = $view->baseUrl();
        }

        return $this->_baseUrl;
    }

    protected function _sortCallback($menuItemA, $menuItemB) {
        return $menuItemA['SM_SEQUENCE'] - $menuItemB['SM_SEQUENCE'];
    }

    protected function _getTopMenuItems() {
        $result = array();

        foreach ($this->_getMenuTree() as $menuDesc) {
            if (isset($menuDesc['SM_MENU_TYPE']) && $menuDesc['SM_MENU_TYPE'] == self::MENU_TYPE_TOP) {
                $result[] = $menuDesc;
            }
        }

        usort($result, array($this, '_sortCallback'));

        return $result;
    }

    protected function _formatMenuUrl($menuDescription) {
        $urlParts = explode('?', $menuDescription['SM_MENU_ACTION']);

        $params = isset($urlParts[1]) ? explode('&', $urlParts[1]) : array();
        array_push($params, 'menuId=' . urlencode($menuDescription['SM_SUBMENU_ID']));
        $urlParts[1] = implode('&', $params);

        return $this->_getBaseUrl() . implode('?', $urlParts);
    }

    protected function _buildTopMenuNavigation($activeMenuId)
    {
        $container = array();
        foreach ($this->_getTopMenuItems() as $menuDesc) {
            $menuElement = array($menuDesc['SM_TITLE'] => $this->_formatMenuUrl($menuDesc));
            $container += $menuElement;
        }

        /*if (empty($container)) {
            if ($this->_getEnvironment()->getCurrentUser()->isAdmin())
                $container['Administration'] = $this->_getBaseUrl() . '/admin';
        }*/

        // changed due to #8426

        if ($this->_getEnvironment()->getCurrentUser()->isAdmin())
            $container['Administration'] = $this->_getBaseUrl() . '/admin';

        return $container;
    }

    protected function _getLeftMenuNavigation($activeMenuId)
    {
        return $this->_buildLeftMenuNavigation($activeMenuId);
    }

    protected function _getLeftMenuItems($menuTree, $rootMenuId) {
        $result = array();

        foreach ($menuTree[$rootMenuId][self::_SUB_MENU_KEY] as $menuId) {
            if (!isset($menuTree[$menuId]))
                continue;

            $menuDesc = $menuTree[$menuId];
            if (!isset($menuDesc['RECORD_ID']))
                continue;

            $result[] = $menuDesc;
        }

        usort($result, array($this, '_sortCallback'));

        return $result;
    }

    protected function _fillNavigationContainer($container, &$menuTree, $rootMenuId, $activeMenuId)
    {
        foreach ($this->_getLeftMenuItems($menuTree, $rootMenuId) as $menuDesc) {
            $title = ($activeMenuId == $menuDesc['SM_SUBMENU_ID']) ? '<'.$menuDesc['SM_TITLE'].'>' : $menuDesc['SM_TITLE'];

            $menuElement = array($title => $this->_formatMenuUrl($menuDesc));

            $node = array();

            if (isset($menuDesc[self::_SUB_MENU_KEY]) && !empty($menuDesc[self::_SUB_MENU_KEY])) {
                foreach ($this->_getLeftMenuItems($menuTree, $menuDesc['SM_SUBMENU_ID']) as $subMenuDesc) {
                   if ($menuDesc['SM_SUBMENU_ID'] == $subMenuDesc['SM_SUBMENU_ID']) {
                       $node += array($title => $menuElement);
                   } else {
                       ($subMenuDesc['SM_SUBMENU_ID'] == $activeMenuId) ?
                       $menuElement = array('<'.$subMenuDesc['SM_TITLE'].'>' => $this->_formatMenuUrl($subMenuDesc)) :
                       $menuElement = array($subMenuDesc['SM_TITLE'] => $this->_formatMenuUrl($subMenuDesc));

                       $node += $menuElement;
                   }
                }
                $container[$title] = $node;
            } else {
                $container += $menuElement;
            }

        }

        return $container;
    }

    protected function _buildLeftMenuNavigation($activeMenuId)
    {
        $container = array();

        $menuTree  = $this->_getMenuTree();

        $activeTopMenuId = $this->_getActiveTopMenu($activeMenuId);

        if (!isset($menuTree[$activeMenuId]))
            return $container;

        /*if (!isset($menuTree[$activeMenuId][self::_SUB_MENU_KEY]))
            return $container;

        if (empty($menuTree[$activeMenuId][self::_SUB_MENU_KEY]))
            return $container;*/

        return $this->_fillNavigationContainer($container,
                                               $this->_getMenuTree(),
                                               $activeTopMenuId,
                                               $activeMenuId);
    }

    protected function _getActiveTopMenu($activeMenuId)
    {
        $menuTree = $this->_getMenuTree();

        $currentMenuId = $activeMenuId;
        while (isset($menuTree[$currentMenuId])) {
            if ($menuTree[$currentMenuId]['SM_MENU_TYPE'] == self::MENU_TYPE_TOP)
                return $currentMenuId;

            $currentMenuId = $menuTree[$currentMenuId]['SM_MENU_ID'];
        }

        return null;
    }

    protected function _getMenuTree()
    {
        $menuTree = $this->_getCachedTree();

        if (is_null($menuTree)) {
            $menuTree = $this->_buildMenuTree();
        }

        return $menuTree;
    }

    public function findMenu($menuId) {}

    protected function _buildMenuTree()
    {
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
                    $menuTree[$parentMenuId] = array('SM_SUBMENU_ID' => $parentMenuId, self::_SUB_MENU_KEY => array());
                }
                $menuTree[$parentMenuId][self::_SUB_MENU_KEY][] = $tmpMenuId;
            }
        }

        return $menuTree;
    }

    protected function _getSysMenu()
    {
        $menuPartBuilder = new Minder_SysScreen_PartBuilder_SysMenu('SYS_MENU');
        return $menuPartBuilder->build();
    }

    protected function _getCachedTree() {}

    private function _getEnvironment()
    {
        return Minder2_Environment::getInstance();
    }

}
