<?php

class ManifestBuilder_TnT_ManifestManager {
    protected $_consignmentManager;

    public function getManifest(ManifestBuilder_TnT_CarrierAccount $carrierService, $manifestId, Zend_Date $currentDate) {
        $result = new ManifestBuilder_TnT_Manifest();
        $result->setManifestId($manifestId);
        $result->setCarrierService($carrierService);
        $result->setConsignments($this->_getManifestConsignments($carrierService, $result));
        $result->setDate($currentDate);

        return $result;
    }

    protected function _getManifestConsignments(ManifestBuilder_TnT_CarrierAccount $carrierService, ManifestBuilder_TnT_Manifest $manifest) {
        return $this->_getConsignmentManager()->getConsignments($carrierService, $manifest);
    }

    protected function _getConsignmentManager() {
        if (empty($this->_consignmentManager)) {
            $this->_consignmentManager = new ManifestBuilder_TnT_ConsignmentManager();
        }

        return $this->_consignmentManager;
    }
}
