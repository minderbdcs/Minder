<?php
class Minder_SysScreen_Model_PickInvoice extends Minder_SysScreen_Model_Editable
{
    protected $warnings = array();
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getTotalInvoiceValue() {
        return $this->getAggregateValue('SUM(PICK_INVOICE.INV_TOTAL_AMOUNT)');
    }
    
    public function selectInvoiceNo($rowOffset, $itemCountPerPage) {
        $invoiceNo = array();
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PICK_INVOICE.INVOICE_NO ');
        if (is_array($result) && count($result) > 0)
            $invoiceNo = array_map(create_function('$item', 'return $item["INVOICE_NO"];'), $result);
        
        return $invoiceNo;
    }
    
    public function selectInvoiceId($rowOffset, $itemCountPerPage) {
        $invoiceId = array();
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PICK_INVOICE.INVOICE_ID ');
        if (is_array($result) && count($result) > 0)
            $invoiceId = array_map(create_function('$item', 'return $item["INVOICE_ID"];'), $result);
        
        return $invoiceId;
    }
    
    public function selectCompleteInvoiceExt($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryDataExt($rowOffset, $itemCountPerPage, 'PICK_INVOICE.*');
    }
    
    public function updateRecords($dataset) {
        $this->validateData($dataset, 'update');
        $oldConditions = $this->getConditions();
        
        $updatedRecords = array();
        $minder = Minder::getInstance();
        
        list($tmpWhId, $tmpLocnId) = $minder->getDeviceWhAndLocation();
        
        foreach ($dataset as $tmpRowId => $dataRow) {
            $tmpConditions = $this->makeConditionsFromId($tmpRowId);
            $this->setConditions($tmpConditions);
            
            $originalRow   = $this->selectCompleteInvoiceExt(0, 1);
            
            if (empty($originalRow))
                throw new Minder_SysScreen_Model_PickInvoice_Exception('Row #' . $tmpRowId . ' does not exists.');
            
            $originalRow = current($originalRow); //as we get array of rows
            $updatingFields = array();
            //walk throw datarow and test for changed fields
            foreach ($dataRow as $dataField) {
                $fullName = strtoupper($dataField['table'] . '.' . $dataField['name']);
                if (!array_key_exists($fullName, $originalRow))
                    throw new Minder_SysScreen_Model_PickInvoice_Exception('Cannot update field ' . $fullName . '.');

                if ($originalRow[$fullName] != $dataField['value']) {
                    $updatingFields[$fullName] = $dataField['value'];
                }
                $originalRow[$fullName] = $dataField['value'];
            }
            
            if (isset($updatingFields['PICK_INVOICE.PICK_ORDER'])) unset($updatingFields['PICK_INVOICE.PICK_ORDER']);
            if (isset($updatingFields['PICK_INVOICE.INVOICE_TYPE'])) unset($updatingFields['PICK_INVOICE.INVOICE_TYPE']);
            if (isset($updatingFields['PICK_INVOICE.INVOICE_NO'])) unset($updatingFields['PICK_INVOICE.INVOICE_NO']);
            if (isset($updatingFields['PICK_INVOICE.INVOICE_ID'])) unset($updatingFields['PICK_INVOICE.INVOICE_ID']);

            $pickInvoice = new Minder_PickInvoice();
            $pickInvoice->freight             = isset($originalRow['PICK_INVOICE.FREIGHT']) ? $originalRow['PICK_INVOICE.FREIGHT'] : 0;
            $pickInvoice->taxRate             = isset($originalRow['PICK_INVOICE.TAX_RATE']) ? $originalRow['PICK_INVOICE.TAX_RATE'] : 0;
            $pickInvoice->adminFeeAmount      = isset($originalRow['PICK_INVOICE.ADMIN_FEE_AMOUNT']) ? $originalRow['PICK_INVOICE.ADMIN_FEE_AMOUNT'] : 0;
            $pickInvoice->adminFeeRate        = isset($originalRow['PICK_INVOICE.ADMIN_FEE_RATE']) ? $originalRow['PICK_INVOICE.ADMIN_FEE_RATE'] : 0;
            $pickInvoice->paidAmount          = isset($originalRow['PICK_INVOICE.AMOUNT_PAID']) ? $originalRow['PICK_INVOICE.AMOUNT_PAID'] : 0;
            $pickInvoice->subTotalTax         = isset($originalRow['PICK_INVOICE.SUB_TOTAL_TAX']) ? $originalRow['PICK_INVOICE.SUB_TOTAL_TAX'] : 0;
            $pickInvoice->subTotalAmount      = isset($originalRow['PICK_INVOICE.SUB_TOTAL_AMOUNT']) ? $originalRow['PICK_INVOICE.SUB_TOTAL_AMOUNT'] : 0;
            
            $pickInvoice->calculateInvoice();
            
            $updatingFields['PICK_INVOICE.FREIGHT_TAX_AMOUNT']  = $pickInvoice->freightTaxAmount;
            $updatingFields['PICK_INVOICE.ADMIN_TAX_AMOUNT']    = $pickInvoice->adminTaxAmount;
            $updatingFields['PICK_INVOICE.TAX_AMOUNT']          = $pickInvoice->taxAmount;
            $updatingFields['PICK_INVOICE.DUE_AMOUNT']          = $pickInvoice->dueAmount;
            $updatingFields['PICK_INVOICE.INV_TOTAL_AMOUNT']    = $pickInvoice->totalAmount;
            
            $transaction = new Transaction_PINVU();
            $transaction->fillFieldsMap();
            $transaction->printerDevice   = $minder->limitPrinter;
            $transaction->invoiceQty      = 0; 
            $transaction->whId            = $tmpWhId;       
            $transaction->locationId      = $tmpLocnId;
            $transaction->orderNo         = $originalRow['PICK_INVOICE.PICK_ORDER'];
            $transaction->invoiceType     = $originalRow['PICK_INVOICE.INVOICE_TYPE'];
            $transaction->invoiceNo       = $originalRow['PICK_INVOICE.INVOICE_NO'];
            
            $transactionReferenceFields = $transaction->getReferenceFields();
            $directUpdateFields         = array_diff(array_keys($updatingFields), $transactionReferenceFields);

            foreach ($transactionReferenceFields as $fieldFullName) {
                $tmpArr   = explode('.', $fieldFullName);
                $tmpTable = $tmpArr[0];
                $tmpName  = $tmpArr[1];
                
                if (isset($updatingFields[$fieldFullName])) {
                    $transaction->setField($tmpName, $tmpTable, $updatingFields[$fieldFullName]);
                } else {
                    if (isset($originalRow[$fieldFullName]) && !empty($originalRow[$fieldFullName])) {
                        $transaction->setField($tmpName, $tmpTable, $originalRow[$fieldFullName]);
                    }
                }
            }
            
            if (false === $minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    ')) {
                throw new Minder_SysScreen_Model_PickInvoice_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $minder->lastError);
            }
            
            if (count($directUpdateFields) > 0) {
                $this->warnings[] = 'Some fields cannot be updated through PINV transaction: ' . implode(', ', $directUpdateFields) . '. Check REF_CODE for PICK_INVOICE table.';
            }
            
            $updateSql = 'UPDATE PICK_INVOICE SET ' . PHP_EOL;
            $queryArgs = array();
            foreach ($directUpdateFields as $fieldFullName) {
                $updateSql   .= $fieldFullName . ' = ?, ';
                $queryArgs[]  = trim($originalRow[$fieldFullName]);
            }
            
            $updateSql = substr($updateSql, 0, -2);
            
            if (count($directUpdateFields) > 0) {
                $updateSql   .= 'WHERE INVOICE_ID = ?';
                $queryArgs[]  = $originalRow['PICK_INVOICE.INVOICE_ID'];
                
                if (false === $minder->execSQL($updateSql, $queryArgs)) 
                    throw new Minder_SysScreen_Model_PickInvoice_Exception('Errors updating PICK_INVOICE: ' . $minder->lastError);
            }
            
            $updatedRecords[] = $originalRow['PICK_INVOICE.INVOICE_ID'];
        }
        
