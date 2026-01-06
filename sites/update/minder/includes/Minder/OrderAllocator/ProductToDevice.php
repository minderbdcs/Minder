<?php

class Minder_OrderAllocator_ProductToDevice extends Minder_OrderAllocator_Abstract {
    /**
     * @return string
     */
    protected function _getTransactionGroup()
    {
        return 'PKAL_DPROD';
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

        if (!is_null($itemLimit)) {
            if (empty($itemLimit)) {
                $itemLimit = $minder->defaultControlValues['MAX_PICK_LINES'];
                $result->warnings[] = 'No Item Limit given. Default value "' . $itemLimit . '" used.';

            }
        }

        $labelNoAndProdIdToAllocate = $itemProvider->selectProdIdAndPickLabelNoToAllocate();

        if (count($labelNoAndProdIdToAllocate) < 1) {
            $result->warnings[] = 'No Open for Picking PICK_ITEMs selected to allocate.';
            return $result;
        }

        $result = $this->_doAllocate($deviceId, $userId, $labelNoAndProdIdToAllocate, $orderLimit, $productLimit, $itemLimit);

        switch (true) {
            case ($result->allocatedProducts == 0):
                $result->warnings[] = 'No PROD_IDs were allocated.';
                break;

            case ($result->allocatedProducts == 1):
                $result->messages[] = '1 PROD_ID was allocated.';
                break;

            case ($result->allocatedProducts > 1):
                $result->messages[] = $result->allocatedProducts . ' PROD_IDs were allocated.';
                break;
        }

        return $result;
    }

    private function _doAllocate($deviceId, $userId, $labelNoAndProdIdToAllocate, $orderLimit, $productLimit, $itemLimit)
    {
        $result = new Minder_OrderAllocator_Result();
        $minder = Minder::getInstance();

        $this->_fillAllocatedOrdersAndProductsFromDevice($deviceId);

        /**
         * @var Transaction_PKALF $transaction
         */
        $transaction = $this->_getTransactionObject();
        $transaction->prodId = '';
        $transaction->pickLabelNo = '';
        $transaction->deviceId = $deviceId;
        $transaction->pickerId = $userId;

        foreach ($labelNoAndProdIdToAllocate as $labelNo => $rowData) {
            if (empty($labelNo)) continue;

            $prodId = $rowData['PROD_ID'];
            $pickOrder = $rowData['PICK_ORDER'];
            $validateResult = $this->_validateAllocateRequest(
                array($pickOrder => $pickOrder),
                array($prodId => $prodId),
                $orderLimit,
                $productLimit,
                array($labelNo => $labelNo),
                $itemLimit
            );

            switch ($validateResult) {
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
            $transaction->pickLabelNo = $labelNo;
            if ($minder->doTransactionResponse($transaction) !== false) {
                $result->messages[] = "PROD_ID #" . $transaction->prodId . " was allocated to device " . $deviceId . ".";

                $this->_allocatedProducts[$prodId]  = $prodId;
                $this->_allocatedOrders[$pickOrder] = $pickOrder;
                $this->_allocatedItems[$labelNo]    = $labelNo;

                $result->allocatedProducts          = count($this->_allocatedProducts);
            } else {
                $result->errors[] = "Error allocating PROD_ID #" . $prodId . ": " . $minder->lastError;
                return $result;
            }
        }

        return $result;
    }
}