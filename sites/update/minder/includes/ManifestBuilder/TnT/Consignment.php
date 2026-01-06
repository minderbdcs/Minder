<?php

/**
 * Class ManifestBuilder_TnT_Consignment
 */
class ManifestBuilder_TnT_Consignment {
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
     * @var ManifestBuilder_TnT_Manifest
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

//Added by akhil
protected $_articles;


    public function toXmlNodeSender(ManifestBuilder_DOMDocument $dom) {
        $resultSender = $dom->createElement('SENDER');
        $resultCollection = $dom->createElement('COLLECTION');
        $pickOrder = $this->getPickOrder();
        $pickDespatch = $this->getPickDespatch();
        $manifest = $this->getManifest();


        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
                'SHIPDATE' => $manifest->getDate()->toString('dd-MM-Y'),
            )),
            $resultCollection
        );

 $carrier = $this->getCarrierService();
 $_person = new ManifestBuilder_TnT_Table_Person();
 $_person_details = $_person->_getSentFromPerson($carrier);


 //var_dump($_person_details->FIRST_NAME);exit;

        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
		//'RECORDTYPE' => "B",
		//'MANIFESTID' => $manifest->getManifestId(),
		
                'COMPANYNAME' => trim($_person_details->FIRST_NAME), /* is the company name from tnt doing the sending */
                'STREETADDRESS1' => trim(substr($pickDespatch->SENT_FROM_ADDRESS_1, 0, 40)),
                'STREETADDRESS2' => trim(substr($pickDespatch->SENT_FROM_ADDRESS_2, 0, 80)),		
                'CITY' => trim($pickDespatch->SENT_FROM_SUBURB),
                'PROVINCE' => trim($pickDespatch->SENT_FROM_STATE),
                'POSTCODE' => trim($pickDespatch->SENT_FROM_POST_CODE),
                'COUNTRY' => trim($pickDespatch->SENT_FROM_COUNTRY), 
		'ACCOUNT' => trim($this->_getAccountNumber()), /* is the tnt account */			
		'VAT' => "",
		'CONTACTNAME' => trim($pickDespatch->SENT_FROM_FIRST_NAME),               
                'CONTACTDIALCODE' => '',               
                'CONTACTTELEPHONE' => trim($pickDespatch->SENT_FROM_PHONE_NO),
                'CONTACTEMAIL' => trim($pickDespatch->SENT_FROM_EMAIL),
            )),
            $resultSender
        );

	// add in COLLECTION
        $resultSender->appendChild($resultCollection);

        return $resultSender;
    }

    public function convertToTxt() {
        $pickOrder = $this->getPickOrder();
        $pickDespatch = $this->getPickDespatch();
        $manifest = $this->getManifest();

    $total_weight  = ($pickDespatch->PICKD_WT_ACTUAL)/1000;  //in kg
    $total_volume  = ($pickDespatch->PICKD_VOL_ACTUAL)/1000; //        


$consign_arr=array(               
                'COMPANYNAME' => trim($pickOrder->CONTACT_NAME), 
                'STREETADDRESS1' => substr($pickOrder->getReceiverAddress(), 0, 40),
                'STREETADDRESS2' => substr($pickOrder->getReceiverAddress(), 40, 80),
                'CITY' => trim($pickOrder->getReceiverSuburb()),
                'PROVINCE' => trim($pickOrder->getReceiverState()),
                'POSTCODE' => trim($pickOrder->getReceiverPostCode()),
                'COUNTRY' => trim($pickOrder->getReceiverCountry()),            
                'VAT'    => '',
                'CONTACTNAME' => trim($pickOrder->getReceiverName()),
                'CONTACTDIALCODE' => '',
                'CONTACTTELEPHONE' => $pickOrder->OTHER5,
                'CONTACTEMAIL' => $pickOrder->OTHER9,
                'CONNUMBER' => trim($pickDespatch->AWB_CONSIGNMENT_NO),
                'CUSTOMERREF' => $pickOrder->PICK_ORDER,
                'CONTYPE' => 'N',
                'PAYMENTIND' => 'S',
                'ITEMS' => $pickDespatch->getTotalLogisticUnits(),
                'TOTALWEIGHT' => $total_weight,
                'TOTALVOLUME' => $total_volume,
                'SERVICE' => trim($this->getCarrierService()->SERVICE_CHARGE_CODES),
                'CURRENCY' => '',
                'GOODSVALUE' => '',         
                'INSURANCEVALUE' => '',     
                'INSURANCECURRENCY' => '', 
                'SERVICE' => '701',            
                'OPTION' => '',            
                'DESCRIPTION' => '',        
                'DELIVERYINST' => ''
        );
    

        return $consign_arr;
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
     * @return ManifestBuilder_TnT_Item[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param ManifestBuilder_TnT_Item[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        return $this;
    }

    /**
     * @return ManifestBuilder_TnT_Manifest
     */
    public function getManifest()
    {
        return $this->_manifest;
    }

    /**
     * @param ManifestBuilder_TnT_Manifest $manifest
     * @return $this
     */
    public function setManifest(ManifestBuilder_TnT_Manifest $manifest)
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
