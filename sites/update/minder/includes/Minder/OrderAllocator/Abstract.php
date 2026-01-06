<?php

/**
 * @throws Minder_Exception|Minder_OrderAllocator_Exception
 */
abstract class Minder_OrderAllocator_Abstract {

    protected $_allocatedOrders = null;
    protected $_allocatedProducts = null;
    protected $_allocatedItems = null;

    protected $_skippedOrders = array();

    const ORDERS_TO_TROLLEY   = 'orders_to_trolley';
    const ORDERS_TO_DEVICE    = 'orders_to_device';
    const PRODUCTS_TO_TROLLEY = 'products_to_trolley';
    const PRODUCTS_TO_DEVICE  = 'products_to_device';

    const REQUEST_ORDER_LIMIT_EXCEEDED   = 'ORDER_LIMIT_EXCEEDED';
    const REQUEST_PRODUCT_LIMIT_EXCEEDED = 'PRODUCT_LIMIT_EXCEEDED';
    const REQUEST_ITEM_LIMIT_EXCEEDED    = 'ITEM_LIMIT_EXCEEDED';
    const REQUEST_VALID                  = 'REQUEST_VALID';

    /**
     * @return array
     */
    protected function _getPickOrdersToAllocate() {
        $minder = Minder::getInstance();

        $sql = "
            SELECT DISTINCT
                PICK_ORDER.PICK_ORDER,
                COALESCE(PICK_ORDER.PICK_DUE_DATE, CURRENT_TIMESTAMP) AS PICK_DUE_DATE
            FROM
                PICK_ORDER
                LEFT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
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

        $result = array();

        if (false !== ($queryResult = $minder->fetchAllAssoc($sql))) {
            foreach ($queryResult as $resultRow) {
                $result[$resultRow['PICK_ORDER']] = $resultRow['PICK_ORDER'];
            }
        }

        return $result;
    }

    protected function _checkPartialDespatchedOrders($orders, $orderLimit) {
        $permission = new Minder_Permission_Order_PartialDespatch();
        $result = array();
        foreach ($orders as $order) {
            if ($permission->allowedFor($order)) {
                $result[$order] = $order;
            } else {
                $this->_skippedOrders[] = $order;
            }

            if (count($result) == $orderLimit) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * @param string $pickOrder
     * @return array
     */
    protected function _selectOrderProductsToAllocate($pickOrder) {
        $sql = "
            SELECT DISTINCT
                PROD_ID
            FROM
                PICK_ITEM
            WHERE
                PICK_LINE_STATUS = 'OP'
                AND PICK_ORDER = ?
        ";

        $result = array();

        if (false !== ($queryResult = Minder::getInstance()->fetchAllAssoc($sql, $pickOrder))) {
            foreach ($queryResult as $resultRow) {
                $result[$resultRow['PROD_ID']] = $resultRow['PROD_ID'];
            }
        }

        return $result;
    }

    /**
     * @param array $ordersToAllocate
     * @param array $productsToAllocate
     * @param int $orderLimit
     * @param int $productLimit
     * @param array $itemsToAllocate
     * @param int $itemLimit
     * @return string
     */
    protected function _validateAllocateRequest($ordersToAllocate, $productsToAllocate, $orderLimit, $productLimit, $itemsToAllocate = array(), $itemLimit = null) {
        if (count(array_merge($ordersToAllocate, $this->_allocatedOrders)) > $orderLimit)
            return self::REQUEST_ORDER_LIMIT_EXCEEDED;

        if (count(array_merge($productsToAllocate, $this->_allocatedProducts)) > $productLimit)
            return self::REQUEST_PRODUCT_LIMIT_EXCEEDED;

        if (!empty($itemLimit)) {
            if (count(array_merge($itemsToAllocate, $this->_allocatedItems)) > $itemLimit)
                return self::REQUEST_ITEM_LIMIT_EXCEEDED;
        }

        return self::REQUEST_VALID;
    }

    /**
     * @static
     *
     * @param string $method
     * @param $hasItemLimit
     * @throws Minder_Exception
     * @return Minder_OrderAllocator_Abstract
     */
    public static function getAllocator($method, $hasItemLimit) {
        switch ($method) {
            case self::ORDERS_TO_TROLLEY:
                return new Minder_OrderAllocator_OrderToTrolley();

            case self::ORDERS_TO_DEVICE:
                return ($hasItemLimit) ? new Minder_OrderAllocator_OrderToDeviceWithItemLimit() : new Minder_OrderAllocator_OrderToDevice();

            case self::PRODUCTS_TO_TROLLEY:
                return new Minder_OrderAllocator_ProductToTrolley();

            case self::PRODUCTS_TO_DEVICE:
                return new Minder_OrderAllocator_ProductToDevice();

            default:
                throw new Minder_Exception("Unsupported allocation method : '$method'.");
        }
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
    abstract public function allocate($itemProvider, $userId, $deviceId, $troleyId, $orderLimit, $productLimit, $itemLimit);

    protected function _getTransactionObject() {
        $transactionClass = Minder::getInstance()->getPKALTransClass($this->_getTransactionGroup());
        switch ($transactionClass) {
            case 'G':
                return new Transaction_PKALG();

            case 'I':
                return new Transaction_PKALI();

            case 'J':
                return new Transaction_PKALJ();

            case 'F':
                return new Transaction_PKALF();

            default:
                throw new Exception('Unsupported PKAL transaction class "' . $transactionClass . '" ');
        }
    }

    /**
     * @abstract
     * @return string
     */
    abstract protected function _getTransactionGroup();

    protected function _fillAllocatedOrdersAndProductsFromDevice($deviceId)
    {
        $sql = "
            SELECT DISTINCT
                PICK_ORDER,
                PROD_ID,
                PICK_LABEL_NO
            FROM
                PICK_ITEM
            WHERE
                PICK_LINE_STATUS IN ('AL','PG','PL')
                AND DEVICE_ID = ?
        ";

        $this->_allocatedOrders = array();
        $this->_allocatedProducts = array();
        $this->_allocatedItems = array();

        if (false !== ($result = Minder::getInstance()->fetchAllAssoc($sql, $deviceId))) {
            foreach ($result as $resultRow) {
                $this->_allocatedOrders[$resultRow['PICK_ORDER']] = $resultRow['PICK_ORDER'];
                $this->_allocatedProducts[$resultRow['PROD_ID']] = $resultRow['PROD_ID'];
                $this->_allocatedItems[$resultRow['PICK_LABEL_NO']] = $resultRow['PICK_LABEL_NO'];
            }
        }
    }
}