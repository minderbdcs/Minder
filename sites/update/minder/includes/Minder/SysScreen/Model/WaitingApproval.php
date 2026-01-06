<?php
class Minder_SysScreen_Model_WaitingApproval extends Minder_SysScreen_Model
{
    public function __construct() {
        parent::__construct();
        
        if (!is_array($this->staticConditions))
            $this->staticConditions = array();
            
        $this->staticConditions = array_merge(
            $this->staticConditions, 
            array(
                '(PICK_ORDER.PICK_STATUS IN (?, ?))' => array('CF', 'HD')
            )
        );
        
    }
}

class Minder_SysScreen_Model_WaitingApproval_Exception extends Minder_SysScreen_Model_Exception {}