<?php

class Minder_ImportPurchaseOrderLines {
    
    protected $warnings = array();
    protected $messages = array();
    
    protected $purchaseOrder       = '';
    protected $purchaseLineNo      = '';
    protected $orderWhId           = '';
    protected $orderPersonId       = '';
    protected $orderGrn            = '';
    protected $grnAlreadyUpdated   = false;
    protected $orderAlreadyUpdated = false;
    protected $ignoredImport       = false;
    
    protected $fileHandle = null;
    
    const CSV_COLUNS_COUNT = 13;
    
    protected $csvWhId = null;
    protected $csvPoContainerNo = null;
    protected $csvGrnVesselName = null;
    protected $csvGrnVoyageNo = null;
    protected $csvGrnDueDate = null;
    protected $csvGrnSupplierId = null;
    protected $csvOwnerId = null;
    protected $csvProdId = null;
    protected $csvPslOrderQty = null;
    protected $csvPslOther1 = null;
    protected $csvPslOther2 = null;
    protected $csvPslOtherDate3 = null;
    protected $csvPslOtherDate4 = null;
    
    protected $previouseProdId = null;
    
    protected $insertedProdIds = array();
    
    protected $minder = null;
    
    public function __construct($purchaseOrder = '') {
        $this->setPurchaseOrder($purchaseOrder);
    }
    
    public function setPurchaseOrder($purchaseOrder) {
        $this->purchaseOrder = $purchaseOrder;
    }
    
    protected function getPurchaseOrderDetails() {
        $minder              = Minder::getInstance();
        $poDetails           = $minder->getPurchaseOrderById($this->purchaseOrder);
        $this->orderWhId     = $poDetails->items['PO_RECEIVE_WH_ID'];
        $this->orderPersonId = $poDetails->items['PERSON_ID'];
        $this->orderGrn      = $poDetails->items['PO_GRN'];
    }
    
    protected function parseDate($date) {
        $result = str_replace('/', '-', $date);         
        $dateArray = explode('-', $result);
                
        if($dateArray[0] > 12 || strlen($dateArray[2]) == 4){
            $result = $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
        }
        return $result;        
    }
    
    protected function parseCsvLine($csvLine = array()) {
        $csvColumnsCount = count($csvLine);
        if ($csvColumnsCount < self::CSV_COLUNS_COUNT)
            throw new Minder_ImportPurchaseOrderLines_Exception('Wrong columns count in given file. ' . self::CSV_COLUNS_COUNT . ' expected, ' . $csvColumnsCount . ' found. Check import file.');
            
        list(
            $this->csvWhId,
            $this->csvPoContainerNo,
            $this->csvGrnVesselName,
            $this->csvGrnVoyageNo,
            $this->csvGrnDueDate,
            $this->csvGrnSupplierId,
            $this->csvOwnerId,
            $this->csvProdId,
            $this->csvPslOrderQty,
            $this->csvPslOther1,
            $this->csvPslOther2,
            $this->csvPslOtherDate3,
            $this->csvPslOtherDate4
        ) = $csvLine;
        
        $this->csvGrnDueDate    = $this->parseDate($this->csvGrnDueDate);
        $this->csvPslOtherDate3 = $this->parseDate($this->csvPslOtherDate3);
        $this->csvPslOtherDate4 = $this->parseDate($this->csvPslOtherDate4);
    }
    
    protected function createGrn() {
        $grnBuilder = new Minder_GrnBuilder();
        
        $grnBuilder->purchaseOrder  = $this->purchaseOrder;
        $grnBuilder->carrierId      = $this->csvWhId . 'INTRANST';
        $grnBuilder->containerNo    = $this->csvPoContainerNo;
        $grnBuilder->pslOrderQty    = $this->csvPslOrderQty;
        $grnBuilder->ownerId        = $this->csvOwnerId;
        $grnBuilder->supplierId     = $this->orderPersonId;
        $grnBuilder->palletOwnerId  = 'N';
        $grnBuilder->crateQty       = 0;
        $grnBuilder->deliveryTypeId = 'IP';
        $grnBuilder->labelQty       = 0;
        
        $grnBuilder->nonEmptyContainerNoRequired = true;
        
        $grnBuilder->doBuild();
        
        $this->orderGrn   = $grnBuilder->grn;
        
        $this->messages[] ='GRNDI: ' . $grnBuilder->grndiMessage;
        $this->messages[] ='GRNDL: ' . $grnBuilder->grndlMessage;
    }
    
