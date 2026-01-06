<?php

class Minder_SysScreen_PartBuilder_TableParam extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'TABLE_PARAM';
    protected $_tableName                     = 'SYS_SCREEN_PROCEDURE';
    protected $_orderByFieldName              = 'SSP_SEQUENCE';

    protected $_staticLimitsMask              = 3;
    protected $_staticLimitsUserExpression    = 'SSP_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSP_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSP_FIELD_STATUS = ?'  => array('OK')
        );
    }
}