<?php
class Minder_Controller_Action_Helper_AwaitingCheckingEdi extends Zend_Controller_Action_Helper_Abstract {

    public function acceptConnote(Minder_PickOrder_Collection $orders, $pickBlock, Minder_JSResponse $response = null) {
        $response = is_null($response) ?  new Minder_JSResponse() : $response;

        if (count($orders) < 1) {
            $response->addErrors('No orders selected');
            return $response;
        }

        $dcNo = $this->getDcNoByLocationId($pickBlock);
        $packSsccList = $this->_fetchSsccPacks($orders, $dcNo);
        $pickItemList = Minder_ArrayUtils::mapKey($this->_fetchPickItems($orders, $dcNo), 'PICK_LABEL_NO');

        $readyForDespatchSsccAmount = 0;

        foreach ($packSsccList as $packSscc) {
            if (!isset($pickItemList[$packSscc['PS_PICK_LABEL_NO']])) {
                continue;
            }

            if ($this->ssccIsReadyForDespatch($packSscc, $pickItemList[$packSscc['PS_PICK_LABEL_NO']])) {
                $readyForDespatchSsccAmount++;
            }
        }

        if ($readyForDespatchSsccAmount < 1) {
            $response->addErrors('No ready for despatch items found.');
            return $response;
        }

        $connoteProcess = $this->_createConnoteProcessInstance($orders, $pickBlock, $pickItemList);

        try {
            $connoteProcess->run();
            $response->success = true;
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        $response->addMessages($connoteProcess->messages);
        $response->addWarnings($connoteProcess->warnings);

        return $response;
    }

    public function fillEdiOnePackSsccCheckData(Minder_PickOrder_Collection $pickOrders, $dcNo, $outSscc, $view) {
        $packSsccList = $this->_fetchSsccPacks($pickOrders, $dcNo);
        $pickItems = $this->_fetchPickItems($pickOrders, $dcNo);

        $view->ediOrderStatistics = $this->_doCalculateEdiOrderStatistics($packSsccList, $pickItems);

        $packSsccList = $this->_filterPackSsccList($packSsccList, $outSscc);
        $pickItems = $this->_filterPickItemList($pickItems, Minder_ArrayUtils::mapField($packSsccList, 'PS_PICK_LABEL_NO'));

        $view->packSscc = $packSsccList;
        $view->pickItems = $pickItems;
    }

    protected function _filterPackSsccList($packSsccList, $outSscc) {
        $result = array();
        foreach ($packSsccList as $packSscc) {
            if ($packSscc['PS_OUT_SSCC'] == $outSscc) {
                $result[] = $packSscc;
            }
        }

        return $result;
    }

    protected function _filterPickItemList($pickItemList, $psPickLabelList) {
        $psPickLabelList = array_flip($psPickLabelList);
        $result = array();

        foreach ($pickItemList as $pickItem) {
            if (isset($psPickLabelList[$pickItem['PICK_LABEL_NO']])) {
                $result[] = $pickItem;
            }
        }

        return $result;
    }

    public function calculateEdiOrderStatistics(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        $pickItemList = $this->_fetchPickItems($pickOrders, $dcNo);
        $packSsccList = $this->_fetchSsccPacks($pickOrders, $dcNo);

        return $this->_doCalculateEdiOrderStatistics($packSsccList, $pickItemList);
    }

    protected function _doCalculateEdiOrderStatistics($packSsccList, $pickItems) {
        $result = new Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics();

        $checkedOutSscc = array();

        foreach ($packSsccList as $packSscc) {
            $result->totalSscc++;

            if ($this->_ssccIsDespatched($packSscc)) {
                $result->despatchedSscc++;
            }

            if ($this->_ssccIsChecked($packSscc) && !$this->_ssccIsDespatched($packSscc)) {
                $result->checkedSscc++;

                if (!isset($checkedOutSscc[$packSscc['PS_OUT_SSCC']])) {
                    $result->totalWeight += $this->_getTotalWeight($packSscc);
                    $result->totalVolume += $this->_getTotalVolume($packSscc);

                    switch (strtoupper(trim($packSscc['PS_PACK_TYPE']))) {
                        case 'C':
                            $result->cartons++;
                            break;
                        case 'P':
                            $result->pallets++;
                            break;
                        case 'S':
                            $result->satchels++;
                            break;
                    }
                }

                $checkedOutSscc[$packSscc['PS_OUT_SSCC']] = $packSscc['PS_OUT_SSCC'];
                $result->awbConsignmentNo = $packSscc['PS_AWB_CONSIGNMENT_NO'];
            }

            if ($this->_ssccIsCancelled($packSscc)) {
                $result->cancelledSscc++;
            }

            if ($this->_inProgressSscc($packSscc)) {
                $result->inProgressSscc++;
            }

            if ($this->_ssccIsChecking($packSscc)) {
                $result->uncheckedSscc++;

                if ($this->_ssccLabelPrinted($packSscc)) {
                    $result->nextPrintedSscc = $packSscc['PS_OUT_SSCC'];
                } else {
                    $result->nextUnprintedSscc = $packSscc['PS_OUT_SSCC'];
                }
            }
        }

        $result->uncheckedSscc -= $result->inProgressSscc;

        foreach ($pickItems as $pickItem) {
            $result->totalPickItems++;

            if ($this->_pickItemCanBeDespatched($pickItem)) {
                $result->readyForDespatchItems++;
                $result->pickedQty += $this->_getPickItemPickedQty($pickItem);
                $result->checkedQty += $this->_getPickItemCheckedQty($pickItem);
            }

            if ($this->_pickItemIsDespatched($pickItem)) {
                $result->despatchedItems++;
                $result->pickedQty += $this->_getPickItemPickedQty($pickItem);
                $result->checkedQty += $this->_getPickItemCheckedQty($pickItem);
            }
        }

        return $result;
    }

    public function getSsccPacks(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        $result = new Minder_PackSscc_Collection();

        if (count($pickOrders) > 0) {
            $result->fromArray($this->_fetchSsccPacks($pickOrders, $dcNo));
    }

        return $result;
    }

    protected function _fetchSsccPacks(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        $sql = "
            SELECT
                RECORD_ID,
                PS_SSCC,
                PS_OUT_SSCC,
                PS_PICK_ORDER,
                PS_PICK_LABEL_NO,
                PS_SSCC_STATUS,
                PS_QTY_ORDERED,
                PS_QTY_SHIPPED,
                PS_LABEL_PRINTED_DATE,
                PS_SSCC_WEIGHT,
                PS_SSCC_CUBIC,
                PS_DEL_TO_DC_IN_HOUSE_NO,
                PS_AWB_CONSIGNMENT_NO
            FROM
                PACK_SSCC
            WHERE
                PS_PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")
        ";

        $args = $pickOrders->PICK_ORDER;

        if (!empty($dcNo)) {
            $sql .= "
                AND PACK_SSCC.PS_DEL_TO_DC_IN_HOUSE_NO = ?
            ";

            $args[]= $dcNo;
        }

        array_unshift($args, $sql);

        return call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args);
    }

