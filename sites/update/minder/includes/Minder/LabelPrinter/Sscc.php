<?php

class Minder_LabelPrinter_Sscc implements Minder_LabelPrinter_Interface {

    protected function _doPrint($ssccRow, $printerId, Minder_JSResponse $resultObject) {
        $transaction = new Transaction_DSPSR();

        $transaction->ssccId = $ssccRow['PACK_SSCC.PS_SSCC'];
        $transaction->printerId = $printerId;
        $transaction->pickOrder = $ssccRow['PACK_SSCC.PS_PICK_ORDER'];

        try {
            $this->_getMinder()->doTransactionResponseV6($transaction);
        } catch (Exception $e) {
            $resultObject->errors[] = ' Error printing ' . $this->_getLabelType() . ': ' . $e->getMessage();
            return false;
        }

        return true;
    }

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
        $printedAmount = 0;

        foreach ($labelData as $ssccRow) {
            if ($this->_doPrint($ssccRow, $printer->getPrinter(), $resultObject)) {
                $printedAmount++;
            }
        }

        if ($printedAmount > 0) {
            $resultObject->messages[] = $printedAmount . ' ' . $this->_getLabelType() . ' label(s) was printed.';
        } else {
            $resultObject->warnings[] = 'No label where printed.';
        }

        return $resultObject;
    }

    /**
     * @return string
     */
    protected function _getLabelType()
    {
        return 'SSCC';
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}