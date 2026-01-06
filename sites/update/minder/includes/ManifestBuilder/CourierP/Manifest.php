<?php

class ManifestBuilder_CourierP_Manifest {

    /**
     * @var ManifestBuilder_CourierP_Consignment[]
     */
    protected $_consignments = array();

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

    public function toXmlNode(ManifestBuilder_DOMDocument $dom) {
        $node = $dom->createElement('CPPLPODManifest');

        foreach ($this->_consignments as $consignment) {
            $node->appendChild($consignment->toXmlNode($dom));
        }

        return $node;
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


}
