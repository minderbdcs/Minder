<?php

class Minder_SysScreen_View_FileSystem_FieldValueInList extends Minder_SysScreen_View_FileSystem_AbstractFieldConstraint {
    public function isValid($rowData)
    {
        $fieldValue = isset($rowData[$this->_getFieldName()]) ? $rowData[$this->_getFieldName()] : null;

        if (is_null($fieldValue)) {
            return false;
        }

        return in_array($fieldValue, $this->_getTerm());
    }
}