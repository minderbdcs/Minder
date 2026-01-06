<?php

class Minder_Form_ImportMap extends Zend_Form {

    const MODE_SEARCH = 'MODE_SEARCH';
    const MODE_ADD    = 'MODE_ADD';

    public function __construct($mode = self::MODE_SEARCH)
    {
        $options = new Zend_Config_Ini(APPLICATION_CONFIG_DIR . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'import-map.ini', 'form');
        parent::__construct($options);

        if ($mode == self::MODE_ADD) {
            $this->getElement('import_filename')->setRequired(true)->setAllowEmpty(false);
            $this->getElement('import_type')->setRequired(true)->setAllowEmpty(false);

            if (Minder::getInstance()->isSysAdmin())
                $this->getElement('import_table')->setRequired(true)->setAllowEmpty(false);
        }
    }

    public function getValues($suppressArrayNotation = false)
    {
        $values = array();
        foreach ($this->getElements() as $key => $element) {
            if (!$element->getIgnore()) {
                $values[$key] = $element->getValue();
            }
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            $fValues = $this->_attachToArray($subForm->getValues(true), $subForm->getElementsBelongTo());
            $values = array_merge($values, $fValues);
        }

        if (!Minder::getInstance()->isSysAdmin()) {
            /**
             * @var Minder_Form_Element_DropDown_ImportMapType $importTypeElement
             */
            $importTypeElement      = $this->getElement('import_type');
            $values['import_table'] = $importTypeElement->getMultiOptionAttribute($values['import_type'], 'DESCRIPTION');
        }

        if (!$suppressArrayNotation && $this->isArray()) {
            $values = $this->_attachToArray($values, $this->getElementsBelongTo());
        }

        return $values;
    }


}