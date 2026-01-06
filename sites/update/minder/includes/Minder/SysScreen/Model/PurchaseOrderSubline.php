<?php
class Minder_SysScreen_Model_PurchaseOrderSubline extends Minder_SysScreen_Model
{
    
    public function makeConditionsFromPurchaseOrderAndLine($data = array()) {
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $data));
        if (empty($data))
            return array();
            
        $args       = array();
        
        $conditionStr = substr(str_repeat('(PURCHASE_SUB_LINE.PURCHASE_ORDER = ? AND PURCHASE_SUB_LINE.PO_LINE = ?) OR ', count($data)), 0, -4);
        $conditionStr = '(' . $conditionStr . ')';
        
        foreach ($data as $dataRow) {
            $args[] = $dataRow['PURCHASE_ORDER'];
            $args[] = $dataRow['PO_LINE'];
        }
        
        return array($conditionStr => $args);
    }
    
    public function selectRecordId($rowOffset, $itemCountPerPage) {
        $recordIds = array();
        if (false !== ($result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT RECORD_ID')))
            $recordIds = array_map(create_function('$item', 'return $item["RECORD_ID"];'), $result);
            
        return $recordIds;
    }
    
    public function selectPurchaseOrderAndLine($rowOffset, $itemCountPerPage) {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PURCHASE_SUB_LINE.PURCHASE_ORDER, PURCHASE_SUB_LINE.PO_LINE');
        if (is_array($result) && count($result) > 0)
            return $result;
        
        return array();
    }
    
    public function selectAll($rowOffset, $itemCountPerPage) {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT *');
        if (is_array($result) && count($result) > 0)
            return $result;
        
        return array();
    }
    
}

class Minder_SysScreen_Model_PurchaseOrderSubline_Exception extends Minder_SysScreen_Model_Exception {}