    public function getPickItems(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        $result = new Minder_PickItem_Collection();

        if (count($pickOrders) > 0) {
            $result->fromArray($this->_fetchPickItems($pickOrders, $dcNo));
        }

        return $result;
    }

    protected function _fetchPickItems(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM.PROD_ID,
                PICK_ITEM.PICKED_QTY,
                PICK_ITEM.PICK_LINE_STATUS,
                PROD_PROFILE.ALTERNATE_ID,
                PICK_ORDER.WH_ID AS ORDER_WH_ID,
                COALESCE(
                    PICK_ITEM.WH_ID,
                    (
                        SELECT FIRST 1
                            PICK_ITEM_DETAIL.FROM_WH_ID
                        FROM
                            PICK_ITEM_DETAIL
                        WHERE
                            PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                            AND PICK_ITEM_DETAIL.FROM_WH_ID IS NOT NULL
                            AND NOT PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('CN', 'XX')
                    ),
                    PICK_ORDER.WH_ID
                    )  AS ITEM_WH_ID,
                COALESCE(PROD_PROFILE.PACK_WEIGHT, 0) AS PACK_WEIGHT,
                COALESCE(PICK_ITEM.PARTIAL_PICK_ALLOWED, PICK_ORDER.PARTIAL_PICK_ALLOWED) AS PARTIAL_PICK_ALLOWED,
                COALESCE ((
                    SELECT
                        SUM(COALESCE(PACK_SSCC.PS_QTY_SHIPPED, 0))
                    FROM
                        PACK_SSCC
                    WHERE
                        PACK_SSCC.PS_PICK_ORDER = PICK_ITEM.PICK_ORDER
                        AND PACK_SSCC.PS_PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                        AND PACK_SSCC.PS_SSCC_STATUS IN ('CL', 'DX', 'DC')
                ), 0) AS CHECKED_QTY
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ORDER ON PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER
                LEFT JOIN PROD_PROFILE ON
                    PICK_ITEM.PROD_ID = PROD_PROFILE.PROD_ID
                    AND (
                        PICK_ORDER.COMPANY_ID = PROD_PROFILE.COMPANY_ID
                        OR (
                            PROD_PROFILE.COMPANY_ID = 'ALL'
                            AND NOT EXISTS (SELECT PROD_ID FROM PROD_PROFILE AS PP WHERE PP.PROD_ID = PROD_PROFILE.PROD_ID AND PP.COMPANY_ID = PICK_ORDER.COMPANY_ID)
                        )
                    )
            WHERE
                PICK_ITEM.PICK_ORDER IN (" . implode(', ', array_fill(0, count($pickOrders), '?')) . ")
        ";

        $args = $pickOrders->PICK_ORDER;

        if (!empty($dcNo)) {
            $sql .= "
                AND PICK_ITEM.PS_DEL_TO_DC_IN_HOUSE_NO = ?
            ";

            $args[]= $dcNo;
        }


        array_unshift($args, $sql);

        return call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args);
    }

    public function acceptSscc(Minder_PickOrder_Collection $orders, $checkStatus, $dimensions, Minder_JSResponse $response) {
        $acceptingSscc = $checkStatus['outSscc'];

        $packSscc = $this->_fetchPackSsccList($acceptingSscc);
        $pickItems = $this->_fetchPickItemList($acceptingSscc);

        $packSscc = Minder_ArrayUtils::recursiveGroup($packSscc, array('PS_OUT_SSCC'));
        $pickItems = Minder_ArrayUtils::mapKey($pickItems, 'PICK_LABEL_NO');


        if (!isset($packSscc[$acceptingSscc])) {
            $response->addErrors('SSCC #' . $acceptingSscc . ' not found.');
            return $checkStatus;
        }

        $ssccList = $this->_simulateChecking($checkStatus, $packSscc[$acceptingSscc], $pickItems, $response);

        if ($response->hasErrors()) {
            return $checkStatus;
        }

        return $this->_executeCheckPlan($ssccList, $pickItems, $checkStatus, $dimensions, $orders, $response);
    }

    public function createSscc($ssccPrototypeRecordId) {
        $result = new Minder_JSResponse();

        try {
            $sscc = $this->_findSscc($ssccPrototypeRecordId);
            $pickOrder = $this->_getMinder()->getPickOrder($sscc['PS_PICK_ORDER']);
            $printer = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($pickOrder->pickOrder);
            $currentDevice = Minder2_Environment::getInstance()->getCurrentDevice();

            $result->addMessages($this->_doDSGSNTransaction($sscc['PS_SSCC'], $printer, $pickOrder, $currentDevice));
        } catch (Exception $e) {
            $result->addErrors($e->getMessage());
        }

        return $result;
    }

    /**
     * @param $ssccLabel
     * @param Minder_PickOrder_Collection $pickOrders
     * @return Minder_JSResponse
     * @throws Exception
     */
    public function startSsccCheck($ssccLabel, Minder_PickOrder_Collection $pickOrders) {
        $packSsccList = $this->_fetchPackSsccList($ssccLabel);
        $pickItemList = Minder_ArrayUtils::mapKey($this->_fetchPickItemList($ssccLabel), 'PICK_LABEL_NO');
        $validationResults = $this->_explainCannotCheckOutSscc($ssccLabel, $packSsccList, $pickItemList, $pickOrders);

        if (!$validationResults->hasErrors()) {
            $this->_lockSsccForChecking($ssccLabel);
        }

        return $validationResults;
    }

    public function cancelSsccCheck($ssccLabel) {
        $transaction = new Transaction_DSACU($ssccLabel, $this->_getEnvironment()->getCurrentDevice()->DEVICE_ID);
        $response = $this->_getMinder()->doTransactionResponseV6($transaction);

        if (!$transaction->parseResponse($response)->isSuccess()) {
            throw new Exception('Cancel check failed: ' . $response);
        }
    }

    public function printAllLabels(Minder_PackSscc_Collection $packSsccList, $pickOrder, $companyId, $printerId) {
        $dcNoList       = $this->_getDcNoForPrint($packSsccList);
        $totalPrinted   = 0;

        if (empty($dcNoList)) {
            return $totalPrinted;
        }

        foreach ($dcNoList as $dcNo) {
            $transaction = new Transaction_DSPSD(
                $pickOrder,
                $dcNo,
                $companyId,
                $printerId
            );

            $this->_getMinder()->doTransactionResponseV6($transaction);
            $totalPrinted++;
        }

        return $totalPrinted;
    }

    protected function _getDcNoForPrint(Minder_PackSscc_Collection $packSsccList) {
        $result = array();

        foreach ($packSsccList->getIterator() as $packSscc) {
            if ($this->_ssccIsChecking($packSscc)) {
                $result[$packSscc['PS_DEL_TO_DC_IN_HOUSE_NO']] = $packSscc['PS_DEL_TO_DC_IN_HOUSE_NO'];
            }
        }

        return $result;
    }

    public function getDcNoByLocationId($locationId) {
        if (empty($locationId)) {
            return '';
        }

        $sql = "
            SELECT FIRST 1
                PS_DEL_TO_DC_IN_HOUSE_NO
            FROM
                PICK_ITEM
            WHERE
                PICK_LINE_STATUS IN ('PL', 'AC', 'CK', 'DS', 'AS')
                AND DESPATCH_LOCATION = ?
            ";
        return $this->_getMinder()->fetchOne($sql, $locationId);
    }

    /**
     * @param $sscc
     * @param $dimensions
     * @param $printer
     * @param $pickOrder
     * @param $currentDevice
     * @throws Exception
     */
    protected function _doDSGSDTransaction($sscc, $dimensions, $printer, PickOrder $pickOrder, Minder2_Model_SysEquip $currentDevice) {
        $dsgsd = new Transaction_DSGSD();

        $dsgsd->SSCC = $sscc['PS_SSCC'];
        $dsgsd->length = $dimensions['L'];
        $dsgsd->width = $dimensions['W'];
        $dsgsd->height = $dimensions['H'];
        $dsgsd->volume = $dimensions['CALCULATED']['TOTAL_VOL'];
        $dsgsd->weight = $dimensions['CALCULATED']['TOTAL_WT'];
        $dsgsd->totalOuters = $dimensions['QTY'];
        $dsgsd->dimUom = $dimensions['UOM']['DT'];
        $dsgsd->volumeUom = $dimensions['UOM']['VT'];
        $dsgsd->weightUom = $dimensions['UOM']['WT'];
        $dsgsd->packType  = $dimensions['TYPE'];
        $dsgsd->labelPrinter = $printer;
        $dsgsd->scannedItems = $this->_getSsccCheckedQty($sscc);

        $dsgsd->setPickOrder($pickOrder);

        $dsgsd->despatchPCLocnId = $currentDevice->getLocation();
        $this->_getMinder()->doTransactionResponseV6($dsgsd);
    }

    protected function _doDSGSNTransaction($toSplit, $printer, PickOrder $pickOrder, Minder2_Model_SysEquip $currentDevice) {
        $dsgsu = new Transaction_DSGSN();
        $dsgsu->SSCC = $toSplit;
        $dsgsu->labelPrinter = $printer;
        $dsgsu->setPickOrder($pickOrder);
        $dsgsu->despatchPCLocnId = $currentDevice->getLocation();
        return $this->_getMinder()->doTransactionResponseV6($dsgsu);
    }

    protected function _simulateChecking($checkingLog, $ssccList, $pickItems, Minder_JSResponse $response) {
        $ssccList = Minder_ArrayUtils::mapKey($ssccList, 'RECORD_ID');
        $totalUnchecked = $this->_calculateUncheckedQty($ssccList, $pickItems);

        foreach ($checkingLog['packSsccCheckStatus'] as $ssccCheckStatus) {
            if (!isset($ssccList[$ssccCheckStatus['RECORD_ID']])) {
                $response->addErrors('SSCC RECORD_ID #' . $ssccCheckStatus['RECORD_ID'] . ' not found.');
                continue;
            }

            $sscc = &$ssccList[$ssccCheckStatus['RECORD_ID']];

            if (!isset($pickItems[$sscc['PS_PICK_LABEL_NO']])) {
                $response->addErrors('PICK_ITEM #' . $sscc['PS_PICK_LABEL_NO'] . ' not found.');
                continue;
            }

            $ssccCheckStatus['checkedQty'] = intval($ssccCheckStatus['checkedQty']);
            $pickItem = $pickItems[$sscc['PS_PICK_LABEL_NO']];
            if ($this->_checkSscc($ssccCheckStatus['checkedQty'], $sscc, $pickItem, $response)) {
                $totalUnchecked -= $ssccCheckStatus['checkedQty'];
            }
        }

        return $ssccList;
    }

    protected function _checkSscc($checkAmount, &$sscc, &$pickItem, Minder_JSResponse $response) {
        if ($this->_ssccIsCancelled($sscc)) {
            $response->addErrors('SSCC RECORD_ID #' . $sscc['RECORD_ID'] . ' is cancelled.');
            return false;
        }

        if ($this->_ssccIsChecked($sscc)) {
            $response->addErrors('SSCC RECORD_ID #' . $sscc['RECORD_ID'] . ' already checked.');
            return false;
        }

        if (!$this->_ssccIsChecking($sscc)) {
            $response->addErrors('SSCC RECORD_ID #' . $sscc['RECORD_ID'] . ' has wrong status ' . $sscc['PS_SSCC_STATUS'] . '.');
            return false;
        }

        if ($this->_getPickItemAvailableQty($pickItem) < 1) {
            $response->addErrors('PICK_ITEM #' . $pickItem['PICK_LABEL_NO'] . ' no more unchecked items left.');
            return false;
        }

        if (!$this->_pickItemCanBeDespatched($pickItem)) {
            $response->addErrors('PICK_ITEM #' . $pickItem['PICK_LABEL_NO'] . ' cannot be despatched.');
            return false;
        }

        if ($this->_getSsccUncheckedQty($sscc, $pickItem) < $checkAmount) {
            $response->addErrors('SSCC RECORD_ID #' . $sscc['RECORD_ID'] . ' not enough items to check.');
            return false;
        }

        if ($this->_getPickItemAvailableQty($pickItem) < $checkAmount) {
            $response->addErrors('SSCC RECORD_ID #' . $sscc['RECORD_ID'] . ' not enough items to check.');
            return false;
        }

        $sscc['PS_QTY_SHIPPED'] = $this->_getSsccCheckedQty($sscc) + $checkAmount;
        $pickItem['CHECKED_QTY'] = $this->_getPickItemCheckedQty($pickItem) + $checkAmount;

        return true;
    }

    protected function _calculateUncheckedQty(&$ssccList, $pickItems) {
        $result = 0;

        foreach ($ssccList as &$sscc) {
            if (!isset($pickItems[$sscc['PS_PICK_LABEL_NO']])) {
                continue;
            }

            $pickItem = $pickItems[$sscc['PS_PICK_LABEL_NO']];
            $sscc['uncheckedQty'] = $this->_getSsccUncheckedQty($sscc, $pickItem);

            if ($this->_ssccCanBeChecked($sscc, $pickItem)) {
                $result += $sscc['uncheckedQty'];
            }
        }

        return $result;
    }

    protected function _getTotalWeight($sscc) {
        return floatval($sscc['PS_SSCC_WEIGHT']);
    }

    protected function _getTotalVolume($sscc) {
        return floatval($sscc['PS_SSCC_CUBIC']);
    }

    protected function _ssccCanBeChecked($sscc, $pickItem) {
        return $this->_ssccIsChecking($sscc) && $this->_pickItemCanBeChecked($pickItem);
    }

    protected function _ssccIsChecked($sscc) {
        return in_array($sscc['PS_SSCC_STATUS'], array('CL', 'DC', 'DX'));
    }

    protected function _ssccIsDespatched($sscc) {
        return in_array($sscc['PS_SSCC_STATUS'], array('DC', 'DX'));
    }

    protected function _ssccIsCancelled($sscc) {
        return in_array($sscc['PS_SSCC_STATUS'], array('CN'));
    }

    protected function _inProgressSscc($sscc) {
        return in_array($sscc['PS_SSCC_STATUS'], array('AC'));
    }

    protected function _ssccIsChecking($sscc) {
        return in_array($sscc['PS_SSCC_STATUS'], array('GO', 'AC'));
    }

    protected function _ssccLabelPrinted($sscc) {
        return !empty($sscc['PS_LABEL_PRINTED_DATE']);
    }

    protected function _pickItemCanBeChecked($pickItem) {
        return $this->_pickItemCanBeDespatched($pickItem) && ($this->_getPickItemAvailableQty($pickItem) > 0);
    }

    protected function _pickItemIsDespatched($pickItem) {
        return in_array($pickItem['PICK_LINE_STATUS'], array('DC', 'DX'));
    }

    protected function _pickItemCanBeDespatched($pickItem) {
        return $this->_itemHasCorrectWarehouse($pickItem)
            && $this->_getPickItemPickedQty($pickItem) > 0
            && $this->_itemIsPicked($pickItem);
    }

    protected function _itemIsPicked($pickItem) {
        return $this->_itemIsFullyPicked($pickItem)
            || ($this->_partialPickIsAllowed($pickItem) && $this->_itemIsPartiallyPicked($pickItem));
    }

    protected function _itemIsFullyPicked($pickItem) {
        return in_array($pickItem['PICK_LINE_STATUS'], array('PL', 'DS', 'AC', 'CK'));
    }

    protected function _itemIsPartiallyPicked($pickItem) {
        return in_array($pickItem['PICK_LINE_STATUS'], array('AS'));
    }

    protected function _itemHasCorrectWarehouse($pickItem) {
        return strtoupper(trim($pickItem['ORDER_WH_ID'])) == strtoupper(trim($pickItem['ITEM_WH_ID']));
    }

    protected function _getSsccUncheckedQty($sscc, $pickItem) {
        return max(0, min($this->_getSsccOrderedQty($sscc), $this->_getPickItemAvailableQty($pickItem)) - $this->_getSsccCheckedQty($sscc));

    }

    protected function _getPickItemAvailableQty($pickItem) {
        return max(0, $this->_getPickItemPickedQty($pickItem) - $this->_getPickItemCheckedQty($pickItem));
    }

    protected function _getPickItemCheckedQty($pickItem) {
        return intval($pickItem['CHECKED_QTY']);
    }

    protected function _getPickItemPickedQty($pickItem) {
        return intval($pickItem['PICKED_QTY']);
    }

    protected function _partialPickIsAllowed($pickItem) {
        return $pickItem['PARTIAL_PICK_ALLOWED'] == 'T';
    }

    protected function _getSsccCheckedQty($sscc) {
        return intval($sscc['PS_QTY_SHIPPED']);
    }

    protected function _getSsccOrderedQty($sscc) {
        return intval($sscc['PS_QTY_ORDERED']);
    }

    /**
     * @return Minder
     */
    protected function _getMinder()
    {
        return Minder::getInstance();
    }

    /**
     * @return Minder2_Environment
     */
    protected function _getEnvironment()
    {
        return Minder2_Environment::getInstance();
    }

    /**
     * @return Minder_Controller_Action_Helper_AwaitingChecking
     */
    private function _awaitingCheckingHelper()
    {
        return $this->getActionController()->getHelper('AwaitingChecking');
    }

    /**
     * @param $ssccList
     * @param $pickItems
     * @param $checkStatus
     * @param $dimensions
     * @param Minder_PickOrder_Collection $orders
     * @param Minder_JSResponse $response
     * @return
     */
    protected function _executeCheckPlan($ssccList, $pickItems, $checkStatus, $dimensions, Minder_PickOrder_Collection $orders, Minder_JSResponse $response)
    {
        $ssccCheckStatus = Minder_ArrayUtils::mapKey($checkStatus['packSsccCheckStatus'], 'RECORD_ID');
        $toSplit = array();
        $notChecked = array();

        try {
            $pickOrder = $this->_getMinder()->getPickOrder(array_shift($orders->PICK_ORDER));
            $printer = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($pickOrder->pickOrder);
            $currentDevice = Minder2_Environment::getInstance()->getCurrentDevice();
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
            return $checkStatus;
        }

        try {
            foreach ($ssccList as $sscc) {
                $pickItem = $pickItems[$sscc['PS_PICK_LABEL_NO']];

                if (!$this->_ssccCanBeChecked($sscc, $pickItem)) {
                    continue;
                }

                if ($this->_getSsccCheckedQty($sscc) > 0) {
                    if ($this->_getSsccUncheckedQty($sscc, $pickItem) > 0) {
                        $toSplit[] = $sscc;
                    }

                    $this->_doDSGSDTransaction($sscc, $dimensions, $printer, $pickOrder, $currentDevice);
                } else {
                    $notChecked[] = $sscc;
                }

                if (isset($ssccCheckStatus[$sscc['RECORD_ID']])) {
                    unset($ssccCheckStatus[$sscc['RECORD_ID']]);
                }

            }

            $checkStatus['completed'] = true;
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        $checkStatus['packSsccCheckStatus'] = array_values($ssccCheckStatus);

        if ($response->hasErrors()) {
            return $checkStatus;
        }

        try {
            if (count($notChecked) > 0) {
                $this->_doDSACUTransaction($checkStatus['outSscc'], $pickOrder, $currentDevice);
            }

            if (count($toSplit) < 1 && count($notChecked) > 0) {
                $sscc = array_shift($notChecked);
                $toSplit[] = $sscc;
                $this->_doDSGSDTransaction($sscc, $dimensions, $printer, $pickOrder, $currentDevice);
            }

            foreach ($toSplit as $sscc) {
                $transactionMessage = $this->_doDSGSNTransaction($sscc['PS_SSCC'], $printer, $pickOrder, $currentDevice);
                $response->addMessages($transactionMessage);
            }
        } catch (Exception $e) {
            $response->addWarnings($e->getMessage());
        }

        return $checkStatus;
    }

    private function _doDSACUTransaction($outSscc, PickOrder $pickOrder, Minder2_Model_SysEquip $currentDevice)
    {
        $dsgsd = new Transaction_DSACU($outSscc, $currentDevice->DEVICE_ID);
        $dsgsd->setPickOrder($pickOrder);
        $this->_getMinder()->doTransactionResponseV6($dsgsd);
    }

    private function ssccIsReadyForDespatch($packSscc, $pickItem)
    {
        return $this->_ssccIsChecked($packSscc)
            && !$this->_ssccIsDespatched($packSscc)
            && $this->_getSsccCheckedQty($packSscc) > 0
            && $this->_pickItemCanBeDespatched($pickItem);
    }

    /**
     * @param Minder_PickOrder_Collection $orders
     * @param $pickBlock
     * @param $pickItemList
     * @return Minder_ConnoteProccess
     */
    protected function _createConnoteProcessInstance(Minder_PickOrder_Collection $orders, $pickBlock, $pickItemList)
    {
        $connoteProcess = new Minder_ConnoteProccess();

        $connoteProcess->orders = array_unique($orders->PICK_ORDER);
        $connoteProcess->lines = array_keys($pickItemList);
        $connoteProcess->pickBlock = $pickBlock;

        $connoteProcess->skipLabelPrinting = $this->getRequest()->getParam('skipLabelPrinting', 'false') == 'true';
        $connoteProcess->palletOwner = $this->getRequest()->getParam('palletOwner');
        $connoteProcess->carrierId = $this->getRequest()->getParam('carrier');
        $connoteProcess->carrierServiceRecordId = $this->getRequest()->getParam('carrierService');
        $connoteProcess->printerId = $this->_getMinder()->limitPrinter;
        $connoteProcess->accountNo = $this->getRequest()->getParam('accountNo');
        $connoteProcess->connoteNo = $this->getRequest()->getParam('consignment');

        $connoteProcess->payerFlag = $this->getRequest()->getParam('payer');
        if (!empty($connoteProcess->payerFlag))
            $connoteProcess->payerFlag = substr($connoteProcess->payerFlag, 0, 1);

        return $this->_fillDimensions($connoteProcess);
    }

    /**
     * @param Minder_ConnoteProccess $connoteProcess
     * @return Minder_ConnoteProccess
     */
    protected function _fillDimensions(Minder_ConnoteProccess $connoteProcess)
    {
        $packDimensions = $this->getRequest()->getParam('dimentions', array());
        array_walk_recursive($packDimensions, 'trim');

        $connoteProcess->palletQty = 0;
        $connoteProcess->cartonQty = 0;
        $connoteProcess->satchelQty = 0;

        foreach ($packDimensions as &$dimension) {
            switch ($dimension['TYPE']) {
                case 'C':
                    $connoteProcess->cartonQty += $dimension['QTY'];
                    break;
                case 'P':
                    $connoteProcess->palletQty += $dimension['QTY'];
                    $dimension['VOL'] = 0;
                    break;
                case 'S':
                    $connoteProcess->satchelQty += $dimension['QTY'];
                    break;
            }
        }

        $connoteProcess->qtyAddressLabel = $connoteProcess->cartonQty + $connoteProcess->palletQty + $connoteProcess->satchelQty;
        $connoteProcess->packDims = $packDimensions;

        return $connoteProcess;
    }

    protected function _fetchPackSsccList($psOutSscc) {
        if (empty($psOutSscc)) {
            return array();
        }

        $query = "
            SELECT
                RECORD_ID,
                PS_SSCC,
                PS_OUT_SSCC,
                PS_PICK_ORDER,
                PS_PICK_LABEL_NO,
                PS_SSCC_STATUS,
                PS_QTY_ORDERED,
                PS_QTY_SHIPPED,
                PS_LABEL_PRINTED_DATE,
                PS_SSCC_WEIGHT,
                PS_SSCC_CUBIC,
                PS_DEL_TO_DC_IN_HOUSE_NO,
                PS_AWB_CONSIGNMENT_NO
            FROM
                PACK_SSCC
            WHERE
                PS_OUT_SSCC = ?
        ";

        return $this->_getMinder()->fetchAllAssoc($query, $psOutSscc);;
    }

    protected function _fetchPickItemList($psOutSscc) {
        if (empty($psOutSscc)) {
            return array();
        }

        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM.PROD_ID,
                PICK_ITEM.PICKED_QTY,
                PICK_ITEM.PICK_LINE_STATUS,
                PROD_PROFILE.ALTERNATE_ID,
                PICK_ORDER.WH_ID AS ORDER_WH_ID,
                COALESCE(
                    PICK_ITEM.WH_ID,
                    (
                        SELECT FIRST 1
                            PICK_ITEM_DETAIL.FROM_WH_ID
                        FROM
                            PICK_ITEM_DETAIL
                        WHERE
                            PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                            AND PICK_ITEM_DETAIL.FROM_WH_ID IS NOT NULL
                            AND NOT PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('CN', 'XX')
                    ),
                    PICK_ORDER.WH_ID
                    )  AS ITEM_WH_ID,
                COALESCE(PROD_PROFILE.PACK_WEIGHT, 0) AS PACK_WEIGHT,
                COALESCE(PICK_ITEM.PARTIAL_PICK_ALLOWED, PICK_ORDER.PARTIAL_PICK_ALLOWED) AS PARTIAL_PICK_ALLOWED,
                COALESCE ((
                    SELECT
                        SUM(COALESCE(PACK_SSCC.PS_QTY_SHIPPED, 0))
                    FROM
                        PACK_SSCC
                    WHERE
                        PACK_SSCC.PS_PICK_ORDER = PICK_ITEM.PICK_ORDER
                        AND PACK_SSCC.PS_PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                        AND PACK_SSCC.PS_SSCC_STATUS IN ('CL', 'DX', 'DC')
                ), 0) AS CHECKED_QTY
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ORDER ON PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER
                LEFT JOIN PROD_PROFILE ON
                    PICK_ITEM.PROD_ID = PROD_PROFILE.PROD_ID
                    AND (
                        PICK_ORDER.COMPANY_ID = PROD_PROFILE.COMPANY_ID
                        OR (
                            PROD_PROFILE.COMPANY_ID = 'ALL'
                            AND NOT EXISTS (SELECT PROD_ID FROM PROD_PROFILE AS PP WHERE PP.PROD_ID = PROD_PROFILE.PROD_ID AND PP.COMPANY_ID = PICK_ORDER.COMPANY_ID)
                        )
                    )
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN (SELECT PS_PICK_LABEL_NO FROM PACK_SSCC WHERE PS_OUT_SSCC = ?)
        ";

        return $this->_getMinder()->fetchAllAssoc($sql, $psOutSscc);
    }

    /**
     * @param $ssccLabel
     * @throws Exception
     */
    protected function _lockSsccForChecking($ssccLabel)
    {
        $transaction = new Transaction_DSACK($this->_getEnvironment()->getCurrentDevice()->DEVICE_ID, $ssccLabel);
        $response = $this->_getMinder()->doTransactionResponseV6($transaction);

        if (!$transaction->parseResponse($response)->isSuccess()) {
            throw new Exception('Cannot lock sscc: ' . $response);
        }
    }

    private function _explainCannotCheckOutSscc($psOutSscc, $packSsccList, $pickLabelList, Minder_PickOrder_Collection $pickOrders)
    {
        $result = new Minder_JSResponse();

        if (count($packSsccList) < 1) {
            $result->addErrors("PS_OUT_SSCC #" . $psOutSscc . " not found.");
        } elseif (count($pickOrders) < 1) {
            $result->addErrors("No Orders found.");
        } else {
            $psPickOrderList = Minder_ArrayUtils::mapField($packSsccList, 'PS_PICK_ORDER');
            $commonOrders = array_intersect($psPickOrderList, $pickOrders->PICK_ORDER);

            if (count($commonOrders) < 1) {
                $result->addErrors("PS_OUT_SSCC #" . $psOutSscc . " does not belong to selected Order(s).");
            }
        }


        return $this->_explainCannotCheckPackSsccList($psOutSscc, $packSsccList, $pickLabelList, $result);
    }

    /**
     * @param $psOutSscc
     * @param $packSsccList
     * @param $pickLabelList
     * @param Minder_JSResponse $result
     * @return Minder_JSResponse
     */
    private function _explainCannotCheckPackSsccList($psOutSscc, $packSsccList, $pickLabelList, Minder_JSResponse $result)
    {
        $checked = 0;
        $cancelled = 0;
        $checking = 0;

        foreach ($packSsccList as $packSscc) {
            if ($this->_ssccIsChecked($packSscc)) {
                $checked++;
            }

            if ($this->_ssccIsCancelled($packSscc)) {
                $cancelled++;
            }

            if ($this->_ssccIsChecking($packSscc) && isset($pickLabelList[$packSscc['PS_PICK_LABEL_NO']])) {
                if ($this->_getSsccUncheckedQty($packSscc, $pickLabelList[$packSscc['PS_PICK_LABEL_NO']]) < 1) {
                    $checked++;
                } else {
                    $checking++;
                }
            }
        }

        if ($checked > 0) {
            $result->addErrors("PS_OUT_SSCC #" . $psOutSscc . " already checked.");
        } else {
            if ($checking < 1 && count($packSsccList) > 0) {
                if ($cancelled > 0) {
                    $result->addErrors("PS_OUT_SSCC #" . $psOutSscc . " cancelled.");
                } else {
                    $result->addErrors("PS_OUT_SSCC #" . $psOutSscc . " is not ready for checking.");
                }
            }
        }

        return $result;
    }

    public function getNextNotPrintedSscc(Minder_PickOrder_Collection $pickOrders, $dcNo) {
        if (count($pickOrders) < 1) {
            return '';
        }

        $sql = "
            SELECT FIRST 1
                PS_OUT_SSCC
            FROM
                PACK_SSCC
            WHERE
                PS_PICK_ORDER IN (" . implode(", ", array_fill(0, count($pickOrders), "?")) . ")
                AND PS_SSCC_STATUS IN ('GO', 'AC')
                AND PS_LABEL_PRINTED_DATE IS NULL
        ";

        $args = $pickOrders->PICK_ORDER;

        if (!empty($dcNo)) {
            $sql .= "
                AND PS_DEL_TO_DC_IN_HOUSE_NO = ?
            ";
            $args[] = $dcNo;
        }

        array_unshift($args, $sql);

        return (string)call_user_func_array(array($this->_getMinder(), "fetchOne"), $args);
    }

    private function _findSscc($recordId)
    {
        $result = $this->_getMinder()->fetchAssoc('SELECT * FROM PACK_SSCC WHERE RECORD_ID = ?', $recordId);

        if (empty($result)) {
            throw new Exception('PACK_SSCC RECORD_ID #' . $recordId . ' not found.');
        }

        return $result;
    }
}