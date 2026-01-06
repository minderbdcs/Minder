<?php

class Minder_OtcProcess_State {
    const TOOL       = 'TOOL';
    const CONSUMABLE = 'CONSUMABLE';

    const COST_CENTER  = 'COST_CENTER';
    const BORROWER     = 'BORROWER';
    const ITEM_ID      = 'ITEM_ID';
    const ISSUE_QTY    = 'ISSUE_QTY';
    const LOANED_TOTAL = 'ISSUE_QTY';
    const LOCATION = 'LOCATION';


    public $processId = null;

    /**
     * @var Minder_OtcProcess_State_CostCenter
     */
    public $chargeTo;

    /**
     * @var Minder_OtcProcess_State_Location
     */
    public $auditLocation;

    /**
     * @var Minder_OtcProcess_State_AbstractLocation
     */
    public $issueTo;

    /**
     * @var Minder_OtcProcess_State_AbstractLocation
     */
    public $returnFrom;

    /**
     * @var Minder_OtcProcess_State_AbstractLocation
     */
    public $returnTo;

    /**
     * @var Minder_OtcProcess_State_ToolTransaction
     */
    public $toolTransaction;

    /**
     * @var Minder_OtcProcess_State_AbstractItem
     */
    public $item;

    public $committed = false;

    public $issueQty    = null;
    public $issueQtyVia = '';
    public $issueQtyDescription = '';

    public $transactionType = null;
    public $transactionMessage = null;
    public $canChangeToolReturnLocation = true;

    public $expectedQty = 0;

    public $checkedIssnList = array();

    public $save = array();

    function __construct()
    {
        $this->setItem(new Minder_OtcProcess_State_AbstractItem());
        $this->setReturnTo(new Minder_OtcProcess_State_AbstractLocation());
        $this->chargeTo = new Minder_OtcProcess_State_CostCenter();
        $this->issueTo = new Minder_OtcProcess_State_AbstractLocation();
        $this->returnFrom = new Minder_OtcProcess_State_AbstractLocation();
        $this->auditLocation = new Minder_OtcProcess_State_Location();
        $this->toolTransaction = new Minder_OtcProcess_State_ToolTransaction();
    }

    public function setItem(Minder_OtcProcess_State_AbstractItem $item, $defaultReturnLocation = null) {
        $this->item = $item;
        $this->canChangeToolReturnLocation = $this->_canChangeToolReturnLocation($defaultReturnLocation);
    }

    public function setReturnTo(Minder_OtcProcess_State_AbstractLocation $location, $defaultReturnLocation = null) {
        $this->returnTo = $location;
        $this->canChangeToolReturnLocation = $this->_canChangeToolReturnLocation($defaultReturnLocation);
    }

    /**
     * @return string
     */
    public function formatTransactionSource() {
        return 'SS' . $this->item->via . 'S' . $this->chargeTo->via . $this->issueQtyVia . 'SS' . $this->issueTo->via;
    }

    public function cleanUpState($except) {
        $except = (is_array($except)) ? $except : array($except);

        if (!in_array(self::COST_CENTER, $except)) {
            $this->chargeTo = new Minder_OtcProcess_State_CostCenter();
        }

        if (!in_array(self::ITEM_ID, $except)) {
            $this->setItem(new Minder_OtcProcess_State_Tool());
        }

        if (!in_array(self::ISSUE_QTY, $except)) {
            $this->issueQty = null;
            $this->issueQtyDescription = '';
        }

        if (!in_array(self::LOANED_TOTAL, $except)) {
        }

    }

    public function doCleanup() {
        $this->save = array();
        $this->toolTransaction = new Minder_OtcProcess_State_ToolTransaction();
    }

    private function _canChangeToolReturnLocation($defaultReturnLocation)
    {
        return !empty($defaultReturnLocation)
            && $this->processId == Minder_OtcProcess::RETURNS
            && $this->item->existed
            && $this->returnTo->existed
            && $defaultReturnLocation != $this->returnTo->location;
    }
}
