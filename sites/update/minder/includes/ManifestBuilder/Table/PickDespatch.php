<?php

class ManifestBuilder_Table_PickDespatch extends Zend_Db_Table {
    protected $_name = 'PICK_DESPATCH';
    protected $_rowClass = 'ManifestBuilder_Model_PickDespatch';

    /**
     * @param $carriersList
     * @param null $manifestId
     * @return Zend_Db_Table_Rowset_Abstract|ManifestBuilder_Model_PickDespatch[]
     */
    public function getCarriersAndServicesForManifest($carriersList, $manifestId = null) {
        $select = $this->select()
            ->from($this, array('PICKD_CARRIER_ID', 'PICKD_SERVICE_TYPE'))
            ->distinct();
        $select->joinLeft('CARRIER', 'PICK_DESPATCH.PICKD_CARRIER_ID = CARRIER.CARRIER_ID', array());
        $select->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array(
            'ACCOUNT' => 'COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT)'
        ));

        if (count($carriersList) > 0)
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carriersList) . "')");

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        return $this->fetchAll($select);
    }

    /**
     * @param ManifestBuilder_CourierP_CarrierAccount $carrierAccount
     * @param null $manifestId
     * @return Zend_Db_Table_Rowset_Abstract|ManifestBuilder_Model_PickDespatch[]
     */
    public function getDespatches($carrierAccount, $manifestId = null) {
        $carrierServices = Minder_ArrayUtils::mapField($carrierAccount->getCarrierServices()->toArray(), 'RECORD_ID');

        $select = $this->select(static::SELECT_WITH_FROM_PART)->distinct()
            ->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array())
            ->joinLeft('CARRIER', 'PICK_DESPATCH.PICKD_CARRIER_ID = CARRIER.CARRIER_ID', array())
            ->where('PICK_DESPATCH.PICKD_CARRIER_ID = ?', $carrierAccount->getCarrier()->CARRIER_ID)
            ->where('PICK_DESPATCH.PICKD_SERVICE_RECORD_ID IN (' . $this->getAdapter()->quote($carrierServices) . ')')
            ->where("PICK_DESPATCH.DESPATCH_STATUS IN ('DC', 'DX')");

        if (is_null($manifestId)) {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        } else {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);
        }

        return $this->fetchAll($select);
    }
}