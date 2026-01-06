<?php

class ManifestBuilder_AustPost_Provider_MerchantLocation implements ManifestBuilder_DbAdapterAwareInterface {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * @var ManifestBuilder_Provider_Carrier
     */
    protected $_carrierProvider;

    /**
     * @var ManifestBuilder_Table_CarrierService
     */
    protected $_carrierServiceTable;

    function __construct(
        Zend_Db_Adapter_Abstract $dbAdapter,
        ManifestBuilder_Provider_Carrier $carrierProvider,
        ManifestBuilder_Table_CarrierService $carrierServiceTable)
    {
        $this->setDbAdapter($dbAdapter);
        $this->_setCarrierProvider($carrierProvider);
        $this->_setCarrierServiceTable($carrierServiceTable);
    }


    public function getMerchantLocationsUsed(array $carriersList, $manifestId) {
        $result = array();

        foreach ($this->_fetchMerchantLocationsUsedForDespatch($carriersList, $manifestId) as $resultRow) {
            $merchantLocationId = $resultRow['SERVICE_LOCATION_ID'];
            $carrier = $this->_getCarrier($resultRow['CARRIER_ID']);
            $carrierServices = $this->_getCarrierServices($carrier, $merchantLocationId);

            $result[] = new ManifestBuilder_AustPost_Model_MerchantLocation($merchantLocationId, $carrier, $carrierServices);
        }

        return $result;
    }

    protected function _fetchMerchantLocationsUsedForDespatch(array $carriersList, $manifestId) {
        $select = $this->getDbAdapter()->select();

        $select->from('PICK_DESPATCH', array('CARRIER_ID' => 'PICKD_CARRIER_ID'))->distinct();
        $select->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_LOCATION_ID'));

        if (count($carriersList) > 0) {
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carriersList) . "')");
        }

        if (is_null($manifestId)) {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        } else {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);
        }

        return $select->query()->fetchAll();
    }

    private function _getCarrier($carrierId)
    {
        return $this->_getCarrierProvider()->getCarrier($carrierId);
    }

    private function _getCarrierServices($carrier, $merchantLocationId)
    {
        return $this->_getCarrierServiceTable()->getByCarrierAndMerchantLocation($carrier, $merchantLocationId);
    }

    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_DbAdapterAwareInterface
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * @return ManifestBuilder_Provider_Carrier
     */
    protected function _getCarrierProvider()
    {
        return $this->_carrierProvider;
    }

    /**
     * @param ManifestBuilder_Provider_Carrier $carrierProvider
     * @return $this
     */
    protected function _setCarrierProvider($carrierProvider)
    {
        $this->_carrierProvider = $carrierProvider;
        return $this;
    }

    /**
     * @return ManifestBuilder_Table_CarrierService
     */
    protected function _getCarrierServiceTable()
    {
        return $this->_carrierServiceTable;
    }

    /**
     * @param ManifestBuilder_Table_CarrierService $carrierServiceTable
     * @return $this
     */
    protected function _setCarrierServiceTable($carrierServiceTable)
    {
        $this->_carrierServiceTable = $carrierServiceTable;
        return $this;
    }
}