<?php

class Minder_Form_Element_MinderDropDown extends Minder_Form_Element_DropDown implements Minder_Form_Element_GridElementInterface {
    /**
     * Flag: autoregister inArray validator?
     * @var bool
     */
    protected $_registerInArrayValidator = false;

    public function init()
    {
        parent::init();
        $this->setAttrib('data-element-type', 'DropDown');
    }


    /**
     * Return amount of grid columns occupied by element
     *
     * @return int
     */
    function getWidth()
    {
        // TODO: Implement getWidth() method.
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

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            $this->addDecorator('EventHandler');
        }
    }


}