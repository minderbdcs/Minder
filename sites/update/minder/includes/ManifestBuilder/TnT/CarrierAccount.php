<?php

class ManifestBuilder_TnT_CarrierAccount {
    protected $_carrierServices;

    protected $_accountNo;

    protected $_carrier;

    function __construct($accountNo, $carrier, $services)
    {
        $this->_setAccountNo($accountNo);
        $this->_setCarrierServices($services);
        $this->_setCarrier($carrier);
    }

    /**
     * @return Zend_Db_Table_Rowset_Abstract|ManifestBuilder_Model_CarrierService[]
     */
    public function getCarrierServices()
    {
        return $this->_carrierServices;
    }

    /**
     * @param Zend_Db_Table_Rowset_Abstract $carrierServices
     * @return $this
     */
    protected function _setCarrierServices($carrierServices)
    {
        $this->_carrierServices = $carrierServices;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNo()
    {
        return $this->_accountNo;
    }

    /**
     * @param string $accountNo
     * @return $this
     */
    protected function _setAccountNo($accountNo)
    {
        $this->_accountNo = $accountNo;
        return $this;
    }

    /**
     * @return ManifestBuilder_Model_Carrier
     */
    public function getCarrier()
    {
        return $this->_carrier;
    }

    /**
     * @param ManifestBuilder_Model_Carrier $carrier
     * @return $this
     */
    protected function _setCarrier($carrier)
    {
        $this->_carrier = $carrier;
        return $this;
    }

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_Model_CarrierService
     * @throws Exception
     */
    public function findCarrierService(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        foreach ($this->getCarrierServices() as $carrierService) {
            if ($carrierService->RECORD_ID === $pickDespatch->PICKD_SERVICE_RECORD_ID) {
                return $carrierService;
            }
        }

        throw new Exception('Carrier Service not found.');
    }
}
