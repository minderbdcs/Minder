<?php

class Minder_Controller_Action_Helper_AwaitingChecking extends Zend_Controller_Action_Helper_Abstract {
    const SSCC_DIMENSIONS = 'SSCC_DIMENSIONS';
    const SSCC_LABELS = 'SSCC_LABELS';
    const SERIAL_NUMBERS = 'SERIAL_NUMBERS';

    const COMPLETED_FLAG = 'completed';

    /**
     * @var Minder2_Options_PickOrderType_Manager
     */
    protected $_orderTypeManager;

    /**
     * @var Minder2_Options
     */
    protected $_options;

    public function commitSerialNumbers($serialNumbers, Minder_JSResponse $response = null) {
        $response = is_null($response) ? new Minder_JSResponse() : $response;

        try {
            $device  = Minder2_Environment::getCurrentDevice();

            foreach ($serialNumbers as $serialNumber) {
                $transaction = new Transaction_PKSNS(
                    $serialNumber['PICK_LABEL_NO'],
                    $serialNumber['SERIAL_NUMBER'],
                    $device->WH_ID,
                    $device->LOCN_ID,
                    'Record Serial Number during despatch',
                    $serialNumber['PROD_ID']
                );

                $this->_getMinder()->doTransactionResponseV6($transaction);
            }
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        $response->serialNumbers = $serialNumbers;
        return $response;
    }

    public function shouldCheckEachLine(Minder_PickOrder_Collection $pickOrders) {
        $result = false;
        $checkLineSubTypes = $this->_getPostCheckLinesSubTypes();

        if (count($checkLineSubTypes)) {
            $commonTypes = array_intersect($checkLineSubTypes, array_unique($pickOrders->PICK_ORDER_SUB_TYPE));
            $result = (count($commonTypes) > 0);
        }

        return $result;
    }

    public function supportsEdiCheckingMethod() {
        return count($this->_getSsccSubTypes()) > 0;
    }

    public function shouldCreateSscc(Minder_PickOrder_Collection $pickOrders) {
        $result = false;
        $sscSubTypes = $this->_getSsccSubTypes();

        if (count($sscSubTypes)) {
            $commonTypes = array_intersect($sscSubTypes, array_unique($pickOrders->PICK_ORDER_SUB_TYPE));
            $result = (count($commonTypes) > 0);
        }

        return $result || $pickOrders->hasEdiOrders();
    }

    public function shouldRecordSerialNumber(Minder_PickOrder_Collection $pickOrders) {
        $result = false;

        $serialNumberSubTypes = $this->_getSerialNumberSubTypes();

        if (count($serialNumberSubTypes) > 0) {
            $commonTypes = array_intersect($serialNumberSubTypes, array_unique($pickOrders->PICK_ORDER_SUB_TYPE));
            $result = (count($commonTypes) > 0);
        }

        return $result;
    }

    protected function _getSsccSubTypes() {
        return array_unique(Minder_ArrayUtils::mapField($this->_getOrderTypeManager()->getEdiTypes(), 'orderSubType'));
    }

    protected function _getPostCheckLinesSubTypes() {
        return array_unique($this->_getOptions()->getPostCheckLinesSubTypes());
    }

    /**
     * @return Minder2_Options_PickOrderType_Manager
     */
    protected function _getOrderTypeManager()
    {
        if (empty($this->_orderTypeManager)) {
            $this->_orderTypeManager = new Minder2_Options_PickOrderType_Manager();
        }

        return $this->_orderTypeManager;
    }

    /**
     * @return Minder2_Options
     */
    protected function _getOptions()
    {
        if (empty($this->_options)) {
            $this->_options = new Minder2_Options();
        }

        return $this->_options;
    }

    protected function _getSession() {
        return $this->getActionController()->session;
    }

    public function cleanSsccDimensions() {
        $this->storeSsccDimensions(array());
    }

    public function storeSsccDimensions($data) {
        $this->_getSession()->{static::SSCC_DIMENSIONS} = $data;
    }

    public function loadSsccDimensions() {
        return isset($this->_getSession()->{static::SSCC_DIMENSIONS}) ?  $this->_getSession()->{static::SSCC_DIMENSIONS} : array();
    }

    public function loadSsccLabels() {
        return isset($this->_getSession()->{static::SSCC_LABELS}) ?  $this->_getSession()->{static::SSCC_LABELS} : array();
    }

    public function storeSsccLabels($data) {
        $this->_getSession()->{static::SSCC_LABELS} = $data;
    }

    public function cleanSsccLabels() {
        $this->storeSsccLabels(array());
    }

    public function loadSerialNumbers() {
        $checkingStatus = $this->getRequest()->getParam('checkingStatus', array());
        return isset($checkingStatus['serialNumbers']) ?  $checkingStatus['serialNumbers'] : array();
    }

    public function storeSerialNumbers($data) {
        $this->_getSession()->{static::SERIAL_NUMBERS} = $data;
    }

    public function cleanSerialNumbers() {
        $this->storeSerialNumbers(array());
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    public function getPackSscc($ssccLabel) {
        return $this->_getMinder()->fetchAssoc('SELECT * FROM PACK_SSCC WHERE PS_SSCC = ?', $ssccLabel);
    }

    public function getDespatchPrinterForPickOrder($pickOrder) {
        $sql = "
            SELECT FIRST 1
                CONTROL.DESPATCH_LABEL_PRINTER AS CONTROL_PRINTER,
                COMPANY.DESPATCH_LABEL_PRINTER AS COMPANY_PRINTER,
                WAREHOUSE.DESPATCH_LABEL_PRINTER AS WH_PRINTER
            FROM
                PICK_ORDER
                LEFT JOIN COMPANY ON PICK_ORDER.COMPANY_ID = COMPANY.COMPANY_ID
                LEFT JOIN WAREHOUSE ON PICK_ORDER.WH_ID = WAREHOUSE.WH_ID
                JOIN CONTROL ON 1=1
            WHERE
                PICK_ORDER.PICK_ORDER = ?
        ";
        $result = $this->_getMinder()->fetchAssoc($sql, $pickOrder);

        if (false === $result) {
            return $this->_getMinder()->limitPrinter;
        }

        if (!empty($result['COMPANY_PRINTER'])) {
            return $result['COMPANY_PRINTER'];
        }

        if (!empty($result['WH_PRINTER'])) {
            return $result['WH_PRINTER'];
        }

        if (!empty($result['CONTROL_PRINTER'])) {
            return $result['CONTROL_PRINTER'];
        }

        return $this->_getMinder()->limitPrinter;
    }

    public function fillCheckDetailsFromSscc($checkDetails, $ssccList) {
        foreach ($checkDetails as &$checkDetail) {
            if ($checkDetail['SELECTED']) {
                $checkDetail['CHECKED_QTY'] = $this->_getTotalChecked($ssccList, $checkDetail);
            }
        }

        return $checkDetails;
    }

    protected function _getTotalChecked($ssccList, $checkDetail) {
        $result = 0;

        foreach($ssccList as $sscc) {
            if (!$this->_isSsccCompleted($sscc)) {
                continue;
            }

            if ($sscc['PS_PICK_LABEL_NO'] === $checkDetail['PICK_LABEL_NO']) {
                $result += intval($sscc['CHECKED_QTY']);
            }
        }

        return $result;
    }

    protected function _isSsccCompleted($sscc) {
        return isset($sscc[static::COMPLETED_FLAG]);
    }

    protected function _findSscc($ssccLines, array $ssccLabels) {
        return array_filter($ssccLines, function($sscc)use($ssccLabels){
            return in_array($sscc['PS_OUT_SSCC'], $ssccLabels);
        });
    }

    protected function _findSsccByProdId($ssccList, $checkDetails, $prodId) {
        $filteredDetails = array_filter($checkDetails, function($checkDetail)use($prodId){
            return $checkDetail['PROD_ID'] == $prodId || $checkDetail['ALTERNATE_ID'] == $prodId;
        });

        $pickLabels = Minder_ArrayUtils::mapField($filteredDetails, 'PICK_LABEL_NO');

        return array_filter($ssccList, function($sscc)use($pickLabels){return in_array($sscc['PS_PICK_LABEL_NO'], $pickLabels);});
    }

    protected function _canBeRePacked($ssccList) {
        $haveBadStatuses = array_filter($ssccList, function($sscc){
            return !in_array(strtoupper($sscc['PS_SSCC_STATUS']), array('DC', 'CL'));
        });

        return count($haveBadStatuses) < 1;
    }

    protected function _getTotalProducts($ssccList) {
        $result = 0;
        foreach ($ssccList as $sscc) {
            $result += intval($sscc['CHECKED_QTY']);
        }

        return $result;
    }

    protected function _validateRePackData($ssccWithProduct, $ssccToRePack, $repackData, Minder_JSResponse $response) {
        if (!$this->_canBeRePacked($ssccToRePack)) {
            $response->addErrors('Some packs cannot be re-packed.');
        }

        if (count($repackData) < 2) {
            $response->addErrors('Nothing to repack. Scan at least two SSCC labels.');
        }

        if ($this->_getTotalProducts($ssccWithProduct) < 1) {
            $response->addErrors('Product not found.');
        }
    }

    protected function _updateSscc($sscc) {
        $sql = "
            UPDATE
                PACK_SSCC
            SET
                PS_QTY_SHIPPED = ?,
                PS_OUT_SSCC = ?
            WHERE
                PS_SSCC = ?
        ";

        if (false === $this->_getMinder()->execSQL($sql, array($sscc['PS_QTY_SHIPPED'], $sscc['PS_OUT_SSCC'], $sscc['PS_SSCC']))) {
            throw new Exception($this->_getMinder()->lastError);
        }
    }

    protected function _insertSscc($sscc) {
        if (isset($sscc['RECORD_ID'])) {
            unset($sscc['RECORD_ID']);
        }

        $sql = "
            INSERT INTO PACK_SSCC (" . implode(', ', array_keys($sscc)) . ")
            VALUES (" . implode(', ', array_fill(0, count($sscc), '?')) . ")
        ";

        if (false === $this->_getMinder()->execSQL($sql, array_values($sscc))) {
            throw new Exception($this->_getMinder()->lastError);
        }
    }

    protected function _totalSsccPerPack($ssccList) {
        $result = array();

        foreach (Minder_ArrayUtils::recursiveGroup($ssccList, array('PS_OUT_SSCC')) as $outSscc => $ssccInPack) {
            $result[$outSscc] = count($ssccInPack);
        }

        return $result;
    }

    protected function _updateWeight($repackData) {
        $sql = "UPDATE PACK_SSCC SET PS_SSCC_WEIGHT = ? WHERE PS_OUT_SSCC = ? AND PS_SSCC_STATUS IN ('CL', 'DC')";

        foreach ($repackData as $dataRow) {
            if (false === $this->_getMinder()->execSQL($sql, array($dataRow['weight'], $dataRow['sscc']))) {
                throw new Exception($this->_getMinder()->lastError);
            }
        }
    }

    public function rePack($ssccLines, $repackData, $checkDetails, $prodId, Minder_JSResponse $response) {
        $ssccLabels = Minder_ArrayUtils::mapField($repackData, 'sscc');
        $ssccToRePack = $this->_findSscc($ssccLines, $ssccLabels);
        $ssccWithProduct = $this->_findSsccByProdId($ssccToRePack, $checkDetails, $prodId);

        $this->_validateRePackData($ssccWithProduct, $ssccToRePack, $repackData, $response);

        if ($response->hasErrors()) {
            return;
        }

        $tmpSsccWithProduct = current($ssccWithProduct);
        $ssccToClone = $this->getPackSscc($tmpSsccWithProduct['PS_SSCC']);
        $pickOrder = $this->_getMinder()->getPickOrder($tmpSsccWithProduct['PS_PICK_ORDER']);
        $ssccPerPacks = $this->_totalSsccPerPack($ssccToRePack);

        $ssccToUpdate = array();

        foreach ($repackData as $dataRow) {
            $ssccList = $this->_findSscc($ssccToRePack, array($dataRow['sscc']));
            $ssccWithProduct = $this->_findSsccByProdId($ssccList, $checkDetails, $prodId);

            if (empty($ssccWithProduct)) {
                if (intval($dataRow['qty']) > 0) {
                    $ssccWithProduct[] = array_merge($ssccToClone, array(
                        'PS_SSCC' => 'new',
                        'PS_QTY_SHIPPED' => $dataRow['qty'],
                        'PS_OUT_SSCC' => $dataRow['sscc'],
                        'PS_SSCC_STATUS' => 'DC'
                    ));
                }
            } else {
                foreach ($ssccWithProduct as &$sscc) {
                    $sscc['PS_QTY_SHIPPED'] = 0;
                }

                $tmpSscc = array_shift($ssccWithProduct);
                $tmpSscc['PS_QTY_SHIPPED'] = $dataRow['qty'];
                array_unshift($ssccWithProduct, $tmpSscc);
            }

            $ssccToUpdate = array_merge($ssccToUpdate, $ssccWithProduct);
        }

        $ssccForExchange = array_filter($ssccToUpdate, function($sscc) use($ssccPerPacks) {
            return ($sscc['PS_SSCC'] != 'new') && (intval($sscc['PS_QTY_SHIPPED']) < 1) && ($ssccPerPacks[$sscc['PS_OUT_SSCC']] > 1);
        });
        $ssccToUpdate = array_filter($ssccToUpdate, function($sscc) use($ssccPerPacks) {
            return ($sscc['PS_SSCC'] == 'new') || (intval($sscc['PS_QTY_SHIPPED']) > 0) || ($ssccPerPacks[$sscc['PS_OUT_SSCC']] < 2);
        });

        foreach ($ssccToUpdate as $sscc) {
            if ($sscc['PS_SSCC'] == 'new') {
                $exchangeSscc = array_shift($ssccForExchange);

                if (empty($exchangeSscc)) {
                    $sscc['PS_SSCC'] = $this->_getNextSscc($pickOrder);
                    $this->_insertSscc($sscc);
                } else {
                    $sscc['PS_SSCC'] = $exchangeSscc['PS_SSCC'];
                    $this->_updateSscc($sscc);
                }
            } else {
                $this->_updateSscc($sscc);
            }
        }

        foreach ($ssccForExchange as $sscc) {
            $this->_updateSscc($sscc);
        }

        $this->_updateWeight($repackData);
    }

    private function _getNextSscc(PickOrder $pickOrder) {
        return $this->_getMinder()->fetchOne('SELECT SSCC_ID FROM NEXT_SSCC_ID(?)', $pickOrder->companyId);
    }

    private function _findSsccByPickLabels(array $pickLabelNos) {
        if (empty($pickLabelNos)) {
            return array();
        }

        $sql = "SELECT * FROM PACK_SSCC WHERE PS_PICK_LABEL_NO IN (" . implode(', ', array_fill(0, count($pickLabelNos), '?')) . ")";

        array_unshift($pickLabelNos, $sql);

        return call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $pickLabelNos);
    }

    private function _validateChangeCarrierServiceData($pickLabelNos, $despatchIds, $carrierId, $serviceId, Minder_JSResponse $response) {
        if (empty($carrierId)) {
            $response->addErrors("CARRIER_ID is empty.");
        }

        if (empty($serviceId)) {
            $response->addErrors("CARRIER_SERVICE.RECORD_ID is empty.");
        }

        if (count($pickLabelNos) < 1) {
            $response->addErrors("No rows selected.");
        }

        if (count($despatchIds) > 1) {
            $response->addErrors("Cannot change Carrier for multiply despatches.");
        }

        if (count($despatchIds) < 1) {
            $response->addErrors("No DESPATCH_ID found.");
        }

        if (count($despatchIds) == 1) {
            $tmpDespatch = current($despatchIds);
            if (empty($tmpDespatch)) {
                $response->addErrors("DESPATCH_ID is empty.");
            }
        }

        return $response;
    }

    public function _changeCarrierService($pickLabelNos, $carrierId, $serviceId, Minder_JSResponse $response) {
        $ssccList = $this->_findSsccByPickLabels($pickLabelNos);
        $despatchIds = array_unique(Minder_ArrayUtils::mapField($ssccList, 'PS_DESPATCH_ID'));

        $response = $this->_validateChangeCarrierServiceData($pickLabelNos, $despatchIds, $carrierId, $serviceId, $response);

        if ($response->hasErrors()) {
            return $response;
        }

        $despatchMap = Minder_ArrayUtils::mapKey($ssccList, "PS_DESPATCH_ID");

        foreach ($despatchIds as $despatchId) {
            $pickOrder = $this->_getMinder()->getPickOrder($despatchMap[$despatchId]['PS_PICK_ORDER']);

            $transaction = new Transaction_DSUC($despatchId, $carrierId, $serviceId, $pickOrder);

            try {
                $this->_getMinder()->doTransactionResponseV6($transaction);
            } catch (Exception $e) {
                $response->addErrors('Error updating Carrier: ' . $e->getMessage());
            }
        }

        return $response;
    }

    public function getRetailUnit($code) {
        return $this->_getMinder()->fetchAssoc('SELECT PROD_EAN, PROD_ID, PROD_ISSUE_QTY FROM PROD_EAN WHERE PROD_EAN = ?', $code);
    }

    public function validateAcceptSsccRequest(Zend_Controller_Request_Abstract $request, Minder_JSResponse $response = null) {
        $response = (is_null($response)) ? new Minder_JSResponse() : $response;

        $labelData = $request->getParam('labelData');
        $isFillingDimensions = $request->getParam('isFillingDimensions', 'false');

        $volumeRequired = (strtolower($request->getParam('volumeRequired', 'false')) == 'true');
        $weightRequired = (strtolower($request->getParam('weightRequired', 'false')) == 'true');


        if (strtolower($isFillingDimensions) != 'true') {
            $response->addErrors('You should scan SSCC label before.');
        } else {
            if (empty($labelData)) {
                $response->addErrors('No label data provided.');
            } else {
                if (isset($labelData['CALCULATED']['TOTAL_VOL'])) {
                    if ($volumeRequired && round($labelData['CALCULATED']['TOTAL_VOL'], 4) < 0.0001) {
                        $response->addErrors('Please fill volume information.');
                    }
                } else {
                    $response->addErrors('Bad label data.');
                }
                if (isset($labelData['CALCULATED']['TOTAL_WT'])) {
                    if ($weightRequired && round($labelData['CALCULATED']['TOTAL_WT'], 4) < 0.0001) {
                        $response->addErrors('Please fill weight information.');
                    }
                } else {
                    $response->addErrors('Bad label data.');
                }
            }
        }

        return $response;
    }

    private function _getSerialNumberSubTypes()
    {
        return array_unique($this->_getOptions()->getSerialNumberSubTypes());
    }
}