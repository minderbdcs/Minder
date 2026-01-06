<?php

class Minder2_Model_SysScreen_ReceivePo extends Minder2_Model_SysScreen {
    protected $_customMasterSlaveHandler = true;

    public function loadPurchaseOrderDetails($row) {
        $conditionObject = $this->makeFindConditions(array($this->_mapRowId($row)));

        $fetchResult = $this->fetchFields(array('PURCHASE_ORDER.*'), $conditionObject, 0, 1, true);

        if (count($fetchResult) > 0)
            return array('purchaseOrder' => array_shift($fetchResult));

        return array('purchaseOrder' => array());
    }
}