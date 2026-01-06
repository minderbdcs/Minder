<?php

class Minder_Controller_Action_Helper_ExportToTxt extends Zend_Controller_Action_Helper_Abstract implements Minder_ExportToInterface {

    public function proceedData(array $headers, array $data)
    {
        foreach ($data as $dataRow) {
            $this->proceedDataRow($headers, $dataRow);
        }
    }

    public function passThru()
    {
        rewind($this->_getFilePointer());
        echo stream_get_contents($this->_getFilePointer());
        fclose($this->_getFilePointer());
    }

    public function sendHttpHeaders()
    {
        $this->getResponse()->setHeader('Content-Type', 'text/plain')
            ->setHeader('Content-Disposition', 'attachment; filename="report.txt"')
            ->setHeader('Cache-Control', 'max-age=0,must-revalidate,post-check=0,pre-check=0');
    }

    public function proceedDataRow(array $headers, array $dataRow)
    {
        $line = '';
        foreach ($headers as $key => $column) {
            $line .= $this->getActionController()->view->escape($dataRow[$key]) . chr(9);
        }
        if (!empty($line)) {
            fputs($this->_getFilePointer(), "$line\n");
        }
    }

    public function proceedHeaders(array $headers)
    {
        $line = '';
        foreach ($headers as $column) {
            $line .= $this->getActionController()->view->escape($column) . chr(9);
        }
        if (!empty($line)) {
            fputs($this->_getFilePointer(), "$line\n");
        }
    }

    protected function _getFilePointer() {
        if (empty($this->_filePointer)) {
            $this->_filePointer = fopen("php://temp/", 'r+');
        }

        return $this->_filePointer;
    }
}