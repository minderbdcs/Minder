<?php

class Minder_SysScreen_View_FileSystem_EndsWith extends Minder_SysScreen_View_FileSystem_AbstractFieldConstraint {

    public function isValid($rowData)
    {
        if (!isset($rowData[$this->_getFieldName()])) {
            return false;
        }

        $value = substr($rowData[$this->_getFieldName()], 0, -strlen($this->_getTerm()));

        return strtolower($value) === strtolower($this->_getTerm());
    }
}