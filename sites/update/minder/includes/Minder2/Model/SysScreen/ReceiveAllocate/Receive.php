<?php

/**
 * @throws Exception
 * @property string $grn
 * @property integer $totalReceivedUnits
 * @property array $pickLabels
 * @property string $orderNo
 * @property string $orderLineNo
 * @property string $printer
 * @property integer $labelQty1
 * @property integer $unitPerLabel1
 * @property integer $labelQty2
 * @property integer $unitPerLabel2
 * @property integer $totalPickLabelUnits
 * @property string $receiveLocation
 */
class Minder2_Model_SysScreen_ReceiveAllocate_Receive {
    protected $_pickLabels = null;

    protected $_printerObject = null;

    /**
     * @var int
     */
    protected $_totalReceivedUnits = null;

    /**
     * @var int
     */
    protected $_labelQty1 = null;

    /**
     * @var int
     */
    protected $_unitPerLabel1 = null;

    /**
     * @var int
     */
    protected $_labelQty2 = null;

    /**
     * @var int
     */
    protected $_unitPerLabel2 = null;

    protected $_grn = null;
    protected $_purchaseOrderNo = null;
    protected $_purchaseLineNo  = null;
    protected $_prodId = null;
    protected $_receiveLocation = null;
    protected $_defaultReceiveLocation = null;
    protected $_printer = null;

    protected $_pickItemRows = array();
    protected $_pickOrderRows = array();

    protected $_purchaseOrderLine = null;

    /**
     * @var Transaction_Response_GRNVP
     */
    protected $_grnvpResponse = null;

    protected $_zoneDevices = array();

    /**
     * @var Minder
     */
    protected $_minder = null;

    /**
     * @var string
     */
    protected $_trolleyDeviceId = null;

    /**
     * @var Minder2_Model_SysScreen_ReceiveAllocate_ReceiveResponse
     */
    protected $_response = null;

    protected $_totalPickLabelUnits = null;

    function __set($name, $value)
    {
        switch ($name) {
            case 'grn':
                $this->_setGrn($value);
                return;
            case 'totalReceivedUnits':
                $this->_setTotalReceivedUnits($value);
                break;
            case 'orderNo':
                $this->_setPurchaseOrderNo($value);
                break;
            case 'orderLineNo':
                $this->_setPurchaseLineNo($value);
                break;
            case 'printer':
                $this->_setPrinter($value);
                break;
            case 'pickLabels':
                $this->_setPickLabels($value);
                break;
            case 'labelQty1':
                $this->_setLabelQty1($value);
                break;
            case 'unitPerLabel1':
                $this->_setUnitPerLabel1($value);
                break;
            case 'labelQty2':
                $this->_setLabelQty2($value);
                break;
            case 'unitPerLabel2':
                $this->_setUnitPerLabel2($value);
                break;
            case 'totalPickLabelUnits':
                $this->_setTotalPickLabelUnits($value);
                break;
            case 'receiveLocation':
                $this->_setReceiveLocation($value);
                break;
        }
    }

    protected function _setTotalPickLabelUnits($val) {
        $val = (is_numeric($val)) ? intval($val) : 0;
        $this->_totalPickLabelUnits = ($val < 0) ? 0 : $val;
    }

    protected function _getTotalPickLabelUnits() {
        if (is_null($this->_totalPickLabelUnits))
            return 0;

        return $this->_totalPickLabelUnits;
    }

    protected function _setLabelQty1($val) {
        $this->_labelQty1 = is_numeric($val) ? intval($val) : 0;
        return $this;
    }

    protected function _getLabelQty1() {
        if (is_null($this->_labelQty1))
            $this->_setLabelQty1(0);

        return $this->_labelQty1;
    }

    protected function _setLabelQty2($val) {
        $this->_labelQty2 = is_numeric($val) ? intval($val) : 0;
        return $this;
    }

    protected function _getLabelQty2() {
        if (is_null($this->_labelQty2))
            $this->_setLabelQty2(0);

        return $this->_labelQty2;
    }

