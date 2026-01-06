<?php

class Minder2_SysScreen_Decorator_JavaScript_AddSubView extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return '';
    }

    protected function _getSubViewVariableName() {
        return $this->getOption('subViewVariable');
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
        $autoloadHelper->appendScript($this->_getVariableName() . '.addSubView(' . $this->_getSubViewVariableName() . ');');

        return $content;
    }


}