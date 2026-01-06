<?php

/**
 * Class ManifestBuilder_CourierP_Consignment
 */
class ManifestBuilder_CourierP_Consignment {
    /**
     * @var ManifestBuilder_Model_PickDespatch
     */
    protected $_pickDespatch;

    /**
     * @var ManifestBuilder_Model_PickOrder
     */
    protected $_pickOrder;

    protected $_containsDangerousGoods = false;

    /**
     * @var ManifestBuilder_CourierP_Manifest
     */
    protected $_manifest;

    /**
     * @var ManifestBuilder_Model_CarrierService
     */
    protected $_carrierService;

    /**
     * @var ManifestBuilder_Model_CarrierDepot
     */
    protected $_carrierDepot;

    protected $_items;

    public function toXmlNode(ManifestBuilder_DOMDocument $dom) {
        $result = $dom->createElement('CONSIGNMENT');
        $pickOrder = $this->getPickOrder();
        $pickDespatch = $this->getPickDespatch();
        $manifest = $this->getManifest();
        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
                'RECORDTYPE' => 'C',
                'CONSIGNMENTNUMBER' => trim($pickDespatch->AWB_CONSIGNMENT_NO),
                'CONSIGNMENTDATE' => $pickDespatch->getCreateDate()->toString('y-MM-dd'),
                'MANIFESTFILENAME' => $manifest->getFileName(),
                'MANIFESTFILEDATE' => $manifest->getDate()->toString('y-MM-dd'),
                'SERVICECODE' => trim($this->getCarrierService()->SERVICE_CHARGE_CODES),
                'ACCOUNTNUMBER' => trim($this->_getAccountNumber()),
                'SENDERNAME' => trim(substr($this->_getSenderName(), 0, 40)),
                'SENDERADDRESS1' => trim(substr($pickDespatch->SENT_FROM_ADDRESS_1, 0, 40)),
                'SENDERADDRESS2' => trim(substr($pickDespatch->SENT_FROM_ADDRESS_2, 0, 80)),
                'SENDERLOCATION' => trim($pickDespatch->SENT_FROM_SUBURB),
                'SENDERSTATE' => trim($pickDespatch->SENT_FROM_STATE),
                'SENDERPOSTCODE' => trim($pickDespatch->SENT_FROM_POST_CODE),
                'RECEIVERNAME' => $pickOrder->getReceiverName(),
                'RECEIVERADDRESS1' => substr($pickOrder->getReceiverAddress(), 0, 40),
                'RECEIVERADDRESS2' => substr($pickOrder->getReceiverAddress(), 40, 80),
                'RECEIVERLOCATION' => trim($pickOrder->getReceiverSuburb()),
                'RECEIVERSTATE' => trim($pickOrder->getReceiverState()),
                'RECEIVERPOSTCODE' => trim($pickOrder->getReceiverPostCode()),
                'PRIMARYREFERENCE' => $pickOrder->OTHER1,
                'OTHERREFERENCE1' => $pickOrder->PICK_ORDER,
                'EMAILADDRESS' => $pickOrder->OTHER9,
                'PHONENUMBER' => $pickOrder->OTHER5,
                'OTHERREFERENCE2' => $pickOrder->OTHER6,
                'OTHERREFERENCE3' => $pickOrder->OTHER7,
                'TOTALLOGISTICSUNITS' => $pickDespatch->getTotalLogisticUnits(),
                'TOTALDEADWEIGHT' => $pickDespatch->PICKD_WT_ACTUAL,
                'TOTALVOLUME' => $pickDespatch->PICKD_VOL_ACTUAL,
                'TESTFLAG' => '0',
                'DANGEROUSGOODSFLAG' => ($this->_containsDangerousGoods ? '0' : '1'),
            )),
            $result
        );

        foreach ($this->getItems() as $item) {
            $result->appendChild($item->toXmlNode($dom));
        }

        return $result;
    }

    protected function _getAccountNumber() {
        return empty($this->getCarrierDepot()->CD_SERVICE_ACCOUNT) ? $this->getCarrierService()->SERVICE_ACCOUNT : $this->getCarrierDepot()->CD_SERVICE_ACCOUNT;
    }

    protected function _getSenderName() {
        return empty($this->getCarrierDepot()->CD_DESCRIPTION) ? $this->getPickOrder()->COMPANY_ID : $this->getCarrierDepot()->CD_DESCRIPTION;
    }

    /**
     * @return ManifestBuilder_Model_PickDespatch
     */
    public function getPickDespatch()
    {
        return $this->_pickDespatch;
    }

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return $this
     */
    public function setPickDespatch(ManifestBuilder_Model_PickDespatch $pickDespatch)
    {
        $this->_pickDespatch = $pickDespatch;
        return $this;
    }

    /**
     * @return ManifestBuilder_Model_PickOrder
     */
    public function getPickOrder()
    {
        return $this->_pickOrder;
    }

    /**
     * @param ManifestBuilder_Model_PickOrder $pickOrder
     * @return $this
     */
    public function setPickOrder($pickOrder)
    {
        $this->_pickOrder = $pickOrder;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getContainsDangerousGoods()
    {
        return $this->_containsDangerousGoods;
    }

    /**
     * @param boolean $containsDangerousGoods
     * @return $this
     */
    public function setContainsDangerousGoods($containsDangerousGoods)
    {
        $this->_containsDangerousGoods = $containsDangerousGoods;
        return $this;
    }

    /**
     * @return ManifestBuilder_CourierP_Item[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param ManifestBuilder_CourierP_Item[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        return $this;
    }

    /**
     * @return ManifestBuilder_CourierP_Manifest
     */
    public function getManifest()
    {
        return $this->_manifest;
    }

    /**
     * @param ManifestBuilder_CourierP_Manifest $manifest
     * @return $this
     */
    public function setManifest(ManifestBuilder_CourierP_Manifest $manifest)
    {
        $this->_manifest = $manifest;
        return $this;
    }

    /**
     * @return \ManifestBuilder_Model_CarrierService
     */
    public function getCarrierService()
    {
        return $this->_carrierService;
    }

    /**
     * @param \ManifestBuilder_Model_CarrierService $carrierService
     * @return $this
     */
    public function setCarrierService(ManifestBuilder_Model_CarrierService $carrierService)
    {
        $this->_carrierService = $carrierService;
        return $this;
    }

    /**
     * @return \ManifestBuilder_Model_CarrierDepot
     */
    public function getCarrierDepot()
    {
        return $this->_carrierDepot;
    }

    /**
     * @param \ManifestBuilder_Model_CarrierDepot $carrierDepot
     * @return $this
     */
    public function setCarrierDepot(ManifestBuilder_Model_CarrierDepot $carrierDepot)
    {
        $this->_carrierDepot = $carrierDepot;
        return $this;
    }
}
