<?php

class Minder_LabelPrinter_Product extends Minder_LabelPrinter_Abstract {

    protected $_prodProfile = array();
    protected $_prodEan     = array();

    protected function _fetchProdProfile($prodId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM PROD_PROFILE WHERE PROD_ID = ?', $prodId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        if (count($queryResult) < 1) {
            throw new Exception('PROD_PROFILE #' . $prodId . ' not found ');
        }

        return array_shift($queryResult);
    }

    protected function _getProdProfile($prodId) {
        if (empty($this->_prodProfile[$prodId])) {
            $this->_prodProfile[$prodId] = $this->_fetchProdProfile($prodId, $prodId);
        }

        return $this->_prodProfile[$prodId];
    }

    protected function _fetchProdEan($prodId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM PROD_EAN WHERE PROD_ID = ?', $prodId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getProdEan($prodId) {
        if (empty($this->_prodEan[$prodId])) {
            $this->_prodEan[$prodId] = $this->_fetchProdEan($prodId, $prodId);
        }

        return $this->_prodEan[$prodId];
    }

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        switch ($tableName) {
            case 'PROD_PROFILE':
                return $this->_getProdProfile($labelId);
            case 'PROD_EAN':
                return $this->_getProdEan($labelId);
            default:
                return array();
        }
    }

    protected function _printLabel($labeldata)
    {
        return $this->_getPrinter()->printProductLabel($labeldata, $this->_getLabelType());
    }
}