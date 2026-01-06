<?php

class Minder_Page_FormController_Builder {
    protected static $_loaders = array();

    /**
     * @param string $formType
     * @return Zend_Loader_PluginLoader
     */
    protected function _getLoader($formType) {
        if (isset(self::$_loaders[$formType]))
            return self::$_loaders[$formType];

        switch ($formType) {
            case Minder_Page::FORM_TYPE_EDIT_FORM:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormController_EditForm' => 'Minder/Page/FormController/EditForm'));
                break;
            case Minder_Page::FORM_TYPE_SEARCH_RESULT:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormController_SearchResult' => 'Minder/Page/FormController/SearchResult'));
                break;
            default:
                throw new Exception('Bad form type: ' . $formType);
        }

        return self::$_loaders[$formType];
    }

    protected function _formatClassName($sysScreen) {
        return Minder_Page::formatSysScreenClassName($sysScreen);
    }

    protected function _loadPageControllerClass($sysScreen, $formType) {
        $loader = $this->_getLoader($formType);

        $class = $loader->load($this->_formatClassName($sysScreen), false);

        if (false === $class)
            $class = $loader->load('Default');

        return new $class($sysScreen, $formType);
    }

    public function build($sysScreen, $formType) {
        return $this->_loadPageControllerClass($sysScreen, $formType);
    }
}