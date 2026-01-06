<?php

class Minder_Report_Formatter_Factory {

    /**
     * @static
     * @throws Minder_Report_Formatter_Exception
     * @param string $reportType
     * @param string $reportFormat
     * @return Minder_Report_Formatter_Abstract
     */
    public static function makeReportFormatter($reportType, $reportFormat) {
        $reportType = strtoupper($reportType);
        switch ($reportType) {
            case 'JS':
                return new Minder_Report_Formatter_JasperReports($reportFormat);
            case 'RM':
                return new Minder_Report_Formatter_ReportManager($reportFormat);
            default:
                throw new Minder_Report_Formatter_Exception('Cannot create Report Formatter for Report Type "' . $reportType . '" and Report Format "' . $reportFormat . '" combination.');
        }
    }
}