<?php

class Minder2_SysScreen_Decorator_JavaScript_DefaultContainer extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    protected function _getDefaultVariablePrefix()
    {
        return '';
    }

    protected function _getContainerId() {
        return $this->getOption('containerId');
    }

    public function render($content)
    {

        $content .= "
            <div id=\"" . $this->_getContainerId() . "\">
                <span></span>
            </div>
        ";

        return $content;
    }


}