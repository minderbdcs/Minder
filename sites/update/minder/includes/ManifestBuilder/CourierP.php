<?php

class ManifestBuilder_CourierP implements ManifestBuilder_BuilderInterface, ManifestBuilder_LoggerAwareInterface {
    protected $_carrierServiceProvider;

    /**
     * @var ManifestBuilder_Logger
     */
    protected $_logger;

    /**
     * @var ManifestBuilder_Date
     */
    protected $_currentDate;

    /**
     * @var ManifestBuilder_Provider_Carrier
     */
    protected $_carrierProvider;

    /**
     * @var ManifestBuilder_CourierP_ManifestManager
     */
    protected $_manifestManager;

    /**
     * @var ManifestBuilder_CourierP_CarrierAccountManager
     */
    protected $_carrierAccountManager;

    public function init() {
        return $this;
    }

    public function build($carrierId, ManifestBuilder_Options $options)
    {
        $result = array();
        foreach ($this->_getCarriersAccounts(array($carrierId), $options->getManifestId()) as $carrierAccount) {
            $manifest = $this->_getManifestManager()->getManifest($carrierAccount, $options->getManifestId(), $this->getCurrentDate());

            $carrierId = $carrierAccount->getCarrier()->CARRIER_ID;

            if (is_null($options->getManifestId())) {
                $this->getLogger()->buildingManifestForAccount($carrierId, $carrierAccount->getAccountNo());
            } else {
                $this->getLogger()->rebuildingManifest($options->getManifestId());
            }

            $fileName = $this->_getFileName($carrierId, $options->getManifestId());
            $path = $options->getManifestDir() . DIRECTORY_SEPARATOR . $fileName;

            $manifest->setFileName($fileName);
            $dom = new ManifestBuilder_DOMDocument('1.0', 'utf-8');
            $dom->appendChild($manifest->toXmlNode($dom));
            file_put_contents($path, $dom->saveXML());

            $this->getLogger()->manifestBuilt($path);
        }

        $this->getLogger()->allManifestsBuilt();
        return $result;
    }

    protected function _getManifestManager() {
        if (empty($this->_manifestManager)) {
            $this->_manifestManager = new ManifestBuilder_CourierP_ManifestManager();
        }

        return $this->_manifestManager;
    }

    /**
     * @param $carriers
     * @param $manifestId
     * @return ManifestBuilder_CourierP_CarrierAccount[]
     */
    protected function _getCarriersAccounts(array $carriers, $manifestId) {
        $this->getLogger()->searchingForCarriers();
        $result = $this->_getCarrierAccountManager()->getAccounts($carriers, $manifestId);
        $this->getLogger()->carrierAccountsFound($result);

        return $result;
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function setLogger(ManifestBuilder_Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    protected function _getFileName($carrierId, $manifestId) {
        $carrier = $this->getCarrierProvider()->getCarrier($carrierId);
        $manifestId = (is_null($manifestId)) ? $this->getCarrierProvider()->getNextManifestNumber($carrierId) : $manifestId;


        return $carrier->CUSTOMER_CODE . $this->getCurrentDate()->toString('yMMdd') . $manifestId . '.xml';
    }

    public function upload(array $manifestList)
    {
        // TODO: Implement upload() method.
    }

    /**
     * @param ManifestBuilder_Date $date
     * @return ManifestBuilder_BuilderInterface|ManifestBuilder_CourierP
     */
    public function setCurrentDate(ManifestBuilder_Date $date)
    {
            $config = Zend_Registry::get('config');

            $timezone = $config->date->timezone;

            $date->setTimezone($timezone);
            
        $this->_currentDate = $date;
        return $this;
    }

    public function getCurrentDate() {
        return $this->_currentDate;
    }

    /**
     * @return ManifestBuilder_Provider_Carrier
     */
    public function getCarrierProvider()
    {
        return $this->_carrierProvider;
    }

    /**
     * @param \ManifestBuilder_Provider_Carrier $carrierServiceProvider
     * @return ManifestBuilder_CourierP
     */
    public function setCarrierProvider(ManifestBuilder_Provider_Carrier $carrierServiceProvider)
    {
        $this->_carrierProvider = $carrierServiceProvider;
        return $this;
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
     * @return ManifestBuilder_CourierP
     */
    public function setCarrierServiceProvider(ManifestBuilder_AustPost_Provider_CarrierService $carrierServiceProvider)
    {
        $this->_carrierServiceProvider = $carrierServiceProvider;
        return $this;
    }

    /**
     * @return ManifestBuilder_CourierP_CarrierAccountManager
     */
    protected function _getCarrierAccountManager()
    {
        return $this->_carrierAccountManager;
    }

    /**
     * @param ManifestBuilder_CourierP_CarrierAccountManager $carrierAccountManager
     * @return $this
     */
    public function setCarrierAccountManager($carrierAccountManager)
    {
        $this->_carrierAccountManager = $carrierAccountManager;
        return $this;
    }

}