    protected function _setUnitPerLabel1($val) {
        $this->_unitPerLabel1 = is_numeric($val) ? intval($val) : 0;
        return $this;
    }

    protected function _getUnitPerLabel1() {
        if (is_null($this->_unitPerLabel1))
            $this->_setUnitPerLabel1(0);

        return $this->_unitPerLabel1;
    }

    protected function _setUnitPerLabel2($val) {
        $this->_unitPerLabel2 = is_numeric($val) ? intval($val) : 0;
        return $this;
    }

    protected function _getUnitPerLabel2() {
        if (is_null($this->_unitPerLabel2))
            $this->_setUnitPerLabel2(0);

        return $this->_unitPerLabel2;
    }

    protected function _getTotalVerified() {
        return $this->_getLabelQty1() * $this->_getUnitPerLabel1() + $this->_getLabelQty2() * $this->_getUnitPerLabel2() + $this->_getTotalPickLabelUnits();
    }

    /**
     * @return Minder2_Model_SysScreen_ReceiveAllocate_ReceiveResponse
     */
    protected function _getResponse() {
        if (is_null($this->_response))
            $this->_response = new Minder2_Model_SysScreen_ReceiveAllocate_ReceiveResponse();

        return $this->_response;
    }

    protected function _setGrn($val) {
        $this->_grn = $val;
        return $this;
    }

    protected function _setTotalReceivedUnits($val) {
        if (is_numeric($val))
            $this->_totalReceivedUnits = intval($val);
        else
            $this->_totalReceivedUnits = 0;

        return $this;
    }

    protected function _setPickLabels(array $val) {
        $this->_pickLabels = $val;
        return $this;
    }

    protected function _setPurchaseOrderNo($val) {
        $this->_purchaseOrderNo = $val;
        $this->_setPurchaseOrderLine(null);
        return $this;
    }

    protected function _setPurchaseLineNo($val) {
        $this->_purchaseLineNo = $val;
        $this->_setPurchaseOrderLine(null);
        return $this;
    }

    protected function _setProdId($val) {
        $this->_prodId = $val;
        return $this;
    }

    protected function _setReceiveLocation($val) {
        $this->_receiveLocation = $val;
        return $this;
    }

    protected function _setPrinter($val) {
        $this->_printer = $val;
        return $this;
    }

    protected function _getPurchaseOrderNo() {
        return $this->_purchaseOrderNo;
    }

    protected function _getPurchaseLineNo() {
        return $this->_purchaseLineNo;
    }

    protected function _getGrn() {
        if (empty($this->_grn))
            throw new Exception('GRN is empty');

        return $this->_grn;
    }

    protected function _fetchDefaultReceiveLocation() {
        $sql = "
            SELECT FIRST 1
                WH_ID || LOCN_ID
            FROM
                PURCHASE_ORDER
                LEFT JOIN LOCATION ON PURCHASE_ORDER.PO_RECEIVE_WH_ID = LOCATION.WH_ID
            WHERE
                PURCHASE_ORDER.PURCHASE_ORDER = ?
                AND MOVE_STAT=?
                AND LOCN_STAT = ?
            ";

        $result = $this->_getMinder()->fetchOne($sql, $this->_getPurchaseOrderNo(), 'RC', 'OK');

        if (false === $result)
            return '';

        return $result;
    }

    protected function _getDefaultReceiveLocation() {
        if (is_null($this->_defaultReceiveLocation))
            $this->_defaultReceiveLocation = $this->_fetchDefaultReceiveLocation();

        return $this->_defaultReceiveLocation;
    }

    protected function _getReceiveLocation() {
        if (empty($this->_receiveLocation))
            $this->_setReceiveLocation($this->_getDefaultReceiveLocation());

        return $this->_receiveLocation;
    }

    protected function _fetchProdId() {
        $poLine = $this->_getPurchaseOrderLine();
        return $poLine['PROD_ID'];
    }

    protected function _getProdId() {
        if (empty($this->_prodId))
            $this->_setProdId($this->_fetchProdId());

        return $this->_prodId;
    }

