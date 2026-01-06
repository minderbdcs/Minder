<?php

class Minder2_SysScreen_Decorator_JavaScript_SearchModel extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return 'searchModel_';
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/model.js');

        switch ($this->_getJavaScriptModel()) {
            case 'Minder_Model_Search':
                $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/Model/search.js');
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
        $autoloadHelper->appendScript('var ' . $this->_getModelVariableName() . ' = new ' . $this->_getJavaScriptModel() . '(' . json_encode($this->_getModel()->getSearchFields()) . ');');

        return $content;
    }


}