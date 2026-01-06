<?php

class Minder_SysBackup_Table_SysLabelVar extends Minder_SysBackup_Table {
    public function __construct()
    {
        parent::__construct('SYS_LABEL_VAR');
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_LABEL_VAR_ID_GEN');
    }
}
