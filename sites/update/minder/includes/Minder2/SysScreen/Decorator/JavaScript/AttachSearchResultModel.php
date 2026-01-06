<?php

class Minder2_SysScreen_Decorator_JavaScript_AttachSearchResultModel extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return '';
    }

    protected function _getPlacement() {
        return $this->getOption('placement');
    }

    public function getSearchResultModelVariableName() {
        return $this->getOption('searchResultModelVariableName');
    }

    protected function _getDefaultSearchMethod() {
        $result = "function(evt) {
            if (typeof " . $this->getSearchResultModelVariableName() ." == 'undefined')
                return;

            if (typeof evt.sender == 'undefined')
                return;

            " . $this->getSearchResultModelVariableName() .".makeSearch(evt.sender.getSearchFields());
        }";

        return $result;
    }

    protected function _getSearchMethodName() {
        $method = $this->getOption('searchMethod');
        return empty($method) ? $this->_getDefaultSearchMethod() : $method;
    }

    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();
        $autoloadHelper->appendScript('$(' . $this->_getVariableName() . ').bind("search", ' . $this->_getSearchMethodName() . ');');

        return $content;
    }


}