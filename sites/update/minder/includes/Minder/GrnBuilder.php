<?php

class Minder_GrnBuilder {
    
    public $purchaseOrder  = '';
    public $carrierId      = '';
    public $containerNo    = '';
    public $pslOrderQty    = '';
    public $ownerId        = '';
    public $personId       = '';
    public $supplierId     = '';
    public $crateOwnerId   = '';
    public $crateQty       = 0;
    public $deliveryTypeId = '';
    public $palletOwnerId  = '';
    public $labelQty       = 0;
    
    public $grn            = '';
    
    public $grndiMessage   = '';
    public $grndlMessage   = '';
    
    public $nonEmptyContainerNoRequired = false;

    protected $minder = null;
    
    public function __construct() {
        
    }

    protected function parseGrndiResult($result) {
        return preg_split('#:|\|#si', $result);
    }
    
    protected function runGrndi() {
        $transaction = new Transaction_GRNDI();
            
        $transaction->orderNo               = $this->purchaseOrder;
            
        $transaction->carrierId             = $this->carrierId;
        $transaction->conNoteNo             = 'IN-TRANSIT IMPORT';
        $transaction->vehicleRegistration   = 'INTRANSIT';
                
        $transaction->deliveryTypeId        = $this->deliveryTypeId;
        $transaction->orderLineNo           = 1;
        $transaction->hasContainer          = ($this->containerNo != '') ? 'Y' : 'N';
        $transaction->palletOwnerId         = $this->palletOwnerId;
        $transaction->palletQty             = $this->pslOrderQty;
        $transaction->crateOwnerId          = $this->crateOwnerId;
        $transaction->supplierId            = $this->supplierId;
        $transaction->crateTypeId           = '';
        $transaction->crateQty              = $this->crateQty;//0; 
        
        $minder = Minder::getInstance();
        
        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    '))) {
            throw new Minder_GrnBuilder_Exception('Error while ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $minder->lastError);
        } else {
            $result             = $this->parseGrndiResult($result);
            $this->grn          = $result[1];
            $this->grndiMessage = $result[5];
        }
    }
    
    protected function runGrndl() {
        $transaction = new Transaction_GRNDL();

        $transaction->orderNo            = $this->purchaseOrder;
        $transaction->grnNo              = $this->grn;
        $transaction->ownerId            = $this->ownerId;
        $transaction->containerNo        = $this->containerNo;
        $transaction->containerTypeId    = '';
        $transaction->supplierId         = $this->supplierId;
        $transaction->deliveryTypeId     = $this->deliveryTypeId;
        $transaction->printerId          = $this->minder->checkPrinterLimited();    
        $transaction->grnLabelQty        = $this->labelQty;

        if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    '))) {
            throw new Minder_GrnBuilder_Exception('Error while ' . $transaction->transCode . $transaction->transClass . ' transaction: ' . $minder->lastError);
        } else {
            $this->grndlMessage = $result;
        }    
    }
    
    public function doBuild() {
        $this->minder = Minder::getInstance();

        if ($this->nonEmptyContainerNoRequired && empty($this->containerNo))
            throw new Minder_GrnBuilder_Exception('Shipping Container No must not be empty.');
            
        $this->runGrndi();
        $this->runGrndl();
    }
}
