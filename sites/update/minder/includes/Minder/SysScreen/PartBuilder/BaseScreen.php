<?php

class Minder_SysScreen_PartBuilder_BaseScreen extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'BASE_SCREEN';
    protected $_tableName                     = 'SYS_SCREEN_TABLE';
    protected $_orderByFieldName              = 'SST_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SST_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SST_DEVICE_TYPE';

    protected function _getPartFilters()
    {
        return array(
            'SST_TABLE = ?' => array('SYS_SCREEN:' . $this->_ssRealName),
            'SST_TABLE_STATUS = ?'  => array('OK')
        );
    }
}