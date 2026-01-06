<?php

class Minder_Form_Crud extends Zend_Form {
    protected $_tableName = null;
    protected $_mode      = null;

    const MODE_ADD    = 'MODE_ADD';
    const MODE_UPDATE = 'MODE_UPDATE';

    public function __construct($tableName, $options = null , $mode = self::MODE_UPDATE)
    {
        $this->_mode = $mode;
        $this->_tableName = $tableName;
        parent::__construct($options);
    }

    /**
     * @param MasterTable_Field $fieldDescription
     * @param array $groupConfig
     * @return void
     */
    protected function _addFieldElement($fieldDescription, &$groupConfig) {

        if ($this->_mode == self::MODE_ADD && strtoupper($fieldDescription->name) == 'RECORD_ID')
            return;

        $extendedType = $fieldDescription->type;

        switch ($extendedType) {
            case 'TIMESTAMP':
                $element = new Minder_Form_Element_CrudDatePicker(array('name' => $fieldDescription->name));
                break;
            default:
                $extendedType .= (empty($fieldDescription->length)) ? '' : '[' . $fieldDescription->length . ']';
                $element = new Minder_Form_Element_CrudText(array('name' => $fieldDescription->name));
        }

        if (strtoupper($fieldDescription->name) == 'RECORD_ID')
            $element->setAttrib('readonly', 'readonly');

        $element->setAttrib('fieldType', $extendedType)->setLabel($fieldDescription->name);

        if ($element->getName())

        $this->addElement($element);

        $groupConfig['elements'][$element->getName()] = $element->getName();

        $rowBreaker = new Minder_Form_Element_RowBreaker(array('name' => uniqid('CRUD_RB_')));
        $this->addElement($rowBreaker);
        $groupConfig['elements'][$rowBreaker->getName()] = $rowBreaker->getName();
    }

    public function init()
    {
        $this->setAttrib('id', uniqid('CRUD_'));
        $this->addPrefixPath('Minder_Form_Decorator_', 'Minder/Form/Decorator', self::DECORATOR);

        $dataset = Minder::getInstance()->getMasterTableDataSet($this->_tableName);

        $fieldGroupConfig = array(
            'options' => array(
                'displayGroupClass' => 'Minder_Form_DisplayGroup_FormElementRow',
                'order' => 1,
                'decorators' => array(
                    'formElements' => array(
                        'decorator' => 'FormElements'
                    ),
                    'HtmlTag' => array(
                        'decorator' => 'HtmlTag',
                        'tag' => 'tr'
                    ),
                    'htmlTagTable' => array(
                        'decorator' => 'HtmlTagTable',
                        'options' => array(
                            'class' => 'summary'
                        )
                    )
                )
            ),
            'elements' => array()
        );

        /**
         * @var MasterTable_Field $datasetField
         */
        foreach ($dataset->getFields() as $datasetField) {
            $this->_addFieldElement($datasetField, $fieldGroupConfig);
        }

        $this->addDisplayGroups(array('fields' => $fieldGroupConfig));

        $this->addElement(new Zend_Form_Element_Hidden(array('name' => 'itemNo', 'options' => array('id' => ''))));
        $this->addElement(new Zend_Form_Element_Hidden(array('name' => 'pageNo', 'options' => array('id' => ''))));

        $this->_initToobar();
    }

    protected function _initToobar() {
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'save_btn', 'id' => '', 'label' => 'SAVE', 'class' => 'green-button')));
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'cancel_btn', 'id' => '', 'label' => 'CANCEL', 'class' => 'green-button')));
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'first_btn', 'id' => '', 'label' => 'FIRST', 'class' => 'green-button')));
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'prev_btn', 'id' => '', 'label' => 'PREV', 'class' => 'green-button')));
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'next_btn', 'id' => '', 'label' => 'NEXT', 'class' => 'green-button')));
        $this->addElement(new Minder_Form_Element_Toolbar_Submit(array('name' => 'last_btn', 'id' => '', 'label' => 'LAST', 'class' => 'green-button')));

        $bottomToolbar = $topToolbar = array(
            'options' => array(
                'displayGroupClass' => 'Minder_Form_DisplayGroup_Toolbar',
                'order' => 0
            ),
            'elements' => array(
                'save_btn'   => 'save_btn',
                'cancel_btn' => 'cancel_btn',
                'first_btn'  => 'first_btn',
                'prev_btn'   => 'prev_btn',
                'next_btn'   => 'next_btn',
                'last_btn'   => 'last_btn'
            )
        );

        $bottomToolbar['options']['order'] = 2;

        $this->addDisplayGroups(
            array(
                'top-toolbar' => $topToolbar,
                'botoom-toolbar' => $bottomToolbar
            )
        );
    }

    
}