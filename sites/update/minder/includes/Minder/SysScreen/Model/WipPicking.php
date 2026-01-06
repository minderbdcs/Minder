<?php
class Minder_SysScreen_Model_WipPicking extends Minder_SysScreen_Model
{
    public function __construct() {
        parent::__construct();
        
        if (!is_array($this->staticConditions))
            $this->staticConditions = array();
            
        $this->staticConditions = array_merge(
            $this->staticConditions, 
            array(
                '(PICK_ORDER.PICK_STATUS IN (?, ?))' => array('DA', 'OP'),
                '(PICK_ITEM.PICK_LINE_STATUS IN (?, ?, ?, ?, ?))' => array('AL', 'PG', 'PL', 'WP', 'UP')
            )
        );
        
    }
}

class Minder_SysScreen_Model_WipPicking_Exception extends Minder_SysScreen_Model_Exception {}