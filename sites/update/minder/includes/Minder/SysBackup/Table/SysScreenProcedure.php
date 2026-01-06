<?php

class Minder_SysBackup_Table_SysScreenProcedure extends Minder_SysBackup_Table_Commentable {

    public function __construct() {
        parent::__construct('SYS_SCREEN_PROCEDURE');
    }

    protected function getCommentExpression() {
        return "SSP_FIELD_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_PROCEDURE_ID_GEN');
    }
}
