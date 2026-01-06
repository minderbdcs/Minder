<?php

class Minder_CreateSsnsFromPurchaseSublines {
    
    protected $ssnId = '';
    protected $orderNo = '';
    protected $orderLineNo = '';
    
    protected $orderGrn = '';
    protected $orderContainerNo = '';
    protected $orderPersonId = '';
    protected $orderReceiver = '';
    protected $orderReceiveWhId = '';
    
    protected $pslOrderQty = '';
    protected $pslOther1 = '';
    protected $pslOther2 = '';
    protected $pslOtherDate3 = '';
    protected $pslOtherDate4 = '';
    protected $recordId    = '';
    
    protected $lineProdId = '';
    protected $lineOriginalQty = '';
    
    protected $issnId = null;
    
    protected $messages = array();
    protected $warnings = array();
    
    protected $whReceiveLocation = null;
    
    protected $minder = null;
    
    public function __construct() {
        
    }
    
    protected function getSublineDetails($recordId) {
        $this->recordId             = $recordId;
        $purchaseOrderSubLineData   =   $this->minder->getPurchaseDetailLineById($recordId);
        
        $this->ssnId = (is_null($purchaseOrderSubLineData['SSN_ID'])) ? '' : $purchaseOrderSubLineData['SSN_ID'];
        
        $this->orderNo       =   $purchaseOrderSubLineData['PURCHASE_ORDER'];
        $this->orderLineNo   =   $purchaseOrderSubLineData['PO_LINE'];
        $this->pslOrderQty   =   $purchaseOrderSubLineData['PSL_ORDER_QTY'];
        $this->pslOther1     =   $purchaseOrderSubLineData['PSL_OTHER1'];
        $this->pslOther2     =   $purchaseOrderSubLineData['PSL_OTHER2'];
        $this->pslOtherDate3 =   $purchaseOrderSubLineData['PSL_OTHER_DATE3'];
        $this->pslOtherDate4 =   $purchaseOrderSubLineData['PSL_OTHER_DATE4'];
    }
    
    protected function getOrderDetails() {
        $purchaseOrderData      =   $this->minder->getPurchaseOrderById($this->orderNo);

        $this->orderGrn         =   $purchaseOrderData['PO_GRN'];
        $this->orderContainerNo =   $purchaseOrderData['PO_CONTAINER_NO'];
        $this->orderPersonId    =   $purchaseOrderData['PERSON_ID'];
        $this->orderReceiver    =   $purchaseOrderData['PO_RECEIVER'];
        $this->orderReceiveWhId =   $purchaseOrderData['PO_RECEIVE_WH_ID'];
    }
    
    protected function getOrderLineDetails() {
        $purchaseOrderLineData      =   current($this->minder->getPurchaseOrderLineById($this->orderNo, $this->orderLineNo));
        $this->lineProdId = $purchaseOrderLineData['PROD_ID'];
        $this->lineOriginalQty = $purchaseOrderLineData['ORIGINAL_QTY'];
    }
    
    protected function createGrn() {
        $grnBuilder = new Minder_GrnBuilder();
        
        $grnBuilder->purchaseOrder  = $this->orderNo;
        $grnBuilder->carrierId      = $this->orderReceiveWhId . 'INTRANST';
        $grnBuilder->containerNo    = $this->orderContainerNo;
        $grnBuilder->palletOwnerId  = 'U';
        $grnBuilder->pslOrderQty    = '0';
        $grnBuilder->crateOwnerId   = 'N';
        $grnBuilder->supplierId     = $this->orderPersonId;
        $grnBuilder->crateQty       = '1';
        $grnBuilder->ownerId        = $this->orderReceiveWhId . 'AMAVO';
        $grnBuilder->deliveryTypeId = 'IP';
        $grnBuilder->labelQty       = 1;
        
        $grnBuilder->doBuild();
        
        $this->orderGrn             = $grnBuilder->grn;
    }
    
    protected function runGrnvp() {
        $transaction                 =   new Transaction_GRNVP();
               
        $transaction->locationId     =   $this->getWhReceiveLocation();
        $transaction->productId      =   $this->lineProdId;
        $transaction->totalVerified  =   ($this->lineOriginalQty !='') ? $this->lineOriginalQty : 1;
        $transaction->grnNo          =   $this->orderGrn;
        $transaction->deliveryTypeId =   'IP';
        $transaction->orderNo        =   $this->orderNo;
        $transaction->orderLineNo    =   $this->orderLineNo;
        $transaction->qtyOfLabels1   =   1;
        $transaction->qtyOnLabels1   =   ($this->pslOrderQty !='') ? $this->pslOrderQty : 1;
        $transaction->qtyOfLabels2   =   0;
        $transaction->qtyOnLabels2   =   0;
        $transaction->printerId      =   $this->minder->checkPrinterLimited();
                
        $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
                
        $resultSave = $result;
        $result     = explode('|', $result);
                
        $this->issnId = array_shift($result);
                
        $message    = array_pop($result);
                
        if($message == 'Processed successfully'){
            $this->messages[] = 'Processed successfully';
        } else {
            throw new Minder_CreateSsnsFromPurchaseSublines_Exception('Error while create ISSN for detail line: ' . $this->recordId . ' ' . $message . 'Error:' . $resultSave);
        }
    }
    
