<?php

class Minder_SysScreen_PartBuilder_SETab extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'SE_TAB';
    protected $_tableName                     = 'SYS_SCREEN_TAB';
    protected $_orderByFieldName              = 'SST_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SST_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SST_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?'           => array($this->_ssRealName),
            'SST_TAB_STATUS = ?'    => array('OK'),
            'SST_FIELD_TYPE = ?'    => array('SE')
        );
    }
}