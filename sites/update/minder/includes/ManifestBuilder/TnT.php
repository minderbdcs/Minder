<?php

class ManifestBuilder_TnT implements ManifestBuilder_BuilderInterface, ManifestBuilder_LoggerAwareInterface {

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
     * @var ManifestBuilder_TnT_ManifestManager
     */
    protected $_manifestManager;

    /**
     * @var ManifestBuilder_TnT_CarrierAccountManager
     */
    protected $_carrierAccountManager;

    public function init() {
        return $this;
    }

    public function build($carrierId, ManifestBuilder_Options $options)
    {
        $result = array();
        $report='';
        foreach ($this->_getCarriersAccounts(array($carrierId), $options->getManifestId()) as $carrierAccount) {
            $manifest = $this->_getManifestManager()->getManifest($carrierAccount, $options->getManifestId(), $this->getCurrentDate());

            if (is_null($options->getManifestId())) {
                $this->getLogger()->buildingManifestForAccount($carrierId, $carrierAccount->getAccountNo());
            } else {
                $this->getLogger()->rebuildingManifest($options->getManifestId());
            }


            $fileName = $this->_getFileName($carrierId, $options->getManifestId());
            $path = $options->getManifestDir() . DIRECTORY_SEPARATOR . $fileName;


            //calling xmlnode function on manifest.php
             $data=$manifest->convertToTxt();


//generating the content to write into txt file


       if (count($data) > 0) {

        $report .= sprintf("%s", join("\t", array_keys($data[0])));
        $report .= "\n";

                      foreach ($data as $row) {

                                              $report .= "";

                                              foreach ($row as $column) {
                                                  $report .= "$column\t";
                                              }
                                              $report .= "\n";
                      }
    } 


    else {
        $report = "No data";
    }


            file_put_contents($path, $report);


          
            $this->getLogger()->manifestBuilt($path);
        }

        $this->getLogger()->allManifestsBuilt();
        return $report;
    }

    protected function _getManifestManager() {
        if (empty($this->_manifestManager)) {
            $this->_manifestManager = new ManifestBuilder_TnT_ManifestManager();
        }

        return $this->_manifestManager;
    }

    /**
     * @param $carriers
     * @param $manifestId
     * @return ManifestBuilder_TnT_CarrierAccount[]
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

        //return name in txt format
        return $carrier->CUSTOMER_CODE . $this->getCurrentDate()->toString('yMMdd') . $manifestId . '.txt';
    }

    protected function _getCarrier($carrierId ) {
        $carrier = $this->getCarrierProvider()->getCarrier($carrierId);

        return $carrier;
    }

// added as part of edits for ftp tnt file
        protected function getSftpParams($carrierId) {

    }
// added as part of edits for ftp tnt file

    public function upload(array $manifestList)
    {
        // TODO: Implement upload() method.

    }

    /**
     * @param ManifestBuilder_Date $date
     * @return ManifestBuilder_BuilderInterface|ManifestBuilder_TnT
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
     * @return ManifestBuilder_TnT
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
     * @return ManifestBuilder_TnT
     */
    public function setCarrierServiceProvider(ManifestBuilder_AustPost_Provider_CarrierService $carrierServiceProvider)
    {
        $this->_carrierServiceProvider = $carrierServiceProvider;
        return $this;
    }

    /**
     * @return ManifestBuilder_TnT_CarrierAccountManager
     */
    protected function _getCarrierAccountManager()
    {
        return $this->_carrierAccountManager;
    }

    /**
     * @param ManifestBuilder_TnT_CarrierAccountManager $carrierAccountManager
     * @return $this
     */
    public function setCarrierAccountManager($carrierAccountManager)
    {
        $this->_carrierAccountManager = $carrierAccountManager;
        return $this;
    }






	
    private function getArticles(ManifestBuilder_TnT_Model_Consignment $consignment)
    {
        return $this->getArticleProvider()->getConnoteArticles($consignment);
    }

    /**
     * @param ManifestBuilder_TnT_Provider_Article $provider
     * @return ManifestBuilder_TnT
     */
    public function setArticleProvider(ManifestBuilder_TnT_Provider_Article $provider) {
        $this->_articleProvider = $provider;
        return $this;
    }
	
     /**
     * @return ManifestBuilder_TnT_Provider_Article
     */
    public function getArticleProvider()
    {
        return $this->_articleProvider;
    }

}
