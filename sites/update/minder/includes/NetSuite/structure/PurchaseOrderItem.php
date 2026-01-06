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
 * PurchaseOrderItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_PurchaseOrderItem {
    public $item; //NetSuite_RecordRef
    public $line;
    public $vendorName;
    public $quantityReceived;
    public $quantityBilled;
    public $quantityAvailable;
    public $quantityOnHand;
    public $quantity;
    public $units; //NetSuite_RecordRef
    public $serialNumbers;
    public $description;
    public $rate;
    public $amount;
    public $options; //NetSuite_CustomFieldList
    public $department; //NetSuite_RecordRef
    public $class; //NetSuite_RecordRef
    public $location; //NetSuite_RecordRef
    public $landedCostCategory; //NetSuite_RecordRef
    public $customer; //NetSuite_RecordRef
    public $isBillable;
    public $isClosed;
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  NetSuite_RecordRef $item, $line, $vendorName, $quantityReceived, $quantityBilled, $quantityAvailable, $quantityOnHand, $quantity, NetSuite_RecordRef $units, $serialNumbers, $description, $rate, $amount, NetSuite_CustomFieldList $options, NetSuite_RecordRef $department, NetSuite_RecordRef $class, NetSuite_RecordRef $location, NetSuite_RecordRef $landedCostCategory, NetSuite_RecordRef $customer, $isBillable, $isClosed, NetSuite_CustomFieldList $customFieldList) {
        $this->item = $item;
        $this->line = $line;
        $this->vendorName = $vendorName;
        $this->quantityReceived = $quantityReceived;
        $this->quantityBilled = $quantityBilled;
        $this->quantityAvailable = $quantityAvailable;
        $this->quantityOnHand = $quantityOnHand;
        $this->quantity = $quantity;
        $this->units = $units;
        $this->serialNumbers = $serialNumbers;
        $this->description = $description;
        $this->rate = $rate;
        $this->amount = $amount;
        $this->options = $options;
        $this->department = $department;
        $this->class = $class;
        $this->location = $location;
        $this->landedCostCategory = $landedCostCategory;
        $this->customer = $customer;
        $this->isBillable = $isBillable;
        $this->isClosed = $isClosed;
        $this->customFieldList = $customFieldList;
    }
}?>