<?php

interface Minder_LabelPrinter_Interface {
    /**
     * @abstract
     * @param array|string $labelId
     * @param Minder_Printer_Abstract $prinder
     * @param Minder_JSResponse $resultObject
     * @return Minder_JSResponse
     */
    public function doPrint($labelId, $prinder, Minder_JSResponse $resultObject = null);

    /**
     * @abstract
     * @param array $labelData
     * @param Minder_Printer_Abstract $printer
     * @param Minder_JSResponse $resultObject
     * @return Minder_JSResponse
     */
    public function directPrint($labelData, $printer, $resultObject = null);
}