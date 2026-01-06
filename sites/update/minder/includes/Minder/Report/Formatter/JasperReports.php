<?php

class Minder_Report_Formatter_JasperReports extends Minder_Report_Formatter_Abstract
{

    /**
     * @throws Minder_Report_Formatter_Exception
     * @param Minder_Report_Abstract $reportInstance
     * @return Minder_Report_Result
     */
    public function makeReport($reportInstance)
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $reportParams = array();

        $reportParams['uri']    = $reportInstance->reportUri;
        $reportParams['format'] = strtoupper($this->_outputFormat);

        /**
         * @var Minder_Report_Detail $reportDetails
         */
        foreach ($reportInstance->getReportDetails() as $reportDetails) {
            $reportParams[$reportDetails->queryField] = strval($reportDetails->queryFieldValue);
        }
        $log->info(print_r($reportParams, true));
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $reportParams));

        $minder = Minder::getInstance();

        //$soap = new Jasper_SoapWrapper($minder->userId, $minder->deviceId, '127.0.0.1', true);
        $log->info(print_r($reportParams, true));
        $log->info(print_r($reportInstance, true));

        $soap = new Jasper_SoapWrapper($minder->userId, $minder->deviceId, $reportInstance->reportUrl, '127.0.0.1', true);

        if (!$soap->jasperLogin()) {
            $log->info("failed to log into jasper " . $soap->lastError);
            throw new Minder_Report_Formatter_Exception('Cannot login to Jasper Server: ' . $soap->lastError);
        } else {
            $log->info("logged into jasper OK");
        }

        if (false === ($pdfImage = $soap->jasperExecuteReport($reportParams)))  {
            throw new Minder_Report_Formatter_Exception('Error executing Jasper Report: ' . $soap->lastError);
        }

        return Minder_Report_Result::makeReportResult($this->_outputFormat)->setResult($pdfImage);
    }
    
}
