<?php

class Minder_PickInvoice {
    
    public $freight             = 0;
    public $taxRate             = 0;
    public $adminFeeAmount      = 0;
    public $adminFeeRate        = 0;
    public $paidAmount          = 0;
    public $subTotalTax         = 0;
    public $subTotalAmount      = 0;
    
    public $freightTaxAmount    = 0;
    public $adminTaxAmount      = 0;
    public $taxAmount           = 0;
    public $dueAmount           = 0;
    public $totalAmount         = 0;
    
    public function calculateInvoice() {
        $this->freightTaxAmount = $this->freight * $this->taxRate / 100;
        $this->adminTaxAmount   = $this->adminFeeAmount * $this->taxRate  / 100;
        $this->taxAmount        = $this->subTotalTax + $this->freightTaxAmount + $this->adminTaxAmount;
        $this->dueAmount        = $this->subTotalAmount + $this->freight + $this->adminFeeAmount + $this->taxAmount - $this->paidAmount;
        $this->totalAmount      = $this->subTotalAmount + $this->freight + $this->adminFeeAmount + $this->taxAmount;
    }
}