<?php

class ManifestBuilder_Table_CarrierDepot extends Zend_Db_Table {
    protected $_name = 'CARRIER_DEPOT';
    protected $_rowClass = 'ManifestBuilder_Model_CarrierDepot';

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_Model_CarrierDepot
     */
    public function getDespatchCarrierDepot(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        if (empty($pickDespatch->PICKD_SERVICE_RECORD_ID)) {
            $result = $this->createRow(array());
        } else {
            $select = $this->select()
                ->from('CARRIER_SERVICE', array())
                ->joinLeft('CARRIER_DEPOT', 'CARRIER_SERVICE.SERVICE_ACCOUNT = CARRIER_DEPOT.CD_SERVICE_ACCOUNT AND CARRIER_SERVICE.CARRIER_ID = CARRIER_DEPOT.CD_CARRIER_ID')
                ->where('CARRIER_SERVICE.RECORD_ID = ?', $pickDespatch->PICKD_SERVICE_RECORD_ID)
                ->limit(1);
            $result = $this->fetchRow($select);
        }

        return is_null($result) ? $this->createRow(array()) : $result;
    }
}