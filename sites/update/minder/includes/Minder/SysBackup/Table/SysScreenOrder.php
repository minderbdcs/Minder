<?php
  
class Minder_SysBackup_Table_SysScreenOrder extends Minder_SysBackup_Table_Commentable {
    
    public function __construct() {
        parent::__construct('SYS_SCREEN_ORDER');
    }
    
    protected function getCommentExpression() {
        return "SSO_ORDER_STATUS = 'BK'";
    }

    protected function _getGenerators()
    {
        return array('RECORD_ID' => 'SYS_SCREEN_ORDER_ID_GEN');
    }
}
