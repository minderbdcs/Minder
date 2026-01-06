<?php

class ManifestBuilder_AustPost_Model_MerchantLocation {
    /**
     * @var string
     */
    protected $_locationId;

    /**
     * @var ManifestBuilder_Model_Carrier
     */
    protected $_carrier;

    /**
     * @var ManifestBuilder_Model_CarrierService[]|Zend_Db_Table_Rowset_Abstract
     */
    protected $carrierServices;

    /**
     * @param string $locationId
     * @param ManifestBuilder_Model_Carrier $carrier
     * @param ManifestBuilder_Model_CarrierService[]|Zend_Db_Table_Rowset_Abstract $carrierServices
     */
    function __construct($locationId, ManifestBuilder_Model_Carrier $carrier, $carrierServices)
    {
        $this->_setLocationId($locationId);
        $this->_setCarrier($carrier);
        $this->_setCarrierServices($carrierServices);
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->_locationId;
    }

    /**
     * @param string $locationId
     * @return $this
     */
    protected function _setLocationId($locationId)
    {
        $this->_locationId = $locationId;
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
     * @return ManifestBuilder_Model_CarrierService[]|Zend_Db_Table_Rowset_Abstract
     */
    public function getCarrierServices()
    {
        return $this->carrierServices;
    }

    /**
     * @param ManifestBuilder_Model_CarrierService[]|Zend_Db_Table_Rowset_Abstract $carrierServices
     * @return $this
     */
    protected function _setCarrierServices($carrierServices)
    {
        $this->carrierServices = $carrierServices;
        return $this;
    }

}