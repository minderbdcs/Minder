<?php

class ManifestBuilder_TnT_Item {
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


protected $_pickOrder;


    public function toXmlNode(ManifestBuilder_DOMDocument $dom) {
        $_product = new ManifestBuilder_TnT_Table_ProdProfile();
        // get prod desc
        $myProdDesc = "";
	$myProductsSet = $_product->_getDespatchedProducts($this->getPackId());
//var_dump($myProductsSet);exit;
/*
*/
        //$myProdDesc = $myProductsSet->getGoodsDescription();
        if (is_null($myProdDesc)) {
                $myProdDesc = $this->_getUnitType($this->getPackId());
        }
            
        
        $result = $dom->createElement('PACKAGE');





//print_r($_product);exit;

$length= ($this->getPackId()->DIMENSION_Z)/100;
$height = ($this->getPackId()->DIMENSION_Y)/100;
$width  = ($this->getPackId()->DIMENSION_X)/100;
$weight = ($this->getPackId()->PACK_WEIGHT)/1000;

$cubic  = ($this->getPackId()->CUBIC_WEIGHT)/1000;



        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(	
		//'LINESEQUENCE' => $this->getPackId()->PACK_SEQUENCE_NO,
		//'NUMOFITEMS' => $this->_pickDespatch->getTotalLogisticUnits(),
		'NUMOFITEMS' => $this->getPackId()->QTY,
                //'DESCRIPTION' => $this->_getUnitType($this->getPackId()),
                //'DESCRIPTION' => $myProdDesc,
                // 'DESCRIPTION' => $this->_product->getGoodsDescription($this->getPackId()->PACK_ID),
                // 'DESCRIPTION' => $this->_product->getGoodsDescription(),
                'DESCRIPTION' => $_product->_getDespatchedProducts($this->getPackId())->getGoodsDescription(),
		'LENGTH' => $length, /* must convert to be in meters x.y not cm */
		'HEIGHT' => $height, /* must be in meters x.y not cm */
		'WIDTH' => $width, /* must be in meters x.y not cm */
		'WEIGHT' => $weight, /* must be kg's */ 
                'HEIGHT' => $height, /* must be in meters x.y not cm */           

            )),
            $result
        );



	$resultArticles = $dom->createElement('ARTICLES');
	$dom->appendChildNodes(
            $dom->createNodesFromArray(array(
		'NUMOFITEMS' => $this->getPackId()->QTY,
                //'DESCRIPTION' => $this->_getUnitType($this->getPackId()),
                //'DESCRIPTION' => $myProdDesc,
                // 'DESCRIPTION' => $this->_product->getGoodsDescription($this->getPackId()->PACK_ID),
                // 'DESCRIPTION' => $this->_product->getGoodsDescription(),
                'DESCRIPTION' => $_product->_getDespatchedProducts($this->getPackId())->getGoodsDescription(),
		'LENGTH' => $length, /* must convert to be in meters x.y not cm */
		'HEIGHT' => $height, /* must be in meters x.y not cm */
		'WIDTH' => $width, /* must be in meters x.y not cm */
		'WEIGHT' => $weight, /* must be kg's */ 
                'HEIGHT' => $height, /* must be in meters x.y not cm */           

            )),
	 $resultArticles
        );

	 $result->appendChild($resultArticles);

	
        return $result;
    }



public function toXmlNodeItems(ManifestBuilder_DOMDocument $dom) {
        $_product = new ManifestBuilder_TnT_Table_ProdProfile();
        // get prod desc
        $myProdDesc = "";
	$myProductsSet = $_product->_getDespatchedProducts($this->getPackId());
//var_dump($myProductsSet);exit;
/*
*/
        //$myProdDesc = $myProductsSet->getGoodsDescription();
        if (is_null($myProdDesc)) {
                $myProdDesc = $this->_getUnitType($this->getPackId());
        }


//print_r($this->_pickDespatch);exit;
 /*$resultItems = $dom->appendChildNodes(
            $dom->createNodesFromArray(array(  
		'RECORDTYPE' => "H",
		'MANIFESTID' =>  ""
            )),
            $resultItems
        );*/

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







    /**
     * @return ManifestBuilder_Model_CarrierService
     */
    public function getPickOrder()
    {
        return $this->_pickOrder;
    }

    /**
     * @param ManifestBuilder_Model_CarrierService $carrierService
     * @return $this
     */
    public function setPickOrder(ManifestBuilder_Model_PickOrder $pickOrder)
    {
        $this->_pickOrder = $pickOrder;
        return $this;
    }

}
