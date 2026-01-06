<?php
  
class Minder_SysBackup_Table_SysScreenColour extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_COLOUR');
    }
    
    protected function getCommentExpression() {
        return "SSC_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_COLOUR_ID_GEN');
    }
}
