<?php

class ManifestBuilder_Table_CarrierService extends Zend_Db_Table {
    protected $_name = 'CARRIER_SERVICE';
    protected $_rowClass = 'ManifestBuilder_Model_CarrierService';

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDespatchCarrierService(ManifestBuilder_Model_PickDespatch $pickDespatch) {
       

        $select = $this->select(static::SELECT_WITH_FROM_PART)
            ->where('RECORD_ID = ?', $pickDespatch->PICKD_SERVICE_RECORD_ID)
            ->limit(1);

        $result = $this->fetchRow($select);

        return is_null($result) ? $this->createRow(array()) : $result;
    }

    public function getByCarrierAndAccount(ManifestBuilder_Model_Carrier $carrier, $account) {
     


        $select = $this->select(static::SELECT_WITH_FROM_PART)
            ->joinLeft('CARRIER', 'CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID', array())
            ->where('CARRIER_SERVICE.CARRIER_ID = ?', $carrier->CARRIER_ID)
            ->where('COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT) = ?', $account);

        return $this->fetchAll($select);
    }

    public function getByCarrierAndMerchantLocation(ManifestBuilder_Model_Carrier $carrier, $merchantLocationId) {
       


        $select = $this->select(static::SELECT_WITH_FROM_PART)
            ->where('CARRIER_SERVICE.CARRIER_ID = ?', $carrier->CARRIER_ID)
            ->where('CARRIER_SERVICE.SERVICE_LOCATION_ID = ?', $merchantLocationId);

        return $this->fetchAll($select);
    }
}