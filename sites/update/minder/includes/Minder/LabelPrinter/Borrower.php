<?php

class Minder_LabelPrinter_Borrower extends Minder_LabelPrinter_Abstract {

    protected $_locations = array();

    protected function _fetchLocation($whId, $locnId) {
        $filters = array();

        if (empty($whId)) {
            $filters[] = "(WH_ID IS NULL OR WH_ID = ?)";
            $whId = '';
        } else {
            $filters[] = "WH_ID = ?";
        }

        if (empty($locnId)) {
            $filters[] = "(LOCH_ID IS NULL OR LOCN_ID = ?)";
            $locnId = '';
        } else {
            $filters[] = 'LOCN_ID = ?';
        }

        $sql = 'SELECT FIRST 1 * FROM LOCATION WHERE ' . implode(' AND ', $filters);

        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt($sql, $whId, $locnId))) {
            throw new Exception('Error fetching label data #' . $whId . $locnId . ': ' . $this->_getMinder()->lastError);
        }

        if (count($queryResult) < 1) {
            throw new Exception('LOCATION #' . $whId . $locnId . ' not found ');
        }

        return array_shift($queryResult);
    }

    protected function _getLocation($labelId) {
        $whId   = isset($labelId['WH_ID']) ? $labelId['WH_ID'] : '';
        $locnId = isset($labelId['LOCN_ID']) ? $labelId['LOCN_ID'] : '';
        if (empty($this->_locations[$whId]) || empty($this->_locations[$whId][$locnId])) {
            $this->_locations[$whId][$locnId] = $this->_fetchLocation($whId, $locnId);
        }

        return $this->_locations[$whId][$locnId];
    }

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        switch ($tableName) {
            case 'LOCATION':
                return $this->_getLocation($labelId);
            default:
                return array();
        }
    }

    protected function _printLabel($labeldata)
    {
	$borrowerId=$labeldata['LOCATION.LOCN_ID'];
        return $this->_getPrinter()->printBorrowerLabel($borrowerId);
    }

    protected function _formatLabelId($labelId)
    {
        $whId   = isset($labelId['WH_ID']) ? $labelId['WH_ID'] : '';
        $locnId = isset($labelId['LOCN_ID']) ? $labelId['LOCN_ID'] : '';
        return $whId . $locnId;
    }

    function __construct()
    {
        parent::__construct('BORROWER');
    }
}
