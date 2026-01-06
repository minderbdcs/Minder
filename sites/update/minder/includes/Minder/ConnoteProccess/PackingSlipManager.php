<?php

class Minder_ConnoteProccess_PackingSlipManager {
    protected $_orders;
    protected $_response;

    function __construct()
    {
        $this->_setResponse(new Minder_JSResponse());
    }

    public function create() {
        $printedPackingSlips = 0;
        foreach ($this->_getOrders() as $order) {
            $orderDetails = $this->_getOrderDetails($order);
            if ($this->_orderRequirePackingSlip($orderDetails)) {
                $this->_printPackingSlip($orderDetails);
                $printedPackingSlips++;
            }
        }

        if ($printedPackingSlips > 0) {
            $this->_getResponse()->addMessages($printedPackingSlips . ' Packing Slip(s) printed.');
        }

        return $this->_getResponse();
    }

    /**
     * @return mixed
     */
    protected function _getOrders()
    {
        return $this->_orders;
    }

    /**
     * @param mixed $despatchId
     * @return $this
     */
    public function setOrders($despatchId)
    {
        $this->_orders = $despatchId;
        return $this;
    }

    /**
     * @return Minder_JSResponse
     */
    protected function _getResponse()
    {
        return $this->_response;
    }

    /**
     * @param Minder_JSResponse $response
     * @return $this
     */
    protected function _setResponse(Minder_JSResponse $response)
    {
        $this->_response = $response;
        return $this;
    }

    private function _getOrderDetails($order)
    {
        $sql = "
            SELECT
                PICK_ORDER.PICK_ORDER,
                PICK_ORDER.COMPANY_ID,
                PICK_ORDER.PICK_ORDER_SUB_TYPE,
                COMPANY.INVOICE_PS_REPORT_ID,
                CONTROL.DEFAULT_DESPATCH_PRINTER AS CONTROL_PRINTER,
                COMPANY.DEFAULT_DESPATCH_PRINTER AS COMPANY_PRINTER,
                WAREHOUSE.DEFAULT_DESPATCH_PRINTER AS WH_PRINTER,
                COALESCE(CONTROL.DESPATCH_CREATE_PACKING_SLIP, 'N') AS CONTROL_CREATE_PS,
                COALESCE(COMPANY.DESPATCH_CREATE_PACKING_SLIP, 'N') AS COMPANY_CREATE_PS,
                COALESCE(WAREHOUSE.DESPATCH_CREATE_PACKING_SLIP, 'N') AS WH_CREATE_PS,
                COMPANY.DESPATCH_PACK_SLIP_SUB_TYPE
            FROM
                PICK_ORDER
                LEFT JOIN COMPANY ON PICK_ORDER.COMPANY_ID = COMPANY.COMPANY_ID
                LEFT JOIN WAREHOUSE ON PICK_ORDER.WH_ID = WAREHOUSE.WH_ID
                JOIN CONTROL ON 1=1
            WHERE
                PICK_ORDER.PICK_ORDER = ?
        ";

        return $this->_getMinder()->fetchAssoc($sql, $order['PICK_ORDER']);
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    private function _orderRequirePackingSlip($orderDetails)
    {

        if (empty($orderDetails['PICK_ORDER_SUB_TYPE']) || !in_array($orderDetails['PICK_ORDER_SUB_TYPE'], explode(',', (string)$orderDetails['DESPATCH_PACK_SLIP_SUB_TYPE']))) {
            return false;
        }

        switch (strtoupper($orderDetails['COMPANY_CREATE_PS'])) {
            case 'T': return true;
            case 'F': return false;
        }

        switch (strtoupper($orderDetails['WH_CREATE_PS'])) {
            case 'T': return true;
            case 'F': return false;
        }

        return strtoupper($orderDetails['CONTROL_CREATE_PS']) === 'T';
    }

    private function _printPackingSlip($orderDetails)
    {
        $report = $this->getReport($orderDetails);

        if (is_null($report)) {
            return;
        }

        $report->printReport($this->_getPrinter($orderDetails));
    }

    private function _getPrinter($orderDetails)
    {
        if (!empty(Minder2_Environment::getCurrentDevice()->DEFAULT_LP_PRINTER)) {
            return $this->_getMinder()->getPrinter(null, Minder2_Environment::getCurrentDevice()->DEFAULT_LP_PRINTER);
        }

        if (!empty($orderDetails['COMPANY_PRINTER'])) {
            return $this->_getMinder()->getPrinter(null, $orderDetails['COMPANY_PRINTER']);
        }

        if (!empty($orderDetails['WH_PRINTER'])) {
            return $this->_getMinder()->getPrinter(null, $orderDetails['WH_PRINTER']);
        }

        if (!empty($orderDetails['CONTROL_PRINTER'])) {
            return $this->_getMinder()->getPrinter(null, $orderDetails['CONTROL_PRINTER']);
        }

        return $this->_getMinder()->getPrinter(null, $this->_getMinder()->limitPrinter);
    }

    private function getReport($orderDetails)
    {
        $reportId = $orderDetails['INVOICE_PS_REPORT_ID'];

        if (empty($reportId)) {
            $this->_getResponse()->addWarnings('Cannot find Packing Slip Report ID for Order #' . $orderDetails['PICK_ORDER']);
            return null;
        }

        $report = Minder_Report_Factory::makeReport($reportId);

        $report->setQueryFieldValue('ParamPARAM_PICK_ORDER', $orderDetails['PICK_ORDER']);

        return $report;
    }
}