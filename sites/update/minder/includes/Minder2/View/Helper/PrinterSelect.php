<?php

class Minder2_View_Helper_PrinterSelect extends Zend_View_Helper_Abstract {

    /**
     * @param Minder2_Model_SysEquip $printerA
     * @param Minder2_Model_SysEquip $printerB
     * @return int
     */
    protected function _sorter($printerA, $printerB) {
        if ($printerA->DEVICE_ID > $printerB->DEVICE_ID)
            return 1;

        if ($printerA->DEVICE_ID < $printerB->DEVICE_ID)
            return -1;

        return 0;
    }

    protected function _getPrinterList() {

        $result = array();

        $printerList = Minder2_Environment::getPrinterList();

        usort($printerList, array($this, '_sorter'));

        /**
         *@var Minder2_Model_SysEquip $printer
         */
        foreach ($printerList as $printer) {
            $result[$printer->DEVICE_ID] = $printer->DEVICE_ID . ' - ' . $printer->EQUIPMENT_DESCRIPTION_CODE;
        }

        return $result;
    }

    protected function _getSelectedPrinter() {
        return Minder2_Environment::getCurrentPrinter()->DEVICE_ID;
    }

    public function printerSelect($name, $attribs = null, $listsep = "<br />\n") {
        return $this->view->formSelect($name, $this->_getSelectedPrinter(), $attribs, $this->_getPrinterList(), $listsep);
    }
}