<?php

class Minder_Form_Element_MinderDatePicker extends Zend_Form_Element implements Minder_Form_Element_GridElementInterface {
    public function init()
    {
        parent::init();
        $this->setAttrib('data-element-type', 'DatePicker');
    }


    /**
     * Return amount of grid columns occupied by element
     * @return int
     */
    function getWidth()
    {
        // TODO: Implement getWidth() method.
        return 1;
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

    /**
     * Set amount of grid columns occupied by element
     * @param int $val
     */
    function setWidth($val)
    {
        // TODO: Implement setWidth() method.
    }

}