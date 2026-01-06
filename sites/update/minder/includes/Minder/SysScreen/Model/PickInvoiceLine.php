<?php
class Minder_SysScreen_Model_PickInvoiceLine extends Minder_SysScreen_Model
{
    public function __construct() {
        parent::__construct();
    }
    
    public function selectLineId($rowOffset, $itemCountPerPage) {
        $lineId = array();
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PICK_INVOICE_LINE.INVOICE_LINE_ID ');
        if (is_array($result) && count($result) > 0)
            $lineId = array_map(create_function('$item', 'return $item["INVOICE_LINE_ID"];'), $result);
        
        return $lineId;
    }
}

class Minder_SysScreen_Model_PickInvoiceLine_Exception extends Minder_SysScreen_Model_Exception {}