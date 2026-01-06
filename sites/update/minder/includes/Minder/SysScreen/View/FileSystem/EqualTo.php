<?php

class Minder_SysScreen_View_FileSystem_EqualTo extends Minder_SysScreen_View_FileSystem_AbstractFieldConstraint {

    public function isValid($rowData)
    {
        if (!isset($rowData[$this->_getFieldName()])) {
            return false;
        }

        return strtolower($rowData[$this->_getFieldName()]) === strtolower($this->_getTerm());
    }
}