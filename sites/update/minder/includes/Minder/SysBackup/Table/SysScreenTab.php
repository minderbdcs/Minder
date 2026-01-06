<?php
  
class Minder_SysBackup_Table_SysScreenTab extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_TAB');
    }
    
    protected function getCommentExpression() {
        return "SST_TAB_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_TAB_ID_GEN');
    }
}
