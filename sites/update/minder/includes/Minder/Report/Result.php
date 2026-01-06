<?php

class Minder_Report_Result implements Minder_Report_Result_Interface
{
    /**
     * @var string
     */
    protected $_outputFormat;

    /**
     * @var mixed
     */
    protected $_result;

    /**
     * @param string $outputFormat
     */
    private function __construct($outputFormat) {
        $this->_outputFormat = $outputFormat;
    }

    /**
     * @return string
     */
    public function getOutputFormat()
    {
        return $this->_outputFormat;
    }

    /**
     * @param string $reportResult
     * @return Minder_Report_Result
     */
    public function setResult($reportResult)
    {
        $this->_result = $reportResult;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @static
     * @throws Minder_Report_Result_Exception
     * @param string $outputFormat
     * @return Minder_Report_Result
     */
    public static function makeReportResult($outputFormat) {
        $outputFormat = strtoupper($outputFormat);
        switch ($outputFormat) {
            case 'PDF':
                return new Minder_Report_Result($outputFormat);
            default:
                throw new Minder_Report_Result_Exception('Unsupported Report Output Format "' . $outputFormat . '".');
        }
    }
}