    protected function updateGrn() {
        if ($this->grnAlreadyUpdated)
            return;
        
        $minder = Minder::getInstance();    
        $minder->updateGrn($this->orderGrn, 'GRN_DUE_DATE', $this->csvGrnDueDate);
        $minder->updateGrn($this->orderGrn, 'VOYAGE_NO',    $this->csvGrnVoyageNo);
        $minder->updateGrn($this->orderGrn, 'VESSEL_NAME',  $this->csvGrnVesselName);
        $minder->updateGrn($this->orderGrn, 'CONTAINER_NO', $this->csvPoContainerNo);
        $minder->updateGrn($this->orderGrn, 'OWNER_ID',     $this->csvGrnSupplierId);
        $minder->updateGrn($this->orderGrn, 'RETURN_ID',    $this->csvGrnSupplierId);
        
        $this->grnAlreadyUpdated = true;
    }
    
    protected function updateOrder() {
        if ($this->orderAlreadyUpdated)
            return;

        $clause =   array('PURCHASE_ORDER = ? ' => $purchaseOrderNo);
            
        $minder = Minder::getInstance();    
        $minder->updatePurchaseOrderField($clause, 'PO_CONTAINER_NO', $this->csvPoContainerNo);
        $minder->updatePurchaseOrderField($clause, 'PO_VESSEL_NAME', $this->csvGrnVesselName);
        $minder->updatePurchaseOrderField($clause, 'PO_VOYAGE_NO', $this->csvGrnVoyageNo);
        $minder->updatePurchaseOrderField($clause, 'PO_RECEIVE_WH_NAME', $this->csvWhId);
            
        $this->orderAlreadyUpdated = true;
    }
    
    protected function createPoLine() {
        $productInfo                             = current($this->minder->getProductInfo($this->csvProdId));
        $this->purchaseLineNo                    = $this->minder->getPurchaseOrderLineId($this->purchaseOrder);
                    
        $line['PURCHASE_ORDER, ']                = $this->purchaseOrder;
        $line['PO_LINE, ']                       = $this->purchaseLineNo;
        $line['PROD_ID, ']                       = $this->csvProdId;
        $line['UOM_ORDER, ']                     = 'EA';
        $line['PO_LINE_STATUS, ']                = 'IN';
        $line['COMMENTS, ']                      = 'Imported by: '. $this->minder->userId .' '.date('Y-m-d H:i:s');
        $line['PO_LINE_DESCRIPTION, ']           = $productInfo['SHORT_DESC'];
        $line['PO_LINE_DUE_DATE, ']              = $this->csvGrnDueDate;
                    
        $result                                  = $this->minder->addPurchaseOrderLine($line);
                    
        $this->previouseProdId                   = $this->csvProdId;
        $this->insertedProdIds[$this->csvProdId] = $this->purchaseLineNo;
    }
    
    protected function createPoSubLine() {
        if (
            empty($this->csvPslOrderQty)
            && empty($this->csvPslOther1)
            && empty($this->csvPslOther2)
            && empty($this->csvPslOtherDate3)
            && empty($this->csvPslOtherDate4)
        ) return; 
       
        $data['PURCHASE_ORDER, ']   = $this->purchaseOrder;
        $data['PO_LINE, ']          = $this->purchaseLineNo;
        $data['PSL_OTHER1, ']       = $this->csvPslOther1;
        $data['PSL_OTHER2, ']       = $this->csvPslOther2;
        $data['PSL_OTHER_DATE3, ']  = $this->csvPslOtherDate3;
        $data['PSL_OTHER_DATE4, ']  = $this->csvPslOtherDate3;
        $data['PSL_ORDER_QTY, ']    = $this->csvPslOrderQty;
        $data['PSL_STATUS, ']       = 'IN';
        $data['USER_ID, ']          = $this->minder->userId; 
        $data['DEVICE_ID, ']        = $this->minder->deviceId;
        $data['CREATE_DATE, ']      = date('Y-m-d H:i:s');
        $data['LAST_UPDATE_DATE, '] = date('Y-m-d H:i:s');
        $data['LAST_UPDATE_BY, ']   = $this->minder->userId;
               
        $result = $this->minder->addPurcahseDetailLine($data);
        if(!$result){
            throw new Minder_ImportPurchaseOrderLines_Exception($this->minder->lastError);
        }
    }
    
