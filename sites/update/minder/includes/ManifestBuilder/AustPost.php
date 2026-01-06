<?php

class ManifestBuilder_AustPost implements ManifestBuilder_BuilderInterface, ManifestBuilder_LoggerAwareInterface {

    /**
     * @var ManifestBuilder_Date
     */
    protected $_currentDate;

    /**
     * @var ManifestBuilder_AustPost_Provider_CarrierService
     */
    protected $_carrierServiceProvider;

    /**
     * @var ManifestBuilder_AustPost_Provider_MerchantLocation
     */
    protected $_merchantLocationProvider;

    /**
     * @var ManifestBuilder_Logger
     */
    public $_logger;

    /**
     * @var ManifestBuilder_AustPost_FormatterInterface
     */
    public $_formatter;
    /**
     * @var ManifestBuilder_AustPost_Provider_Consignment
     */
    protected $_consignmentProvider;

    /**
     * @var ManifestBuilder_AustPost_Provider_Article
     */
    protected $_articleProvider;

    /**
     * @var ManifestBuilder_AustPost_Provider_Item
     */
    protected $_itemProvider;

    public function build($carrierId, ManifestBuilder_Options $options)
    {
        $result = array();
        foreach ($this->getMerchantLocationsUsed(array($carrierId), $options->getManifestId()) as $merchantLocation) {

            $userName = $merchantLocation->getCarrier()->FTP_USER;
            if (empty($userName)) {
                $this->getLogger()->noUserNameWarning($merchantLocation->getCarrier()->CARRIER_ID);
                continue;
            }

            $locationId = $merchantLocation->getLocationId();
            if (empty($locationId)) {
                $this->getLogger()->noMerchantLocationIdWarning($merchantLocation->getCarrier()->CARRIER_ID, '');
                continue;
            }

            if (is_null($options->getManifestId())) {
                $this->getLogger()->buildingManifestForAccount($merchantLocation->getCarrier()->CARRIER_ID, $merchantLocation->getLocationId());
            } else {
                $this->getLogger()->rebuildingManifest($options->getManifestId());
            }

            $newManifestNumber = $this->_getManifestNumber($merchantLocation, $options);

            $manifestContent = $this->getManifestContent($merchantLocation, $options->getManifestId(), $newManifestNumber);

            $fileName = $this->getManifestFilePath($options, $locationId, $newManifestNumber, $userName);
            file_put_contents($fileName, $manifestContent);

            $this->getLogger()->manifestBuilt($fileName);
        }
    }

    private function getManifestContent(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation, $originalManifestId, $newManifestNumber)
    {
        $formatter = $this->getFormatter();
        $formatter->reset();

        $formatter->addHeader($this->getManifestHeader($merchantLocation));

        $manifest = $formatter->addPcmsManifest($this->getPcmsManifest($newManifestNumber, $merchantLocation->getLocationId()));

        foreach ($this->getConsignments($merchantLocation, $originalManifestId) as $consignment) {
            $connoteNode = $manifest->addConsignment($consignment);

            foreach ($this->getArticles($consignment) as $article) {
                $articleNode = $connoteNode->addArticle($article);

                foreach ($this->getItems($article) as $item) {
                    $articleNode->addItem($item);
                }
            }
        }

        return $formatter->getContent();
    }

    public function upload(array $manifestList)
    {
        // TODO: Implement upload() method.
    }

    /**
     * @param ManifestBuilder_Date $date
     * @return ManifestBuilder_BuilderInterface
     */
    public function setCurrentDate(ManifestBuilder_Date $date)
    {
            $config = Zend_Registry::get('config');

            $timezone = $config->date->timezone;

            $date->setTimezone($timezone);

        $this->_currentDate = $date;
        return $this;
    }

    /**
     * @return ManifestBuilder_Date
     */
    public function getCurrentDate()
    {
        return $this->_currentDate;
    }

