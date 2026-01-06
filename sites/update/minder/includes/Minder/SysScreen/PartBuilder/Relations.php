<?php

abstract class Minder_SysScreen_PartBuilder_Relations extends Minder_SysScreen_PartBuilder {
    protected $_tableName                     = 'SYS_SCREEN_VAR';
    protected $_orderByFieldName              = 'SSV_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SSV_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSV_DEVICE_TYPE';

    protected function _parseMasterFieldDescription($field) {
        $tmpDescr = explode('.', $field);

        return array(
            'MASTER_SS_NAME' => isset($tmpDescr[0]) ? $tmpDescr[0] : '',
            'MASTER_TABLE'   => isset($tmpDescr[1]) ? $tmpDescr[1] : '',
            'MASTER_FIELD'   => isset($tmpDescr[2]) ? $tmpDescr[2] : ''
        );
    }

    protected function _isValidForeignKey($row) {
        if (!isset($row['MASTER_SS_NAME']) || empty($row['MASTER_SS_NAME']))
            throw new Minder_SysScreen_Builder_Exception('Master Sys Screen name is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        if (!isset($row['MASTER_TABLE']) || empty($row['MASTER_TABLE']))
            throw new Minder_SysScreen_Builder_Exception('Master Table name is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        if (!isset($row['MASTER_FIELD']) || empty($row['MASTER_FIELD']))
            throw new Minder_SysScreen_Builder_Exception('Master Field is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        if (!isset($row['SLAVE_SS_NAME']) || empty($row['SLAVE_SS_NAME']))
            throw new Minder_SysScreen_Builder_Exception('Slave Sys Screen name is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        if (!isset($row['SLAVE_TABLE']) || empty($row['SLAVE_TABLE']))
            throw new Minder_SysScreen_Builder_Exception('Slave Table name is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        if (!isset($row['SLAVE_FIELD']) || empty($row['SLAVE_FIELD']))
            throw new Minder_SysScreen_Builder_Exception('Slave Field is not defined for #' . $row['RECORD_ID'] . ' foreign key.');

        return true;
    }

    protected function _doBuild()
    {
        $result = array();

        foreach ($this->_selectDbRows() as $row) {

            if (!$this->_UserCategoryIsValid($row)) {
                continue;
            }

            $row = array_merge($row, $this->_parseMasterFieldDescription($row['SSV_EXPRESSION']));
            $row['SLAVE_TABLE']   = $row['SSV_TABLE'];
            $row['SLAVE_FIELD']   = $row['SSV_NAME'];
            $row['SLAVE_SS_NAME'] = $row['SS_NAME'];

            if ($this->_isValidForeignKey($row))
                $result[$row['RECORD_ID']]  = $this->_prepareDbRow($row);
        }

        return $result;
    }
}