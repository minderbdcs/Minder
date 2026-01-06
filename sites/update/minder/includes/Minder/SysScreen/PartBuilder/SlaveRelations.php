<?php

class Minder_SysScreen_PartBuilder_SlaveRelations extends Minder_SysScreen_PartBuilder_Relations {
    protected $_partName                      = 'SLAVE_RELATIONS';

    protected function _getPartFilters()
    {
        return array(
            'SSV_EXPRESSION STARTING(?)' => array($this->_ssRealName . '.'),
            'SSV_FIELD_TYPE =?' => array('FK'),
            'SSV_FIELD_STATUS = ?' => array('OK')
        );
    }
}