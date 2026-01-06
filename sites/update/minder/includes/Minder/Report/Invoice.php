<?php

/**
 * @throws Minder_Report_Invoice_Exception
 * @property string pickOrder
 * @property string pickInvoice
 * @property string pickInvoiceNo
 */
class Minder_Report_Invoice extends Minder_Report_Abstract {

    protected $_pickOrder     = null;
    protected $_pickInvoice   = null;
    protected $_pickInvoiceNo = null;

    public function __get($name) {
        $name = '_' . $name;
        return $this->$name;
    }

    public function __set($name, $value) {
        $name = '_' . $name;
        $this->$name = strval($value);
    }

    /**
     * @throws Minder_Report_Invoice_Exception
     * @param string $reportDetail
     * @return string
     */
    protected function _getQueryFieldValue($queryField) {
        switch ($queryField) {
            case 'PARAM_PICK_ORDER':
                return $this->_pickOrder;
            case 'PARAM_INVOICE_NO':
                return $this->_pickInvoiceNo;
            case 'PARAM_INVOICE_ID':
                return $this->_pickInvoice;
            default:
                throw new Minder_Report_Invoice_Exception('Unsupported Query Field "' . $queryField . '"');
        }
    }

    /**
     * @param Minder_Report_Formatter_Abstract | null $reportFormatter
     * @return Minder_Report_Result
     */
    public function makeReport($reportFormatter = null)
    {
        /**
         * @var Minder_Report_Detail $reportDetail
         */
        foreach ($this->_reportDetails as &$reportDetail) {
            $reportDetail->queryFieldValue = $this->_getQueryFieldValue($reportDetail->queryField);
        }

        return parent::makeReport($reportFormatter);
    }
}
