<?php
class Minder_SysScreen_Model_OrderCheckLine extends Minder_SysScreen_Model implements Minder_SysScreen_Model_ConnoteLine_Interface
{
    const PICK_BLOCK_LIMIT = 'PICK_BLOCK_LIMIT';

    public function __construct() {
        parent::__construct();
    }

    public function selectSsccLabels($pickLabelNos) {
        $result = array();
        $items= array_values($pickLabelNos);
        $offset = 0;
        $window = 1000;

        while ($tmpArr = array_slice($items, $offset, $window)) {
            $result = array_merge($result, $this->_doSelect($tmpArr));
            $offset += $window;
        }

        return $result;
    }

    protected function _doSelect($pickLabels) {
        if (empty($pickLabels)) {
            return array();
        }

        $sql = "
            SELECT
                PS_SSCC,
                PS_PICK_ORDER,
                PS_PICK_LABEL_NO,
                COALESCE(PS_OUT_SSCC, PS_SSCC) AS PS_OUT_SSCC,
                PS_PACK_TYPE,
                PS_SSCC_STATUS,
                PS_SSCC_WEIGHT,
                PS_SSCC_DIM_X,
                PS_SSCC_DIM_Y,
                PS_SSCC_DIM_Z,
                PS_SSCC_WEIGHT_UOM,
                PS_SSCC_DIM_UOM,
                PS_AWB_CONSIGNMENT_NO,
                CASE WHEN PS_SSCC_STATUS = 'GO' THEN 0 ELSE COALESCE(PS_QTY_SHIPPED, 0) END AS CHECKED_QTY
            FROM
                PACK_SSCC
            WHERE
                PS_PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($pickLabels)), 0, -2) . ")
        ";
        array_unshift($pickLabels, $sql);

        $result = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $pickLabels);

        foreach ($result as &$resultRow) {
            if (in_array($resultRow['PS_SSCC_STATUS'], array('CL', 'DC', 'DX'))) {
                $resultRow['completed'] = 'completed';
            }
        }

        return Minder_ArrayUtils::mapKey($result, 'PS_SSCC');
    }

    public function selectOrderFromSscc($pickLabelNos) {
        return array_shift(Minder_ArrayUtils::mapField(
            $this->selectSsccLabels(array_slice($pickLabelNos, 0, 1, true)),
            'PS_PICK_ORDER'
        ));
    }

    protected function _getDCNoByPickBlockLocationId($locationId) {
        $sql = "
            SELECT FIRST 1
                PS_DEL_TO_DC_IN_HOUSE_NO
            FROM
                PICK_ITEM
            WHERE
                PICK_LINE_STATUS IN ('PL', 'AC', 'CK', 'DS', 'AS')
                AND DESPATCH_LOCATION = ?
        ";

        return $this->_getMinder()->fetchOne($sql, $locationId);
    }

    public function addPickBlockLimit($locnId) {
        $conditions = $this->getConditionObject();

        $dcNo = $this->_getDCNoByPickBlockLocationId($locnId);

        if (false === $dcNo) {
            $conditions->addConditions(array('1 = 2' => array()));
        } else {
            if ($this->_tableExists('PICK_ITEM')) {
                $conditions->addConditions(array('PICK_ITEM.PS_DEL_TO_DC_IN_HOUSE_NO = ?' => array($dcNo)), static::PICK_BLOCK_LIMIT);
            } elseif ($this->_tableExists('PICK_ITEM_DETAIL')) {
                $conditions->addConditions(array('PICK_ITEM_DETAIL.PS_DEL_TO_DC_IN_HOUSE_NO = ?' => array($dcNo)), static::PICK_BLOCK_LIMIT);
            }
        }

        $this->setConditionObject($conditions);
    }

    public function removePickBlockLimit() {
        $conditions = $this->getConditionObject();
        $conditions->deleteConditions(static::PICK_BLOCK_LIMIT);
        $this->setConditionObject($conditions);
    }

    /**
     * @deprecated
     * @param $rowOffset
     * @param $itemCountPerPage
     * @return array
     * @throws Minder_SysScreen_Model_ConnoteLine_Exception
     */
    public function selectPickLabelNo($rowOffset, $itemCountPerPage) {
        $pkey = $this->getPrimaryIdExpression() . ' AS ' . $this->getPKeyAlias();

        if ($this->_tableExists('PICK_ITEM'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ' . $pkey . ', PICK_ITEM.PICK_LABEL_NO');

        if ($this->_tableExists('PICK_ITEM_DETAIL'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ' . $pkey . ', PICK_ITEM_DETAIL.PICK_LABEL_NO');
            
        throw new Minder_SysScreen_Model_ConnoteLine_Exception('PICK_ITEM_DETAIL and PICK_ITEM tables were not found in models table list, cannot get PICK_LABEL_NO.');
    }
    
    public function selectStatuses($rowOffset, $itemCountPerPage) {
        if ($this->_tableExists('PICK_ITEM'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ITEM.PICK_LINE_STATUS AS STATUS');
            
        if ($this->_tableExists('PICK_ITEM_DETAIL'))
            return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ITEM_DETAIL.PICK_DETAIL_STATUS AS STATUS');
            
        throw new Minder_SysScreen_Model_ConnoteLine_Exception('PICK_ITEM_DETAIL and PICK_ITEM tables were not found in models table list, cannot get PICK_LABEL_NO.');
    }

    protected function _selectCheckLineDetails($pickLabelNo) {
        $result = array();

        if (empty($pickLabelNo)) {
            return $result;
        }

        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO,
                PROD_PROFILE.PROD_ID,
                PROD_PROFILE.ALTERNATE_ID,
                PROD_PROFILE.PROD_TYPE,
                COALESCE(PROD_PROFILE.PACK_WEIGHT, 0) AS PACK_WEIGHT,
                PICK_ITEM.PICKED_QTY,
                0 AS CHECKED_QTY
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ORDER ON PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER
                LEFT JOIN PROD_PROFILE ON
                    PICK_ITEM.PROD_ID = PROD_PROFILE.PROD_ID
                    AND (
                        PICK_ORDER.COMPANY_ID = PROD_PROFILE.COMPANY_ID
                        OR (
                            PROD_PROFILE.COMPANY_ID = 'ALL'
                            AND NOT EXISTS (SELECT PROD_ID FROM PROD_PROFILE AS PP WHERE PP.PROD_ID = PROD_PROFILE.PROD_ID AND PP.COMPANY_ID = PICK_ORDER.COMPANY_ID)
                        )
                    )
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN ('" . implode("', '", $pickLabelNo) . "')
        ";

        $tmpResult = $this->_getMinder()->fetchAllAssoc($sql);

        if (!empty($tmpResult)) {
            $result = Minder_ArrayUtils::mapKey($tmpResult, 'PICK_LABEL_NO');
        }

        return $result;
    }

    public function selectPickLabelNos($rowOffset, $itemCountPerPage) {
        $pKeyAlias = $this->getPKeyAlias();
        $tmpResult = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ' . $this->getPrimaryIdExpression() . ' AS ' . $pKeyAlias . ', PICK_ITEM.PICK_LABEL_NO');

        if ($tmpResult) {
            return Minder_ArrayUtils::mapKeyValue($tmpResult, $pKeyAlias, 'PICK_LABEL_NO');
        }

        return array();
    }

    public function selectCheckLineDetails($pickLabelNos) {
        if (empty($pickLabelNos)) {
            return array();
        }

        $pickItems = array_values($pickLabelNos);
        $start = 0;
        $window = 1000;
        $result = array();

        while ($start < count($pickItems)) {
            $result = array_merge($result, $this->_selectCheckLineDetails(array_slice($pickItems, $start, $window)));
            $start += $window;
        }

        return Minder_ArrayUtils::reMap($result, $pickLabelNos);
    }

    protected function _getDespatchStatuses($items) {
        if (empty($items))
            return array();

        $sql = "
            SELECT
                PICK_ITEM.PICK_LABEL_NO,
                PICK_ITEM.PICK_LINE_STATUS,
                COALESCE(PICK_ITEM.PICKED_QTY, 0) AS PICKED_QTY,
                COALESCE(PICK_ITEM.PARTIAL_PICK_ALLOWED, PICK_ORDER.PARTIAL_PICK_ALLOWED) AS PARTIAL_PICK_ALLOWED,
                PICK_ORDER.WH_ID AS ORDER_WH_ID,
                COALESCE(
                    PICK_ITEM.WH_ID,
                    (
                        SELECT FIRST 1
                            PICK_ITEM_DETAIL.FROM_WH_ID
                        FROM
                            PICK_ITEM_DETAIL
                        WHERE
                            PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                            AND PICK_ITEM_DETAIL.FROM_WH_ID IS NOT NULL
                            AND NOT PICK_ITEM_DETAIL.PICK_DETAIL_STATUS IN ('CN', 'XX')
                    ),
                    PICK_ORDER.WH_ID
                    )  AS ITEM_WH_ID,
                COALESCE(
                    (
                        SELECT FIRST 1
                            'T'
                        FROM
                            PACK_SSCC
                        WHERE
                            PACK_SSCC.PS_PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                            AND PACK_SSCC.PS_LABEL_PRINTED_DATE IS NOT NULL
                    ),
                    'F'
                ) AS HAS_PRINTED_SSCC_LABELS
            FROM
                PICK_ITEM
                LEFT JOIN PICK_ORDER ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($items)), 0, -2) . ")
        ";

        $args = $items;

        array_unshift($args, $sql);

        if (false === ($queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args)  )) {
            throw new Minder_SysScreen_Model_AwaitingCheckingOrders_Exception('Error selecting DESPATCH status: ' . $this->_getMinder()->lastError);
        }

        return Minder_ArrayUtils::mapKey($queryResult, 'PICK_LABEL_NO');

    }

    public function getDespatchStatus($pickLabelNos) {
        if (empty($pickLabelNos)) {
            return array();
        }

        $lines  = array_values($pickLabelNos);
        $offset = 0;
        $window = 1000;
        $result = array();

        while ($tmpLines = array_slice($lines, $offset, $window)) {
            $result = array_merge($result, $this->_getDespatchStatuses($tmpLines));
            $offset += $window;
        }

        return array_values(Minder_ArrayUtils::reMap($result, $pickLabelNos, $this->getPKeyAlias()));

    }

    public function isReadyForDespatch($row) {
        if (in_array($row['PICK_LINE_STATUS'], array('DS', 'PL', 'AC', 'CK')))
            return true;

        if ($row['PICK_LINE_STATUS'] == 'AS' && $row['PARTIAL_PICK_ALLOWED'] == 'T')
            return true;

        return false;
    }

    public function isWhValid($row) {
        return strtoupper(trim($row['ORDER_WH_ID'])) == strtoupper(trim($row['ITEM_WH_ID']));
    }

    protected function _selectPackSsccExt($pickLabels) {
        $sql = "
            SELECT DISTINCT
                PACK_SSCC.PS_DEL_TO_DC_IN_HOUSE_NO,
                PICK_ORDER.COMPANY_ID,
                PICK_ORDER.PICK_ORDER
            FROM
                PACK_SSCC
                LEFT JOIN PICK_ORDER ON PACK_SSCC.PS_PICK_ORDER = PICK_ORDER.PICK_ORDER
            WHERE
                PACK_SSCC.PS_PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($pickLabels)), 0, -2) . ")
        ";

        $args = $pickLabels;
        array_unshift($args, $sql);

        return call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args);
    }

    protected function _hasPrintedLabels($ssccList) {
        foreach ($ssccList as $sscc) {
            if (!empty($sscc['PS_LABEL_PRINTED_DATE'])) {
                return true;
            }
        }

        return false;
    }

    protected function _printLabels($ssccList, $printer) {
        if (count($ssccList) < 1) {
            return 0;
        }

        $totalPrinted = count($ssccList);

        $labelData = array_shift($ssccList);

        $transaction = new Transaction_DSPSB();

        $transaction->printerId = $printer;
        $transaction->orderNo = $labelData['PS_PICK_ORDER'];
        $transaction->prodId = $labelData['PS_PRODUCT_GTIN'];
        $transaction->companyId = $labelData['COMPANY_ID'];
        $this->_getMinder()->doTransactionResponseV6($transaction);

        return $totalPrinted;
    }

    protected function _rePrintLabels($ssccList, $printer) {
        if (count($ssccList) < 1) {
            return 0;
        }

        $totalPrinted = 0;

        foreach ($ssccList as $labelData) {
            $transaction = new Transaction_DSPSR();

            $transaction->printerId = $printer;
            $transaction->ssccId = $labelData['PS_SSCC'];
            $transaction->orderNo = $labelData['PS_PICK_ORDER'];
            $transaction->prodId = $labelData['PS_PRODUCT_GTIN'];
            $transaction->companyId = $labelData['COMPANY_ID'];
            $this->_getMinder()->doTransactionResponseV6($transaction);
            $totalPrinted++;
        }

        return $totalPrinted;
    }

    protected function _printPackSsccByDcNo($dcNoList, $printer) {
        if (count($dcNoList) < 1) {
            return 0;
        }

        $totalPrinted = 0;

        foreach ($dcNoList as $labelData) {
            $transaction = new Transaction_DSPSD(
                $labelData['PICK_ORDER'],
                $labelData['PS_DEL_TO_DC_IN_HOUSE_NO'],
                $labelData['COMPANY_ID'],
                $printer
            );

            $this->_getMinder()->doTransactionResponseV6($transaction);
            $totalPrinted++;
        }

        return $totalPrinted;
    }

    public function printSsccPackLabels($pickLabelNos, $printer) {
        if (empty($pickLabelNos)) {
            return 0;
        }

        return $this->_printPackSsccByDcNo($this->_selectPackSsccExt(array_values($pickLabelNos)), $printer);
    }
}

class Minder_SysScreen_Model_OrderCheckLine_Exception extends Minder_SysScreen_Model_Exception {}