    protected function addIgnoredImportWarningToGrn() {
        $this->warnings[] = 'Check Import - ignored rows.';
        $sql = 'UPDATE GRN SET GRN.COMMENTS = GRN.COMMENTS || ? WHERE GRN.GRN = ?';
        $this->minder->execSQL($sql, array('Check Import - ignored rows.', $this->orderGrn));
    }
    
    protected function addWrongWhIdWarning($fileLineNo) {
        $this->warnings[] = 'Error importing Csv File Line #' . $fileLineNo . '. WH_ID "' . $this->csvWhId . '" does not match PO_RECEIVE_WH_ID "' . $this->orderWhId . '".';
    }
    
    protected function updateImportedLinesQty() {
        // update PO_LINE TOTAL & ORIGINAL_QTY
        foreach($this->insertedProdIds as $purchaseLineNo){
            $subLineQtySum  =   $this->minder->getDetailQtySum($this->purchaseOrder, $purchaseLineNo);
            $clause         =   array(
                                    'PO_LINE_QTY = ?, ' => $subLineQtySum,  
                                    'ORIGINAL_QTY = ?, '  => $subLineQtySum
            );
                
            $this->minder->updatePurchaseOrderLineByOrderId($clause, $this->purchaseOrder, $purchaseLineNo);
        }    
    }
    
    protected function importLines() {
        $fileLineNo = 1;
        while($nextrCsvLine = fgetcsv($this->fileHandle)) {
            $this->parseCsvLine($nextrCsvLine);
            
            if ($this->csvWhId != $this->orderWhId) {
                $this->addWrongWhIdWarning($fileLineNo);
                continue;
            }
            
            if (empty($this->orderGrn)) {
                $this->createGrn();
            }
            
            $this->updateGrn();
            $this->updateOrder();
            
            try {
                if ($this->previouseProdId != $this->csvProdId) {
                    if (!isset($this->insertedProdIds[$this->csvProdId])) {
                        $this->createPoLine();
                    } else {
                        $this->ignoredImport = true;
                        continue;
                    }
                }
            
                $this->createPoSubLine();
                
                $this->messages[] = 'Csv File Line #' . $fileLineNo . ' imported successfuly.';
            } catch (Exception $e) {
                $this->warnings[] = 'Error importing Csv File Line #' . $fileLineNo . '. ' . $e->getMessage();
            }
            
            $fileLineNo++;
        }
        
        $this->updateImportedLinesQty();

        if ($this->ignoredImport)
            $this->addIgnoredImportWarningToGrn();
            
    }

    protected function openFile($file) {
        if (!file_exists($file))
            throw new Minder_ImportPurchaseOrderLines_Exception('File "' . $file . '" not exists.');
        
        if (!is_readable($file)) 
            throw new Minder_ImportPurchaseOrderLines_Exception('Cannot open file "' . $file . '" for reading.');
            
        if (false === ($this->fileHandle = fopen($file, 'r')))
            throw new Minder_ImportPurchaseOrderLines_Exception('Cannot open file "' . $file . '" for reading.');
    }
    
    protected function closeFile() {
        if (!is_null($this->fileHandle))
            fclose($this->fileHandle);
    }
    
    public function doImport($file) {
        try {
            $this->minder = Minder::getInstance();
            $this->getPurchaseOrderDetails();
            $this->openFile($file);
            $this->importLines();
            $this->closeFile();
        } catch (Exception $e) {
            $this->closeFile();
            throw $e;
        }
    }
    
    public function getWarnings() {
        return $this->warnings;
    }
    
    public function getMessages() {
        return $this->messages;
    }
}

class Minder_ImportPurchaseOrderLines_Exception extends Minder_Exception {}