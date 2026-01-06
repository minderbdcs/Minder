<?php

class Minder_LabelPrinter_GrnOrder extends Minder_LabelPrinter_Abstract {
    protected $_grnOrders   = array();
    protected $_grn         = array();

    protected function _fetchGrnOrder($grnLabelNo, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM GRN_ORDER WHERE GRN_LABEL_NO = ?', $grnLabelNo))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        if (count($queryResult) < 1) {
            throw new Exception('GRN_ORDER #' . $grnLabelNo . ' not found ');
        }

        return array_shift($queryResult);

    }

    protected function _getGrnOrder($grnLabelNo) {
        if (empty($this->_grnOrders[$grnLabelNo])) {
            $this->_grnOrders[$grnLabelNo] = $this->_fetchGrnOrder($grnLabelNo, $grnLabelNo);
        }

        return $this->_grnOrders[$grnLabelNo];
    }

    protected function _fetchGrn($grnNo, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM GRN WHERE GRN = ?', $grnNo))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getGrn($grnLabelNo) {
        $grnOrder = $this->_getGrnOrder($grnLabelNo);

        $grnNo = $grnOrder['GRN_ORDER.GRN'];

        if (empty($this->_grn[$grnNo])) {
            $this->_grn[$grnNo] = $this->_fetchGrn($grnNo, $grnLabelNo);
        }

        return $this->_grn[$grnNo];
    }

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        switch ($tableName) {
            case 'GRN_ORDER':
                return $this->_getGrnOrder($labelId);
            case 'GRN':
                return $this->_getGrn($labelId);
            default:
                return array();
        }
    }

    protected function _printLabel($labeldata)
    {
        return $this->_getPrinter()->printGrnOrderLabel($labeldata);
    }

    function __construct()
    {
        parent::__construct('GRNORDER');
    }


}