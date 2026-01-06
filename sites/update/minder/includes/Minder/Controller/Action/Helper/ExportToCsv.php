<?php

class Minder_Controller_Action_Helper_ExportToCsv extends Zend_Controller_Action_Helper_Abstract implements Minder_ExportToInterface {

    protected $_filePointer;

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
        $this->getResponse()
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="report.csv"')
            ->setHeader('Cache-Control', 'max-age=0,must-revalidate,post-check=0,pre-check=0');
    }

    public function proceedDataRow(array $headers, array $dataRow)
    {
        $line = array();
        foreach ($headers as $key => $val) {
            $line[] = $dataRow[$key];
        }
        fputcsv($this->_getFilePointer(), $line, ',');
    }

    public function proceedHeaders(array $headers)
    {
        // do not include headers into csv file
    }

    protected function _getFilePointer() {
        if (empty($this->_filePointer)) {
            $this->_filePointer = fopen("php://temp/", 'r+');
        }

        return $this->_filePointer;
    }
}