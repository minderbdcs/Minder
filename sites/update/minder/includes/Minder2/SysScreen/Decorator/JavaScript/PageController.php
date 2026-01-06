<?php

class Minder2_SysScreen_Decorator_JavaScript_PageController extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return 'pageController_';
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/page.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/pageController.js');

    }

    protected function _getShortcutsContainer() {
        return $this->getOption('shortcutsContainer');
    }

    protected function _getShortcutsSwitcher() {
        return $this->getOption('shortcutsSwitcher');
    }

    protected function _getPageContentContainer() {
        return $this->getOption('pageContentContainer');
    }

    protected function _getLeftPannelContainer() {
        return $this->getOption('leftPannelConatiner');
    }

    protected function _getModulesContainer() {
        return $this->getOption('modulesContainer');
    }

    protected function _getModulesSwitcher() {
        return $this->getOption('modulesSwitcher');
    }

    protected function _getShortModulesContainer() {
        return $this->getOption('shortModulesContainer');
    }


    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        $this->_includeRequiredLibraries();

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new Minder_PageController(' . $this->_getModelVariableName() . ');');

        $settings = array();

        $option = $this->_getLeftPannelContainer();
        if (!empty($option))
            $settings[] = "'\$_leftPannelConatiner' : " . $option;

        $option = $this->_getPageContentContainer();
        if (!empty($option))
            $settings[] = "'\$_pageContentContainer' : " . $option;

        $option = $this->_getShortcutsSwitcher();
        if (!empty($option))
            $settings[] = "'\$_shortcutsSwitcher' : " . $option;

        $option = $this->_getShortcutsContainer();
        if (!empty($option))
            $settings[] = "'\$_shortcutsContainer' : " . $option;

        $option = $this->_getModulesContainer();
        if (!empty($option))
            $settings[] = "'\$_modulesContainer' : " . $option;

        $option = $this->_getShortModulesContainer();
        if (!empty($option))
            $settings[] = "'\$_shortModulesContainer' : " . $option;

        $option = $this->_getModulesSwitcher();
        if (!empty($option))
            $settings[] = "'\$_modulesSwitcher' : " . $option;



        $autoloadHelper->appendScript($this->_getVariableName() . '.init({' . implode(', ', $settings)  . '});');

        return $content;
    }
}