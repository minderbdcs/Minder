<?php

class Minder_ConnoteProccess_ItemMover_PickBlock extends Minder_ConnoteProccess_ItemMover_Abstract {
    protected $_movedItems = array();
    protected $_itemCandidates;

    function __construct($itemsToMove)
    {
        $this->_itemCandidates = $itemsToMove;
    }

    protected function _doSelectItemsWithWrongDetailsStatus($items) {
        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN (" . implode(', ', array_fill(0, count($items), '?')) . ")
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

        $args = array_merge(array($sql), array_values($items));
       $args[] = "|FETCH_MODE=SPLIT_ARRAY|";
        if (false === ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args))) {
            return array();
        }

        return Minder_ArrayUtils::mapField($queryResult, 'PICK_LABEL_NO');
    }

    /**
     * @return array
     */
    protected function _selectItemsWithWrongDetailsStatus()
    {
        $from = 0;
        $window = 1000;
        $result = array();

        while ($from < count($this->_itemCandidates)) {
            $result = array_merge($result, $this->_doSelectItemsWithWrongDetailsStatus(array_slice($this->_itemCandidates, 0, $window)));
            $from += $window;
        }

        return $result;
    }

    protected function _doFetchItemsToMove($items) {
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
                PICK_ITEM.PICK_LINE_STATUS IN ('PL', 'AS', 'AC', 'CK')
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('PL', 'AS')
                AND PICK_ITEM.PICK_LABEL_NO IN (" . implode(', ', array_fill(0, count($items), '?')) . ")
                AND PICK_ITEM_DETAIL.QTY_PICKED IS NOT NULL
                AND PICK_ITEM_DETAIL.QTY_PICKED > 0
        ";

        $args = array_merge(array($sql), array_values($items));
        $args[] = "|FETCH_MODE=SPLIT_ARRAY|";
        if (false === ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args))) {
            return array();
        }

        return $queryResult;
    }

    protected function _fetchItemsToMove() {
        $from = 0;
        $window = 1000;
        $result = array();

        while ($from < count($this->_itemCandidates)) {
            $result = array_merge($result, $this->_doFetchItemsToMove(array_slice($this->_itemCandidates, 0, $window)));
            $from += $window;
        }

        return $result;
    }

    protected function _doMove($whId, $locnId)
    {
        $itemsToMove = $this->_fetchItemsToMove();

        if (empty($itemsToMove)) {
            return; //nothing to move
        }

        $transaction = new Transaction_PKILL();
        $transaction->toWhId = $whId;
        $transaction->toLocnId = $locnId;

        $tmpArray = array();

        foreach ($itemsToMove as $item) {
            $fromWhId   = $item['WH_ID'];
            $fromLocnId = $item['LOCN_ID'];
            $key        = $fromWhId . '-' . $fromLocnId;

            if (!isset($tmpArray[$key])) {
                $transaction->setPickOrder($this->_getMinder()->getPickOrder($item['PICK_ORDER']));
                $transaction->fromLocnId = $fromLocnId;
                $transaction->fromWhId   = $fromWhId;

                if (false === $this->_getMinder()->doTransactionResponse($transaction)) {
                    throw new Minder_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $this->_getMinder()->lastError);
                }
            }

            $tmpArray[$key]         = $key;
            $this->_movedItems[]    = $item;
        }
    }

    /**
     * @return array
     */
    protected function _getMovedItems()
    {
        return $this->_movedItems;
    }

    /**
     * @param mixed $movedItems
     * @return $this
     */
    protected function _setMovedItems($movedItems)
    {
        $this->_movedItems = $movedItems;
        return $this;
    }
}
