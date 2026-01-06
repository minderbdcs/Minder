<?php

class ManifestBuilder_CourierP_Item {
    /**
     * @var ManifestBuilder_Model_PackId
     */
    protected $_packId;

    /**
     * @var ManifestBuilder_Model_CarrierService
     */
    protected $_carrierService;

    /**
     * @var ManifestBuilder_Model_PickDespatch
     */
    protected $_pickDespatch;

    public function toXmlNode(ManifestBuilder_DOMDocument $dom) {
        $result = $dom->createElement('ITEM');

        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
                'RECORD_TYPE' => 'I',
                'CONSIGNMENTNUMBER' => trim($this->getPickDespatch()->AWB_CONSIGNMENT_NO),
                'ACCOUNTNUMBER' => $this->getCarrierService()->SERVICE_ACCOUNT,
                'LABELNUMBER' => $this->getPackId()->DESPATCH_LABEL_NO,
                'UNITTYPE' => $this->_getUnitType($this->getPackId()),
                'LABELTESTFLAG' => '0',
                'INTERNALLABEL' => '0'
            )),
            $result
        );

        return $result;
    }

    protected function _getUnitType(ManifestBuilder_Model_PackId $packId) {
        switch ($packId->PACK_TYPE) {
            case 'C':
                return 'CTN';
            case 'P':
                return 'PAL';
            case 'S':
                return 'SAT';
            default:
                return '';
        }
    }

    /**
     * @return ManifestBuilder_Model_PackId
     */
    public function getPackId()
    {
        return $this->_packId;
    }

    /**
     * @param ManifestBuilder_Model_PackId $packId
     * @return $this
     */
    public function setPackId(ManifestBuilder_Model_PackId $packId)
    {
        $this->_packId = $packId;
        return $this;
    }

    /**
     * @return ManifestBuilder_Model_CarrierService
     */
    public function getCarrierService()
    {
        return $this->_carrierService;
    }

    /**
     * @param ManifestBuilder_Model_CarrierService $carrierService
     * @return $this
     */
    public function setCarrierService(ManifestBuilder_Model_CarrierService $carrierService)
    {
        $this->_carrierService = $carrierService;
        return $this;
    }

    /**
     * @return \ManifestBuilder_Model_PickDespatch
     */
    public function getPickDespatch()
    {
        return $this->_pickDespatch;
    }

    /**
     * @param \ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return $this
     */
    public function setPickDespatch(ManifestBuilder_Model_PickDespatch $pickDespatch)
    {
        $this->_pickDespatch = $pickDespatch;
        return $this;
    }

}