    /**
     * @param ManifestBuilder_Logger $logger
     * @return ManifestBuilder_LoggerAwareInterface
     */
    public function setLogger(ManifestBuilder_Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * @return ManifestBuilder_Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return ManifestBuilder_AustPost_Provider_CarrierService
     */
    public function getCarrierServiceProvider()
    {
        return $this->_carrierServiceProvider;
    }

    /**
     * @param ManifestBuilder_AustPost_Provider_CarrierService $carrierServiceProvider
     * @return ManifestBuilder_AustPost
     */
    public function setCarrierServiceProvider(ManifestBuilder_AustPost_Provider_CarrierService $carrierServiceProvider)
    {
        $this->_carrierServiceProvider = $carrierServiceProvider;
        return $this;
    }

    /**
     * @param $carriers
     * @param $manifestId
     * @return ManifestBuilder_AustPost_Model_MerchantLocation[]
     */
    private function getMerchantLocationsUsed(array $carriers, $manifestId)
    {
        $this->getLogger()->searchingForCarriers();
        $result = $this->_getMerchantLocationProvider()->getMerchantLocationsUsed($carriers, $manifestId);
        $this->getLogger()->carrierAccountsFound($result);

        return $result;
    }

    private function _getManifestNumber(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation, ManifestBuilder_Options $options)
    {
        if (is_null($options->getManifestId())) {
            return $this->getCarrierServiceProvider()->getNextManifestNumber($merchantLocation);
        } else {
            return $options->getManifestId();
        }
    }

    private function getManifestFilePath(ManifestBuilder_Options $options, $locationId, $manifestNumber, $userName)
    {
        return $options->getManifestDir() . DIRECTORY_SEPARATOR . sprintf('%s_%s_%s.xml', $locationId, $manifestNumber, $userName);
    }

    /**
     * @return ManifestBuilder_AustPost_FormatterInterface
     */
    public function getFormatter()
    {
        return $this->_formatter;
    }

    /**
     * @param ManifestBuilder_AustPost_FormatterInterface $formatter
     * @return ManifestBuilder_AustPost
     */
    public function setFormatter(ManifestBuilder_AustPost_FormatterInterface $formatter)
    {
        $this->_formatter = $formatter;
        return $this;
    }

    private function getManifestHeader(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation)
    {
        return $this->getCarrierServiceProvider()->getManifestHeader($merchantLocation, $this->getCurrentDate());
    }

    private function getPcmsManifest($manifestId, $locationId)
    {
        return $this->getCarrierServiceProvider()->getPcmsConsignment($manifestId, $locationId, $this->getCurrentDate());
    }

    /**
     * @param $carrierService
     * @param $newManifestId
     * @return ManifestBuilder_AustPost_Model_Consignment[]
     */
    private function getConsignments($carrierService, $originalManifestId)
    {
        return $this->getConsignmentProvider()->getConsignments($carrierService, $originalManifestId, $this->getCurrentDate());
    }

    /**
     * @return ManifestBuilder_AustPost_Provider_Consignment
     */
    public function getConsignmentProvider()
    {
        return $this->_consignmentProvider;
    }

    /**
     * @param ManifestBuilder_AustPost_Provider_Consignment $provider
     * @return ManifestBuilder_AustPost
     */
    public function setConsignmentProvider(ManifestBuilder_AustPost_Provider_Consignment $provider) {
        $this->_consignmentProvider = $provider;
        return $this;
    }

    private function getArticles(ManifestBuilder_AustPost_Model_Consignment $consignment)
    {
        return $this->getArticleProvider()->getConnoteArticles($consignment);
    }

    /**
     * @param ManifestBuilder_AustPost_Provider_Article $provider
     * @return ManifestBuilder_AustPost
     */
    public function setArticleProvider(ManifestBuilder_AustPost_Provider_Article $provider) {
        $this->_articleProvider = $provider;
        return $this;
    }

    /**
     * @return ManifestBuilder_AustPost_Provider_Article
     */
    public function getArticleProvider()
    {
        return $this->_articleProvider;
    }

    private function getItems(ManifestBuilder_AustPost_Model_Article $article)
    {
        return $this->getItemProvider()->getItems($article);
    }

    /**
     * @return ManifestBuilder_AustPost_Provider_Item
     */
    private function getItemProvider()
    {
        return $this->_itemProvider;
    }

    /**
     * @param ManifestBuilder_AustPost_Provider_Item $provider
     * @return ManifestBuilder_AustPost
     */
    public function setItemProvider(ManifestBuilder_AustPost_Provider_Item $provider) {
        $this->_itemProvider = $provider;
        return $this;
    }

    public function init()
    {
        $this->setFormatter(new ManifestBuilder_AustPost_FormatterXml());
        return $this;
    }

    /**
     * @return ManifestBuilder_AustPost_Provider_MerchantLocation
     */
    protected function _getMerchantLocationProvider()
    {
        return $this->_merchantLocationProvider;
    }

    /**
     * @param ManifestBuilder_AustPost_Provider_MerchantLocation $merchantLocationProvider
     * @return $this
     */
    public function setMerchantLocationProvider($merchantLocationProvider)
    {
        $this->_merchantLocationProvider = $merchantLocationProvider;
        return $this;
    }
}
