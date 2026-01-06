<?php

class Minder2_SysScreen_Decorator_JavaScript_Render extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return '';
    }

    protected function _getPlacement() {
        return $this->getOption('placement');
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
        $autoloadHelper->appendScript($this->_getVariableName() . '.render(' . $this->_getPlacement() . ');');

        return $content;
    }


}