    protected function _getPrinterId() {
        return $this->_printer;
    }

    protected function _getTotalReceivedUnits() {
        if (empty($this->_totalReceivedUnits))
            $this->_setTotalReceivedUnits(0);

        return $this->_totalReceivedUnits;
    }

    protected function _getMinder() {
        if (is_null($this->_minder))
            $this->_minder = Minder::getInstance();

        return $this->_minder;
    }

    protected function _setGrnvpResponse(Transaction_Response_GRNVP $val) {
        $this->_grnvpResponse = $val;
    }

    protected function _getGrnvpResponse() {
        if (is_null($this->_grnvpResponse))
            throw new Exception('GRNV P transaction response is null.');

        return $this->_grnvpResponse;
    }

    protected function _runGrnvpTransaction() {
        $transaction                    = new Transaction_GRNVP();
        $transaction->orderNo           = $this->_getPurchaseOrderNo();
        $transaction->orderLineNo       = $this->_getPurchaseLineNo();
        $transaction->deliveryTypeId    = 'PO';
        $transaction->grnNo             = $this->_getGrn();
        $transaction->locationId        = $this->_getReceiveLocation();
        $transaction->productId         = $this->_getProdId();
        $transaction->printerId         = $this->_getPrinterId();
        $transaction->totalVerified     = $this->_getTotalVerified();
        $transaction->qtyOnLabels1      = $this->_getUnitPerLabel1();
        $transaction->qtyOfLabels1      = $this->_getLabelQty1();
        $transaction->qtyOnLabels2      = $this->_getUnitPerLabel2();
        $transaction->qtyOfLabels2      = $this->_getLabelQty2();
        $transaction->qtyOnLabels3      = $this->_getTotalPickLabelUnits();
        $transaction->qtyOfLabels3      = ($transaction->qtyOnLabels3 > 0) ? 1 : 0;

        if (false === ($responseString = $this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResponse()->errors[] = 'Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            $this->_setGrnvpResponse($transaction->parseResponse(''));
        } else {
            $this->_setGrnvpResponse($transaction->parseResponse($responseString));

            if (!$this->_getGrnvpResponse()->isSuccess())
                $this->_getResponse()->errors[] = 'Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $responseString;
            else
                $this->_getResponse()->messages[] = 'GRNV P transaction success.';
        }

        return $this->_getGrnvpResponse()->isSuccess();
    }

    protected function _runPkalFTransaction($pickLabelNo) {
        $transaction = new Transaction_PKALF();
        $transaction->deviceId = Minder2_Environment::getCurrentDevice()->DEVICE_ID;
        $transaction->pickLabelNo = $pickLabelNo;
        $transaction->pickerId = $this->_getMinder()->userId;
        $transaction->prodId = $this->_getProdId();
        $transaction->orderNo = $this->_getPickOrder($pickLabelNo);
        $transaction->companyId = $this->_getPickOrderCompanyId($transaction->orderNo);

        //if (false === ($this->_getMinder()->doTransactionResponse($transaction))) {
        if (false === ($resultV6 = $this->_getMinder()->doTransactionResponseV6($transaction ))) {
            $this->_getResponse()->errors[] = 'Error allocating PICK_ITEM #' . $pickLabelNo . ': Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            return false;
        }

        return true;
    }

    protected function _fetchPickItemRow($pickLabelNo) {
        $sql = "SELECT * FROM PICK_ITEM WHERE PICK_LABEL_NO = ?";

        if (false === ($result = $this->_getMinder()->fetchAssoc($sql, $pickLabelNo)))
            return array();

        return $result;
    }

    protected function _getPickItemRow($pickLabelNo, $forceFetch = false) {
        if ($forceFetch || !isset($this->_pickItemRows[$pickLabelNo]))
            $this->_pickItemRows[$pickLabelNo] = $this->_fetchPickItemRow($pickLabelNo);

        return $this->_pickItemRows[$pickLabelNo];
    }

    protected function _getPickOrder($pickLabelNo) {
        $pickItemRow = $this->_getPickItemRow($pickLabelNo);
        return $pickItemRow['PICK_ORDER'];
    }

    protected function _getRequiredQty($pickLabelNo) {
        $pickItemRow = $this->_getPickItemRow($pickLabelNo);
        $pickOrderQty = empty($pickItemRow['PICK_ORDER_QTY']) ? 0 : $pickItemRow['PICK_ORDER_QTY'];
        $pickOrderQty = is_numeric($pickOrderQty) ? intval($pickOrderQty) : 0;

        $pickedQty = empty($pickItemRow['PICKED_QTY']) ? 0 : $pickItemRow['PICKED_QTY'];
        $pickedQty = is_numeric($pickedQty) ? intval($pickedQty) : 0;

        return ($pickOrderQty > $pickedQty) ? $pickOrderQty - $pickedQty : 0;
    }

    protected function _fetchTrolleyDeviceId() {
        $sql = "SELECT FIRST 1 DEVICE_ID FROM SYS_EQUIP WHERE DEVICE_TYPE = 'TR' ORDER BY DEVICE_ID";

        $result = $this->_getMinder()->fetchOne($sql);
        if (false == $result)
            return '';

        return $result;
    }

    protected function _getTrolleyDeviceId() {
        if (is_null($this->_trolleyDeviceId))
            $this->_trolleyDeviceId = $this->_fetchTrolleyDeviceId();

        return $this->_trolleyDeviceId;
    }

    protected function _fetchPickOrderRow($pickOrder) {
        $sql = "SELECT * FROM PICK_ORDER WHERE PICK_ORDER = ?";

        $result = $this->_getMinder()->fetchAssoc($sql, $pickOrder);
        if (false === $result)
            return array();

        return $result;
    }

    protected function _getPickOrderRow($pickOrder) {
        if (!isset($this->_pickOrderRows[$pickOrder]))
            $this->_pickOrderRows[$pickOrder] = $this->_fetchPickOrderRow($pickOrder);

        return $this->_pickOrderRows[$pickOrder];
    }

    /**
     * @param $pickOrder - PICK_ORDER.PICK_ORDER field value to search for
     * @return string
     */
    protected function _getPickOrderWhId($pickOrder) {
        $pickOrderRow = $this->_getPickOrderRow($pickOrder);

        return isset($pickOrderRow['WH_ID']) ? $pickOrderRow['WH_ID'] : '';
    }

    protected function _runPoalDTransaction($pickLabelNo) {
        $transaction = new Transaction_POALD();
        $transaction->pickOrderWhId = $this->_getPickOrderWhId($this->_getPickOrder($pickLabelNo));
        $transaction->trolleyDevice = $this->_getTrolleyDeviceId();
        $transaction->orderNo = $this->_getPickOrder($pickLabelNo);
        $transaction->pickUser = $this->_getMinder()->userId;
        $transaction->allocatedDevice = Minder2_Environment::getCurrentDevice()->DEVICE_ID;
        $transaction->comment = 'Receive Purchase Order Line';

        if (false === ($this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResponse()->errors[] = 'Error allocating PICK_ITEM #' . $pickLabelNo . ': Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            return false;
        }

        return true;
    }

    protected function _getSsnIdCreatedByGrnvp() {
        return $this->_getGrnvpResponse()->issn1;
    }

    protected function _runPkolBTransaction($pickLabelNo, $rerun = false) {
        $transaction = new Transaction_PKOLB();
        $transaction->issnLocation = $rerun ? '          ' : $this->_getReceiveLocation();
        $transaction->ssnId = $this->_getSsnIdCreatedByGrnvp();
        $transaction->pickQty = $rerun ? 0 : $this->_getRequiredQty($pickLabelNo);
        $transaction->pickLabelNo = $pickLabelNo;

        if (false === ($this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResponse()->errors[] = 'Error allocating PICK_ITEM #' . $pickLabelNo . ': Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            return false;
        }

        return true;
    }

    protected function _runPkilDTransaction($pickLabelNo) {
        $transaction = new Transaction_PKILD();
        $transaction->directDeliveryLocation = Minder2_Environment::getInstance()->getSystemControls()->PICK_DIRECT_DELIVERY_LOCATION;
        $transaction->pickLabelNo = $pickLabelNo;
        $transaction->userId = $this->_getMinder()->userId;
        $transaction->pickDevice = Minder2_Environment::getCurrentDevice()->DEVICE_ID;
        $transaction->comment = 'Receive Purchase Order Line';

        if (false === ($this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResponse()->errors[] = 'Error allocating PICK_ITEM #' . $pickLabelNo . ': Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            return false;
        }

        return true;
    }

    protected function _getPickOrderCompanyId($pickOrder) {
        $pickOrderRow = $this->_getPickOrderRow($pickOrder);

        return isset($pickOrderRow['COMPANY_ID']) ? $pickOrderRow['COMPANY_ID'] : '';
    }

    protected function _fetchCompanyZoneDevice($companyId) {
        $sql = "SELECT DEFAULT_DEVICE_ID FROM ZONE WHERE COMPANY_ID = ?";

        $result = $this->_getMinder()->fetchOne($sql, $companyId);

        if (false === $result)
            return '';

        return $result;
    }

    protected function _getCompanyZoneDevice($companyId) {
        if (!isset($this->_zoneDevices[$companyId]))
            $this->_zoneDevices[$companyId] = $this->_fetchCompanyZoneDevice($companyId);

        return $this->_zoneDevices[$companyId];
    }

    protected function _getConveyorDevice($pickOrder) {
        return $this->_getCompanyZoneDevice($this->_getPickOrderCompanyId($pickOrder));
    }

    protected function _runTrpkOTransaction($pickLabelNo) {
        $transaction = new Transaction_TRPKo();
        $transaction->orderNo = $this->_getPickOrder($pickLabelNo);
        $transaction->fromDevice = Minder2_Environment::getCurrentDevice()->DEVICE_ID;
        $transaction->comment = 'Receive Purchase Order Line';
        $transaction->toDevice = $this->_getConveyorDevice($this->_getPickOrder($pickLabelNo));
        $transaction->toDevice = empty($transaction->toDevice) ? $transaction->fromDevice : $transaction->toDevice;

        if (false === ($this->_getMinder()->doTransactionResponse($transaction))) {
            $this->_getResponse()->errors[] = 'Error allocating PICK_ITEM #' . $pickLabelNo . ': Error executing transaction ' . $transaction->transCode . ' ' . $transaction->transClass . ': ' . $this->_getMinder()->lastError;
            return false;
        }

        return true;
    }

    protected function _getPickLabels() {
        if (is_null($this->_pickLabels))
            $this->_setPickLabels(array());

        return $this->_pickLabels;
    }

    protected function _getPrinter() {
        if (is_null($this->_printerObject))
            $this->_printerObject = $this->_getMinder()->getPrinter(null, $this->_getPrinterId());

        return $this->_printerObject;
    }

    protected function _printPickLabel($pickLabelNo) {
        $labelPrinter = new Minder_LabelPrinter_PickLabel();
        $printResponse = $labelPrinter->doPrint($pickLabelNo, $this->_getPrinter());

        $this->_getResponse()->messages = array_merge($this->_getResponse()->messages, $printResponse->messages);
        $this->_getResponse()->warnings = array_merge($this->_getResponse()->warnings, $printResponse->warnings, $printResponse->errors);
    }

    protected function _allocatePickItems() {
        foreach ($this->_getPickLabels() as $pickLabelNo) {

            if (false === $this->_runPkalFTransaction($pickLabelNo))
                continue;

            if (false === $this->_runPoalDTransaction($pickLabelNo))
                continue;

            if (false === $this->_runPkolBTransaction($pickLabelNo))
                continue;

            $pickItemRow = $this->_getPickItemRow($pickLabelNo, true);

            if ($pickItemRow['PICK_LINE_STATUS'] == 'PG')
                $this->_runPkolBTransaction($pickLabelNo, true);

            if (false === $this->_runPkilDTransaction($pickLabelNo))
                continue;

            if (false ===  $this->_runTrpkOTransaction($pickLabelNo))
                continue;

            $this->_getResponse()->messages[] = 'PICK_ITEM #' . $pickLabelNo . ': allocated.';

            $this->_printPickLabel($pickLabelNo);
        }
    }

    protected function _setPurchaseOrderLine($val) {
        $this->_purchaseOrderLine = $val;
        $this->_setProdId(null);
        return $this;
    }

    protected function _fetchPurchaseOrderLine() {
        $result = $this->_getMinder()->getPoLine($this->_getPurchaseLineNo(), $this->_getPurchaseOrderNo());

        if (false === $result)
            return array();

        return $result;
    }

    protected function _getPurchaseOrderLine() {
        if (is_null($this->_purchaseOrderLine))
            $this->_setPurchaseOrderLine($this->_fetchPurchaseOrderLine());

        return $this->_purchaseOrderLine;
    }

    protected function _getPoLineStatus() {
        $purchaseOrderLine = $this->_getPurchaseOrderLine();

        return $purchaseOrderLine['PO_LINE_STATUS'];
    }

    protected function _directDeliveryEnabled() {
        return Minder2_Environment::getInstance()->getSystemControls()->RECEIVE_DIRECT_DELIVERY == 'T';
    }

    protected function _validate() {
        $purchaseOrder = $this->_getPurchaseOrderNo();
        if (empty($purchaseOrder))
            $this->_getResponse()->errors[] = 'PURCHASE_ORDER is empty.';

        $poLine = $this->_getPurchaseLineNo();
        if (empty($poLine))
            $this->_getResponse()->errors[] = 'PO_LINE is empty.';

        $purchaseOrderLine = array();
        if (!empty($purchaseOrder) && !empty($poLine)) {
             $purchaseOrderLine = $this->_getPurchaseOrderLine();

            if (empty($purchaseOrderLine))
                $this->_getResponse()->errors[] = 'PURCHASE_ORDER_LINE #(' . $purchaseOrder . ',' . $poLine . ') not found.';
        }

        if (!empty($purchaseOrderLine)) {
            $prodId = $this->_getProdId();

            if (empty($prodId))
                $this->_getResponse()->errors[] = 'PROD_ID is empty.';

            $poLineStatus = $this->_getPoLineStatus();

            if ('CL' == $poLineStatus)
                $this->_getResponse()->errors[] = 'PURCHASE_ORDER_LINE is completed already.';

            if ('OS' == $poLineStatus)
                $this->_getResponse()->errors[] = 'PURCHASE_ORDER_LINE is oversupplied.';
        }

        $grn = $this->_getGrn();
        if (empty($grn))
            $this->_getResponse()->errors[] = 'GRN is empty.';

        $totalReceivedUnits = $this->_getTotalReceivedUnits();
        $totalVerified = $this->_getTotalVerified();
        if ($totalReceivedUnits < 1) {
            $this->_getResponse()->errors[] = 'Total Received Units should be greater then 0.';
        } else {
            if ($totalReceivedUnits < $totalVerified)
                $this->_getResponse()->errors[] = 'Total Received Units should be greater then Total Print Unit.';
        }

        if ($totalVerified < 1) {
            $this->_getResponse()->errors[] = 'Total Print Unit should be greater then 0.';
        }

        if (!$this->_directDeliveryEnabled() && count($this->_getPickLabels()) > 0)
            $this->_getResponse()->errors[] = 'Receive Direct Delivery is disabled. Check system setup.';

        return count($this->_getResponse()->errors) < 1;
    }

    public function doReceive() {
        if (!$this->_validate())
            return $this->_getResponse();

        if (!$this->_runGrnvpTransaction())
            return $this->_getResponse();

        if ($this->_directDeliveryEnabled())
            $this->_allocatePickItems();

        return $this->_getResponse();
    }
}
