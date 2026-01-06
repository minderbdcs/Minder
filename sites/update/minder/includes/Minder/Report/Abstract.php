<?php

class Minder_Report_Abstract {
    public $name         = '';
    public $reportType   = '';
    public $reportUri    = '';
    public $reportFormat = '';
    public $reportUrl    = '';
    public $userName     = '';
    public $password     = '';

    /**
     * @var Zend_Session_Namespace|null
     */
    static $_session   = null;

    /**
     * @var array of Minder_Report_Detail
     */
    protected $_reportDetails = array();

    /**
     * @param  Minder_Report_Detail $reportDetail
     * @return Minder_Report_Abstract
     */
    public function addReportDetail($reportDetail) {
        $this->_reportDetails[$reportDetail->getDetailId()] = $reportDetail;
        return $this;
    }

    /**
     * @param  string $queryField
     * @return Minder_Report_Detail|null
     */
    public function findDetailByQueryField($queryField) {
        /**
         * @var Minder_Report_Detail $reportDetail
         */
        foreach ($this->_reportDetails as $reportDetail)
            if ($reportDetail->queryField === $queryField)
                return $reportDetail;

        return null;
    }

    /**
     * @param  string $queryField
     * @param  mixed $value
     * @return Minder_Report_Abstract
     */
    public function setQueryFieldValue($queryField, $value) {
        $reportDetail = $this->findDetailByQueryField($queryField);
        if (!is_null($reportDetail)) {
            $reportDetail->queryFieldValue = $value;
            $this->addReportDetail($reportDetail);
        }
        return $this;
    }

    /**
     * @param  $queryField
     * @return mixed|null
     */
    public function getQueryFieldValue($queryField) {
        $reportDetail = $this->findDetailByQueryField($queryField);
        return (is_null($reportDetail)) ? null : $reportDetail->queryFieldValue;
    }

    /**
     * @return array
     */
    public function getQueryFieldValues() {
        $result = array();
        /**
         * @var Minder_Report_Detail $reportDetail
         */
        foreach ($this->_reportDetails as $reportDetail) {
            $result[$reportDetail->queryField] = $reportDetail->queryFieldValue;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getReportDetails() {
        return $this->_reportDetails;
    }

    /**
     * @param Minder_Report_Formatter_Abstract | null $reportFormatter
     * @return Minder_Report_Result
     */
    public function makeReport($reportFormatter = null) {
        if (is_null($reportFormatter))
            $reportFormatter = Minder_Report_Formatter_Factory::makeReportFormatter($this->reportType, $this->reportFormat);

        return $reportFormatter->makeReport($this);
    }

    /**
     * @return string - report PDF image
     */
    public function getPdfImage() {
        return $this->makeReport(Minder_Report_Formatter_Factory::makeReportFormatter($this->reportType, 'pdf'))->getResult();

    }

    /**
     * @param  Minder_Printer_Abstract $printer
     * @return void
     */
    public function printReport($printer) {
        $printer->printPdfImage($this->getPdfImage());
    }

    /**
     * @param array $values
     * @param array $map
     * @return Minder_Report_Abstract
     */
    public function fillQueryFieldsWithMap(array $values = array(), array $map = array()) {
        foreach ($map as $valuesField => $queryField)
            $this->setQueryFieldValue($queryField, $values[$valuesField]);

        return $this;
    }

    /**
     * @param array $map
     * @return array
     */
    public function fillStaticParams(array $map = array()) {
        foreach ($map as $source => $queryField) {
            $matches = array();
            if (preg_match('/^CONST:(.*)/', $source, $matches)) {
                $this->setQueryFieldValue($queryField, $matches[1]);
                unset($map[$source]);
            }
        }
        
        return $map;
    }

    /**
     * @return Zend_Session_Namespace
     */
    static protected function _getSession() {
        if (is_null(self::$_session))
            self::$_session = new Zend_Session_Namespace('reports');

        return self::$_session;
    }

    /**
     * @return array
     */
    static protected function _loadSavedPdfImages() {
        $session = self::_getSession();

        $result = array();
        if (isset($session->reportImages))
            $result = $session->reportImages;

        return $result;
    }

    /**
     * @param array $reportImages
     * @return Minder_Report_Abstract
     */
    protected function _savePdfImages(array $reportImages) {
        $this->_getSession()->reportImages = $reportImages;
        return $this;
    }

    /**
     * @param string $pdfImage
     * @return string
     */
    protected function _savePdfImage($pdfImage) {
        $filePath = tempnam(sys_get_temp_dir(), 'report_') . '.pdf';

        file_put_contents($filePath, $pdfImage);
        chmod($filePath, 0640);

        $savedImages = $this->_loadSavedPdfImages();

        $imageId = uniqid('pdf_image_', true);

        $savedImages[$imageId] = $filePath;
        $this->_savePdfImages($savedImages);

        return $imageId;
    }

    /**
     * @throws Minder_Report_Exception
     * @param string $imageId
     * @return string
     */
    static public function loadSavedPdfImage($imageId) {
        $savedImages = self::_loadSavedPdfImages();
        if (!isset($savedImages[$imageId]))
            throw new Minder_Report_Exception('Pdf File not found.');

        $savedFileName = $savedImages[$imageId];

        if (!is_readable($savedFileName))
            throw new Minder_Report_Exception('Pdf File not found.');

        if (false === ($pdfImage = file_get_contents($savedFileName)))
            throw new Minder_Report_Exception('Pdf File not found.');

        return $pdfImage;
    }

    /**
     * @param  Minder_Printer_Abstract $printer
     * @return string
     */
    public function printAndSavePdfImage($printer) {
        $pdfImage = $this->getPdfImage();
        $printer->printPdfImage($pdfImage);
        return $this->_savePdfImage($pdfImage);
    }

    public function preparePdfImage() {
        return $this->_savePdfImage($this->getPdfImage());
    }
}
