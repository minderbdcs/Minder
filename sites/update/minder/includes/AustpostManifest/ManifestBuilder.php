<?php

class AustpostManifest_ManifestBuilder {

    protected $builtManifests = array();
    /**
     * @deprecated
     * @var int
     */
    protected $atDate = 0;
    protected $manifestDir = '';

    protected $carriersList = array();
    protected $manifestId   = null;

    public function __construct() {
        
    }
    
    protected function buildCarriersList() {
        AustpostManifest::info('Searching for Carriers which need manifests ...');
        
        $despatches = new AustpostManifest_Db_Table_PickDespatch();
        
        $carriers = $despatches->getCarriersAndServicesForManifest($this->carriersList, $this->manifestId);
        foreach ($carriers as $carrier) {
            $this->builtManifests[$carrier['CARRIER_ID']][$carrier['SERVICE_TYPE']] = array();
        }
        
        AustpostManifest::info('... ' . count($carriers) . ' carriers found');
        AustpostManifest::debug($this->builtManifests, 'found CARRIERS_ID and SERVICE_TYPES to build manifests for: ');
    }
    
    protected function getNextManifestNumber($carrierId) {
        $db = AustpostManifest::getInstance()->getDb();
        $carrierData = $db->fetchRow("SELECT FTP_MANIFEST_GENERATOR, FTP_MANIFEST_START_NO, FTP_MANIFEST_END_NO, FTP_MANIFEST_PREFIX FROM CARRIER WHERE CARRIER_ID = ?", array($carrierId));
        $generator = $carrierData['FTP_MANIFEST_GENERATOR'];
        
        if (empty($generator))
            throw new Exception('Manifest generator is not defined for CARRIER "' . $carrierId . '"');
        
        //$nextManifestNumber = $db->fetchOne('SELECT GEN_ID(' . $generator . ', 1 ) FROM RDB$DATABASE');

        $sql            = 'SELECT GEN_ID(' . $generator  . ', 1 ) FROM RDB$DATABASE;';
        $nextManifestNumber = $db->fetchOne($sql);
	if (empty($carrierData['FTP_MANIFEST_START_NO']))
            $carrierData['FTP_MANIFEST_START_NO'] = 0;
	if (empty($carrierData['FTP_MANIFEST_END_NO']))
            $carrierData['FTP_MANIFEST_END_NO'] = 9999999;
        $wkDiff = 0;
        if ($carrierData['FTP_MANIFEST_START_NO'] > $nextManifestNumber)
            $wkDiff += $carrierData['FTP_MANIFEST_START_NO'] ;
        if ($carrierData['FTP_MANIFEST_END_NO'] < $nextManifestNumber )
            $wkDiff -= $carrierData['FTP_MANIFEST_END_NO'] ;
        if ($wkDiff != 0) {
            $sql            = 'SELECT GEN_ID(' . $generator . ', ' . $wkDiff .' ) FROM RDB$DATABASE;';
            $nextManifestNumber = $db->fetchOne($sql);
        }
        if (strlen($nextManifestNumber) < 7)
            $nextManifestNumber = str_repeat('0', 7 - strlen($nextManifestNumber)) . $nextManifestNumber;
	if (!empty($carrierData['FTP_MANIFEST_PREFIX']))
            $nextManifestNumber = $carrierData['FTP_MANIFEST_PREFIX'] . "-" . $nextManifestNumber;
        
        return $nextManifestNumber;
    }
    
    protected function getNextTransactionId($carrierId) {
        $db = AustpostManifest::getInstance()->getDb();
        $generator = $db->fetchOne("SELECT FTP_ID_GENERATOR FROM CARRIER WHERE CARRIER_ID = ?", array($carrierId));
        
        if (empty($generator))
            throw new Exception('FTP_ID_GENERATOR generator is not defined for CARRIER "' . $carrierId . '"');
        
        $nextTransactionId = $db->fetchOne('SELECT GEN_ID(' . $generator . ', 1 ) FROM RDB$DATABASE');
        
        return $nextTransactionId;
    }
    
    protected function getTransactionSequence($carrierId) {
        return 0; //always 0
    }
    
    protected function getApplicationId() {
        return 'MERCHANT';
    }
    
