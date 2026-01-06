<?php

class ManifestBuilder_TnT_CarrierAccountManager implements ManifestBuilder_DbAdapterAwareInterface {
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

    function __construct(ManifestBuilder_Provider_Carrier $carrierProvider, ManifestBuilder_Table_CarrierService $carrierServiceTable, Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->setDbAdapter($dbAdapter);
        $this->_setCarrierProvider($carrierProvider);
        $this->_setCarrierServiceTable($carrierServiceTable);
    }


    public function getAccounts(array $carrierIds, $manifestId) {
        $result = array();

        foreach ($this->_getCarrierAccountsUsed($carrierIds, $manifestId) as $dataRow) {
            $carrier   = $this->_getCarrier($dataRow['CARRIER_ID']);
            $carrierServices = $this->_getCarrierServices($carrier, $dataRow['ACCOUNT']);
            $result[] = new ManifestBuilder_TnT_CarrierAccount($dataRow['ACCOUNT'], $carrier, $carrierServices);
        }

        return $result;
    }

    protected function _getCarrierAccountsUsed(array $carrierIds, $manifestId) {
        $select = $this->getDbAdapter()->select();

        $select->from('PICK_DESPATCH', array('CARRIER_ID' => 'PICKD_CARRIER_ID'))->distinct();
        $select->joinLeft('CARRIER', 'PICK_DESPATCH.PICKD_CARRIER_ID = CARRIER.CARRIER_ID', array());
        $select->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array(
            'ACCOUNT' => 'COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT)'
        ));

        if (count($carrierIds) > 0) {
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carrierIds) . "')");
        }

        if (is_null($manifestId)) {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        } else {
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);
        }

        return $select->query()->fetchAll();
    }

    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_DbAdapterAwareInterface
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    private function _getCarrier($carrierId)
    {
        return $this->_getCarrierProvider()->getCarrier($carrierId);
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
    protected function _setCarrierProvider(ManifestBuilder_Provider_Carrier $carrierProvider)
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

    private function _getCarrierServices($carrier, $account)
    {
        return $this->_getCarrierServiceTable()->getByCarrierAndAccount($carrier, $account);
    }
}
