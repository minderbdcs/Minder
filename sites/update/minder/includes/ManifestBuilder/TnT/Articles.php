<?php

class ManifestBuilder_TnT_Articles {
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

    /**
     * @var ManifestBuilder_TnT_Model_Product
     */
    protected $_product;

    public function toXmlNode(ManifestBuilder_DOMDocument $dom) {
        $_product = new ManifestBuilder_TnT_Table_ProdProfile();
        // get prod desc
        $myProdDesc = "";
	//$myProductsSet = $_product->_getDespatchedProducts($this->getPackId());
//var_dump($myProductsSet);
/*
*/
        //$myProdDesc = $myProductsSet->getGoodsDescription();
        if (is_null($myProdDesc)) {
                $myProdDesc = $this->_getUnitType($this->getPackId());
        }
            
        
        $result = $dom->createElement('PACKAGE');

        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
                'ITEMS' => $this->_pickDespatch->getTotalLogisticUnits(),
                //'DESCRIPTION' => $this->_getUnitType($this->getPackId()),
                //'DESCRIPTION' => $myProdDesc,
                // 'DESCRIPTION' => $this->_product->getGoodsDescription($this->getPackId()->PACK_ID),
                // 'DESCRIPTION' => $this->_product->getGoodsDescription(),
                'DESCRIPTION' => $_product->_getDespatchedProducts($this->getPackId())->getGoodsDescription(),

                'LENGTH' => $this->getPackId()->DIMENSION_Z, /* must convert to be in meters x.y not cm */
                'HEIGHT' => $this->getPackId()->DIMENSION_Y, /* must be in meters x.y not cm */
                'WIDTH' => $this->getPackId()->DIMENSION_X, /* must be in meters x.y not cm */
                'WEIGHT' => $this->getPackId()->PACK_WEIGHT, /* must be kg's */
/*
                'RECORD_TYPE' => 'I',
                'CONSIGNMENTNUMBER' => trim($this->getPickDespatch()->AWB_CONSIGNMENT_NO),
                'ACCOUNTNUMBER' => $this->getCarrierService()->SERVICE_ACCOUNT,
                'LABELNUMBER' => $this->getPackId()->DESPATCH_LABEL_NO,
                'UNITTYPE' => $this->_getUnitType($this->getPackId()),
                'LABELTESTFLAG' => '0',
                'INTERNALLABEL' => '0'
*/
            )),
            $result
        );

        return $result;
    }

    protected function _getUnitType(ManifestBuilder_Model_PackId $packId) {
        switch ($packId->PACK_TYPE) {
            case 'C':
                return 'Carton';
            case 'P':
                return 'Pallet';
            case 'S':
                return 'Satchel';
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
