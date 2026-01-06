<?php
/**
* Simple model for order selection. Use it when you need just to select some PICK_ORDER ids with RowSelector helper
* and don't want to use real SysScreen_Model.
*/

class Minder_SysScreen_Model_OrdersIds extends Minder_SysScreen_Model
{
    public function __construct() {
        parent::__construct();
        
        if (!is_array($this->staticConditions))
            $this->staticConditions = array();
            
        $this->fields = array(
            'ADOF_1' => array(
                'RECORD_ID' => 'ADOF_1', 
                'SSV_NAME' => 'PICK_ORDER', 
                'SSV_SEQUENCE' => '0', 
                'SSV_EXPRESSION' => 'PICK_ORDER.PICK_ORDER', 
                'SSV_ALIAS' => 'PICK_ORDER', 
                'SSV_TITLE' => 'Order #',
                'SSV_TABLE' => 'PICK_ORDER',
                'SSV_PRIMARY_ID' => 'T'
            )
        );
        
        $this->pkeys = array(
            'ADOF_1' => array(
                'RECORD_ID' => 'ADOF_1', 
                'SSV_NAME' => 'PICK_ORDER', 
                'SSV_SEQUENCE' => '0', 
                'SSV_EXPRESSION' => 'PICK_ORDER.PICK_ORDER', 
                'SSV_ALIAS' => 'PICK_ORDER', 
                'SSV_TITLE' => 'Order #',
                'SSV_TABLE' => 'PICK_ORDER',
                'SSV_PRIMARY_ID' => 'T'
            )
        );
        
        $this->tables = array(
            'ADOTBL_1' => array(
                'RECORD_ID' => 'ADOTBL_1',
                'SST_TABLE' => 'PICK_ORDER',
                'SST_SEQUENCE' => '0',
                'SST_ALIAS' => 'PICK_ORDER',
                'SST_VIA' => '',
                'SST_JOIN' => '',
                'ORDER_BY_FIELD_NAME' => 'SST_SEQUENCE'
            )
        );
    }
}

class Minder_SysScreen_Model_OrdersIds_Exception extends Minder_SysScreen_Model_Exception {}