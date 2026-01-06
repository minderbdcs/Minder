<?php

class Minder2_LabelPrinter_Grn_Default extends Minder2_LabelPrinter_Abstract {

    protected function _getGrns($paramsMap, $data) {
        if (isset($paramsMap['GRN']))
            return $this->_extractFieldValue($data, 'GRN');

        return array();
    }

    /**
     * @param array $grns
     * @param Minder_Printer_Abstract $printer
     * @throws Exception
     * @return array
     */
    protected function _printLabel($grns, $printer) {
        $result = array();
        foreach ($grns as $grn) {
            $printResult = $printer->printGrnLabel(array('GRN' => $grn));

            if ($printResult['RES'] < 0) {
                throw new Exception(' Error printing GRN label #' . $grn . ': ' . $printResult['ERROR_TEXT']);
            }

            $result[] = 'GRN label #' . $grn . ': ' . $printResult['ERROR_TEXT'];
        }

        return $result;
    }

    public function printLabel($paramsMap, $data, $printer)
    {
        return $this->_printLabel($this->_getGrns($paramsMap, $data), $printer);
    }

}