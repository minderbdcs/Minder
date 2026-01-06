<?php

class ManifestBuilder_AustPost_FormatterXml implements ManifestBuilder_AustPost_FormatterInterface {
    /**
     * @var ManifestBuilder_DOMDocument
     */
    protected $_document;

    protected $_node;

    /**
     * @param ManifestBuilder_AustPost_Model_ManifestHeader $manifestHeader
     */
    public function addHeader(ManifestBuilder_AustPost_Model_ManifestHeader $manifestHeader)
    {
        $headerNode = $this->getDocument()->createElement('header');

        $headerNode->appendChild($this->getDocument()->createElementWithTextNode('TransactionDateTime', $manifestHeader->transactionDateTime));
        $headerNode->appendChild($this->getDocument()->createElementWithTextNode('TransactionId', $manifestHeader->transactionId));
        $headerNode->appendChild($this->getDocument()->createElementWithTextNode('TransactionSequence', $manifestHeader->transactionSequence));
        $headerNode->appendChild($this->getDocument()->createElementWithTextNode('ApplicationId', $manifestHeader->applicationId));

        $this->getNode()->appendChild($headerNode);
    }

    /**
     * @param ManifestBuilder_AustPost_Model_PcmsManifest $pcmsManifest
     * @return ManifestBuilder_AustPost_FormatterXml_PcmsManifest
     */
    public function addPcmsManifest(ManifestBuilder_AustPost_Model_PcmsManifest $pcmsManifest) {
        $pcmsNode = $this->getDocument()->createElement('PCMSManifest');

        $pcmsNode->appendChild($this->getDocument()->createElementWithTextNode('MerchantLocationId', $pcmsManifest->merchantLocationId));
        $pcmsNode->appendChild($this->getDocument()->createElementWithTextNode('ManifestNumber', $pcmsManifest->manifestNumber));
        $pcmsNode->appendChild($this->getDocument()->createElementWithTextNode('DateSubmitted', $pcmsManifest->dateSubmitted));
        $pcmsNode->appendChild($this->getDocument()->createElementWithTextNode('DateLodged', $pcmsManifest->dateLodged));

        $body = $this->getDocument()->createElement('body');
        $body->appendChild($pcmsNode);

        $this->getNode()->appendChild($body);

        return new ManifestBuilder_AustPost_FormatterXml_PcmsManifest($pcmsNode, $this->getDocument());
    }

    /**
     * @return \ManifestBuilder_DOMDocument
     */
    public function getDocument()
    {
        if (empty($this->_document)) {
            $this->setDocument(new ManifestBuilder_DOMDocument('1.0', 'utf-8'));
        }

        return $this->_document;
    }

    /**
     * @param \ManifestBuilder_DOMDocument $document
     * @return \ManifestBuilder_AustPost_FormatterXml
     */
    public function setDocument($document)
    {
        $this->_document = $document;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getDocument()->saveXML();
    }

    public function getNode()
    {
        if (empty($this->_node)) {
            $pcms = $this->getDocument()->createElementNS('http://www.auspost.com.au/xml/pcms', 'PCMS');
            $sendPCMSManifest = $this->getDocument()->createElement('SendPCMSManifest');
            $pcms->appendChild($sendPCMSManifest);
            $this->getDocument()->appendChild($pcms);

            $this->_node = $sendPCMSManifest;
        }

        return $this->_node;
    }

    public function reset()
    {
        $this->_document = null;
        $this->_node = null;
    }
}