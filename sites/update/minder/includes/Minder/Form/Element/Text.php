<?php

class Minder_Form_Element_Text extends Zend_Form_Element_Text {
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper')
                 ->addDecorator('HtmlTag', array('tag' => 'td'))
                 ->addDecorator('Label', array('tag' => 'td'));
        }
    }

}