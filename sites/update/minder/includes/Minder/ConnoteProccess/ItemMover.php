<?php

/**
 * @throws Minder_ConnoteProccess_Exception
 */
class Minder_ConnoteProccess_ItemMover {
    protected $_itemCandidates = array();
    protected $_itemsToMove    = null;
    protected $_movedItems     = null;
    protected $_minder;

    function __construct($itemCandidates)
    {
        $this->_itemCandidates = $itemCandidates;
        $this->_minder         = Minder::getInstance();
    }

    protected function _findItemsToMove() {
        $sql = "
            SELECT
                PICK_ITEM.PICK_ORDER,
                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM_DETAIL.PICK_DETAIL_ID,
                PICK_ITEM_DETAIL.QTY_PICKED,
                PICK_ITEM_DETAIL.DESPATCH_LOCATION AS ITEM_ORIGINAL_LOCN_ID,
                CASE
                    WHEN (PICK_ITEM.PROD_ID IS NULL OR PICK_ITEM.PROD_ID = '') THEN ISSN.PROD_ID
                    ELSE PICK_ITEM.PROD_ID
                END AS PROD_ID,
                ISSN.WH_ID,
                ISSN.LOCN_ID,
                ISSN.SSN_ID
            FROM
                PICK_ITEM
                LEFT OUTER JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                LEFT OUTER JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
            WHERE
                PICK_ITEM.PICK_LINE_STATUS IN ('PL', 'AS')
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('PL', 'AS')
                AND PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($this->_itemCandidates)), 0, -2) . ")
                AND PICK_ITEM_DETAIL.QTY_PICKED IS NOT NULL
                AND PICK_ITEM_DETAIL.QTY_PICKED > 0
        ";

        $searchConditionsArgs = array_values($this->_itemCandidates);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $searchConditionsArgs));

        $args                  = array_merge(array($sql), $searchConditionsArgs);
        if (false === ($queryResult = call_user_func_array(array($this->_minder, 'fetchAllAssoc'), $args)))
            return array();

        return $queryResult;
    }

    protected function _selectItemsWithWrongDetailsStatus() {
        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($this->_itemCandidates)), 0, -2) . ")
                AND PICK_ITEM.PICK_LINE_STATUS IN ('PL')
                AND NOT EXISTS (
                    SELECT
                        PICK_DETAIL_ID
                    FROM
                        PICK_ITEM_DETAIL
                    WHERE
                        PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                        AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('PL')
                )
        ";

        $searchConditionsArgs = array_values($this->_itemCandidates);

        $args                  = array_merge(array($sql), $searchConditionsArgs);
        if (false === ($queryResult = call_user_func_array(array($this->_minder, 'fetchAllAssoc'), $args))) {
            return array();
        }

        $result = array();
        foreach ($queryResult as $resultRow) {
            $result[] = $resultRow['PICK_LABEL_NO'];
        }

        return $result;

    }

    protected function _moveItemToLocation($item, $targetLocation) {
        $transaction = new Transaction_PKILG();
        $transaction->locnId    = $targetLocation;
        $transaction->objectId  = $item['PROD_ID'];
        $transaction->reference = $item['LOCN_ID'];
        $transaction->pickedQty = $item['QTY_PICKED'];
        $transaction->subLocnId = $item['PICK_ORDER'];
        if (strlen($item['PICK_ORDER']) > 10) {
                $transaction->subLocnId = 'ORDER2BIG';
                $transaction->reference = $item['LOCN_ID'] .  "|" . $item['PICK_ORDER'] . "|";
        }

        if (false === $this->_minder->doTransactionResponse($transaction)) {
            throw new Minder_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $this->_minder->lastError);
        }

    }

    public function moveItemsToDespatchLocation() {
        list($whId, $locnId) = $this->_minder->getDeviceWhAndLocation();
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $whId, $locnId));
        if (!$this->_minder->isDespatchLocation($whId, $locnId))
            throw new Minder_ConnoteProccess_Exception('Current device is not defined as despatch location.');

        $badItems = $this->_selectItemsWithWrongDetailsStatus();
        if (count($badItems) > 0) {
            throw new Minder_ConnoteProccess_Exception('Cannot move Items (' . implode(', ', $badItems) . ') to despatch location: not Item Details found.');
        }

        $itemsToMove = $this->_findItemsToMove();
        if (empty($itemsToMove))
            return;

        foreach ($itemsToMove as $item) {
            $this->_moveItemToLocation($item, $whId . $locnId);
            $this->_movedItems[] = $item;
        }
    }

    public function moveItemsToOriginalLocation() {
        $transaction         = new Transaction_PKUBB();

        $servedOrders = array();

        foreach ($this->_movedItems as $item) {
            if (isset($servedOrders[$item['PICK_ORDER']]))
                continue;

            $transaction->whId = $this->_minder->whId;
            $transaction->locnId = $item['ITEM_ORIGINAL_LOCN_ID'];
            $transaction->pickOrder = $item['PICK_ORDER'];

            if (false === $this->_minder->doTransactionResponse($transaction)) {
                throw new Minder_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $this->_minder->lastError);
            }

            $servedOrders[$transaction->pickOrder] = $transaction->pickOrder;
        }
    }
}