        $this->setConditions($oldConditions);
        
        return $updatedRecords;
    }

    public function hold() {
        $invoiceIds = $this->selectInvoiceId(0, count($this));
        
        $invoiceHeld = array();
        
        $sql = 'UPDATE PICK_INVOICE SET INVOICE_STATUS = ? WHERE INVOICE_ID = ?';
        $minder = Minder::getInstance();
        foreach ($invoiceIds as $invoiceId) {
            $minder->execSQL($sql, array('HD', $invoiceId));
            $invoiceHeld[] = $invoiceId;
        }
        
        return $invoiceHeld;
    }

    public function createRecords($dataset) {
        throw new Minder_SysScreen_Model_PickInvoice_Exception('Not implemented');
    }
    
    public function getWarnings() {
        return $this->warnings;
    }

    /**
     * @param int $rowOffset
     * @param int $itemCountPerPage
     * @return array
     */
    protected function _selectDataForPrinting($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_INVOICE.PICK_ORDER, PICK_INVOICE.PDF_IMAGE, PICK_INVOICE.INVOICE_ID');
    }

    /**
     * @param PickOrder $pickOrder
     * @return string
     */
    protected function _getPdfImage($invoiceId, $companyId, $paramsMap, $invoiceType) {
        $invoiceReport            = Minder_Report_Factory::makeInvoiceReportForCompany($companyId, $invoiceType);
        $paramsMap = $invoiceReport->fillStaticParams($paramsMap);

        if (!empty($paramsMap)) {
            $originalConditions = $this->getConditions();
            $this->addConditions(array('PICK_INVOICE.INVOICE_ID = ?' => array($invoiceId)));
            foreach ($this->selectArbitraryDataExt(0, 1, 'DISTINCT ' . implode(', ', array_keys($paramsMap))) as $resultRow) {
                $invoiceReport->fillQueryFieldsWithMap($resultRow, $paramsMap);
            }
            $this->setConditions($originalConditions);
        }
        return $invoiceReport->getPdfImage();
    }

    /**
     * @throws Exception
     * @param PickOrder $pickOrder
     * @param string $pdfImage
     * @return array
     */
    protected function _savePdfImage($pickOrder, $pdfImage) {
        /**
         * @var Company $companyObject
         */
        $companyObject = Minder::getInstance()->getCompany($pickOrder->companyId);
        if (is_null($companyObject))
            throw new Exception('Company #' . $pickOrder->companyId . ' not found.');

        $tmpDateStr = (empty($pickOrder->approvedDespDate)) ? $pickOrder->createDate : $pickOrder->approvedDespDate;

        if (false === ($tmpTimestamp = strtotime($tmpDateStr)))
            throw new Exception('Bad Date provided "' . $tmpDateStr . '"');

        $dateArray = getdate($tmpTimestamp);
        $tmpMonth  = (strlen($dateArray['mon']) < 2) ? '0' . $dateArray['mon'] : $dateArray['mon'];
        $uniqNo    = $companyObject->saveInvoiceImage($pickOrder->pickOrder, $dateArray['year'], $tmpMonth, $pdfImage);

        return array(
            'pickOrder' => $pickOrder->pickOrder,
            'uniqNo' => $uniqNo,
            'year' => $dateArray['year'],
            'month' => $tmpMonth
        );
    }

    protected function _updatePdfImage($invoiceId, $pdfImage) {
        $sql = "
            UPDATE PICK_INVOICE SET
                PDF_IMAGE = ?
            WHERE
                INVOICE_ID = ?
        ";

        Minder::getInstance()->execSQL($sql, array($pdfImage, $invoiceId));
    }

    /**
     * @param $invoiceType
     * @param $paramMap
     * @param Minder_Printer_Abstract $printer
     * @return array
     */
    public function printInvoice($invoiceType, $paramMap, $printer) {
        $savedInvoices = array();

        $totalRows = count($this);
        if ($totalRows < 1)
            return $savedInvoices;

        $dataForPrinting = $this->_selectDataForPrinting(0, $totalRows);

        foreach ($dataForPrinting as $dataRow) {
            $pickOrder = Minder::getInstance()->getPickOrder($dataRow['PICK_ORDER']);
            $pdfImage  = $this->_getPdfImage($dataRow['INVOICE_ID'], $pickOrder->companyId, $paramMap, $invoiceType);

            $printer->printPdfImage($pdfImage);
            $savedInvoices[] = $this->_savePdfImage($pickOrder, $pdfImage);
        }

        return $savedInvoices;
    }

    /**
     * @deprecated
     * @param $invoiceId
     * @param $reportId
     * @param $paramsMap
     * @return string
     */
    protected function _runReport($invoiceId, $reportId, $paramsMap) {
        $report    = Minder_Report_Factory::makeReport($reportId);
        $paramsMap = $report->fillStaticParams($paramsMap);

        if (!empty($paramsMap)) {
            $originalConditions = $this->getConditions();
            $this->addConditions(array('PICK_INVOICE.INVOICE_ID = ?' => array($invoiceId)));
            foreach ($this->selectArbitraryDataExt(0, 1, 'DISTINCT ' . implode(', ', array_keys($paramsMap))) as $resultRow) {
                $report->fillQueryFieldsWithMap($resultRow, $paramsMap);
            }
            $this->setConditions($originalConditions);
        }

        return $report->getPdfImage();
    }

    /**
     * @deprecated
     * @param $printer
     * @param $reportId
     * @param $paramsMap
     * @return array
     */
    public function printInvoiceReport($printer, $reportId, $paramsMap) {
        $savedInvoices = array();

        $totalRows = count($this);
        if ($totalRows < 1)
            return $savedInvoices;

        $dataForPrinting = $this->_selectDataForPrinting(0, $totalRows);

        foreach ($dataForPrinting as $dataRow) {
            $pickOrder = Minder::getInstance()->getPickOrder($dataRow['PICK_ORDER']);

            $pdfImage = '';
            if (!empty($dataRow['INVOICE_ID'])) {
                $pdfImage = $this->_runReport($dataRow['INVOICE_ID'], $reportId, $paramsMap);
                $this->_updatePdfImage($dataRow['INVOICE_ID'], $pdfImage);
            }

            $printer->printPdfImage($pdfImage);
            $savedInvoices[] = $this->_savePdfImage($pickOrder, $pdfImage);
        }

        return $savedInvoices;
    }
}

class Minder_SysScreen_Model_PickInvoice_Exception extends Minder_SysScreen_Model_Exception {}