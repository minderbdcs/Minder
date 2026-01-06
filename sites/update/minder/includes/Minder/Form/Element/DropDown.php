<?php

class Minder_Form_Element_DropDown extends Zend_Form_Element_Xhtml {

    /**
     * Use formSelect view helper by default
     * @var string
     */
    public $helper = 'minderDropDown';

    /**
     * Flag: autoregister inArray validator?
     * @var bool
     */
    protected $_registerInArrayValidator = true;

    /**
     * Separator to use between options; defaults to '<br />'.
     * @var string
     */
    protected $_separator = '<br />';

    public $options = null;
    protected $_valueFieldName = null;
    protected $_labelFieldName = null;

    /**
     * Retrieve separator
     *
     * @return mixed
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Set separator
     *
     * @param mixed $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }

    public function setValueField($fieldName) {
        $this->_valueFieldName = $fieldName;
        $this->setAttrib('valueField', $fieldName);
    }

    public function getValueField() {
        if (is_null($this->_valueFieldName))
            $this->setValueField('value');

        return $this->_valueFieldName;
    }

    public function setLabelField($fieldName) {
        $this->_labelFieldName = $fieldName;
        $this->setAttrib('labelField', $fieldName);
    }

    public function getLabelField() {
        if (is_null($this->_labelFieldName))
            $this->setLabelField('label');

        return $this->_labelFieldName;
    }

    public function addMultiOptions(array $options)
    {
        foreach ($options as $option) {
            $this->options[] = $option;
        }

        return $this;
    }

    /**
     * Set all options at once (overwrites)
     *
     * @param  array $options
     * @return Minder_Form_Element_DropDown
     */
    public function setMultiOptions(array $options)
    {
        $this->clearMultiOptions();
        return $this->addMultiOptions($options);
    }

    public function getMultiOptionRow($option) {
        foreach ($this->_getMultiOptions() as $optionsElement) {
            if ($optionsElement[$this->getValueField()] == $option)
                return $optionsElement;
        }

        return null;
    }

    public function getMultiOptionAttribute($option, $attribute) {
        $optionRow = $this->getMultiOptionRow($option);

        if (is_null($optionRow)) return null;

        if (isset($optionRow[$attribute])) return $optionRow[$attribute];

        return null;
    }

    public function getMultiOption($option)
    {
        foreach ($this->_getMultiOptions() as $optionsElement) {
            if ($optionsElement[$this->getValueField()] == $option)
                return $optionsElement[$this->getLabelField()];
        }

        return null;
    }

    public function getMultiOptions()
    {
        return $this->_getMultiOptions();
    }

    /**
     * Clear all options
     *
     * @return Minder_Form_Element_DropDown
     */
    public function clearMultiOptions()
    {
        $this->options = array();
        return $this;
    }

    public function isValid($value, $context = null)
    {
        if ($this->registerInArrayValidator()) {
            if (!$this->getValidator('InArray')) {
                $multiOptions = $this->getMultiOptions();
                $options      = array();

                foreach ($multiOptions as $optRow) {
                    $options[] = $optRow[$this->getValueField()];
                }

                $this->addValidator(
                    'InArray',
                    true,
                    array($options)
                );
            }
        }
        return parent::isValid($value, $context);
    }

    private function _getMultiOptions()
    {
        if (is_null($this->options))
            $this->options = array();

        return $this->options;
    }

    /**
     * Get status of auto-register inArray validator flag
     *
     * @return bool
     */
    public function registerInArrayValidator()
    {
        return $this->_registerInArrayValidator;
    }

    /**
     * Set flag indicating whether or not to auto-register inArray validator
     *
     * @param  bool $flag
     * @return Minder_Form_Element_DropDown
     */
    public function setRegisterInArrayValidator($flag)
    {
        $this->_registerInArrayValidator = (bool) $flag;
        return $this;
    }

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