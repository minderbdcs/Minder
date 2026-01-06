<?php

class ManifestBuilder_TnT_ItemManager {
    /**
     * @var ManifestBuilder_Table_CarrierService
     */
    protected $carrierServiceManager;

    /**
     * @var ManifestBuilder_Table_PackId
     */
    protected $packIdManager;

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_TnT_Item[]
     */
    public function getItems(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        $result = array();
        $carrierService = $this->_getDespatchCarrierService($pickDespatch);

        foreach ($this->_getDespatchPackIds($pickDespatch) as $packId) {
            $newItem = new ManifestBuilder_TnT_Item();
            $newItem->setPickDespatch($pickDespatch);
            $newItem->setCarrierService($carrierService);
            $newItem->setPackId($packId);

            $result[] = $newItem;
        }

        return $result;
    }

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_Model_CarrierService
     */
    protected function _getDespatchCarrierService(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getCarrierServiceManager()->getDespatchCarrierService($pickDespatch);
    }

    /**
     * @return ManifestBuilder_Table_CarrierService
     */
    protected function _getCarrierServiceManager() {
        if (empty($this->carrierServiceManager)) {
            $this->carrierServiceManager = new ManifestBuilder_Table_CarrierService();
        }

        return $this->carrierServiceManager;
    }

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_Model_PackId[]|Zend_Db_Table_Rowset_Abstract
     */
    protected function _getDespatchPackIds(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getPackIdManager()->_getDespatchedPackIds($pickDespatch);
    }

    /**
     * @return ManifestBuilder_Table_PackId
     */
    protected function _getPackIdManager() {
        if (empty($this->packIdManager)) {
            $this->packIdManager = new ManifestBuilder_Table_PackId();
        }

        return $this->packIdManager;

    }
}
