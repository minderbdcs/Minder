<?php
  
class Minder_SysBackup_Table_SysScreenButton extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_BUTTON');
    }
    
    protected function getCommentExpression() {
        return "SSB_TAB_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_BUTTON_ID_GEN');
    }
}
