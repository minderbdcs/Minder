<?php

class Minder_SysScreen_PartBuilder_SysScreen extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'SYS_SCREEN';
    protected $_tableName                     = 'SYS_SCREEN';
    protected $_orderByFieldName              = '';

    protected $_staticLimitsMask              = 4;
    protected $_staticLimitsUserExpression    = '';
    protected $_staticLimitsDeviceExpression  = '';
    protected $_staticLimitsWhExpression      = '';
    protected $_staticLimitsCompanyExpression = 'COMPANY_ID';

    protected function _getPartFilters()
    {
        return array('SS_NAME = ?' => array($this->_ssRealName));
    }
}