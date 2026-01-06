<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * InventoryAdjustment map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_InventoryAdjustment {
    public $postingPeriod; //NetSuite_RecordRef
    public $tranDate;
    public $tranId;
    public $account; //NetSuite_RecordRef
    public $estimatedTotalValue;
    public $customer; //NetSuite_RecordRef
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $memo;
    public $inventoryList; //NetSuite_InventoryAdjustmentInventoryList
    public $internalId;
    public $externalId;

    public function __construct(  NetSuite_RecordRef $postingPeriod, $tranDate, $tranId, NetSuite_RecordRef $account, $estimatedTotalValue, NetSuite_RecordRef $customer, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, $memo, NetSuite_InventoryAdjustmentInventoryList $inventoryList, $internalId, $externalId) {
        $this->postingPeriod = $postingPeriod;
        $this->tranDate = $tranDate;
        $this->tranId = $tranId;
        $this->account = $account;
        $this->estimatedTotalValue = $estimatedTotalValue;
        $this->customer = $customer;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->memo = $memo;
        $this->inventoryList = $inventoryList;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>