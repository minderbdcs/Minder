<?php

class Minder_OrderAllocator_OrderToDevice extends Minder_OrderAllocator_Abstract {
    /**
     * @return string
     */
    protected function _getTransactionGroup()
    {
        return 'PKAL_DORDS';
    }

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

        $result = $this->_doAllocate($deviceId, $userId, $pickOrdersToAllocate, $orderLimit, $productLimit);

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

    protected function _doAllocate($deviceId, $userId, $pickOrdersToAllocate, $orderLimit, $productLimit) {
        $result = new Minder_OrderAllocator_Result();
        $minder = Minder::getInstance();

        $this->_fillAllocatedOrdersAndProductsFromDevice($deviceId);

        /**
         * @var Transaction_PKALI $transaction
         */
        $transaction = $this->_getTransactionObject();
        $transaction->orderNo  = '';
        $transaction->deviceId = $deviceId;
        $transaction->pickerId = $userId;

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
                $result->messages[] = "Order #" . $transaction->orderNo . " was allocated to device " . $deviceId . ".";

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