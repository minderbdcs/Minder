<?php

class Minder_OtcProcess_State_AbstractItem implements Minder_OtcProcess_State_ItemInterface {
    const TOOL       = 'TOOL';
    const CONSUMABLE = 'CONSUMABLE';

    public $id;
    public $via;
    public $description;
    public $existed = false;
    public $images = null;
    public $scannedItemType = null;
    public $itemType = null;
    public $defaultIssueQty = 1;
    public $defaultIssueUom = 'EA';
    public $homeLocation = '';
    public $onLoan = false;
    public $onLoanAt = '';
    public $loanedTo = '';
    public $transferConfirmed = false;
    public $whId = '';
    public $locnId = '';

    public $homeWhId = '';
    public $locnName = '';

    /**
     * @var Minder_OtcProcess_CheckPeriod
     */
    public $safetyTestPeriod;
    /**
     * @var Minder_OtcProcess_CheckPeriod
     */
    public $calibratePeriod;
    /**
     * @var Minder_OtcProcess_CheckPeriod
     */
    public $inspectionPeriod;
    public $expirationConfirmed = true;

    public $legacyId = false;
    public $currentQty;
    public $companyId = '';

    function __construct($id = null, $via = 'S')
    {
        $this->id = $id;
        $this->via = $via;
        $this->description = empty($id) ? '' : 'Item not found.';
        $this->calibratePeriod = new Minder_OtcProcess_CheckPeriod();
        $this->safetyTestPeriod = new Minder_OtcProcess_CheckPeriod();
        $this->inspectionPeriod = new Minder_OtcProcess_CheckPeriod();
    }


    /**
     * @return boolean
     */
    public function isTool()
    {
        return $this->itemType == self::TOOL;
    }

    /**
     * @return boolean
     */
    public function isConsumable()
    {
        return $this->itemType == self::CONSUMABLE;
    }

    public function isExisted()
    {
        return $this->existed;
    }

    public function doesExpirationConfirmed()
    {
        return $this->expirationConfirmed;
    }

    public function isOnLoan()
    {
        return $this->onLoan;
    }

    public function doesTransferConfirmed()
    {
        return $this->transferConfirmed;
    }

    public function confirmExpiration()
    {
        $this->expirationConfirmed = true;
    }

    public function confirmTransfer()
    {
        $this->transferConfirmed = true;
    }

    public function getLocationId() {
        return str_pad($this->whId, 2 - strlen($this->whId), ' ') . $this->locnId;
    }
}