    protected function getWhReceiveLocation() {
        if (is_null($this->whReceiveLocation))
            $this->whReceiveLocation = current($this->minder->getWhReceiveLocationList());
            
        return $this->whReceiveLocation;
    }
    
    protected function runUio() {
        if(!empty($this->pslOther1)){
            $transaction                =   new Transaction_UIO1A(); 
            $transaction->locnId        =   $this->getWhReceiveLocation();
            $transaction->objectId      =   $this->issnId;
            $transaction->other1Value   =   $this->pslOther1;
            $transaction->qty           =   $this->pslOrderQty;
                
            $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');    
        }

        if(!empty($this->pslOther2)) {
            $transaction                =   new Transaction_UIO2A(); 
            $transaction->locnId        =   $this->getWhReceiveLocation();
            $transaction->objectId      =   $this->issnId;
            $transaction->other2Value   =   $this->pslOther2;
            $transaction->qty           =   $this->pslOrderQty;
        
            $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
        }
        
        if(!empty($this->pslOtherDate3)) {
            $transaction                =   new Transaction_UIO3A(); 
            $transaction->locnId        =   $this->getWhReceiveLocation();
            $transaction->objectId      =   $this->issnId;
            $transaction->other3Value   =   $this->pslOtherDate3;
            $transaction->qty           =   $this->pslOrderQty;
        
            $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
        }
        
        if(!empty($this->pslOtherDate4)) {
            $transaction                =   new Transaction_UIO4A(); 
            $transaction->locnId        =   $this->getWhReceiveLocation();
            $transaction->objectId      =   $this->issnId;
            $transaction->other4Value   =   $this->pslOtherDate4;
            $transaction->qty           =   $this->pslOrderQty;
        
            $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
        }
    }
    
    protected function updateDetailLine() {
        $data       = array('SSN_ID = ?, ' => $this->issnId);
        $message    = '';
        
        try{
            $result     = $this->minder->updatePurchaseDetailLine($this->recordId, $data);
            $message    = $this->minder->lastError;
        
        } catch(Exception $ex){
            $message    =   $this->minder->lastError;    
        }
        
        if($result){
            $this->messages[] = 'Detail line was successfully updated.';
        } else {
            $this->warnings[] = 'Error while update detail line for PURCHASE_SUB_LINE #' . $this->recordId . ': ' . $message;
        }
    }
    
    protected function printIssn() {
        // print new ISSN
        $printerObj = $this->minder->getPrinter(null, $this->minder->limitPrinter);
        $issnData   = $this->minder->getIssnForPrint($this->issnId);
        
        try{
            $result    =    $printerObj->printIssnLabel($issnData);
        
            if($result['RES'] < 0){
                $this->warnings[] = 'Error while print label(s) for ISSN #' . $this->issnId . ': ' . $result['ERROR_TEXT'];
            } else {
                $this->messages[] = $result['ERROR_TEXT'];
            }             
        } catch(Exception $ex){
                $this->warnings[] = 'Error while print label(s) for ISSN #' . $this->issnId . ': ' . $ex->getMessage();
        }
    }
    
    public function doCreate($sublineRecordIds = array()) {
        $sublineRecordIds = is_array($sublineRecordIds) ? $sublineRecordIds : array($sublineRecordIds);
        $this->minder = Minder::getInstance();
        
        foreach($sublineRecordIds as $recordId) {
            
            $this->getSublineDetails($recordId);
            
            if(empty($this->ssnId)){
                try {
                    $this->getOrderDetails();
                    $this->getOrderLineDetails();
                
                    if(empty($this->orderGrn)){
                        $this->createGrn();
                    } 
                
                    $this->runGrnvp();
                    $this->runUio();
                    $this->printIssn();
                    
                } catch (Exception $e) {
                    $this->warnings[] = 'Error creating ISSN for PURCHASE_SUB_LINE #' . $this->recordId . '. ' . $e->getMessage();
                }
            }
        }

    }
    
    public function getWarnings() {
        return $this->warnings;
    }
    
    public function getMessages() {
        return $this->messages;
    }
}

class Minder_CreateSsnsFromPurchaseSublines_Exception  extends Minder_Exception {}
