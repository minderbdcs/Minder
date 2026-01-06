<?php

class Minder_LabelPrinter_CrudPackId implements Minder_LabelPrinter_Interface {

    /**
     * @var Minder_PackIdPrintTools;
     */
    protected $_printTool = null;

    /**
     * @param array|string $labelId
     * @param Minder_Printer_Abstract $prinder
     * @param Minder_JSResponse $resultObject
     * @return Minder_JSResponse
     */
    public function doPrint($labelId, $prinder, Minder_JSResponse $resultObject = null)
    {
        // TODO: Implement doPrint() method.
        throw new Exception('Not implemented');
    }

    /**
     * @param array $labelData
     * @param Minder_Printer_Abstract $printer
     * @param Minder_JSResponse $resultObject
     * @return Minder_JSResponse
     */
    public function directPrint($labelData, $printer, $resultObject = null)
    {
        $resultObject = is_null($resultObject) ? new Minder_JSResponse() : $resultObject;
        $printTools = $this->getPrintTool();
        $printedAmount = 0;

        foreach ($labelData as $packIdRow) {
            $tempResult = $printTools->reprintLabel($packIdRow['PACK_ID.PACK_ID'], $printer);

            $resultObject->merge($tempResult);

            $printedAmount++;
        }

        return $resultObject;
    }

    /**
     * @return string
     */
    protected function _getLabelType()
    {
        return '';
    }

    protected function getPrintTool() {
        if (is_null($this->_printTool)) {
            $this->_printTool = new Minder_PackIdPrintTools();
        }

        return $this->_printTool;

    }
}