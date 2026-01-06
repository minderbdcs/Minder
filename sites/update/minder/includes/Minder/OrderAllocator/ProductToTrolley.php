<?php

class Minder_OrderAllocator_ProductToTrolley extends Minder_OrderAllocator_Abstract {
    /**
     * @return string
     */
    protected function _getTransactionGroup()
    {
        return 'PKAL_TPROD';
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

        $prodIdToAllocate = $itemProvider->selectProdIdToAllocate($productLimit);

        if (count($prodIdToAllocate) < 1) {
            $result->warnings[] = 'No PROD_ID selected to allocate.';
            return $result;
        }

        $result = $this->_doAllocate($deviceId, $userId, $prodIdToAllocate);

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

    private function _doAllocate($deviceId, $userId, $prodIdToAllocate)
    {
        $result = new Minder_OrderAllocator_Result();
        $minder = Minder::getInstance();

        /**
         * @var Transaction_PKALJ $transaction
         */
        $transaction = $this->_getTransactionObject();
        $transaction->prodId = '';
        $transaction->deviceId = $deviceId;
        $transaction->pickerId = $userId;

        foreach ($prodIdToAllocate as $prodId) {
            if (empty($prodId)) continue;

            $transaction->prodId = $prodId;
            if ($minder->doTransactionResponse($transaction) !== false) {
                $result->messages[] = "PROD_ID #" . $transaction->prodId . " was allocated to device " . $deviceId . ".";

                $this->_allocatedProducts[$prodId] = $prodId;

                $result->allocatedProducts++;
            } else {
                $result->errors[] = "Error allocating PROD_ID #" . $prodId . ": " . $minder->lastError;
                return $result;
            }
        }

        return $result;
    }

}