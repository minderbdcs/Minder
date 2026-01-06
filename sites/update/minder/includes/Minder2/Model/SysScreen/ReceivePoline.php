<?php

class Minder2_Model_SysScreen_ReceivePoline extends Minder2_Model_SysScreen {
    protected $_customMasterSlaveHandler = true;

    protected function _getLineDetails($row) {
        $rowIds = array($this->_mapRowId($row));

        $conditionObject = $this->makeFindConditions($rowIds);
        $result = $this->fetchFields(array('PURCHASE_ORDER_LINE.PURCHASE_ORDER, PURCHASE_ORDER_LINE.PO_LINE'), $conditionObject, 0, 1);

        if (count($result) < 0)
            return array();

        $result = current($result);

        return Minder::getInstance()->getPoLine($result['PO_LINE'], $result['PURCHASE_ORDER']);
    }

    protected function _getGrnMapper() {
        return new Minder2_Model_Mapper_Grn();
    }

    protected function _getGrn($grn) {
        return $this->_getGrnMapper()->find($grn)->GRN;
    }

    public function loadGrnDetails($row, $grn = '') {
        $grnDetails = new Minder2_Model_SysScreen_ReceivePoline_GrnDetail();
        $grnDetails->purchaseOrderLine = $this->_getLineDetails($row);
        $grnDetails->purchaseOrderLine['SHORT_DESC'] = $this->_getProdShortDesc($grnDetails->purchaseOrderLine['PROD_ID']);
        $grnDetails->grn = $this->_getGrn($grn);
        return $grnDetails;
    }

    private function _getProdShortDesc($prodId)
    {
        return current(Minder::getInstance()->getProductList($prodId));
    }

    public function accept($row, $acceptDetails = array()) {
        if (empty($row))
            throw new Exception('No Purchase Order Line selected.');

        $rowIds = array($this->_mapRowId($row));
        $result = $this->fetchFields(array('PURCHASE_ORDER_LINE.PURCHASE_ORDER, PURCHASE_ORDER_LINE.PO_LINE'), $this->makeFindConditions($rowIds), 0, 1);

        if (empty($result))
            throw new Exception('Purchase Order Line not found.');

        $result = current($result);

        $acceptProcess = new Minder2_Model_SysScreen_ReceivePoline_Accept();

        return $acceptProcess->doAccept($result['PURCHASE_ORDER'], $result['PO_LINE'], $acceptDetails);
    }
}
