<?php

class Minder_Controller_Action_Helper_ExportToXml extends Zend_Controller_Action_Helper_Abstract implements Minder_ExportToInterface {

    protected $_dom;
    protected $_root;

    public function proceedData(array $headers, array $data)
    {
        foreach ($data as $dataRow) {
            $this->proceedDataRow($headers, $dataRow);
        }
    }

    public function passThru()
    {
        $dom = $this->_getDocument();
        $dom->formatOutput = true;
        echo $dom->saveXML();
    }

    public function sendHttpHeaders()
    {
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/octet-stream');
        $response->setHeader('Content-type', 'application/force-download');
        $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
    }

    public function proceedDataRow(array $headers, array $dataRow)
    {
        $dom = $this->_getDocument();
        $domRow = $dom->createElement('row');
        foreach ($headers as $key => $val) {
            $domRow->appendChild($dom->createElement('cell', $this->getActionController()->view->escape($dataRow[$key])));
        }
        $this->_getRootNode()->appendChild($domRow);
    }

    public function proceedHeaders(array $headers)
    {
        $dom = $this->_getDocument();
        $headerNode = $dom->createElement('header');
        foreach ($headers as $column) {
            $headerNode->appendChild($dom->createElement('cell', $this->getActionController()->view->escape($column)));
        }
        $this->_getRootNode()->appendChild($headerNode);
    }

    protected function _getDocument() {
        if (empty($this->_dom)) {
            $this->_dom = new DOMDocument('1.0', 'iso-8859-1');
        }

        return $this->_dom;
    }

    protected function _getRootNode() {
        if (empty($this->_root)) {
            $this->_root = $this->_getDocument()->createElement('root');
            $this->_getDocument()->appendChild($this->_root);
        }

        return $this->_root;
    }
}