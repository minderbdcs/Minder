<?php

class ManifestBuilder_Options {

    protected $_createManifest = false;
    protected $_uploadManifest = false;
    protected $_carriersList;
    protected $_manifestId;
    protected $_manifestDir;
    protected $_showHelp = false;
    protected $_outputFormat;


    public function fillCommandLineOptions(Zend_Console_Getopt $options) {
        $this->_createManifest = (bool)$options->getOption('create-manifest');
        $this->_uploadManifest = (bool)$options->getOption('upload-manifest');
        $tmpCarriersList    = $options->getOption('carriers');
        $this->_carriersList = (is_string($tmpCarriersList)) ? explode('|', $tmpCarriersList) : array();
        $this->_manifestId   = $options->getOption('pick-manifest-id');

        $tmpManifestDir = $options->getOption('manifest-dir');
        $this->setManifestDir((empty($tmpManifestDir)) ? ROOT_DIR . DIRECTORY_SEPARATOR . 'manifests' : $tmpManifestDir);

        $this->_showHelp = (bool)$options->getOption('help');
        $this->_outputFormat = $options->getOption('output-format');
    }

    public function showHelp() {
        return $this->_showHelp;
    }

    public function createManifest() {
        return $this->_createManifest;
    }

    public function uploadManifest() {
        return $this->_uploadManifest;
    }

    /**
     * @return array
     */
    public function getCarriersList()
    {
        return $this->_carriersList;
    }

    /**
     * @return null|string
     */
    public function getManifestId()
    {
        return (empty($this->_manifestId)) ? null : $this->_manifestId;
    }

    public function getManifestDir() {
        return $this->_manifestDir;
    }

    public function setManifestDir($manifestDir)
    {
        $this->_manifestDir = realpath($manifestDir);
        if (false === $this->_manifestDir) {
            throw new Exception('Manifest Directory "' . $manifestDir . '" does not exists or not writable.');
        }
        return $this;
    }

    public function getOutputFormat()
    {
        return $this->_outputFormat;
    }
}