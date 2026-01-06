<?php

class Minder_Form_Element_In extends Zend_Form_Element implements Minder_Form_Element_GridElementInterface {
    protected $_width = 1;

    /**
     * Default view helper to use
     * @var string
     */
    public $helper = 'formText';

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

    function setWidth($val)
    {
        $this->_width = intval($val);
        return $this;
    }

    function getWidth() {
        if (empty($this->_width))
            $this->setWidth(1);

        return $this->_width;
    }
}