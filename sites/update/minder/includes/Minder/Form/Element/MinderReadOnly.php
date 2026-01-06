<?php

class Minder_Form_Element_MinderReadOnly extends Zend_Form_Element implements Minder_Form_Element_GridElementInterface {
    public $helper = 'formText';

    public function init()
    {
        $this->setAttrib('readonly', 'readonly');
    }

    function getWidth()
    {
        // TODO: Implement getWidth() method.
        return 1;
    }

    function setWidth($val)
    {
        // TODO: Implement setWidth() method.
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
        }
    }


}