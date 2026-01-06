<?php

class Minder_SysBackup_Table_SysScreen extends Minder_SysBackup_Table {
    public function __construct()
    {
        parent::__construct('SYS_SCREEN');
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_ID_GEN');
    }
}
