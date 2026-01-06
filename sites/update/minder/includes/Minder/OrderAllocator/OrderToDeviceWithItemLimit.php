<?php

class Minder_OrderAllocator_OrderToDeviceWithItemLimit extends Minder_OrderAllocator_Abstract {

    /**
     * @param Minder_OrderAllocator_ItemProvider_Interface $itemProvider
     * @param string $userId - reserved for future use
     * @param string $deviceId
     * @param string $troleyId
     * @param int $orderLimit
     * @param int $productLimit
     * @param int $itemLimit
     * @return \Minder_OrderAllocator_Result
     */
    public function allocate($itemProvider, $userId, $deviceId, $troleyId, $orderLimit, $productLimit, $itemLimit)
    {
        $result = new Minder_OrderAllocator_Result();

        if (empty($deviceId)) {
            $result->errors[] = 'Select Device ID';
            return $result;
        }

        $minder = Minder::getInstance();

        if (empty($orderLimit)) {
            $orderLimit = $minder->defaultControlValues['MAX_PICK_ORDERS'];
            $result->warnings[] = 'No Order Limit given. Default value "' .$orderLimit . '" used.';
        }

        if (empty($productLimit)) {
            $productLimit = $minder->defaultControlValues['MAX_PICK_PRODUCTS'];
            $result->warnings[] = 'No Product Limit given. Default value "' .$productLimit . '" used.';
        }

        if (empty($itemLimit)) {
            $itemLimit = $minder->defaultControlValues['MAX_PICK_LINES'];
            $result->warnings[] = 'No Product Limit given. Default value "' . $itemLimit . '" used.';
        }

        $itemsToAllocate = $itemProvider->selectProdIdAndPickLabelNoToAllocate();
        if (empty($itemsToAllocate)) {
            $itemsToAllocate = $this->_getItemsAndProductsToAllocate();
        }

        $this->_skippedOrders = array();
        $ordersToAllocate = $this->_checkPartialDespatchedOrders(array_unique(Minder_ArrayUtils::mapField($itemsToAllocate, 'PICK_ORDER')), $orderLimit);

        if (count($this->_skippedOrders) > 0) {
            $result->warnings[] = 'Skipping partially despatched orders: ' . implode(', ', $this->_skippedOrders) . '.';
        }

        if (count($ordersToAllocate) < 1) {
            $result->warnings[] = 'No Orders left to allocate.';
            return $result;
        }

        $itemsToAllocate = $this->_filterItems($itemsToAllocate, $ordersToAllocate);

        if (count($itemsToAllocate) < 1) {
            $result->warnings[] = 'No Orders left to allocate.';
            return $result;
        }

        $result = $this->_doAllocate($deviceId, $userId, $itemsToAllocate, $orderLimit, $productLimit, $itemLimit);

        switch (true) {
            case ($result->allocatedOrders == 0):
                $result->warnings[] = 'No orders were allocated.';
                break;

            case ($result->allocatedOrders == 1):
                $result->messages[] = '1 order was allocated.';
                break;

            case ($result->allocatedOrders > 1):
                $result->messages[] = $result->allocatedOrders . ' orders were allocated.';
                break;
        }

        if (count($this->_skippedOrders) > 0) {
            $result->warnings[] = 'Skipping partially despatched orders: ' . implode(', ', $this->_skippedOrders) . '.';
        }

        return $result;
    }

    protected function _doAllocate($deviceId, $userId, $itemsToAllocate, $orderLimit, $productLimit, $itemLimit) {
        $result = new Minder_OrderAllocator_Result();
        $minder = Minder::getInstance();

        $this->_fillAllocatedOrdersAndProductsFromDevice($deviceId);

        $transaction = new Transaction_PKALF();
        $transaction->prodId = '';
        $transaction->pickLabelNo = '';
        $transaction->deviceId = $deviceId;
        $transaction->pickerId = $userId;

        foreach ($itemsToAllocate as $pickItem) {
            $pickOrder = $pickItem['PICK_ORDER'];
            $pickLabelNo = $pickItem['PICK_LABEL_NO'];
            $prodId = $pickItem['PROD_ID'];
            $pickLine = $minder->getPickItemById($pickLabelNo);
            $pickCompanyId = $pickLine['COMPANY_ID'];

            $validationResult = $this->_validateAllocateRequest(
                array($pickOrder => $pickOrder),
                array($prodId => $prodId),
                $orderLimit,
                $productLimit,
                array($pickLabelNo => $pickLabelNo),
                $itemLimit
            );

            switch ($validationResult) {
                case self::REQUEST_ORDER_LIMIT_EXCEEDED:
                    $result->messages[] = 'Order Limit reached.';
                    return $result;

                case self::REQUEST_PRODUCT_LIMIT_EXCEEDED:
                    $result->messages[] = 'Product Limit reached.';
                    return $result;

                case self::REQUEST_ITEM_LIMIT_EXCEEDED:
                    $result->messages[] = 'Item Limit reached.';
                    return $result;
            }

            $transaction->prodId = $prodId;
            $transaction->pickLabelNo = $pickLabelNo;
            $transaction->orderNo = $pickOrder;
            $transaction->companyId = $pickCompanyId;

            //if ($minder->doTransactionResponse($transaction) !== false) {
            if (false !== ($resultV6 = $minder->doTransactionResponseV6($transaction ))) {
                $result->messages[] = "PICK_ITEM #" . $transaction->pickLabelNo . " was allocated to device " . $deviceId . ".";

                $this->_allocatedProducts[$prodId]  = $prodId;
                $this->_allocatedOrders[$pickOrder] = $pickOrder;
                $this->_allocatedItems[$pickLabelNo] = $pickLabelNo;

                $result->allocatedOrders = count($this->_allocatedOrders);
            } else {
                $result->errors[] = "Error allocating PICK_ITEM #" . $pickLabelNo . ": " . $minder->lastError;
                return $result;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function _getTransactionGroup()
    {
        return '';
    }

    private function _getItemsAndProductsToAllocate()
    {
        $minder = Minder::getInstance();

        $sql = "
            SELECT DISTINCT
                PICK_ORDER.PICK_ORDER,
                PICK_ITEM.PICK_LABEL_NO,
                CASE
                    WHEN PICK_ITEM.PROD_ID IS NULL THEN ISSN.PROD_ID
                    ELSE PICK_ITEM.PROD_ID
                END AS PROD_ID
            FROM
                PICK_ORDER
                LEFT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
                LEFT OUTER JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
            WHERE
                PICK_ORDER.PICK_STATUS IN ('DA', 'OP')
                AND PICK_ITEM.PICK_LINE_STATUS IN ('OP')
        " . $minder->getWarehouseAndCompanyLimit(0, false, 'PICK_ORDER.', 'PICK_ORDER.');

        $sql = substr($sql, 0, -5);

        $sql .= "
            ORDER BY
                PICK_DUE_DATE ASC,
                PICK_ORDER.PICK_PRIORITY ASC
        ";

        if (false !== ($queryResult = $minder->fetchAllAssoc($sql))) {
            return $queryResult;
        }

        return array();
    }

    private function _filterItems($itemsToAllocate, $ordersToAllocate)
    {
        $ordersToAllocate = array_flip($ordersToAllocate);
        return array_filter($itemsToAllocate, function($item)use($ordersToAllocate){
            return isset($ordersToAllocate[$item['PICK_ORDER']]);
        });
    }
}
