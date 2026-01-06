<?php

class Minder_LabelPrinter_CostCentre extends Minder_LabelPrinter_Abstract {

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        return array();
    }

    protected function _printLabel($labeldata)
    {
        return $this->_getPrinter()->printCostCentreLabel($labeldata);
    }
}