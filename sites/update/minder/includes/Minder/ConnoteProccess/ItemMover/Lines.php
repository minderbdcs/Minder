<?php

class Minder_ConnoteProccess_ItemMover_Lines extends Minder_ConnoteProccess_ItemMover_Abstract {
    protected $_itemCandidates = array();
    protected $_movedItems = null;

    function __construct($itemCandidates)
    {
        $this->_itemCandidates = $itemCandidates;
    }

    protected function _findItemsToMove()
    {
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
                AND PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($this->_itemCandidates)), 0, -2) . ")
                AND PICK_ITEM_DETAIL.QTY_PICKED IS NOT NULL
                AND PICK_ITEM_DETAIL.QTY_PICKED > 0
        ";

        $searchConditionsArgs = array_values($this->_itemCandidates);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $searchConditionsArgs));

        $args = array_merge(array($sql), $searchConditionsArgs);
        $args[] = "|FETCH_MODE=SPLIT_ARRAY|";
        if (false === ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args)))
            return array();

        return $queryResult;
    }

    protected function _selectItemsWithWrongDetailsStatus()
    {
        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($this->_itemCandidates)), 0, -2) . ")
                AND PICK_ITEM.PICK_LINE_STATUS IN ('PL', 'AC', 'CK')
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

        $args = array_merge(array($sql), $searchConditionsArgs);
        $args[] = "|FETCH_MODE=SPLIT_ARRAY|";
        if (false === ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args))) {
            return array();
        }

        $result = array();
        foreach ($queryResult as $resultRow) {
            $result[] = $resultRow['PICK_LABEL_NO'];
        }

        return $result;

    }

    protected function _moveItemToLocation($item, $targetLocation)
    {
        $transaction = new Transaction_PKILG();
        $transaction->locnId = $targetLocation;
        $transaction->objectId = $item['PROD_ID'];
        $transaction->reference = $item['LOCN_ID'];
        $transaction->pickedQty = $item['QTY_PICKED'];
        $transaction->subLocnId = $item['PICK_ORDER'];
        if (strlen($item['PICK_ORDER']) > 10) {
            $transaction->subLocnId = 'ORDER2BIG';
            $transaction->reference = $item['LOCN_ID'] . "|" . $item['PICK_ORDER'] . "|";
        }

        if (false === $this->_getMinder()->doTransactionResponse($transaction)) {
            throw new Minder_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $this->_getMinder()->lastError);
        }
    }

    /**
     * @param $whId
     * @param $locnId
     * @throws Minder_Exception
     */
    protected function _doMove($whId, $locnId)
    {
        $itemsToMove = $this->_findItemsToMove();
        if (empty($itemsToMove))
            return;

        foreach ($itemsToMove as $item) {
            $this->_moveItemToLocation($item, $whId . $locnId);
            $this->_movedItems[] = $item;
        }
    }

    /**
     * @return array
     */
    protected function _getMovedItems()
    {
        return $this->_movedItems;
    }
}