    /**
    * Get Ftp Username for given Carrier Id
    * 
    * @param string $carrierId
    * @return string
    */
    protected function getUserName($carrierId) {
        $db = AustpostManifest::getDb();
        $select = new Zend_Db_Select($db);
        $select->from('CARRIER', 'FTP_USER')
                ->where('CARRIER_ID = ?', $carrierId);
        
        return $db->fetchOne($select);
    }
    
    /**
    * Get Merchant Location ID for given Carrier ID and Service Type
    * 
    * @param string $carrierId
    * @param string $serviceType
    * @return string
    */
    protected function getLocationId($carrierId, $serviceType) {
        $db = AustpostManifest::getDb();
        $select = new Zend_Db_Select($db);
        $select->from('CARRIER_SERVICE', 'SERVICE_LOCATION_ID')
                ->where('CARRIER_ID = ?', $carrierId)
                ->where('SERVICE_TYPE = ?', $serviceType);
        
        return $db->fetchOne($select);
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
    * Build XML document with manifest data for given CarrierId and ServiceType
    * 
    * @param string $carrierId
    * @param string $serviceType
    * @return DOMDocument
    * 
    * @throws AustpostManifest_ManifestBuilder_NoManifestData_Exception
    */
    protected function getManifestXml($carrierId, $serviceType, $locationId, $manifestNumber, $userName) {

        $dateSubmitted      = $dateLodged = AustpostManifest::getInstance()->formatDateTime();
        
        $manifestXml = new DOMDocument('1.0', 'utf-8');
        
        $header = $manifestXml->createElement('header');
        $header->appendChild($this->_createDomElementWithTextValue($manifestXml, 'TransactionDateTime', AustpostManifest::getInstance()->formatDateTime() ));
        $header->appendChild($this->_createDomElementWithTextValue($manifestXml, 'TransactionId', $this->getNextTransactionId($carrierId) ));
        $header->appendChild($this->_createDomElementWithTextValue($manifestXml, 'TransactionSequence', $this->getTransactionSequence($carrierId) ));
        $header->appendChild($this->_createDomElementWithTextValue($manifestXml, 'ApplicationId', $this->getApplicationId() ));

        $sendPCMSManifest = $manifestXml->createElement('SendPCMSManifest');
        $sendPCMSManifest->appendChild($header);
        
        $pcmsManifest = $manifestXml->createElement('PCMSManifest');
        
        $pcmsManifest->appendChild($this->_createDomElementWithTextValue($manifestXml, 'MerchantLocationId', $locationId ));
        $pcmsManifest->appendChild($this->_createDomElementWithTextValue($manifestXml, 'ManifestNumber', $manifestNumber ));
        $pcmsManifest->appendChild($this->_createDomElementWithTextValue($manifestXml, 'DateSubmitted', $dateSubmitted ));
        $pcmsManifest->appendChild($this->_createDomElementWithTextValue($manifestXml, 'DateLodged', $dateLodged ));

        $consignmentCollection = new AustpostManifest_ConsignmentCollection($carrierId, $serviceType, microtime(true), $this->manifestId);
        $articleCollection     = new AustpostManifest_ArticleCollection();
        $contentItemCollection = new AustpostManifest_ContentItemCollection();

        /**
         * @var AustpostManifest_ConsignmentCollection $consignment
         */
        foreach ($consignmentCollection as $consignment) {
            $pcmsConsignment = $consignment->getXmlElement($manifestXml);
            
            $articleCollection->setConsignmentNo($consignment->getConsignmentNo());

            /**
             * @var AustpostManifest_ArticleCollection $article 
             */
            foreach ($articleCollection as $article) {
                $articleXml = $article->getXmlElement($manifestXml);
                $contentItemCollection->setPackId($article->getPackId());

                /**
                 * @var AustpostManifest_ContentItemCollection $contentItem
                 */
                foreach ($contentItemCollection as $contentItem) {
                    $articleXml->appendChild($contentItem->getXmlElement($manifestXml));
                }
                
                $pcmsConsignment->appendChild($articleXml);
            }
            
            $pcmsManifest->appendChild($pcmsConsignment);
        }
        
        $body = $manifestXml->createElement('body');
        $body->appendChild($pcmsManifest);
        
        $sendPCMSManifest->appendChild($body);

        $pcms = $manifestXml->createElementNS('http://www.auspost.com.au/xml/pcms', 'PCMS');
        $pcms->appendChild($sendPCMSManifest);
        $manifestXml->appendChild($pcms);
        
        return $manifestXml;
    }
    
    protected function buildManifestForCarrierAndType($carrierId, $serviceType) {
        AustpostManifest::getInstance()->info('Building manifest for "' . $carrierId . '", service type "' . $serviceType . '" .... ');
        $e = new Exception('Uncknonw error.');
        $wasErrors = false;

        try {
            $userName = $this->getUserName($carrierId);
            if (empty($userName)) {
                AustpostManifest::warning('Cannot find FTP Username for carrier "' . $carrierId . '". Aborting manifest building.');
                return;
            }
                
            $locationId = $this->getLocationId($carrierId, $serviceType);
            if (empty($locationId)) {
                AustpostManifest::warning('Cannot find Merchant Location ID for Carrier "' . $carrierId . '" and Service Type "' . $serviceType . '" combination. Aborting manifest building.');
                return;
            }
            
            //$manifestNumber = $this->getNextManifestNumber($carrierId);
            if (is_null($this->manifestId))
            {
            	$manifestNumber = $this->getNextManifestNumber($carrierId);
            } else {
            	$manifestNumber = $this->manifestId;
            }
          
            $manifestFileName = sprintf('%s_%s_%s.xml', $locationId, $manifestNumber, $userName);
            $manifestFileName = $this->manifestDir . DIRECTORY_SEPARATOR . $manifestFileName;
            
            $manifestXml = $this->getManifestXml($carrierId, $serviceType, $locationId, $manifestNumber, $userName);
            
            file_put_contents($manifestFileName, $manifestXml->saveXML());
        
            $this->builtManifests[$carrierId][$serviceType] = $manifestFileName;

        } catch(AustpostManifest_ManifestBuilder_ManifestGeneratorNotDefined_Exception $e) {
            $wasErrors = true;
        } catch(AustpostManifest_ManifestBuilder_NoManifestData_Exception $e) {
            $wasErrors = true;
        }
        
        if ($wasErrors) {
            AustpostManifest::warning($e->getMessage() . ' Aborting manifest building for "' . $carrierId . '", service type "' . $serviceType . '".');
            if (isset($this->builtManifests[$carrierId][$serviceType]))
                unset($this->builtManifests[$carrierId][$serviceType]);
        } else {
            AustpostManifest::getInstance()->info('.... manifest for "' . $carrierId . '", service type "' . $serviceType . '" built. File: ' . $manifestFileName);
        }
    }
    
    protected function getManifestDir() {
        if (false === ($manifestDir = realpath(Zend_Registry::get('manifest_dir'))))
            throw new Exception('Manifest dir "' . Zend_Registry::get('manifest_dir') . '" is inaccessible.');
        
        return $manifestDir;
    }
    
    /**
    * Build manifest
    * 
    * @param array $carriersList - carriers list to build manifest for
     *@param int   $manifestId
    * 
    * @return array
    */
    public function build($carriersList = array(), $manifestId = null) {
        $this->carriersList = $carriersList;
        $this->manifestId   = $manifestId;
        
        if (false === ($this->manifestDir = realpath($this->getManifestDir())) )  {
            throw new Minder_Exception('Manifest target dir not exists.');
        }
        
        $this->buildCarriersList();
        
        foreach ($this->builtManifests as $carrierId => $usedServicesList) {
            foreach ($usedServicesList as $serviceType => $manifest)
                $this->buildManifestForCarrierAndType($carrierId, $serviceType);
        }
        
        
        return $this->builtManifests;
    }
}

class AustpostManifest_ManifestBuilder_Exception extends AustpostManifest_Exception {}

class AustpostManifest_ManifestBuilder_NoCarrier_Exception extends AustpostManifest_ManifestBuilder_Exception {}

class AustpostManifest_ManifestBuilder_NoManifestData_Exception extends AustpostManifest_ManifestBuilder_Exception {}

class AustpostManifest_ManifestBuilder_EmptyFtpUser_Exception extends AustpostManifest_ManifestBuilder_Exception {}

class AustpostManifest_ManifestBuilder_ManifestGeneratorNotDefined_Exception extends AustpostManifest_ManifestBuilder_Exception {}
