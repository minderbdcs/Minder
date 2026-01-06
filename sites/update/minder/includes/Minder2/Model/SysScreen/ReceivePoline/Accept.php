<?php

/**
 * @throws Exception
 * @property $carrierId
 * @property $vehicleRegistration
 * @property $conNoteNo
 * @property $shipDate
 * @property $containerNo
 * @property $crateQty
 * @property $crateTypeId
 * @property $palletOwnerId
 * @property $crateOwnerId
 * @property $palletQty
 * @property $receiptFlag
 */
class Minder2_Model_SysScreen_ReceivePoline_Accept {

    protected $_purchaseOrder = null;
    protected $_lineNo        = null;
    protected $_acceptDetails = null;

    /**
     * @var Minder2_Model_SysScreen_ReceivePoline_Accept_Result
     */
    protected $_result = null;

    function __get($name)
    {
        return $this->_getAcceptDetail($name);
    }

    function __isset($name)
    {
        $acceptDetails = $this->_getAcceptDetails();
        return isset($acceptDetails[$name]);
    }


    /**
     * @param $val
     * @return Minder2_Model_SysScreen_ReceivePoline_Accept
     */
    protected function _setPurchaseOrder($val) {
        $this->_purchaseOrder = $val;
        $this->_resetResult();
        return $this;
    }

    /**
     * @throws Exception
     * @return string
     */
    protected function _getPurchaseOrder() {
        if (empty($this->_purchaseOrder))
            throw new Exception('Purchase Order is empty.');

        return $this->_purchaseOrder;
    }

    /**
     * @param $val
     * @return Minder2_Model_SysScreen_ReceivePoline_Accept
     */
    protected function _setLineNo($val) {
        $this->_lineNo = $val;
        $this->_resetResult();
        return $this;
    }

    /**
     * @throws Exception
     * @return string
     */
    protected function _getLineNo() {
        if (empty($this->_lineNo))
            throw new Exception('PO_LINE is empty.');

        return $this->_lineNo;
    }

    /**
     * @param $val
     * @return Minder2_Model_SysScreen_ReceivePoline_Accept
     */
    protected function _setAcceptDetails($val) {
        $this->_acceptDetails = $val;
        $this->_resetResult();
        return $this;
    }

    /**
     * @return Minder2_Model_SysScreen_ReceivePoline_Accept
     */
    protected function _resetResult() {
        $this->_result = null;
        return $this;
    }

    /**
     * @return Minder2_Model_SysScreen_ReceivePoline_Accept_Result
     */
    protected function _getResult() {
        if (is_null($this->_result))
            $this->_result = new Minder2_Model_SysScreen_ReceivePoline_Accept_Result();

        return $this->_result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getAcceptDetails() {
        if (empty($this->_acceptDetails))
            throw new Exception('Accept details are empty.');

        return $this->_acceptDetails;
    }

    protected function _getAcceptDetail($name) {
        $acceptDetails = $this->_getAcceptDetails();

        return isset($acceptDetails[$name]) ? $acceptDetails[$name] : '';
    }

    /**
     * @return array
     */
    protected function _getCarriersList() {
        return $this->_getMinder()->getCarriersList();
    }

    /**
     * @return array
     */
    protected function _getPurchaseOrderLine() {
        if (false === ($result = $this->_getMinder()->getPoLine($this->_getLineNo(), $this->_getPurchaseOrder())))
            return array();
        return $result;
    }

    private function acceptDetailsAreValid()
    {
        if (empty($this->carrierId))
            $this->_getResult()->errors[] = 'Carrier is empty.';
        else {
            $carriersList = $this->_getCarriersList();
            if (!isset($carriersList[$this->carrierId]))
                $this->_getResult()->errors[] = 'Carrier #' . $this->carrierId . ' not found.';
        }

        $this->_getResult()->purchaseOrderLine = $this->_getPurchaseOrderLine();
        if (empty($this->_getResult()->purchaseOrderLine))
            $this->_getResult()->errors[] = 'Purchase Order Line not found.';

        return empty($this->_getResult()->errors);
    }

    /**
     * @return void
     */
    protected function _doAccept() {
        if (!$this->acceptDetailsAreValid())
            return;

        $transaction = new Transaction_GRNDB();

        $transaction->carrierId = $this->carrierId;
        $transaction->vehicleRegistration = $this->vehicleRegistration;
        $transaction->conNoteNo = $this->conNoteNo;
        $transaction->shipDate  = $this->shipDate;
        $transaction->hasContainer = !empty($this->containerNo);
        $transaction->orderNo = $this->_getPurchaseOrder();
        $transaction->orderLineNo = $this->_getLineNo();
        $transaction->deliveryTypeId = 'PO';

        $transaction->crateQty    = empty($this->crateQty) ? 0 : intval($this->crateQty);
        $transaction->crateQty    = is_nan($transaction->crateQty) ? 0 : $transaction->crateQty;
        $transaction->crateQty    = ($transaction->crateQty < 0 ) ? 0 : $transaction->crateQty;

        $transaction->crateTypeId = $this->crateTypeId;
        $transaction->palletOwnerId = empty($this->palletOwnerId) ? 'N' : $this->palletOwnerId;
        $transaction->crateOwnerId = $this->crateOwnerId;

        $transaction->palletQty = empty($this->palletQty) ? 0 : intval($this->palletQty);
        $transaction->palletQty = is_nan($transaction->palletQty) ? 0 : $transaction->palletQty;
        $transaction->palletQty = $transaction->palletQty < 0 ? 0 : $transaction->palletQty;

        if (false === ($result = $this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResult()->errors[] = 'Error executing transaction ' .$transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
        } else {
            $tmpArr = preg_split('#:|\|#si', $result);
            $this->_getResult()->grn = $tmpArr[1];
            $this->_getResult()->purchaseOrder = $tmpArr[3];
            $this->_getResult()->messages[] = $transaction->transCode . ' ' . $transaction->transClass . ': ' . $tmpArr[5];
        }
    }

    public function doAccept($purchaseOrder, $lineNo, $acceptDetails) {
        $this->_setLineNo($lineNo)
                ->_setPurchaseOrder($purchaseOrder)
                ->_setAcceptDetails($acceptDetails)
                ->_doAccept();

        return $this->_getResult();
    }

}