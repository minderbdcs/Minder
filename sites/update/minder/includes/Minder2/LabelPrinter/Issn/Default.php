<?php

class Minder2_LabelPrinter_Issn_Default extends Minder2_LabelPrinter_Abstract {

    protected function _getSsnIds($paramsMap, $data) {
        if (isset($paramsMap['SSN_ID']))
            return $this->_extractFieldValue($data, 'SSN_ID');

        return array();
    }

    /**
     * @param array $ssnIds
     * @param Minder_Printer_Abstract $printer
     * @throws Exception
     * @return array
     */
    protected function _printLabel($ssnIds, $printer) {
        $issnLabelPrinter = new Minder_LabelPrinter_Issn();

        $result = $issnLabelPrinter->doPrint($ssnIds, $printer);

        if (count($result->errors) > 0) {
            throw new Exception(implode('. ', $result->errors));
        }

        return $result->messages;
    }

    public function printLabel($paramsMap, $data, $printer)
    {
        return $this->_printLabel($this->_getSsnIds($paramsMap, $data), $printer);
    }


}