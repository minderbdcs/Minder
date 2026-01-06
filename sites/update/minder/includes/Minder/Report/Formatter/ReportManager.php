<?php

class Minder_Report_Formatter_ReportManager extends Minder_Report_Formatter_Abstract
{
    protected function formatGetParamsString($getParams) {
        $tmpArray = array();
        foreach ($getParams as $paramName => $paramValue) {
            $tmpArray[] = urlencode($paramName) . '=' . urlencode($paramValue);
        }

        return implode('&', $tmpArray);
    }

    protected function _parseUri($reportUri) {
        $tmpArr = explode('?', $reportUri);

        $paramString = isset($tmpArr[1]) ? $tmpArr[1] : $tmpArr[0];

        $result = array();
        foreach (explode('&', $paramString) as $nameValuePair) {
            list($name, $value) = explode('=', $nameValuePair);
            $result[urldecode($name)] = urldecode($value);
        }

        return $result;
    }

    /**
     * @param Minder_Report_Abstract $reportInstance
     * @return Minder_Report_Result
     */
    public function makeReport($reportInstance)
    {
        if (strtolower($this->_outputFormat) != 'pdf')
            throw new Minder_Report_Formatter_Exception('Report Manager can make only PDF reports but "' . $this->_outputFormat . '" required.');

        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('preparing request......');
        $getFields = $this->_parseUri($reportInstance->reportUri);

        /**
         * @var Minder_Report_Detail $reportDetail
         */
        foreach ($reportInstance->getReportDetails() as $reportDetail) {
            $getFields[$reportDetail->queryField] = $reportDetail->queryFieldValue;
        }

        $rmClient = new Minder_ReportManager_Client();
//        $rmClient->port = 8082; //test port
        $rmClient->url  = $reportInstance->reportUrl;
        $rmClient->userName = $reportInstance->userName;
        $rmClient->password = $reportInstance->password;

        $log->done();
        try {
            $log->starting('trying to log in......');
            $rmClient->login();
            $log->done();

            $log->starting('executing report......');
            $pdfImage = $rmClient->executeReport($getFields);
            $log->done();
        } catch (Minder_ReportManager_Client_Exception $e) {
            $log->doneWithErrors($e->getMessage());
            throw new Minder_Report_Formatter_Exception('Report Manager error: ' . $e->getMessage());
        }

        return Minder_Report_Result::makeReportResult($this->_outputFormat)->setResult($pdfImage);
    }

}