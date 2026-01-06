<?php
class Minder_SysScreen_Model_PurchaseOrder extends Minder_SysScreen_Model
{
    public function selectPurchaseOrder($rowOffset, $itemCountPerPage) {
        $purchaseOrders = array();
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PURCHASE_ORDER.PURCHASE_ORDER ');
        if (is_array($result) && count($result) > 0)
            $purchaseOrders = array_map(create_function('$item', 'return $item["PURCHASE_ORDER"];'), $result);
        
        return $purchaseOrders;
    }
}

class Minder_SysScreen_Model_PurchaseOrder_Exception extends Minder_SysScreen_Model_Exception {}