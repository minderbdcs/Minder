<?php

class Minder2_SysScreen_Decorator_JavaScript_Model extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    const MODEL_VAR_PREFIX = 'model_';

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/model.js');

        switch ($this->_getJavaScriptModel()) {
            case 'Minder_Model_Page':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/page.js');
                break;
            case 'Minder_Model_PageRemote':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/page.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/pageRemote.js');
                break;
            case 'Minder_Model_SysScreen':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/page.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/dataSet.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/search.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/sysScreen.js');
                break;
            case 'Minder_Model_ChartScreen':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/page.js');
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/chartScreen.js');
                break;
        }
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
        $autoloadHelper->appendScript('var ' . $this->_getModelVariableName() . ' = new ' . $this->_getJavaScriptModel() . '(' . json_encode($this->_getModel()->getFields()) . ');');

        return $content;
    }


    protected function _getDefaultVariablePrefix()
    {
        return self::MODEL_VAR_PREFIX;
    }

}