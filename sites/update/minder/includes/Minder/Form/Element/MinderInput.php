<?php

class Minder_Form_Element_MinderInput extends Zend_Form_Element implements Minder_Form_Element_GridElementInterface {
    /**
     * Return amount of grid columns occupied by element
     * @return int
     */
    function getWidth()
    {
        // TODO: Implement getWidth() method.
var_dump(__FILE__, __LINE__, debug_backtrace());
        return 1;
    }

    /**
     * Set amount of grid columns occupied by element
     * @param int $val
     */
    function setWidth($val)
    {
        // TODO: Implement setWidth() method.
    }

    public function init()
    {
        parent::init();

        $this->setAttrib('data-element-type', 'Input');
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