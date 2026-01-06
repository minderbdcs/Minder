<?php

class ManifestBuilder_CourierP_FormatterXml implements ManifestBuilder_CourierP_FormatterInterface {
    /**
     * @var ManifestBuilder_DOMDocument
     */
    protected $_document;

    /**
     * @var DOMElement
     */
    protected $_manifestNode;

    /**
     * @var Zend_Date
     */
    protected $_currentDate;

    protected $_fileName;

    /**
     * @var ManifestBuilder_Model_Company
     */
    protected $_currentCompany;

    public function init(Zend_Date $currentDate, $fileName, ManifestBuilder_Model_Company $currentCompany) {
        $this->setDocument(new ManifestBuilder_DOMDocument('1.0', 'utf-8'));
        $this->setManifestNode($this->getDocument()->createElement('CPPLPODManifest'));
        $this->setCurrentDate($currentDate);
        $this->setFileName($fileName);
        $this->setCurrentCompany($currentCompany);
    }

    public function addConnote(ManifestBuilder_Model_Consignment $consignment, ManifestBuilder_Model_DeliveryAddress $deliveryAddress, $containsDangerousGoods) {
        $connoteNode = $this->getDocument()->createElement('CONSIGNMENT');

        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECORDTYPE', 'C'));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('CONSIGNMENTNUMBER', trim($consignment->AWB_CONSIGNMENT_NO)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('CONSIGNMENTDATE', $consignment->getCreateDate()->toString('y-MM-dd')));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('MANIFESTFILENAME', $this->getFileName()));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('MANIFESTFILEDATE', $this->getCurrentDate()->toString('y-MM-dd')));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SERVICECODE', trim($consignment->SERVICE_CHARGE_CODES)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('ACCOUNTNUMBER', trim($consignment->getAccountNumber())));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERNAME', trim($this->getCurrentCompany()->PERSON_ID)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERADDRESS1', substr($this->getCurrentCompany()->getAddress(), 0, 40)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERADDRESS2', substr($this->getCurrentCompany()->getAddress(), 4, 80)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERLOCATION', $this->getCurrentCompany()->CITY));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERSTATE', $this->getCurrentCompany()->STATE));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('SENDERPOSTCODE', $this->getCurrentCompany()->POST_CODE));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERNAME', $deliveryAddress->getName()));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERADDRESS1', substr($deliveryAddress->getAddress(), 0, 40)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERADDRESS2', substr($deliveryAddress->getAddress(), 40, 80)));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERLOCATION', trim($deliveryAddress->getSuburb())));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERSTATE', trim($deliveryAddress->getState())));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('RECEIVERPOSTCODE', trim($deliveryAddress->getPostCode())));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('PRIMARYREFERENCE', $consignment->PO_OTHER1));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('OTHERREFERENCE1', $consignment->PICK_ORDER));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('TOTALLOGISTICSUNITS', $consignment->getTotalLogisticUnits()));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('TOTALDEADWEIGHT', $consignment->PICKD_WT_ACTUAL));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('TOTALVOLUME', $consignment->PICKD_VOL_ACTUAL));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('TESTFLAG', '0'));
        $connoteNode->appendChild($this->getDocument()->createElementWithTextNode('DANGEROUSGOODSFLAG', ($containsDangerousGoods) ? '1' : '0'));

        $connote = new ManifestBuilder_CourierP_FormatterXml_Connote($connoteNode, $this->getDocument());
        $this->getManifestNode()->appendChild($connoteNode);
        return $connote;
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
     * @return ManifestBuilder_CourierP_FormatterXml
     */
    public function setDocument(ManifestBuilder_DOMDocument $document)
    {
        $this->_document = $document;
        return $this;
    }

    /**
     * @return DOMElement
     */
    public function getManifestNode()
    {
        return $this->_manifestNode;
    }

    /**
     * @param DOMElement $manifestNode
     * @return ManifestBuilder_CourierP_FormatterXml
     */
    public function setManifestNode(DOMElement $manifestNode)
    {
        $this->_manifestNode = $manifestNode;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->getDocument()->appendChild($this->getManifestNode());
        return $this->getDocument()->saveXML();
    }

    public function getCurrentDate()
    {
        return $this->_currentDate;
    }

    /**
     * @param Zend_Date  $currentDate
     * @return ManifestBuilder_CourierP_FormatterXml
     */
    public function setCurrentDate($currentDate)
    {
      
            $config = Zend_Registry::get('config');

            $timezone = $config->date->timezone;

            $currentdate->setTimezone($timezone);


	
        $this->_currentDate = $currentDate;
        return $this;
    }

    public function getFileName()
    {
        return $this->_fileName;
    }

    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        return $this;
    }

    public function getCurrentCompany()
    {
        return $this->_currentCompany;
    }

    public function setCurrentCompany(ManifestBuilder_Model_Company $currentCompany)
    {
        $this->_currentCompany = $currentCompany;
        return $this;
    }
}
