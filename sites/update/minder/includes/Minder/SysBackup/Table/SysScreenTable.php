<?php
  
class Minder_SysBackup_Table_SysScreenTable extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_TABLE');
    }
    
    protected function getCommentExpression() {
        return "SST_TABLE_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_TABLE_ID_GEN');
    }
}
