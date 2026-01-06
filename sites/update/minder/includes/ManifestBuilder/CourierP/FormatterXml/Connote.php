<?php

class ManifestBuilder_CourierP_FormatterXml_Connote implements ManifestBuilder_CourierP_ConnoteInterface {
    /**
     * @var ManifestBuilder_DOMDocument
     */
    protected $_document;

    /**
     * @var DOMElement
     */
    protected $_node;

    function __construct(DOMElement $node, ManifestBuilder_DOMDocument $document)
    {
        $this->setDocument($document)->setNode($node);
    }

    protected function _getUnitType(ManifestBuilder_Model_Item $item) {
        switch ($item->PACK_TYPE) {
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

    public function addItem(ManifestBuilder_Model_Item $item, ManifestBuilder_Model_Consignment $consignment) {
        $itemNode = $this->getDocument()->createElement('ITEM');

        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('RECORD_TYPE', 'I'));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('CONSIGNMENTNUMBER', trim($consignment->AWB_CONSIGNMENT_NO)));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('ACCOUNTNUMBER', trim($consignment->getAccountNumber())));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('LABELNUMBER', trim($item->DESPATCH_LABEL_NO)));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('UNITTYPE', $this->_getUnitType($item)));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('LABELTESTFLAG', '0'));
        $itemNode->appendChild($this->getDocument()->createElementWithTextNode('INTERNALLABEL', '0')); //todo

        $this->getNode()->appendChild($itemNode);
    }

    public function getDocument()
    {
        return $this->_document;
    }

    public function setDocument(ManifestBuilder_DOMDocument $document)
    {
        $this->_document = $document;
        return $this;
    }

    public function getNode()
    {
        return $this->_node;
    }

    public function setNode(DOMElement $node)
    {
        $this->_node = $node;
        return $this;
    }

}