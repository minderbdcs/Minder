<?php

class Minder_Page_FormFiller {

    protected static $_loaders = array();

    /**
     * @param string $formType
     * @return Zend_Loader_PluginLoader
     * @throws Exception
     */
    protected function _getFormFillerLoader($formType) {
        if (isset(self::$_loaders[$formType]))
            return self::$_loaders[$formType];

        switch ($formType) {
            case Minder_Page::FORM_TYPE_SEARCH_RESULT:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormFiller_SearchResults' => 'Minder/Page/FormFiller/SearchResults'));
                break;
            case Minder_Page::FORM_TYPE_SEARCH_FORM:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormFiller_SearchForm' => 'Minder/Page/FormFiller/SearchForm'));
                break;
            case Minder_Page::FORM_TYPE_EDIT_FORM:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormFiller_EditForm' => 'Minder/Page/FormFiller/EditForm'));
                break;
            case Minder_Page::FORM_TYPE_NEW_FORM:
                self::$_loaders[$formType] = new Zend_Loader_PluginLoader(array('Minder_Page_FormFiller_NewForm' => 'Minder/Page/FormFiller/NewForm'));
                break;
            default:
                throw new Exception('Bad formType ' . $formType);
        }

        return self::$_loaders[$formType];
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
     * @return Minder_Page_FormFiller_Interface
     */
    protected function _getConcreteFiller($sysScreenName, $formType) {
        $fillerLoader = $this->_getFormFillerLoader($formType);

        $className = $fillerLoader->load($this->_formatClassName($sysScreenName), false);

        if (false === $className)
            $className = $fillerLoader->load('Default');

        return new $className($sysScreenName);
    }

    /**
     * @param Zend_Form $form
     * @param string $sysScreenName
     * @param string $formType
     * @return Zend_Form
     */
    function fillDefaults(Zend_Form $form, $sysScreenName, $formType) {
        return $this->_getConcreteFiller($sysScreenName, $formType)->fillDefaults($form);
    }

    /**
     * @param Zend_Form $form
     * @param string $sysScreenName
     * @param string $formType
     * @return Zend_Form
     */
    function fillMultiOptions(Zend_Form $form, $sysScreenName, $formType) {
        return $this->_getConcreteFiller($sysScreenName, $formType)->fillMultiOptions($form);
    }

    function getMultiOptions(Zend_Form $form, $sysScreenName, $formType) {
        return $this->_getConcreteFiller($sysScreenName, $formType)->getMultiOptions($form);
    }
}