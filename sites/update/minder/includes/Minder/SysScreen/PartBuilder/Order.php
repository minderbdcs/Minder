<?php

class Minder_SysScreen_PartBuilder_Order extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'ORDER';
    protected $_tableName                     = 'SYS_SCREEN_ORDER';
    protected $_orderByFieldName              = 'SSO_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SSO_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSO_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSO_ORDER_STATUS = ?'  => array('OK')
        );
    }
}