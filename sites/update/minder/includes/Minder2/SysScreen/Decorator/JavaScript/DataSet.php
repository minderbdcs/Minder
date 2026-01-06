<?php

class Minder2_SysScreen_Decorator_JavaScript_DataSet extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    const DATA_SET_VAR_PREFIX = 'dataSet_';

    protected function _getDefaultVariablePrefix()
    {
        return self::DATA_SET_VAR_PREFIX;
    }

    protected function _getData() {
        return $this->getOption('data');
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
        $autoloadHelper->appendScript('var ' . $this->_getVariableName() . ' = new Minder_Model_DataSet(' . json_encode($this->_getData()) . ');');

        return $content;
    }


}