<?php

class Minder_Form_Decorator_FormPage extends Zend_Form_Decorator_Abstract {

    public function render($content)
    {
        return '<div class="ui-tabs-nav ui-tabs-panel" id="' . $this->getElement()->getName() . '">' . $content . '</div>';
    }

}