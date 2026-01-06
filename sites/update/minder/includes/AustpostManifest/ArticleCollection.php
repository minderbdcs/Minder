<?php

class AustpostManifest_ArticleCollection implements Iterator {
    /**
     * @deprecated
     * @var string
     */
    protected $despatchId  = '';
    protected $consignmentNo = '';
    protected $rowsRange   = null;
    
    protected $currentRow = 0;
    protected $totalRows  = -1;
    /**
     * @var Zend_Db_Select
     */
    protected $dbSelect    = null;
    
    public function __construct($consignmentNo = '') {
        $this->setConsignmentNo($consignmentNo);
        
        $this->initDbSelect();
    }
    
    public function initDbSelect() {
        $this->dbSelect = new Zend_Db_Select(AustpostManifest::getInstance()->getDb());
        $this->dbSelect
                ->from('PACK_ID', array('PACK_ID', 'DESPATCH_LABEL_NO', 'DIMENSION_X', 'DIMENSION_Y', 'DIMENSION_Z', 'PACK_WEIGHT', 'DIMENSION_UOM', 'PACK_WEIGHT_UOM', 'PACK_SERIAL_NO', 'PACK_SEQUENCE_NO', 'PACK_LAST_SEQUENCE_INDICATOR'))
                ->join('PICK_DESPATCH', 'PACK_ID.DESPATCH_ID = PICK_DESPATCH.DESPATCH_ID', array())
                ->join('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_TRANSIT_COVER_REQD', 'SERVICE_TRANSIT_COVER_AMOUNT', 'SERVICE_LOCATION_ID', 'SERVICE_SERVICE_CODE'))
                ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?');
    }

    /**
     * @deprecated
     * @param  $despatchId
     * @return void
     */
    public function setDespatchId($despatchId) {
        $this->despatchId = $despatchId;
    }

    public function setConsignmentNo($consignmentNo) {
        $this->consignmentNo = $consignmentNo;
    }
    
    public function rewind() {
        $this->rowsRange  = $this->dbSelect->query(null, array($this->consignmentNo))->fetchAll();
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

    protected function _getArticleNumber($currentRow) {
        $rawNumber = $currentRow['SERVICE_LOCATION_ID']
               . $currentRow['PACK_SERIAL_NO']
               . $currentRow['PACK_SEQUENCE_NO']
               . $currentRow['SERVICE_SERVICE_CODE']
               . $currentRow['PACK_LAST_SEQUENCE_INDICATOR'];

        $rawNumber = trim($rawNumber);

        $articleNo = AustpostManifest::getInstance()->getDb()->fetchOne("SELECT out_data FROM CALC_CHECK_DIGIT_BDCS('" . $rawNumber . "', 'T')");

        return $articleNo;
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
     * @throws Exception
     * @param DOMDocument $xmlDoc
     * @return DOMElement
     */
    public function getXmlElement($xmlDoc) {
        if (!$this->valid())
            throw new Exception('Out of range');
        
        $pcmsDomesticArticle = $xmlDoc->createElement('PCMSDomesticArticle');
        $currentRow          = $this->rowsRange[$this->currentRow];
        
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ArticleNumber', $this->_getArticleNumber($currentRow) ));
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'BarcodeArticleNumber', trim($currentRow['DESPATCH_LABEL_NO']) ));

        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'Length', trim(sprintf("%d",$currentRow['DIMENSION_X'])) ));
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'Width', trim(sprintf("%d",$currentRow['DIMENSION_Y'])) ));
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'Height', trim(sprintf("%d",$currentRow['DIMENSION_Z'])) ));

        $totalWeight = round(floatval(trim($currentRow['PACK_WEIGHT'])), 2);
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ActualWeight', $totalWeight ));

        //$pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'IsTransitCoverRequired', (empty($currentRow['SERVICE_TRANSIT_COVER_REQD'])) ? 'N' : $currentRow['SERVICE_TRANSIT_COVER_REQD'] ));
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'IsTransitCoverRequired', ($currentRow['SERVICE_TRANSIT_COVER_REQD'] == 'T') ? 'Y' : 'N' ));
        $pcmsDomesticArticle->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'TransitCoverAmount', (empty($currentRow['SERVICE_TRANSIT_COVER_AMOUNT'])) ? 0 : $currentRow['SERVICE_TRANSIT_COVER_AMOUNT'] ));

        return $pcmsDomesticArticle;
    }
    
    public function getPackId() {
        if (!$this->valid())
            throw new AustpostManifest_ArticleCollection_Exception('Out of range');

        $currentRow          = $this->rowsRange[$this->currentRow];
        return $currentRow['PACK_ID'];
    }
}

class AustpostManifest_ArticleCollection_Exception extends AustpostManifest_Exception {}
