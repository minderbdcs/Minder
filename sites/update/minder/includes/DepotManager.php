<?php

class DepotManager {
    public function find($depotId) {
        $dataRow = $this->_getMinder()->fetchAssoc('SELECT FIRST 1 * FROM CARRIER_DEPOT WHERE RECORD_ID = ?', $depotId);

        if (empty($dataRow)) {
            throw new Exception('CARRIER_SERVICE #' . $depotId . ' is not found.');
        }

        return new Depot($dataRow);
    }

    public function getDepots() {
        $result = array();

        foreach ($this->_fetchAllActive() as $dataRow) {
            $result[] = new Depot($dataRow);
        }

        return $result;
    }

    protected function _fetchAllActive() {
        return $this->_getMinder()->fetchAllAssoc("SELECT * FROM CARRIER_DEPOT WHERE CD_DEPOT_STATUS = 'OK'");
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}