<?php

class ManifestBuilder_TnT_Manifest {

    /**
     * @var ManifestBuilder_TnT_Consignment[]
     */
    protected $_consignments = array();

    /**
     * @var ManifestBuilder_TnT_Login[]
     */
    protected $_carrierService = array();

    /**
     * @var Zend_Date
     */
    protected $_date;

    /**
     * @var string
     */
    protected $_fileName;

    /**
     * @var string
     */
    protected $_manifestId;

    public function setConsignments(array $consignments) {
        $this->_consignments = $consignments;
    }

    public function getLoginNode(ManifestBuilder_DOMDocument $dom) {
            $carrierId = $this->_carrierService->getCarrier()->CARRIER_ID;
            $userName = $this->_carrierService->getCarrier()->FTP_USER;
            $userPass = $this->_carrierService->getCarrier()->FTP_PASSWORD;
        $nodeLogin = $dom->createElement('LOGIN');
        $dom->appendChildNodes(
            $dom->createNodesFromArray(array(
                'COMPANY' => trim($userName),
                'PASSWORD' => trim($userPass),
                'APPID' => "IN",
                'APPVERSION' => "2.2",
            )),
        $nodeLogin
        );
        return $nodeLogin;
    }


    public function getActivityNode(ManifestBuilder_DOMDocument $dom) {            
        $nodeActivity = $dom->createElement('ACTIVITY');
	$nodePrint = $dom->createElement('PRINT');
	$nodeConnote = $dom->createElement('CONNOTE');
	$dom->appendChildNodes(
		$dom->createNodesFromArray(array(
			'CONREF' => ""
		)),
	$nodeConnote
	);
        return $nodeActivity;
    }


  

    public function convertToTxt() {

        //new array for consignment return data array store
        $consign_array=array();

        foreach ($this->_consignments as $consignment) {
                        
            //array push consignment data array into a common new array
            $c_arr=$consignment->convertToTxt();
            array_push($consign_array,$c_arr);
        }

        //returning consignment array
        return $consign_array;
    }

    /**
     * @return \Zend_Date
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * @param \Zend_Date $date
     * @return $this
     */
    public function setDate(Zend_Date $date)
    {
            $config = Zend_Registry::get('config');

            $timezone = $config->date->timezone;

            $date->setTimezone($timezone);

        $this->_date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getManifestId()
    {
        return $this->_manifestId;
    }

    /**
     * @param string $manifestId
     * @return $this
     */
    public function setManifestId($manifestId)
    {
        $this->_manifestId = $manifestId;
        return $this;
    }

    /**
     * @param string $carrierService
     * @return $this
     */
    public function setCarrierService($carrierService)
    {
        $this->_carrierService = $carrierService;
        return $this;
    }

}
