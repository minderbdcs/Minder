<?php

class ManifestBuilder_CourierP_ManifestManager {
    protected $_consignmentManager;

    public function getManifest(ManifestBuilder_CourierP_CarrierAccount $carrierService, $manifestId, Zend_Date $currentDate) {
        $result = new ManifestBuilder_CourierP_Manifest();
        $result->setManifestId($manifestId);
        $result->setConsignments($this->_getManifestConsignments($carrierService, $result));
        $result->setDate($currentDate);

        return $result;
    }

    protected function _getManifestConsignments(ManifestBuilder_CourierP_CarrierAccount $carrierService, ManifestBuilder_CourierP_Manifest $manifest) {
        return $this->_getConsignmentManager()->getConsignments($carrierService, $manifest);
    }

    protected function _getConsignmentManager() {
        if (empty($this->_consignmentManager)) {
            $this->_consignmentManager = new ManifestBuilder_CourierP_ConsignmentManager();
        }

        return $this->_consignmentManager;
    }
}
