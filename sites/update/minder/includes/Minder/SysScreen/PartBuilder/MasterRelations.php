<?php

class Minder_SysScreen_PartBuilder_MasterRelations extends Minder_SysScreen_PartBuilder_Relations {
    protected $_partName                      = 'MASTER_RELATIONS';

    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSV_FIELD_TYPE =?' => array('FK'),
            'SSV_FIELD_STATUS = ?' => array('OK')
        );
    }
}