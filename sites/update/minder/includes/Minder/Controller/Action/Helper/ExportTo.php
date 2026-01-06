<?php

class Minder_Controller_Action_Helper_ExportTo extends Zend_Controller_Action_Helper_Abstract implements Minder_ExportToInterface
{
    protected $_format;

    public function exportTo($format = null, array $headers = null, array $data = null) {

        if (!is_null($format)) {
            $this->_setFormat($format);
        }

        if (is_array($headers)) {
            $this->proceedHeaders($headers);

            if (is_array($data)) {
                $this->proceedData($headers, $data);
                $this->sendHttpHeaders();
                $this->passThru();
            }
        }

        return $this;
    }

    public function proceedHeaders(array $headers) {
        $this->_getFormatter($this->_format)->proceedHeaders($headers);
    }

    public function proceedData(array $headers, array $data) {
        foreach ($data as $dataRow) {
            if (isset($dataRow->items)) {
                $this->proceedDataRow($headers, $dataRow->items);
            } else {
                $this->proceedDataRow($headers, $dataRow);
            }
        }
    }

    public function proceedDataRow(array $headers, array $dataRow) {
        $this->_getFormatter($this->_format)->proceedDataRow($headers, $dataRow);
    }

    public function sendHttpHeaders() {
        $this->_getFormatter($this->_format)->sendHttpHeaders();
    }

    public function passThru() {
        $this->_getFormatter($this->_format)->passThru();
    }

    protected function _setFormat($format) {
        $this->_format = $format;
    }

    /**
     * @param $format
     * @return Minder_ExportToInterface
     * @throws Exception
     */
    protected function _getFormatter($format) {
        switch (strtoupper($format)) {
            case 'REPORT: XML':
                return $this->getActionController()->getHelper('ExportToXml');
            case 'REPORT: TXT':
                return $this->getActionController()->getHelper('ExportToTxt');
            case 'REPORT: CSV':
                return $this->getActionController()->getHelper('ExportToCsv');
            case 'REPORT: XLS':
                return $this->getActionController()->getHelper('ExportToXls');
            case 'XLSX':
            case 'REPORT: XLSX':
                /**
                 * @var Minder_ExportTo_FormatAwareInterface $formatter
                 */
                $formatter = $this->getActionController()->getHelper('ExportToXls');
                $formatter->setFormat(Minder_ExportTo_FormatAwareInterface::OOXML_XLS);
                return $formatter;
            default:
                throw new Exception('Unsupported format "' . $format . '"');
        }
    }
}