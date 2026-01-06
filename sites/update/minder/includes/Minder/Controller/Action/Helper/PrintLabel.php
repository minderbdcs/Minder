<?php

class Minder_Controller_Action_Helper_PrintLabel extends Zend_Controller_Action_Helper_Abstract {
    const PRINT_COST_CENTRE = "PRINT COST CENTRE";
    const LABEL_QTY = "labelqty";

    /**
     * @param $labelData
     * @param $action
     * @param null $labelType
     * @return Minder_JSResponse
     * @throws Exception
     */
    public function printLabel($labelData, $action, $labelType = null) {
        $labelType = empty($labelType) ? $this->_getLabelType($action) : $labelType;
        $labelPrinter = $this->_getLabelPrinter($labelType);
        $labelData = $this->_prepareData($labelData, $this->_getTable($action));
        return $labelPrinter->directPrint(array($labelData), Minder::getInstance()->getPrinter());
    }

    protected function _prepareData($labelData, $tableName) {
        if (empty($tableName))
            return $labelData;

        $result = array();
        foreach ($labelData as $key => $value) {
            $result[$tableName . '.' . $key] = $value;
        }

        return $result;
    }

    protected function _getTable($action) {
        switch ($action) {
            case 'PRINT LOCATION':
	    case 'PRINT BORROWER':
                return 'LOCATION';
            case 'PRINT LOGON':
                return 'SYS_USER';
            case 'PRINT SSCC':
                return 'PACK_SSCC';
            case 'PRINT PACK LABEL':
                return 'PACK_ID';
            case static::PRINT_COST_CENTRE:
                return 'COST_CENTRE';
            case 'PRINT PRODUCT':
            case 'PRINT LABEL':
            default:
                return null;
        }
    }

    /**
     * @param $action
     * @returns string
     * @throws Exception
     */
    protected function _getLabelType($action) {
        switch ($action) {
            case 'PRINT LOCATION':
                return 'LOCATION';
	    case 'PRINT BORROWER':
		return 'BORROWER';
            case 'PRINT LOGON':
                return 'LOGON';
            case 'PRINT PRODUCT':
                return 'PRODUCT_LABEL';
            case 'PRINT LABEL':
                return 'GRN';
            case 'PRINT SSCC':
                return 'SSCC';
            case 'PRINT PACK LABEL':
                return 'PACK_ID';
            case static::PRINT_COST_CENTRE:
                return 'COST_CENTRE';
            default:
                throw new Exception('Unsupported action: ' . $action);
        }
    }

    protected function _getLabelPrinter($labelType) {
        return Minder_LabelPrinter_Factory::getLabelPrinter($labelType);
    }

    public function getMessage($action) {
        switch ($action) {
            case 'PRINT LOCATION':
                return 'LOCATION printed successfully';
	    case 'PRINT BORROWER':
		return 'BORROWER printed successfully';
            case 'PRINT LOGON':
                return 'SYS_USER printed successfully';
            case 'PRINT PRODUCT':
                return 'PRODUCT printed successfully';
            case 'PRINT LABEL':
                return 'GRN printed successfully';
            case 'PRINT SSCC':
                return 'SSCC label(s) printed successfully';
            case 'PRINT PACK LABEL':
                return 'PACK label(s) printed successfully';
            default:
                return '';
        }
    }

    public function printCostCentreLabel($labelData, $labelsAmount) {
        $labelData = $this->_prepareData($labelData, 'COST_CENTRE');
        $labelData[static::LABEL_QTY] = $labelsAmount;

        return $this->printLabel($labelData, static::PRINT_COST_CENTRE);
    }
}
