<?php

/**
 * @property string $despatchId
 * @property int $alreadyPrintedAmount
 * @property int $totalLabelAmount
 */
class Minder_PackIdPrintTools {

    protected $_printData = array();
    protected $_allreadyPrintedAmount = null;
    protected $_totalLabelAmount   = null;

    protected $_despatchId    = null;
    protected $_packId        = null;

    function __get($name)
    {
        switch ($name) {
            case 'despatchId':
                return $this->_despatchId;
            case 'alreadyPrintedAmount':
                return $this->_getAllreadyPrintedAmount();
            case 'totalLabelAmount':
                return $this->_getTotalLabelAmount();
            default:
                return null;
        }
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'despatchId':
                $this->_despatchId            = strval($value);
                $this->_allreadyPrintedAmount = null;
                $this->_totalLabelAmount      = null;
                break;
        }
    }

    protected function _fillEmptyResult($packId, $table) {
        $table = strtoupper($table);
        $fieldList = Minder::getInstance()->getFieldList($table);

        foreach ($fieldList as $fieldName) {
            $this->_printData[$packId][$table . '.' . $fieldName] = null;
        }
    }

    protected function _fillPostcodeDepotInfo($packId, $addressDetails) {
        $sql = "
            SELECT FIRST 1
                *
            FROM
                POSTCODE_DEPOT
            WHERE
                POST_CODE = ?
        ";

        if (empty($addressDetails['POST_CODE'])) {
            $this->_fillEmptyResult($packId, 'POSTCODE_DEPOT');
        } else {
            if (false !== ($queryResult = Minder::getInstance()->fetchAllAssocExt($sql, $addressDetails['POST_CODE']))) {
                $postcode = current($queryResult);
                if (empty($postcode)) {
                    $this->_fillEmptyResult($packId, 'POSTCODE_DEPOT');
                } else {
                    $this->_printData[$packId] = array_merge($this->_printData[$packId], $postcode);
                }
            }
        }
    }

    protected function _fillIssnInfo($packId) {
        $sql = '
            SELECT FIRST 1
                *
            FROM
                ISSN
            WHERE
                ISSN.ORIGINAL_SSN = ?
        ';

        $this->_printData[$packId]['PROD_ID'] = $this->_printData[$packId]['PACK_ID.PROD_ID'];
        $tmpSsnId = $this->_printData[$packId]['PACK_ID.SSN_ID'];

        if (empty($tmpSsnId)) {
            $this->_fillEmptyResult($packId, 'ISSN');
        } else {
            if (false !== ($queryResult = Minder::getInstance()->fetchAllAssocExt($sql, $tmpSsnId))) {
                $this->_printData[$packId] = array_merge($this->_printData[$packId], current($queryResult));

                if (empty($this->_printData[$packId]['PROD_ID']))
                    $this->_printData[$packId]['PROD_ID'] = $this->_printData[$packId]['ISSN.PROD_ID'];
            }
        }

    }

    protected function _fillPickItemInfo($packId) {
        $sql = '
            SELECT FIRST 1
                PICK_ITEM_DETAIL.*,
                PICK_ITEM.*
            FROM
                PACK_ID
                LEFT OUTER JOIN PICK_ITEM_DETAIL ON PICK_ITEM_DETAIL.PACK_ID = PACK_ID.PACK_ID
                LEFT OUTER JOIN PICK_ITEM ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
            WHERE
                PACK_ID.PACK_ID = ?
        ';

        if (false !== ($queryResult = Minder::getInstance()->fetchAllAssocExt($sql, $packId))) {
            $this->_printData[$packId] = array_merge($this->_printData[$packId], current($queryResult));
        }
    }

    protected function _getAddressDetails($data) {
        $country = empty($data['PICK_ORDER.D_POST_CODE']) ? $data['PICK_ORDER.P_COUNTRY'] : $data['PICK_ORDER.D_COUNTRY'];
        $country = empty($country) ? 'AU' : strtoupper(trim($country));

        if (in_array($country, array('AU', 'AUST', 'AUSTRALIA'))) {
            return array(
                'POST_CODE' => (empty($data['PICK_ORDER.D_POST_CODE']) ? $data['PICK_ORDER.P_POST_CODE'] : $data['PICK_ORDER.D_POST_CODE']),
                'STATE' => (empty($data['PICK_ORDER.D_POST_CODE']) ? $data['PICK_ORDER.P_STATE'] : $data['PICK_ORDER.D_STATE'])
            );
        }

        return array(
            'POST_CODE' => '',
            'STATE' => ''
        );
    }

    protected function _fillExtendedInfo() {
        foreach ($this->_printData as $packId => $packIdData) {
            $this->_fillIssnInfo($packId);
            $this->_fillPickItemInfo($packId);
            $this->_fillPostcodeDepotInfo($packId, $this->_getAddressDetails($this->_printData[$packId]));
        }
    }

    protected function _fillMainInfo($isForRePrint) {
        $sql = '
            SELECT
                *
            FROM
                PACK_ID
                LEFT OUTER JOIN PICK_DESPATCH ON PACK_ID.DESPATCH_ID = PICK_DESPATCH.DESPATCH_ID
                LEFT OUTER JOIN CARRIER_SERVICE ON PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID
                LEFT OUTER JOIN CARRIER ON CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID
        ';

        $args = array();

        if ($isForRePrint) {
            $sql .= '
                WHERE
                    PACK_ID.PACK_ID = ?
            ';
            $args[] = $this->_packId;
        } else {
            $sql .= '
                WHERE
                    PACK_ID.DESPATCH_ID = ?
                    AND PACK_ID.LABEL_PRINTED_DATE IS NULL
            ';
            $args[] = $this->_despatchId;
        }

        $sql .= '
            ORDER BY PACK_ID.PACK_ID DESC
        ';

        array_unshift($args, $sql);

        $minder = Minder::getInstance();

        $this->_printData = array();
        if (false !== ($queryResult = call_user_func_array(array($minder, 'fetchAllAssocExt'), $args))) {
            foreach ($queryResult as $resultRow) {
                $this->_printData[$resultRow['PACK_ID.PACK_ID']] = $resultRow;
            }
        }
    }

    protected function _fillPickOrderInfo($isForRePrint) {
        $sql = "
            SELECT FIRST 1
                PICK_ORDER.*
            FROM
                PACK_ID
                LEFT OUTER JOIN PICK_ITEM_DETAIL ON PICK_ITEM_DETAIL.DESPATCH_ID = PACK_ID.DESPATCH_ID
                LEFT OUTER JOIN PICK_ITEM ON PICK_ITEM.PICK_LABEL_NO = PICK_ITEM_DETAIL.PICK_LABEL_NO
                LEFT OUTER JOIN PICK_ORDER ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ORDER.PICK_ORDER IS NOT NULL
        ";

        $args = array();

        if ($isForRePrint) {
            $sql .= '
                AND PACK_ID.PACK_ID = ?
            ';
            $args[] = $this->_packId;
        } else {
            $sql .= '
                    AND PACK_ID.DESPATCH_ID = ?
            ';
            $args[] = $this->_despatchId;
        }

        array_unshift($args, $sql);

        $minder = Minder::getInstance();

        if (false !== ($queryResult = call_user_func_array(array($minder, 'fetchAllAssocExt'), $args))) {
            $pickOrderInfo = current($queryResult);
            $pickOrderInfo = is_array($pickOrderInfo) ? $pickOrderInfo : array();
            foreach ($this->_printData as &$printDataRow) {
                $printDataRow = array_merge($printDataRow, $pickOrderInfo);
            }
        }
    }

    protected function _getAllreadyPrintedAmount() {
        if (is_null($this->_allreadyPrintedAmount)) {
            if (empty($this->_despatchId))
                return null;

            $this->_allreadyPrintedAmount = 0;
            $sql = 'SELECT COUNT(DISTINCT PACK_ID.PACK_ID) FROM PACK_ID WHERE PACK_ID.DESPATCH_ID = ? AND PACK_ID.LABEL_PRINTED_DATE IS NOT NULL';
            $this->_allreadyPrintedAmount = intval(Minder::getInstance()->findValue($sql, $this->_despatchId));
        }

        return $this->_allreadyPrintedAmount;
    }

    protected function _getTotalLabelAmount() {
        if (is_null($this->_totalLabelAmount)) {
            if (empty($this->_despatchId))
                return null;

            $this->_totalLabelAmount = 0;
            $sql = 'SELECT PICK_DESPATCH.PICKD_ADDRESS_QTY FROM PICK_DESPATCH WHERE PICK_DESPATCH.DESPATCH_ID = ?';
            $this->_totalLabelAmount = intval(Minder::getInstance()->findValue($sql, $this->_despatchId));

        }

        return $this->_totalLabelAmount;
    }

    public function getPackIdListForPrint($despatchId) {
        $this->despatchId = $despatchId;
        $this->_fillMainInfo(false);
        $this->_fillPickOrderInfo(false);
        $this->_fillExtendedInfo();

        return $this->_printData;
    }

    public function getPackIdListForRePrint($packId) {
        $this->_packId = $packId;
        $this->_fillMainInfo(true);
        $this->_fillPickOrderInfo(true);
        $this->_fillExtendedInfo();

        if (count($this->_printData) > 0) {
            $tmpFirstRow = current($this->_printData);
            $this->despatchId = $tmpFirstRow['PACK_ID.DESPATCH_ID'];
        }

        return $this->_printData;
    }

    /**
     * @param string $despatchId
     * @param Minder_Printer_Abstract $printerObject
     * @return \Minder_JSResponse
     */
    public function printLabels($despatchId, $printerObject) {
        $printResult = new Minder_JSResponse();
        $printResult->printedTotal = 0;

        try {
            $printData = $this->getPackIdListForPrint($despatchId);
            $tmpLabelsCount  = $this->totalLabelAmount;
            $initialPrinted  = $tmpPrintedCount = $this->alreadyPrintedAmount;

            if(count($printData) > 0){
                foreach ($printData as $labelRow) {
                    $labelRow['labelqty'] = ++$tmpPrintedCount;
                    $labelRow['ofqty']    = $tmpLabelsCount;

                    $result =    $printerObject->printDespatchAddressLabel($labelRow, 'DESPATCH');

                    if($result['RES'] < 0){
                        throw new Minder_Exception($result['ERROR_TEXT']);
                    }

                    $printResult->printedTotal++;
                }

                $tmpPrintedCount -= $initialPrinted;

                if ($tmpLabelsCount > $tmpPrintedCount) {
                    $printResult->messages[] = '#' . $despatchId . ' ' . $tmpPrintedCount . ' labels of ' . $tmpLabelsCount . ' was printed.';
                } else {
                    $printResult->messages[] = '#' . $despatchId . ' all ' . $tmpLabelsCount . ' labels was printed.';
                }
            } else {
                if ($tmpLabelsCount > 0) {
                    $printResult->warnings[] = '#' . $despatchId . ' all labels was printed. Use REPRINT LABEL insteed.';
                }
            }
        } catch (Exception $e) {
            $printResult->errors[] = 'Error printing label for #' . $despatchId . ': ' . $e->getMessage();
        }

        return $printResult;
    }

    /**
     * @param string $packId
     * @param Minder_Printer_Abstract $printerObject
     * @return Minder_JSResponse
     */
    public function reprintLabel($packId, $printerObject) {
        $printResult               = new Minder_JSResponse();
        $printResult->printedTotal = 0;

        try {
            $printData                 = $this->getPackIdListForRePrint($packId);

            if (count($printData) > 0) {
                foreach ($printData as $labelRow) {
                    $labelRow['labelqty'] = ++$printResult->printedTotal;
                    $labelRow['ofqty']    = $this->totalLabelAmount;

                    $result =    $printerObject->printDespatchAddressLabel($labelRow, 'DESPATCH');

                    if($result['RES'] < 0){
                        $printResult->printedTotal--;
                        $printResult->errors[] = 'Error re-printing label #' . $packId . ': ' . $result['ERROR_TEXT'];
                        break;
                    } else {
                        $printResult->messages[] = 'Label #' . $packId . ' was re-printed.';
                    }
                }
            } else {
                $printResult->errors[] = 'PACK_ID #' . $packId . ' not found.';
            }
        } catch (Exception $e) {
            $printResult->errors[] = 'Error re-printing label #' . $packId . ': ' . $e->getMessage();
        }

        return $printResult;
    }
}