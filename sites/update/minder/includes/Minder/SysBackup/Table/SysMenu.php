<?php
  
class Minder_SysBackup_Table_SysMenu extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_MENU');
    }
    
    protected function getCommentExpression() {
        return "SM_MENU_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_MENU_ID_GEN');
    }
}
