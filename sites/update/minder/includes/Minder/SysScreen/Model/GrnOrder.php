<?php

class Minder_SysScreen_Model_GrnOrder extends Minder_SysScreen_Model {

    /**
     * @throws Minder_SysScreen_Model_Exception
     * @param string $grnLabelNo
     * @return array
     */
    protected function _getGrnOrderLabelDataForPrinting($grnLabelNo) {
        $sql = 'SELECT * FROM GRN_ORDER LEFT JOIN GRN ON GRN_ORDER.GRN = GRN.GRN WHERE GRN_LABEL_NO = ?';

        $minder = Minder::getInstance();
        if (false === ($result = $minder->fetchAllAssocExt($sql, $grnLabelNo)))
            throw new Minder_SysScreen_Model_Exception('GRN_ORDER #' . $grnLabelNo . ' not found.');

        return array_shift($result);
    }

    /**
     * @param Minder_Printer_Abstract $printerObj
     * @return Minder_JSResponse
     */
    public function printLabels($printerObj) {
        $orders = $this->selectArbitraryExpression(0, count($this), 'DISTINCT GRN_LABEL_NO');

        $grnLabelNo = array_map(create_function('$item', 'return $item["GRN_LABEL_NO"];'), $orders);
        $grnOrderPrinter = new Minder_LabelPrinter_GrnOrder();
        return $grnOrderPrinter->doPrint($grnLabelNo, $printerObj);
    }
}