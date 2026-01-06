<?php

class Minder_Report_Factory {

    /**
     * @static
     * @throws Minder_Report_Factory_Exception
     * @param string $reportId
     * @param null $reportObject
     * @return Minder_Report_Abstract
     */
    public static function makeReport($reportId, $reportObject = null) {
        if (empty($reportId))
            throw new Minder_Report_Factory_Exception('Cannot build report for empty Id.');

        $sql = 'SELECT * FROM REPORTS WHERE REPORT_ID = ?';
        $minder = Minder::getInstance();
        if (false === ($reportData = $minder->fetchAssoc($sql, $reportId)))
            throw new Minder_Report_Factory_Exception('Cannot find Report Id: "' . $reportId . '".');

        if (is_null($reportObject))
            $reportObject       = new Minder_Report_Abstract();

        $reportObject->name         = $reportData['NAME'];
        $reportObject->reportFormat = $reportData['REPORT_FORMAT'];
        $reportObject->reportUri    = $reportData['REPORT_URI'];
        $reportObject->reportType   = $reportData['REPORT_TYPE'];
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $reportObject));

        $sql = 'SELECT * FROM REPORTS_TYPE WHERE CODE = ?';

        if (false !== ($types = $minder->fetchAssoc($sql, $reportObject->reportType))) {
            $reportObject->reportUrl = $types['RT_URL'] ;
            $reportObject->userName  = $types['RT_USER_ID'];
            $reportObject->password  = $types['RT_PASS_WORD'];
        }

        $sql = 'SELECT * FROM REPORTS_DETAIL WHERE REPORT_ID = ?';

        if (false !== ($details = $minder->fetchAllAssoc($sql, $reportId))) {
            foreach ($details as $detailsRow) {
                $reportObject->addReportDetail(new Minder_Report_Detail($detailsRow));
            }
        }

        return $reportObject;
    }

    public static function getInvoiceReport($companyId, $invoiceType = 'TI') {
        $minder = Minder::getInstance();
/*
        $sql = "SELECT DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = 'RPTINVOICE' AND CODE = ?";

        if (false === ($reportId = $minder->fetchOne($sql, $companyId . '|' . $invoiceType))) {
            return null;
        }
*/
        //$sql = "SELECT INVOICE_TI_REPORT_ID FROM COMPANY WHERE COMPANY_ID = ?";
        $sql = "SELECT INVOICE_%INVOICETYPE%_REPORT_ID FROM COMPANY WHERE COMPANY_ID = ?";
        $sqlWType = str_replace("%INVOICETYPE%", $invoiceType, $sql);
        if (false === ($reportId = $minder->fetchOne($sqlWType, $companyId ))) {
            return null;
        }

        return $reportId;
    }

    /**
     * @static
     * @throws Minder_Report_Factory_Exception
     * @param string $companyId
     * @param string $invoiceType
     * @return Minder_Report_Invoice
     */
    public static function makeInvoiceReportForCompany($companyId, $invoiceType = 'TI') {
        $reportId = self::getInvoiceReport($companyId, $invoiceType);

        if (is_null($reportId))
            throw new Minder_Report_Factory_Exception('Cannot find REPORT_ID for given COMPANY_ID: "' . $companyId . '" and INVOICE_TYPE: "' . $invoiceType . '" combination.');

        return self::makeReport($reportId);
    }

    /**
     * @static
     * @param string $reportId
     * @return Minder_Report_Invoice
     */
    public static function makeInvoiceReport($reportId) {
        return self::makeReport($reportId, new Minder_Report_Invoice());
    }

    public static function makeTaxInvoiceReport() {
        $minder = Minder::getInstance();
        $sql = "SELECT REPORT_ID FROM REPORTS WHERE NAME = ?";

        if (false === ($reportId = $minder->fetchOne($sql, 'REP_TAX_INVOICE')))
            throw new Minder_Report_Factory_InvoiceReport_Exception('Cannot find REPORT with name "REP_TAX_INVOICE". Check system setup.');

        return self::makeInvoiceReport($reportId);
    }
}
