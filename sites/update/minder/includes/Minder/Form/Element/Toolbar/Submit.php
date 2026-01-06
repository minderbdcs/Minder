<?php

class Minder_Form_Element_Toolbar_Submit extends Zend_Form_Element_Submit {
    public function init()
    {
        $this->setAttrib('class', 'green-button');
        parent::init();
    }


    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Tooltip')
                 ->addDecorator('ViewHelper')
                 ->addDecorator('htmlTag', array('tag' => 'li'));
        }
    }

}