<?php

class AustpostManifest_ConsignmentCollection implements Iterator {
    protected $carrierId   = '';
    protected $serviceType = '';
    protected $manifestId  = '';
    protected $atDate      = '';
    protected $rowsRange   = null;
    
    protected $currentRow = 0;
    protected $totalRows  = -1;

    /**
     * @var Zend_Db_Select
     */
    protected $dbSelect    = null;
    
    public function __construct($carrierId, $serviceType, $atDate, $manifestId) {
        $this->carrierId = $carrierId;
        $this->serviceType = $serviceType;
        $this->atDate = $atDate;
        $this->manifestId = $manifestId;
        
        $this->initDbSelect();
    }
    
    public function initDbSelect() {
        $this->dbSelect = new Zend_Db_Select(AustpostManifest::getInstance()->getDb());
        $this->dbSelect->distinct()
                        ->from('PICK_DESPATCH', array('AWB_CONSIGNMENT_NO'))
                        ->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_CHARGE_CODES', 'SERVICE_SIGNATURE_REQD', 'SERVICE_PARTIAL_DELIVERY'))
                        ->joinLeft('CARRIER', 'CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID', array('ACCOUNT'))
                        ->where('PICK_DESPATCH.PICKD_CARRIER_ID = ?', $this->carrierId)
                        ->where('PICK_DESPATCH.PICKD_SERVICE_TYPE = ?', $this->serviceType);

        if (is_null($this->manifestId))
            $this->dbSelect->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $this->dbSelect->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $this->manifestId);
    }
    
    public function rewind() {
        $this->rowsRange  = $this->dbSelect->query()->fetchAll();
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
    

    protected function getDeliveryName($row) {
        $title     = (empty($row['D_FIRST_NAME'])) ? $row['P_TITLE']      : $row['D_TITLE'];
        $firstName = (empty($row['D_FIRST_NAME'])) ? $row['P_FIRST_NAME'] : $row['D_FIRST_NAME'];
        $lastName  = (empty($row['D_FIRST_NAME'])) ? $row['P_LAST_NAME']  : $row['D_LAST_NAME'];
        
        $deliveryName = empty($title) ? $firstName : $title . ' ' . $firstName . ' ' . $lastName;
        return $deliveryName;
    }
    
    protected function getDeliveryAddress($row) {
        $addressLine = (empty($row['D_FIRST_NAME'])) ? 
                            $row['P_ADDRESS_LINE1'] . $row['P_ADDRESS_LINE2'] . $row['P_ADDRESS_LINE3'] . $row['P_ADDRESS_LINE4'] . $row['P_ADDRESS_LINE5']
                            : $row['D_ADDRESS_LINE1'] . $row['D_ADDRESS_LINE2'] . $row['D_ADDRESS_LINE3'] . $row['D_ADDRESS_LINE4'] . $row['D_ADDRESS_LINE5'];
        return $addressLine;
    }
    
    protected function splitAddressLineForManifest($addressLine) {
        return array(
            substr($addressLine, 0, 40),
            substr($addressLine, 40, 60),
            substr($addressLine, 100, 40),
            substr($addressLine, 140, 40)
        );
    }
    
    protected function getDeliverySuburb($row) {
        return (empty($row['D_FIRST_NAME'])) ? $row['P_CITY']      : $row['D_CITY'];
        
    }

    protected function getDeliveryStateCode($row) {
        return (empty($row['D_FIRST_NAME'])) ? $row['P_STATE']      : $row['D_STATE'];
        
    }

    protected function getDeliveryPostCode($row) {
        return (empty($row['D_FIRST_NAME'])) ? $row['P_POST_CODE']      : $row['D_POST_CODE'];
        
    }

    protected function getDeliveryCountryCode($row) {
        $tmpDeliveryCountry = strtoupper(empty($row['D_COUNTRY']) ? $row['P_COUNTRY'] : $row['D_COUNTRY']);
        return (in_array($tmpDeliveryCountry, array('AU', 'AUSTRALIA', ''))) ? 'AU' : $tmpDeliveryCountry;
        
    }

    protected function _getIsInternationalDelivery($row) {
        if ($this->getDeliveryCountryCode($row) == 'AU')
            return 'false';

        return 'true';
    }

    protected function _getContainsDangerousGoods($row) {

        $dbSelect = new Zend_Db_Select(AustpostManifest::getInstance()->getDb());
        $dbSelect->from('PICK_DESPATCH', array())
                ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
                ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', array())
                ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID'))
                ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $row['AWB_CONSIGNMENT_NO'])
                ->where('PROD_PROFILE.PP_HAZARD_STATUS IS NOT NULL AND PROD_PROFILE.PP_HAZARD_STATUS <> ?', '')
                ->limit(1);

        if (count($dbSelect->query()->fetchAll()) > 0)
            return 'true';

        return 'false';
    }

    protected function _fillDespatchAddressDetails($row) {
        $dbSelect = new Zend_Db_Select(AustpostManifest::getInstance()->getDb());
        $dbSelect->from('PICK_DESPATCH', array())
                ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
                ->joinLeft('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array())
                ->joinLeft('PICK_ORDER', 'PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER')
                ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $row['AWB_CONSIGNMENT_NO'])
                ->limit(1);

        if (is_array($fetchedRow = $dbSelect->query()->fetch())) {
            $row = array_merge($row, $fetchedRow);
        }

        return $row;
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
     * @throws AustpostManifest_ConsignmentCollection_Exception
     * @param DOMDocument $xmlDoc
     * @return DOMElement
     */
    public function getXmlElement($xmlDoc) {
        if (!$this->valid())
            throw new AustpostManifest_ConsignmentCollection_Exception('Out of range');
        
        $pcmsConsignment = $xmlDoc->createElement('PCMSConsignment');
        $currentConnoteRow = $this->_fillDespatchAddressDetails($this->rowsRange[$this->currentRow]);
        
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ConsignmentNumber', trim($currentConnoteRow['AWB_CONSIGNMENT_NO']) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ChargeCode', trim($currentConnoteRow['SERVICE_CHARGE_CODES']) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryName', $this->getDeliveryName($currentConnoteRow) ));

        list($addressLine1, $addressLine2, $addressLine3, $addressLine4) = $this->splitAddressLineForManifest($this->getDeliveryAddress($currentConnoteRow));
        
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryAddressLine1', $addressLine1 ));
        if (!empty($addressLine2)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryAddressLine2', $addressLine2 ));
        if (!empty($addressLine3)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryAddressLine3', $addressLine3 ));
        if (!empty($addressLine4)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryAddressLine4', $addressLine4 ));

        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliverySuburb', $this->getDeliverySuburb($currentConnoteRow) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryStateCode', $this->getDeliveryStateCode($currentConnoteRow) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryPostcode', $this->getDeliveryPostCode($currentConnoteRow) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliveryCountryCode', $this->getDeliveryCountryCode($currentConnoteRow) ));

        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'IsInternationalDelivery', $this->_getIsInternationalDelivery($currentConnoteRow) ));

        $amInstance = AustpostManifest::getInstance();
        $companyId = $amInstance->getDefaultControl('COMPANY_ID');
        
        $db = $amInstance->getDb();
        $companyAddrSelect = new Zend_Db_Select($db);
        $companyAddrSelect->from('PERSON', array('ADDRESS_LINE1', 'ADDRESS_LINE2', 'ADDRESS_LINE3', 'ADDRESS_LINE4', 'ADDRESS_LINE5', 'CITY', 'COUNTRY', 'STATE', 'POST_CODE'))
                            ->where('COMPANY_ID = ?', $companyId);
                        
        $companyRow = $db->fetchRow($companyAddrSelect);
        
        if (empty($companyRow))
            throw new AustpostManifest_ConsignmentCollection_Exception('No PERSON records with COMPANY_ID = "' . $companyId . '" was found. Cannot get Return Address information.');
        
        list($addressLine1, $addressLine2, $addressLine3, $addressLine4) = $this->splitAddressLineForManifest($companyRow['ADDRESS_LINE1'] . $companyRow['ADDRESS_LINE2'] . $companyRow['ADDRESS_LINE3'] . $companyRow['ADDRESS_LINE4'] . $companyRow['ADDRESS_LINE5']);
        
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnAddressLine1', $addressLine1 ));
        if (!empty($addressLine2)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnAddressLine2', $addressLine2 ));
        if (!empty($addressLine3)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnAddressLine3', $addressLine3 ));
        if (!empty($addressLine4)) $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnAddressLine4', $addressLine4 ));

        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnSuburb', $companyRow['CITY'] ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnStateCode', $companyRow['STATE'] ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnPostcode', $companyRow['POST_CODE'] ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ReturnCountryCode', $companyRow['COUNTRY'] ));

        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'CreatedDateTime', AustpostManifest::getInstance()->formatDateTime($this->atDate) ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'PostChargeToAccount', $currentConnoteRow['ACCOUNT'] ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'IsSignatureRequired', ($currentConnoteRow['SERVICE_SIGNATURE_REQD'] == 'T') ? 'Y' : 'N' ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'CTCAmount', 0 ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'DeliverPartConsignment', ($currentConnoteRow['SERVICE_PARTIAL_DELIVERY'] == 'T') ? 'Y' : 'N' ));
        $pcmsConsignment->appendChild($this->_createDomElementWithTextValue($xmlDoc, 'ContainsDangerousGoods', $this->_getContainsDangerousGoods($currentConnoteRow) ));

        return $pcmsConsignment;
    }

    /**
     * @throws AustpostManifest_ConsignmentCollection_Exception
     * @return string
     */
    public function getConsignmentNo() {
        if (!$this->valid())
            throw new AustpostManifest_ConsignmentCollection_Exception('Out of range');

        $currentConnoteRow = $this->rowsRange[$this->currentRow];
        return $currentConnoteRow['AWB_CONSIGNMENT_NO'];
    }
}

class AustpostManifest_ConsignmentCollection_Exception extends Minder_Exception {}

