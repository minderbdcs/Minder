<?php
class Minder_SysScreen_Model_AwaitingCheckingOrders extends Minder_SysScreen_Model
{
    /**
    * Selects PICK_ORDER.PICK_ORDER for given conditions
    * 
    * 
    * @return array
    */
    public function selectPickOrder($rowOffset, $itemCountPerPage) {
        $pickOrders = array();
        
        if (false !== ($result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ORDER.PICK_ORDER'))) {
            $pickOrders = array_map(create_function('$item', 'return $item["PICK_ORDER"];'), $result);
        }
        
        return $pickOrders;
    }
    
    public function selectStatus($rowOffset, $itemCountPerPage) {
        $statuses = array();
        
        if (false !== ($result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ORDER.PICK_STATUS'))) {
            $statuses = array_map(create_function('$item', 'return $item["PICK_STATUS"];'), $result);
        }
        
        return $statuses;
    }

    public function selectPickOrderSubType($rowOffset, $itemCountPerPage) {
        $statuses = array();

        if (false !== ($result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ORDER.PICK_ORDER_SUB_TYPE'))) {
            $statuses = array_map(create_function('$item', 'return $item["PICK_ORDER_SUB_TYPE"];'), $result);
        }

        return $statuses;
    }

    protected function _fetchPickOrders($pickOrders) {
        $query = "
            SELECT
                PICK_ORDER.*,
                CASE
                    WHEN EXISTS (SELECT PICK_LABEL_NO FROM PICK_ITEM WHERE PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER AND PICK_ITEM.PICK_LINE_STATUS IN ('UC', 'PG', 'AS', 'HD', 'OP', 'CF')) THEN 'T'
                    ELSE 'F'
                END AS HAS_UNPICKED_ITEMS
            FROM
                PICK_ORDER
            WHERE
                PICK_ORDER IN (" . substr(str_repeat('?, ', count($pickOrders)), 0, -2) . ")
        ";
        array_unshift($pickOrders, $query);

        return call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $pickOrders);
    }

    public function getPickOrders($rowOffset, $itemCountPerPage) {
        $result = new Minder_PickOrder_Collection();

        $pickOrders = $this->selectPickOrder($rowOffset, $itemCountPerPage);

        if (!empty($pickOrders)) {
            $result->fromArray($this->_fetchPickOrders($pickOrders));
        }

        return $result;
    }

    /**
    * Used to move all PICK_ITEMS which belong to selected orders to given despatch location.
    * PICK_ITEMs moves only if PICK_ORDER has PICK_ORDER.PICK_STATUS = 'DA'
    * and if PICK_ITEM.PICK_LINE_STATUS = 'OP'
    * 
    * @param string $targetLocation - location to move PICK_ITEMs to
    * @param array  $searchFields - search fields
    * 
    * @return void
    * 
    * @throws Minder_SysScreen_Model_AwaitingCheckingOrders_Exception
    */
    public function movePickItemsToDespatchLocation($targetLocation = '', $searchFields = array()) {
        if (empty($targetLocation))
            throw new Minder_SysScreen_Model_AwaitingCheckingOrders_Exception('Cannot move to empty location.');
            
        $originalConditions = $this->getConditions();
        $this->addConditions(array('PICK_ORDER.PICK_STATUS = ?' => array('DA')));
        $ordersToMove       = $this->selectPickOrder(0, count($this));
        $this->setConditions($originalConditions);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $ordersToMove));
        if (count($ordersToMove) < 1) 
            return; //no valid orders, do nothing
            
//2010/08/13
//there are some questions about which transaction to use (see http://binary-studio.office-on-the.net/issues/3232),
//so until they be resolved I will update PICK_ITEMS directly
//        $sql = "
//            SELECT
//                case 
//                    when (PICK_ITEM.PROD_ID IS NULL OR PICK_ITEM.PROD_ID = '') then ISSN.PROD_ID
//                    else PICK_ITEM.PROD_ID
//                end AS PROD_ID
//            FROM
//                PICK_ITEM
//                LEFT JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
//            WHERE
//                PICK_ITEM.PICK_LINE_STATUS = 'PL'
//                AND PICK_ITEM.PICK_ORDER IN (" . substr(str_repeat('?, ', count($ordersToMove)), 0, -2) . ")
//        ";
//        
//        $minder                = Minder::getInstance();
//        $prodIds               = array();
//        $args                  = array_merge(array($sql), $ordersToMove);
//        $prodIds               = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
//        if (!is_array($prodIds) || count($prodIds) < 1) 
//            return; //no valid lines, do nothing
//        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $prodIds));
//        $transaction           = new Transaction_PKILP();
//        $transaction->location = $targetLocation;
//        foreach ($prodIds as $prodIdsRow) {
//            $transaction->productId = $prodIdsRow['PROD_ID'];
//            
//            if (false === $minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    ')) {
//                throw new Minder_SysScreen_Model_AwaitingCheckingOrders_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $minder->lastError);
//            }
//        }

        $minder                = Minder::getInstance();
        $searchConditions = array();
        
        foreach ($searchFields as $field) {
            if (empty($field['SEARCH_VALUE']))
                continue;
                
            switch ($field['SSV_NAME']) {
                case 'DESPATCH_LOCATION':
                    //check if this is movable location
                    $whId   = substr($field['SEARCH_VALUE'], 0, 2);
                    $locnId = substr($field['SEARCH_VALUE'], 2);
                    if ($minder->isMoveableLocation($locnId)) {
                        $tmpCondString = "
                            (PICK_ITEM.DESPATCH_LOCATION = ?
                            OR PICK_ITEM_DETAIL.DESPATCH_LOCATION = ?
                            OR ISSN.LOCN_ID = ?
                            OR PICK_ITEM.DESPATCH_LOCATION = ?
                            OR PICK_ITEM_DETAIL.DESPATCH_LOCATION = ?
                            OR ISSN.LOCN_ID = ?)
                        ";
                        $tmpArgs = array($locnId, $locnId, $locnId, $whId . $locnId, $whId . $locnId, $whId . $locnId);
                    } else {
                        $tmpCondString = "
                            (PICK_ITEM.DESPATCH_LOCATION = ?
                            OR PICK_ITEM_DETAIL.DESPATCH_LOCATION = ?
                            OR ISSN.LOCN_ID = ?)
                        ";
                        $tmpArgs = array($whId . $locnId, $whId . $locnId, $whId . $locnId);
                    }
                    $searchConditions[$tmpCondString] = $tmpArgs;
                    break;
                case 'PICK_ORDER':
                    $searchConditions['PICK_ITEM.PICK_ORDER LIKE ?'] = array($field['SEARCH_VALUE']);
                    break;
            }
        }

        //will do everithing in single transaction
        $minder->beginTransaction();
        try {
            $sql = "
                SELECT
                    PICK_ITEM.PICK_LABEL_NO,
                    PICK_ITEM_DETAIL.PICK_DETAIL_ID,
                    ISSN.SSN_ID
                FROM
                    PICK_ITEM 
                    LEFT OUTER JOIN PICK_ITEM_DETAIL ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                    LEFT OUTER JOIN ISSN ON PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID
                    LEFT OUTER JOIN PICK_ORDER ON PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER
                WHERE
                PICK_ITEM.PICK_LINE_STATUS = 'PL'
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = 'PL'
                AND PICK_ITEM.PICK_ORDER IN (" . substr(str_repeat('?, ', count($ordersToMove)), 0, -2) . ")
            ";
            
            $searchConditionsArgs = array();
            if (count($searchConditions) > 0) {
                $sql .= ' AND ' . implode(' AND ', array_keys($searchConditions));
                $searchConditionsArgs = array_reduce(array_values($searchConditions), create_function('$res, $item', '$res = (empty($res)) ? array() : $res; return array_merge($res, $item);'), array());
                $searchConditionsArgs = empty($searchConditionsArgs) ? array() : $searchConditionsArgs;
            }
            
            $lines                 = array();
            $args                  = array_merge(array($sql), $ordersToMove, $searchConditionsArgs);
            $lines                 = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
            
            if (!is_array($lines) || count($lines) < 1)  {
                $minder->rollbackTransaction();
                return; //no valid lines, do nothing
            }
            
            $pickItems       = array_unique(array_map(create_function('$item', 'return $item["PICK_LABEL_NO"];'), $lines));
            $pickItemDetails = array_unique(array_map(create_function('$item', 'return $item["PICK_DETAIL_ID"];'), $lines));
            $issns           = array_unique(array_map(create_function('$item', 'return $item["SSN_ID"];'), $lines));
            
            $sql = "
                UPDATE PICK_ITEM SET
                    PICK_ITEM.PICK_LINE_STATUS  = ?,
                    PICK_ITEM.DESPATCH_LOCATION = ?
                WHERE
                    PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($pickItems)), 0, -2) . ")
            ";
            
