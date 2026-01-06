<?php

class Minder_SysScreen_PartBuilder_SRPrimaryKeys extends Minder_SysScreen_PartBuilder_SRFields {
    protected $_partName                      = 'PKEYS';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSV_FIELD_TYPE =?' => array('SR'),
            'SSV_FIELD_STATUS = ?' => array('OK'),
            'SSV_PRIMARY_ID = ?' => array('T')
        );
    }

    protected function _doBuild()
    {
        $result = array();
        $rows = $this->_selectDbRows();

        foreach ($rows as $row) {
            $result[$row['RECORD_ID']] = $this->_prepareDbRow($row);
        }

        return $result;
    }


}