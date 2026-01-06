<?php

class Minder_Form_Element_MinderToolButton extends Zend_Form_Element_Button {
    public function init()
    {
        parent::init();
        $this->setAttrib('class', 'green-button');
        $this->setAttribs(array(
            'class'     => 'green-button',
            'data-type' => 'MinderElement',
            'data-element-type'=> 'ToolButton'
        ));
    }


    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('HtmlTag', array('tag' => 'li'));
            $this->addDecorator('EventHandler');
        }
    }

}