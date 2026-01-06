<?php

class Minder_SysScreen_PartBuilder_Color extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'COLOUR';
    protected $_tableName                     = 'SYS_SCREEN_COLOUR';
    protected $_orderByFieldName              = 'SSC_SEQUENCE';

    protected $_staticLimitsMask              = 0;

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?'       => array($this->_ssRealName),
            'SSC_STATUS = ?'    => array('OK')
        );
    }
}