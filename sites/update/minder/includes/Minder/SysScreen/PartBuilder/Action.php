<?php

class Minder_SysScreen_PartBuilder_Action extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'ACTION';
    protected $_tableName                     = 'SYS_SCREEN_ACTION';
    protected $_orderByFieldName              = 'SSA_SEQUENCE';

    protected $_staticLimitsMask              = 0;

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSA_ACTION_STATUS = ?' => array('OK')
        );
    }
}