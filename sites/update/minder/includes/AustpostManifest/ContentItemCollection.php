<?php

class AustpostManifest_ContentItemCollection implements Iterator {
    protected $packId    = ''; 
    protected $rowsRange = null;
    
    protected $currentRow = 0;
    protected $totalRows  = -1;
    
    protected $dbSelect    = null;
    
    public function __construct($packId = '') {
        $this->setPackId($packId)
                ->initDbSelect();
    }
    
    /**
    * Creates DB_SELECT object to use later
    * 
    * @return AustpostManifest_ContentItemCollection
    */
    public function initDbSelect() {
        //TODO: now cannot trace whick PICK_ITEM belongs to which PACK_ID
        
        $this->dbSelect = new Zend_Db_Select(AustpostManifest::getDb());
        
        $this->dbSelect->from('PACK_ID', array('DESPATCH_ID'))
                        ->joinLeft('PICK_ITEM_DETAIL', 'PACK_ID.PACK_ID = PICK_ITEM_DETAIL.PACK_ID', array('PICK_LABEL_NO', 'QTY_PICKED', 'PICK_DETAIL_STATUS'))
                        ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', 'SSN_ID')
                        ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID', 'SHORT_DESC'))
                        ->where('PACK_ID.PACK_ID = ?');
        
        return $this;
    }
    
    /**
    * Set PACK_ID for search
    * 
    * @param mixed $packId
    * @return AustpostManifest_ContentItemCollection
    */
    public function setPackId($packId) {
        $this->packId = $packId;
        return $this;
    }
    
    public function rewind() {
        $this->rowsRange = $this->dbSelect->query(null, array($this->packId))->fetchAll();
        
        $this->totalRows  = count($this->rowsRange);
        $this->currentRow = 0;
    }
    
    public function current() {
        return $this;
    }
    
    public function key() {
        return $this->currentRow;
    }
    
    public function next() {
        $this->currentRow++;
    }
    
    public function valid() {
        return ($this->currentRow < $this->totalRows);
    }

    /**
     * @param DOMDocument $xmlDoc
     * @param string $name
     * @param string $value
     * @return DOMElement
     */
    protected function _createDomElementWithTextValue($xmlDoc, $name, $value) {
        $element = $xmlDoc->createElement($name);
        $element->appendChild($xmlDoc->createTextNode(iconv('UTF-8', 'UTF-8//IGNORE', $value)));
        return $element;
    }

    /**
     * @throws AustpostManifest_ContentItemCollection_Exception
     * @param DOMDocument $xmlDoc
     * @return DOMElement
     */
    public function getXmlElement($xmlDoc) {
        if (!$this->valid())
            throw new AustpostManifest_ContentItemCollection_Exception('Out of range');
        
        $pcmsContentsItem = $xmlDoc->createElement('ContentsItem');
        $currentRow       = $this->rowsRange[$this->currentRow];
        
        if ((trim($currentRow['QTY_PICKED']) != "" ) and
            (trim($currentRow['QTY_PICKED'])  != "0") and 
            (trim($currentRow['PICK_DETAIL_STATUS'] )  != "CN") and 
            (trim($currentRow['PICK_DETAIL_STATUS'] )  != "XX"))  {

        	$pcmsContentsItem->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'GoodsDescription', trim($currentRow['SHORT_DESC'])));
//        $pcmsContentsItem->appendChild($xmlDoc->createElement('Weight', trim($currentRow['Weight'])));
        	$pcmsContentsItem->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'Quantity', trim($currentRow['QTY_PICKED'])));
//        $pcmsContentsItem->appendChild($xmlDoc->createElement('UnitValue', trim($currentRow['UnitValue'])));
//        $pcmsContentsItem->appendChild($xmlDoc->createElement('Value', trim($currentRow['Value'])));
	}
        
        return $pcmsContentsItem;
    }
}
class AustpostManifest_ContentItemCollection_Exception extends AustpostManifest_Exception {}
