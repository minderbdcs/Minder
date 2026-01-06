<?php

class Minder_Controller_Action_Helper_ExportToXls extends Zend_Controller_Action_Helper_Abstract implements Minder_ExportToInterface, Minder_ExportTo_FormatAwareInterface {

    const INTERNAL_FORMAT_EXCEL5 = 'Excel5';
    const INTERNAL_FORMAT_EXCEL2007 = 'Excel2007';

    private static $_formatMap = array(
        Minder_ExportTo_FormatAwareInterface::BIFF8 => Minder_Controller_Action_Helper_ExportToXls::INTERNAL_FORMAT_EXCEL5,
        Minder_ExportTo_FormatAwareInterface::XLS => Minder_Controller_Action_Helper_ExportToXls::INTERNAL_FORMAT_EXCEL5,
        Minder_ExportTo_FormatAwareInterface::OOXML_XLS => Minder_Controller_Action_Helper_ExportToXls::INTERNAL_FORMAT_EXCEL2007,
        Minder_ExportTo_FormatAwareInterface::XLSX => Minder_Controller_Action_Helper_ExportToXls::INTERNAL_FORMAT_EXCEL2007,
    );

    protected $_format = null;

    /**
     * @var PHPExcel
     */
    protected $_xlsObject;

    protected $_currentRow;

    public function init()
    {
        parent::init();
        $this->_currentRow = 3;
    }


    public function proceedData(array $headers, array $data)
    {
        foreach ($data as $dataRow) {
            $this->proceedDataRow($headers, $dataRow);
        }
    }

    public function passThru()
    {
        $this->_getXlsObject()->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($this->_getXlsObject(), $this->_getInternalFormat());
        $objWriter->save('php://output');
    }

    public function sendHttpHeaders()
    {
        $response = $this->getResponse();

        switch ($this->_getInternalFormat()) {
            case static::INTERNAL_FORMAT_EXCEL2007:
                $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->setHeader('Content-Disposition', 'attachment;filename="report.xlsx"');
                break;
            case static::INTERNAL_FORMAT_EXCEL5:
                $response->setHeader("Content-type", "application/vnd.ms-excel");
                $response->setHeader("Content-Disposition", 'attachment; filename="report.xls"');
                break;
            default:
                throw new Exception('Unsupported internal format "' . $this->_getInternalFormat() . '"');
        }
        $response->setHeader('Cache-Control', 'max-age=0,must-revalidate,post-check=0,pre-check=0');
    }

    public function proceedDataRow(array $headers, array $dataRow)
    {
        $sheet = $this->_getXlsObject()->setActiveSheetIndex(0);
        $row = $this->_currentRow++;
        $column = 0;
        foreach ($headers as $key => $title) {
            $sheet->setCellValueByColumnAndRow($column++, $row, $dataRow[$key]);
        }
    }

    public function proceedHeaders(array $headers)
    {
        $sheet = $this->_getXlsObject()->setActiveSheetIndex(0);

        $column = 0;
        foreach ($headers as $title) {
            $sheet->setCellValueByColumnAndRow($column, 2, $title, true);
            $column++;
        }
    }

    public function setFormat($format)
    {
        $this->_format = $format;
    }

    protected function _getFormat() {
        if (is_null($this->_format)) {
            $this->setFormat($this->_fetchFormat());
        }

        return $this->_format;
    }

    protected function _getInternalFormat() {
        $format = $this->_getFormat();

        if (!isset(static::$_formatMap[$format])) {
            throw new Exception('Unsupported format "' . $format . '"');
        }

        return static::$_formatMap[$format];
    }

    protected function _fetchFormat() {
        $minder = Minder::getInstance();
        return empty($minder->defaultControlValues['EXPORT_XLS_EXTENSION'])
            ? Minder_ExportTo_FormatAwareInterface::BIFF8
            : $minder->defaultControlValues['EXPORT_XLS_EXTENSION'];
    }

    protected function _getXlsObject() {
        if (empty($this->_xlsObject)) {
            $this->_xlsObject = new PHPExcel();
        }
        return $this->_xlsObject;
    }
}