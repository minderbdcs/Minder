<?php

class Minder_SysScreen_PartBuilder_Group extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'GROUP';
    protected $_tableName                     = 'SYS_SCREEN_GROUP';
    protected $_orderByFieldName              = 'SSG_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SSG_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSG_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSG_GROUP_STATUS = ?'  => array('OK')
        );
    }
}