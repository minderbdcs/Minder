<?php
  
class Minder_SysBackup_Table_SysScreenAction extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_ACTION');
    }
    
    protected function getCommentExpression() {
        return "SSA_ACTION_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_ACTION_ID_GEN');
    }
}
