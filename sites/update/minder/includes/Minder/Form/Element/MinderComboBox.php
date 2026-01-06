<?php

class Minder_Form_Element_MinderComboBox extends Minder_Form_Element_DropDown implements Minder_Form_Element_GridElementInterface {

    /**
     * Use formSelect view helper by default
     * @var string
     */
    public $helper = 'formText';

    public function init()
    {
        parent::init();
        $this->setAttrib('data-element-type', 'ComboBox');
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
        }
    }

}