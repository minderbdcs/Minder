<?php

class Minder_Page_FormBuilder_EditForm_Ssn extends Minder_Page_FormBuilder_EditForm_Default {

    protected function _getMandatoryFields() {
        return array('SSN_ID', 'ORIGINAL_QTY', 'SSN_TYPE', 'CREATE_DATE', 'CREATED_BY');
    }

    protected function _getNotesFields() {
        return array('NOTES', 'DISPOSAL_NOTES');
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return array
     */
    protected function _getElementConfig($fieldEntry)
    {
        $config = parent::_getElementConfig($fieldEntry);

        if (in_array($fieldEntry->SSV_NAME, $this->_getMandatoryFields()))
            $config['options']['class'] = 'mandatory';

        if (in_array($fieldEntry->SSV_NAME, $this->_getNotesFields())) {
            $config['type'] = 'minderText';
            $config['options']['minderOptions']['WIDTH'] = 2;
        }

        return $config;
    }

}