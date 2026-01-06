<?php

class ManifestBuilder_AustPost_FormatterXml_PartFormatter {
    /**
     * @var DOMElement
     */
    protected $_node;

    /**
     * @var ManifestBuilder_DOMDocument
     */
    protected $_document;

    function __construct(DOMElement $node, ManifestBuilder_DOMDocument $document)
    {
        $this->setNode($node)->setDocument($document);
    }

    /**
     * @return DOMElement
     */
    public function getNode()
    {
        return $this->_node;
    }

    /**
     * @param DOMElement $xmlNode
     * @return ManifestBuilder_AustPost_FormatterXml_PartFormatter
     */
    public function setNode(DOMElement $xmlNode)
    {
        $this->_node = $xmlNode;
        return $this;
    }

    /**
     * @return ManifestBuilder_DOMDocument
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * @param ManifestBuilder_DOMDocument $document
     * @return ManifestBuilder_AustPost_FormatterXml_PartFormatter
     */
    public function setDocument(ManifestBuilder_DOMDocument $document)
    {
        $this->_document = $document;
        return $this;
    }
}