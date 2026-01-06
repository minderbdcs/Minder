<?php

class Minder_SysScreen_PartBuilder_SysMenu extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'SYS_MENU';
    protected $_tableName                     = 'SYS_MENU';
    protected $_orderByFieldName              = 'SM_SEQUENCE';

    protected $_staticLimitsMask              = 13;
    protected $_staticLimitsUserExpression    = 'SM_USER_CATEGORY';
    protected $_staticLimitsDeviceExpression  = '';
    protected $_staticLimitsWhExpression      = 'SM_WH_ID';
    protected $_staticLimitsCompanyExpression = 'SM_COMPANY_ID';

    protected function _getPartFilters()
    {
        return array(
            'SM_MENU_STATUS = ?'  => array('OK')
        );
    }
}