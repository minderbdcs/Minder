<?php

class Minder_Page_FormBuilder {

    protected static $_formBuilderLoaders = array();

    /**
     * @param string $formType
     * @return Zend_Loader_PluginLoader
     * @throws Exception
     */
    protected function _getFormBuilderLoader($formType) {

        if (isset(self::$_formBuilderLoaders[$formType]))
            return self::$_formBuilderLoaders[$formType];

        switch ($formType) {
            case Minder_Page::FORM_TYPE_SEARCH_RESULT:
                self::$_formBuilderLoaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormBuilder_SearchResults' => 'Minder/Page/FormBuilder/SearchResults'));
                break;
            case Minder_Page::FORM_TYPE_SEARCH_FORM:
                self::$_formBuilderLoaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormBuilder_SearchForm' => 'Minder/Page/FormBuilder/SearchForm'));
                break;
            case Minder_Page::FORM_TYPE_EDIT_FORM:
                self::$_formBuilderLoaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormBuilder_EditForm' => 'Minder/Page/FormBuilder/EditForm'));
                break;
            case Minder_Page::FORM_TYPE_NEW_FORM:
                self::$_formBuilderLoaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormBuilder_NewForm' => 'Minder/Page/FormBuilder/NewForm'));
                break;
            default:
                throw new Exception('Bad formType ' . $formType);
        }

        return self::$_formBuilderLoaders[$formType];
    }

    /**
     * @param string $sysScreenName
     * @return string
     */
    protected function _formatClassName($sysScreenName) {
        $parts = explode('_', strtolower($sysScreenName));
        foreach ($parts as &$part)
            ucfirst($part);
        return implode('', $parts);
    }

    /**
     * @param string $sysScreenName
     * @param string $formType
     * @return Minder_Page_FormBuilder_Interface
     */
    protected function _getConcreteBuilder($sysScreenName, $formType) {
        $builderLoader = self::_getFormBuilderLoader($formType);

        $className = $builderLoader->load($this->_formatClassName($sysScreenName), false);

        if (false === $className)
            $className = $builderLoader->load('Default');

        return new $className($sysScreenName);
    }

    public function build($sysScreenName, $formType) {
        return $this->_getConcreteBuilder($sysScreenName, $formType)->build($sysScreenName);
    }

    public function buildEditForm($sysScreenName) {
        return $this->build($sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM);
    }
}