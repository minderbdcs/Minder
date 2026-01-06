<?php

class Minder_SysBackup_Table_SysLabel extends Minder_SysBackup_Table {
    public function __construct()
    {
        parent::__construct('SYS_LABEL');
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_LABEL_ID_GEN');
    }
}
