<?php
class Minder_SysScreen_Model_PurchaseOrderLine extends Minder_SysScreen_Model
{
    public function selectPurchaseOrderAndLine($rowOffset, $itemCountPerPage) {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PURCHASE_ORDER_LINE.PURCHASE_ORDER, PURCHASE_ORDER_LINE.PO_LINE');
        if (is_array($result) && count($result) > 0)
            return $result;
        
        return array();
    }
    
    public function setPoLineStatus($status = '') {
        $ordersAndLines = $this->selectPurchaseOrderAndLine(0, count($this));
        $minder = Minder::getInstance();
        $sql = 'UPDATE PURCHASE_ORDER_LINE SET PO_LINE_STATUS = ? WHERE PURCHASE_ORDER = ? AND PO_LINE = ?';
        
        $errors   = array();
        $messages = array();
        
        foreach ($ordersAndLines as $dataRow) {
            if (false !==$minder->execSQL($sql, array($status, $dataRow['PURCHASE_ORDER'], $dataRow['PO_LINE']))) {
                $messages[] = 'Purchase Order #' . $dataRow['PURCHASE_ORDER'] . ' Line #' . $dataRow['PO_LINE'] . ' status updated.';
            } else {
                $errors[] = 'Error updating Purchase Order #' . $dataRow['PURCHASE_ORDER'] . ' Line #' . $dataRow['PO_LINE'] . ' status. ' . $minder->lastError;
            }
        }
        
        return array($errors, $messages);
    }
}

class Minder_SysScreen_Model_PurchaseOrderLine_Exception extends Minder_SysScreen_Model_Exception {}