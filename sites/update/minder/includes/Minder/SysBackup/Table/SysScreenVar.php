<?php
  
class Minder_SysBackup_Table_SysScreenVar extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_VAR');
    }
    
    protected function getCommentExpression() {
        return "SSV_FIELD_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_VAR_ID_GEN');
    }
}
