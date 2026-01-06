<?php

class Minder_Form_Element_MinderCheckBox extends Zend_Form_Element_Checkbox {
    /**
     * Value when checked
     * @var string
     */
    protected $_checkedValue = 'T';

    /**
     * Value when not checked
     * @var string
     */
    protected $_uncheckedValue = 'F';


    public function init()
    {
        parent::init();

        $inputMethodStructure  = $this->_parseInputMethod();
        $this->_checkedValue   = isset($inputMethodStructure['CHECKED_VALUE'])   ? $inputMethodStructure['CHECKED_VALUE']   : $this->_checkedValue;
        $this->_uncheckedValue = isset($inputMethodStructure['UNCHECKED_VALUE']) ? $inputMethodStructure['UNCHECKED_VALUE'] : $this->_uncheckedValue;

        $this->setAttrib('data-checked-value', $this->_checkedValue);
        $this->setAttrib('data-unchecked-value', $this->_uncheckedValue);
    }

    protected function _getNameValuePair($source) {
        if (empty($source))
            return null;

        $parts = explode('=', $source);

        return array(
            'name'  => $parts[0],
            'value' => isset($parts[1]) ? $parts[1] : null
        );
    }

    protected function _parseInputMethod() {
        $minderOptions = $this->getAttrib('minderOptions');

        $result = array(
            'CHECKED_VALUE' => $this->_checkedValue,
            'UNCHECKED_VALUE' => $this->_uncheckedValue
        );

        if (empty($minderOptions))
            return $result;

        $ssvInputMethod = isset($minderOptions['SSV_INPUT_METHOD']) ? $minderOptions['SSV_INPUT_METHOD'] : 'CB' ;

        $parts = explode('|', $ssvInputMethod);
        while ($part = array_shift($parts)) {
            $nameValuePair = $this->_getNameValuePair($part);

            if (!empty($nameValuePair)) {
                $result[$nameValuePair['name']] = $nameValuePair['value'];
            }
        }

        return $result;
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