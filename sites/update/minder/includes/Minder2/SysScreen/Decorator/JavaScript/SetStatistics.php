<?php

class Minder2_SysScreen_Decorator_JavaScript_SetStatistics extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    public function render($content)
    {
        $view = $this->_getView();

        if (is_null($view))
            return $content;

        /**
         * @var Minder2_View_Helper_AutoloadScript $autoloadHelper
         */
        $autoloadHelper = $view->autoloadScript();
        $statistics     = $this->_getModel()->getStatistics();

        if (!empty($statistics))
            $autoloadHelper->appendScript($this->_getModelVariableName() . '.setFields(' . json_encode($statistics) . ');');

        return $content;
    }

    protected function _getDefaultVariablePrefix()
    {
        return '';
    }


}