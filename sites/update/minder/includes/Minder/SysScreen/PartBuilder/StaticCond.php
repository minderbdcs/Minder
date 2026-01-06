<?php

class Minder_SysScreen_PartBuilder_StaticCond extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'STATIC_COND';
    protected $_tableName                     = 'SYS_SCREEN_VAR';
    protected $_orderByFieldName              = 'SSV_SEQUENCE';

    protected $_staticLimitsMask              = 12;
    protected $_staticLimitsUserExpression    = 'SSV_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSV_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?'                   => array($this->_ssRealName),
            'SSV_FIELD_STATUS = ?'          => array('OK'),
            'SSV_FIELD_TYPE = ?'            => array('SE'),
            'SSV_INPUT_METHOD IN (?, ?)'    => array('RO', 'NONE')
        );
    }
}