<?php

class Minder_ImportPurchaseOrderSubline
{
    protected $minder       = null;
    protected $fileHandle   = null;

    public function __construct($purchaseOrder = '') {
        // to implement
    }

    public function getPurchaseOrderSublineDetails() {

    }

    public function doImport($file) {
        try {
            $this->minder = Minder::getInstance();
            $this->getPurchaseOrderSublineDetails();
            $this->openFile($file);
            $this->importLines();
            $this->closeFile();
        } catch (Exception $e) {
            $this->closeFile();
            throw $e;
        }
    }

    public function openFile($file) {
        if (!file_exists($file))
            throw new Minder_ImportPurchaseOrderSubline_Exception('File "' . $file . '" not exists.');

        if (!is_readable($file))
            throw new Minder_ImportPurchaseOrderSubline_Exception('Cannot open file "' . $file . '" for reading.');

        if (false === ($this->fileHandle = fopen($file, 'r')))
            throw new Minder_ImportPurchaseOrderSubline_Exception('Cannot open file "' . $file . '" for reading.');
    }

    public function importLines() {

    }

    public function closeFile() {
        if (!is_null($this->fileHandle))
            fclose($this->fileHandle);
    }
}

class Minder_ImportPurchaseOrderSubline_Exception extends Minder_Exception {}
