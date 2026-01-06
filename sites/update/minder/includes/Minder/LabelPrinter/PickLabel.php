<?php

class Minder_LabelPrinter_PickLabel extends Minder_LabelPrinter_Abstract {
    protected $_pickOrder    = array();
    protected $_prodProfile  = array();
    protected $_pickItem     = array();

    protected function _getPickOrder($pickLabelNo) {
        $pickItem = $this->_getPickItem($pickLabelNo);

        $pickOrder = $pickItem['PICK_ITEM.PICK_ORDER'];

        if (!isset($this->_pickOrder[$pickOrder]))
            $this->_pickOrder[$pickOrder] = $this->_fetchPickOrder($pickLabelNo, $pickOrder);

        return $this->_pickOrder[$pickOrder];
    }

    protected function _getProdProfile($pickLabelNo) {
        $pickItem = $this->_getPickItem($pickLabelNo);
        $prodId = $pickItem['PICK_ITEM.PROD_ID'];

        if (!isset($this->_prodProfile[$prodId]))
            $this->_prodProfile[$prodId] = $this->_fetchProdProfile($pickLabelNo, $prodId);

        return $this->_prodProfile[$prodId];
    }

    protected function _getPickItem($pickLabelNo) {
        if (!isset($this->_pickItem[$pickLabelNo]))
            $this->_pickItem[$pickLabelNo] = $this->_fetchPickItem($pickLabelNo);
        
        return $this->_pickItem[$pickLabelNo];
    }

    protected function _fetchProdProfile($pickLabelNo, $prodId) {
        $sql = "SELECT FIRST 1 SHORT_DESC, UOM FROM PROD_PROFILE WHERE PROD_ID = ? ";
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt($sql, $prodId)))
            throw new Exception('Error fetching label data #' . $pickLabelNo . ': ' . $this->_getMinder()->lastError);

        if (count($queryResult) < 1)
            return array('PROD_PROFILE.SHORT_DESC' => '', 'PROD_PROFILE.UOM' => '');
        
        return array_shift($queryResult);
    }

    protected function _fetchPickOrder($pickLabelNo, $pickOrder) {
        $sql = "SELECT FIRST 1 * FROM PICK_ORDER WHERE PICK_ORDER = ? ";
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt($sql, $pickOrder)))
            throw new Exception('Error fetching label data #' . $pickLabelNo . ': ' . $this->_getMinder()->lastError);

        if (count($queryResult) < 1)
            throw new Exception('PICK_ITEM #' . $pickLabelNo . ' not found.');

        return array_shift($queryResult);
    }

    protected function _fetchPickItem($pickLabelNo)
    {
        $sql = "SELECT FIRST 1 * FROM PICK_ITEM WHERE PICK_LABEL_NO = ?";
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt($sql, $pickLabelNo)))
            throw new Exception('Error fetching label data #' . $pickLabelNo . ': ' . $this->_getMinder()->lastError);

        if (count($queryResult) < 1)
            throw new Exception('PICK_ITEM #' . $pickLabelNo . ' not found.');

        return array_shift($queryResult);
    }

    protected function _fetchLabelDataFromTable($tableName, $labelId) {
        switch (strtoupper($tableName)) {
            case 'PICK_ITEM':
                $dataPart = $this->_getPickItem($labelId);
                break;
            case 'PICK_ORDER':
                $dataPart = $this->_getPickOrder($labelId);
                break;
            case 'PROD_PROFILE':
                $dataPart = $this->_getProdProfile($labelId);
                break;
            default:
                $dataPart = array();
        }

        return $dataPart;
    }

    protected function _printLabel($labelData) {
        return $this->_getPrinter()->printPickLabel($labelData);
    }

    function __construct()
    {
        parent::__construct('PICK_LABEL');
    }
}