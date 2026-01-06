<?php

class Minder_OrderAllocator_OrderToTrolley extends Minder_OrderAllocator_Abstract {
    /**
     * @return string
     */
    protected function _getTransactionGroup()
    {
        return 'PKAL_TORDS';
    }


    protected function _fillAllocatedOrdersAndProductsFromTrolley($trolley) {
        $sql = "
            SELECT DISTINCT
                PICK_ORDER,
                PROD_ID
            FROM
                PICK_ITEM
            WHERE
                PICK_LINE_STATUS IN ('AL','PG','PL')
                AND DESPATCH_LOCATION_GROUP = ?
        ";

        $this->_allocatedOrders   = array();
        $this->_allocatedProducts = array();

        if (false !== ($result = Minder::getInstance()->fetchAllAssoc($sql, $trolley))) {
            foreach ($result as $resultRow) {
                $this->_allocatedOrders[$resultRow['PICK_ORDER']] = $resultRow['PICK_ORDER'];
                $this->_allocatedProducts[$resultRow['PROD_ID']] = $resultRow['PROD_ID'];
            }
        }
    }

    /**
     * @param Minder_OrderAllocator_ItemProvider_Interface $itemProvider - ignored in this allocator
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

        if (empty($troleyId)) {
            $result->errors[] = 'Select Trolley ID';
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

        $pickOrdersToAllocate = $itemProvider->selectPickOrderToAllocate($orderLimit);
        if (empty($pickOrdersToAllocate)) {
            $pickOrdersToAllocate = $this->_getPickOrdersToAllocate($orderLimit);
        }

        $this->_skippedOrders = array();
        $pickOrdersToAllocate = $this->_checkPartialDespatchedOrders($pickOrdersToAllocate, $orderLimit);

        if (count($this->_skippedOrders) > 0) {
            $result->warnings[] = 'Skipping partially despatched orders: ' . implode(', ', $this->_skippedOrders) . '.';
        }

        if (count($pickOrdersToAllocate) < 1) {
            $result->warnings[] = 'No Orders left to allocate.';
            return $result;
        }

        $result = $this->_doAllocate($troleyId, $deviceId, $userId, $pickOrdersToAllocate, $orderLimit, $productLimit);

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

    /**
     * @param string $troleyId
     * @param string $deviceId
     * @param string $userId - for future use
     * @param array $pickOrdersToAllocate
     * @param int $orderLimit
     * @param int $productLimit
     * @return Minder_OrderAllocator_Result
     */
    protected function _doAllocate($troleyId, $deviceId, $userId, $pickOrdersToAllocate, $orderLimit, $productLimit)
    {
        $result = new Minder_OrderAllocator_Result();
        $this->_fillAllocatedOrdersAndProductsFromTrolley($troleyId);

        $minder = Minder::getInstance();

        /**
         * @var Transaction_PKALG | Transaction_PKALI $transaction
         */
        $transaction = $this->_getTransactionObject();
        $transaction->orderNo = '';
        $transaction->deviceId = $deviceId;
        $transaction->pickerId = $userId;

        if ($transaction instanceof Transaction_PKALG) {
            $transaction->trolleyId = $troleyId;
            $transaction->orderQty = $orderLimit;
        }


        foreach ($pickOrdersToAllocate as $pickOrder) {
            $productsToAllocate = $this->_selectOrderProductsToAllocate($pickOrder);

            switch ($this->_validateAllocateRequest(array($pickOrder => $pickOrder), $productsToAllocate, $orderLimit, $productLimit)) {
                case self::REQUEST_ORDER_LIMIT_EXCEEDED:
                    $result->messages[] = 'Order Limit reached.';
                    return $result;

                case self::REQUEST_PRODUCT_LIMIT_EXCEEDED:
                    $result->messages[] = 'Product Limit reached.';
                    return $result;
            }

            $transaction->orderNo = $pickOrder;
            if ($minder->doTransactionResponse($transaction) !== false) {
                $result->messages[] = "Order #" . $transaction->orderNo . " was allocated to trolley " . $troleyId . ".";

                $this->_allocatedOrders[$pickOrder] = $pickOrder;
                $this->_allocatedProducts = array_merge($this->_allocatedProducts, $productsToAllocate);

                $result->allocatedOrders++;
                $result->allocatedProducts += count($this->_allocatedProducts);
            } else {
                $result->errors[] = "Error allocating Order #" . $pickOrder . ": " . $minder->lastError;
                return $result;
            }
        }

        return $result;
    }

}