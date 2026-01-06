<?php

class Minder2_SysScreen_Decorator_JavaScript_MultyElement extends Minder2_SysScreen_Decorator_JavaScript_ViewElement {
    
    protected function _getOptionsDatasetVariable() {
        return $this->getOption('optionsDatasetVariable');
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/container.js');
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/Minder/View/multy.js');
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
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new ' . $this->_getElementJavaScriptClass() . '("' . $this->_getName() . '", ' . $this->_getModelVariableName() . ',' . $this->_getTemplate() . ', ' . json_encode($this->getOption('settings')) . ');');
        $autoloadHelper->appendScript($this->_getVariableName() . '.setOptionsDataset(' . $this->_getOptionsDatasetVariable() . ');');

        return $content;
    }

}