            $args = array_merge(array('DS', substr($targetLocation, 2)), $pickItems);
            $minder->execSQL($sql, $args);
            
            $sql = "
                UPDATE PICK_ITEM_DETAIL SET
                    PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?,
                    PICK_ITEM_DETAIL.DESPATCH_LOCATION  = ?
                WHERE
                    PICK_ITEM_DETAIL.PICK_DETAIL_ID IN (" . substr(str_repeat('?, ', count($pickItemDetails)), 0, -2) . ")
            ";
            
            $args = array_merge(array('DS', substr($targetLocation, 2)), $pickItemDetails);
            $minder->execSQL($sql, $args);
            
            $sql = "
                UPDATE ISSN SET
                    ISSN.ISSN_STATUS       = ?,
                    ISSN.PREV_PREV_WH_ID   = ISSN.PREV_WH_ID,
                    ISSN.PREV_PREV_LOCN_ID = ISSN.PREV_LOCN_ID,
                    ISSN.PREV_WH_ID        = ISSN.WH_ID,
                    ISSN.PREV_LOCN_ID      = ISSN.LOCN_ID,
                    WH_ID                  = ?,
                    LOCN_ID                = ?
                WHERE
                    ISSN.SSN_ID IN (" . substr(str_repeat('?, ', count($issns)), 0, -2) . ")
            ";
            
            $args = array_merge(array('DS', substr($targetLocation, 0, 2), substr($targetLocation, 2)), $issns);
            $minder->execSQL($sql, $args);
            
            $minder->commitTransaction();
        } catch (Exception $e) {
            $minder->rollbackTransaction();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));

            throw new Minder_SysScreen_Model_AwaitingCheckingOrders_Exception('Error transfering Pick Items: ' . $e->getMessage());
        }
        
        return;
    }
}

class Minder_SysScreen_Model_AwaitingCheckingOrders_Exception extends Minder_SysScreen_Model_Exception {}