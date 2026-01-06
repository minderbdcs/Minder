<?php

class Minder_Form_Decorator_EventHandler extends Zend_Form_Decorator_Abstract {
    /**
     * @return Minder2_View_Helper_AutoloadScript
     */
    protected function _getAutoLoadScriptHelper() {
        return $this->getElement()->getView()->autoloadScript();
    }

    public function render($content)
    {
        $handlers = $this->_getHandlers();

        if (empty($handlers))
            return $content;

        $autoloadScript = $this->_getAutoLoadScriptHelper();

        foreach ($handlers as $handlerName => $eventHandlers) {
            foreach ($eventHandlers as $handlerBody)
                $autoloadScript->appendScript(
                    "Minder2.Registry.registerHandler('" . $handlerName . "', function(evt){" . $handlerBody . "});"
                );
        }

        return $content;
    }

    private function _getHandlers()
    {
        $element = $this->getElement();

        if (empty($element))
            return array();

        $handlers = $element->getAttrib('handlers');
        $element->setAttrib('handlers', null);
        return $handlers;
    }

}