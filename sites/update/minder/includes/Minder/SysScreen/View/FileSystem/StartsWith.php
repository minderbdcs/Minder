<?php

class Minder_SysScreen_View_FileSystem_StartsWith extends Minder_SysScreen_View_FileSystem_AbstractFieldConstraint {

    public function isValid($rowData)
    {
        if (!isset($rowData[$this->_getFieldName()])) {
            return false;
        }

        $value = $rowData[$this->_getFieldName()];

        return stripos($value, $this->_getTerm()) === 0;
    }
}