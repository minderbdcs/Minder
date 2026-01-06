<?php

class Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics {
    public $totalSscc = 0;
    public $cancelledSscc = 0;
    public $despatchedSscc = 0;
    public $checkedSscc = 0;
    public $uncheckedSscc = 0;
    public $inProgressSscc = 0;
    public $nextPrintedSscc = '';
    public $nextUnprintedSscc = '';

    public $totalPickItems = 0;
    public $despatchedItems = 0;
    public $readyForDespatchItems = 0;
    public $pickedQty = 0;
    public $checkedQty = 0;

    public $totalWeight = 0;
    public $totalVolume = 0;
    public $pallets = 0;
    public $satchels = 0;
    public $cartons = 0;

    public $awbConsignmentNo = '';
}