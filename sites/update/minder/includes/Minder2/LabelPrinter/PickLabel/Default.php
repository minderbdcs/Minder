<?php

class Minder2_LabelPrinter_PickLabel_Default extends Minder2_LabelPrinter_Abstract {

    protected function _filterEmptySsnId($item) {

    }

    protected function _fetchPickLabelNoBySsnId($data, $ssnIdFieldName) {
//        array_filter($data)

        $ssnIds = array_filter($this->_extractFieldValue($data, $ssnIdFieldName));

        if (empty($ssnIds))
            return array();

        $query = "
            SELECT DISTINCT
                PICK_LABEL_NO
            FROM
                ISSN
                LEFT JOIN PICK_ITEM_DETAIL ON ISSN.SSN_ID = PICK_ITEM_DETAIL.SSN_ID
            WHERE
                ISSN.SSN_ID IN (" . substr(str_repeat('?, ', count($ssnIds)), 0, -2) . ")
                AND ISSN.ISSN_STATUS = ?
                AND PICK_ITEM_DETAIL.PICK_DETAIL_STATUS = ?
        ";

        $args = $ssnIds;
        $args[] = 'PL';
        $args[] = 'PL';
        array_unshift($args, $query);

        $queryResult = call_user_func_array(array($this->_getMinder(), 'fetchAllAssoc'), $args);

        return $this->_extractFieldValue($queryResult, 'PICK_LABEL_NO');
    }

    protected function getPickLabeslNo($paramsMap, $data) {
        if (isset($paramsMap['PICK_LABEL_NO']))
            //return $this->_extractFieldValue($data, $paramsMap['PICK_LABEL_NO']);
            return $this->_extractFieldValue($data, 'PICK_LABEL_NO');

        if (isset($paramsMap['SSN_ID']))
            return $this->_fetchPickLabelNoBySsnId($data, 'SSN_ID');

        return array();
    }

    protected function _printLabels($pickLabelNos, $printer) {

        if (empty($pickLabelNos))
            return array('No labels where printed.');

        $legacyLabelPrinter = new Minder_LabelPrinter_PickLabel();
        $printResult = new Minder_JSResponse();

        foreach ($pickLabelNos as $pickLabelNo) {
            $printResult = $legacyLabelPrinter->doPrint($pickLabelNo, $printer, $printResult);
            if (count($printResult->errors) > 0)
                throw new Exception(implode(' ', $printResult->errors));
        }

        return $printResult->messages;
    }

    public function printLabel($paramsMap, $data, $printer) {
        return $this->_printLabels($this->getPickLabeslNo($paramsMap, $data), $printer) ;
    }
}
