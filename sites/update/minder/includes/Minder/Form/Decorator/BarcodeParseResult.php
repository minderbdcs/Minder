<?php

class Minder_Form_Decorator_BarcodeParseResult extends Zend_Form_Decorator_Abstract {
    public function render($content) {
        $element = $this->getElement();

        if (empty($element))
            return $content;

        $view = $element->getView();
        if (empty($view))
            return $content;

        $messageContainerId = $this->getOption('messageContainerId');

        if (empty($messageContainerId))
            return $content;

        $elementId = $element->getId();

        $helper = $view->autoloadScript();

        // query SYS_SCREEN_VAR
        $str =  "$('#" . $view->escape($elementId) . "').bind('parse-success', function(evt) {
            $('#" . $view->escape($messageContainerId) . "').text(evt.parseResult.paramDesc.param_name + ': ' + evt.parseResult.paramDesc.param_filtered_value);
        })";

        $helper->appendScript($str);
        return $content;
    }
}
