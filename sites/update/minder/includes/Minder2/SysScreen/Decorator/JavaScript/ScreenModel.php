<?php

class Minder2_SysScreen_Decorator_JavaScript_ScreenModel extends Minder2_SysScreen_Decorator_JavaScript_Model {
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

        /**
         * @var Minder2_Model_SysScreen $sysScreen
         */
        $sysScreen = $this->_getModel();
        $autoloadHelper->appendScript('var ' . $this->_getModelVariableName() . ' = new ' . $this->_getJavaScriptModel() . '(' . json_encode($sysScreen->getFields()) . ');');
        $autoloadHelper->appendScript($this->_getJavaScriptModel() . '.registerScreenModel(' . $this->_getModelVariableName() . ', ' . ($sysScreen->hasCustomMasterSlaveHandler() ? 'false' : 'true') . ');');

        return $content;
    }

    protected function _getDefaultVariablePrefix()
    {
        return '';
    }


}