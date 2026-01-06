<?php

class Minder2_Page_Builder {

    const SCREEN_BUILDER = 'SCREEN_BUILDER';
    const PAGE_BUILDER   = 'PAGE_BUILDER';

    static protected $_pluginLoaders = array();

    protected $_buildMenu      = true;
    protected $_buildShortcuts = true;
    protected $_buildSysScreens = true;
    protected $_buildDecorators = true;

//    protected function _getPageDescription($pageRecordId) {
//        $menuMapper = new Minder2_SysMenu_Mapper();
//        $unifiedMenu = $menuMapper->getSysMenu();
//
//        foreach ($unifiedMenu as $menuDesc) {
//            if ($menuDesc['RECORD_ID'] == $pageRecordId)
//                return $menuDesc;
//        }
//
//        return null;
//    }

    /**
     * @throws Minder_Exception
     * @param string $menuId
     * @return Minder2_Model_Page
     */
    public function build($menuId) {
        $menuManager = new Minder2_SysMenu_Manager();
        $pageDescription = $menuManager->findMenu($menuId);

        if (is_null($pageDescription))
            throw new Minder_Exception('Menu #' . $menuId . ' is not defined.');

        $page = new Minder2_Model_Page($pageDescription);
        $page->restoreState();
        $page->menuId           = $menuId;
        $page->serviceUrl = '/minder/dashboard/page/';

        if ($this->_buildDecorators)
            $page = $this->_doDecoratorsBuild($page);

        try{
            if ($this->_buildSysScreens)
                $page->setScreens($this->doSysScreenBuild($menuId));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e));
            $page->addErrors($e->getMessage());
        }
        return $page;
    }

    /**
     * @param Minder2_Model_Page $page
     * @return Minder2_Model_Page
     */
    protected function _doDecoratorsBuild($page) {
        $page->addPrefixPath('Minder2_SysScreen_Decorator_', 'Minder2/SysScreen/Decorator/', Minder2_Model_SysScreen::DECORATOR);
        $page->addPrefixPath('Minder2_SysScreen_Decorator_JavaScript_', 'Minder2/SysScreen/Decorator/JavaScript/', Minder2_Model_SysScreen::DECORATOR);
        $page->addDecorator('pageMessages', array('decorator' => 'PageMessages'));
        $page->addDecorator('pageTitle', array('decorator' => 'PageTitle'));
        $page->addDecorator('pageModel', array('decorator' => 'Model', 'javaScriptModel' => 'Minder_Model_PageRemote'));
        $page->addDecorator('pageController', array(
                                                   'decorator'            => 'PageController',
                                                   'shortcutsContainer'   => "$('#left .glossymenu')",
                                                   'shortcutsSwitcher'    => "$('.shortcutsSwitcher')",
                                                   'pageContentContainer' => "$('#page')",
                                                   'leftPannelConatiner'  => "$('#left')",
                                                   'modulesContainer' => '$("#modules")',
                                                   'modulesSwitcher' => "$('.modules-switcher')",
                                                   'shortModulesContainer' => "$('.short-module-container')"
                                              ));

        $page->addDecorator('exportModule', array('decorator' => 'exportModule'));
        $page->addDecorator('sysScreens', array('decorator' => 'SysScreens'));

        return $page;
    }

    public function disableMenuBuild() {
        $this->_buildMenu = false;
        return $this;
    }

    public function enableMenuBuild() {
        $this->_buildMenu = true;
        return $this;
    }

    public function doSysScreenBuild($menuId) {
        $sysScreens = array();

        foreach ($this->_getSysScreens($menuId) as $resultRow) {
            $tmpScreen = $this->getScreenBuilder($resultRow['SS_NAME'])->build($resultRow['SS_NAME']);

            if (!is_null($tmpScreen))
                $sysScreens[] = $tmpScreen;
        }

        return $sysScreens;
    }

    protected  function _getSysScreens($menuId) {
        $sql = "
            SELECT DISTINCT
                SS_NAME
            FROM
                SYS_SCREEN
            WHERE
                SS_MENU_ID = ?
        ";

        if (false === ($result = Minder::getInstance()->fetchAllAssoc($sql, $menuId)))
            return array();

        return $result;
    }

    /**
     * @param Zend_Navigation_Container $menu
     * @param array $menuRoots
     * @param array $menuMap
     * @return Zend_Navigation_Container
     */
    protected function _buildSubMenu($menu, &$menuRoots, &$menuMap) {
        foreach ($menuRoots as $menuDesc) {
            $pageConfig = $menuDesc;
            $pageConfig['uri'] = $menuDesc['SM_MENU_ACTION'];

            $page = Zend_Navigation_Page::factory($pageConfig);

            if (isset($menuMap[$menuDesc['SM_SUBMENU_ID']]) && !empty($menuMap[$menuDesc['SM_SUBMENU_ID']])) {
                $page = $this->_buildSubMenu($page, $menuMap[$menuDesc['SM_SUBMENU_ID']], $menuMap);
            }

            $menu->addPage($page);
        }

        return $menu;
    }

//    public function doMenuBuild() {
//        $sysMenuMapper = new Minder2_SysMenu_Mapper();
//        return $this->_buildSubMenu(new Zend_Navigation(null), $sysMenuMapper->getSysMenuRoots(), $sysMenuMapper->getSysMenuMap());
//    }

    /**
     * @throws Minder_Exception
     * @param $prefix
     * @param $path
     * @param $type
     * @return void
     */
    public static function addPrefixPath($prefix, $path, $type) {
        $type = strtoupper($type);
        switch ($type) {
            case self::SCREEN_BUILDER:
            case self::PAGE_BUILDER:
                $loader = self::_getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
            default:
                throw new Minder_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    static protected function _getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset(self::$_pluginLoaders[$type])) {
            switch ($type) {
                case self::SCREEN_BUILDER:
                    self::$_pluginLoaders[$type] = new Zend_Loader_PluginLoader(
                        array('Minder2_SysScreen_Builder_' => 'Minder2/SysScreen/Builder/')
                    );
                    break;
                case self::PAGE_BUILDER:
                    self::$_pluginLoaders[$type] = new Zend_Loader_PluginLoader(
                        array('Minder2_Page_Builder_' => 'Minder2/Page/Builder/')
                    );
                    break;
                default:
                    throw new Minder_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }

        }

        return self::$_pluginLoaders[$type];
    }

    static protected function _formatBuilderClassName($ssName) {
        $nameArray = explode('_', strtolower($ssName));

        foreach ($nameArray as &$namePart)
            $namePart = ucfirst($namePart);

        return implode('', $nameArray);
    }

    /**
     * @param string $ssName
     * @return Minder2_SysScreen_Builder_Interface
     */
    protected function getScreenBuilder($ssName) {
        $loader = $this->_getPluginLoader(self::SCREEN_BUILDER);
        $builderClass = $loader->load($this->_formatBuilderClassName($ssName), false);

        if (false === $builderClass)
            $builderClass = $loader->load('Default');

        return new $builderClass();
    }

    public static function getPageBuilder($menuId) {
        $loader = self::_getPluginLoader(self::PAGE_BUILDER);
        $builderClass = $loader->load(self::_formatBuilderClassName($menuId), false);

        if (false === $builderClass)
            return new Minder2_Page_Builder();

        return new $builderClass();
    }
}