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
 * ItemFulfillmentItem map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_ItemFulfillmentItem {
    public $jobName;
    public $itemReceive;
    public $itemName;
    public $description;
    public $location; //NetSuite_RecordRef
    public $onHand;
    public $quantity;
    public $unitsDisplay;
    public $binNumbers;
    public $serialNumbers;
    public $poNum;
    public $item; //NetSuite_RecordRef
    public $orderLine;
    public $quantityRemaining;
    public $options; //NetSuite_CustomFieldList
    public $customFieldList; //NetSuite_CustomFieldList

    public function __construct(  $jobName, $itemReceive, $itemName, $description, NetSuite_RecordRef $location, $onHand, $quantity, $unitsDisplay, $binNumbers, $serialNumbers, $poNum, NetSuite_RecordRef $item, $orderLine, $quantityRemaining, NetSuite_CustomFieldList $options, NetSuite_CustomFieldList $customFieldList) {
        $this->jobName = $jobName;
        $this->itemReceive = $itemReceive;
        $this->itemName = $itemName;
        $this->description = $description;
        $this->location = $location;
        $this->onHand = $onHand;
        $this->quantity = $quantity;
        $this->unitsDisplay = $unitsDisplay;
        $this->binNumbers = $binNumbers;
        $this->serialNumbers = $serialNumbers;
        $this->poNum = $poNum;
        $this->item = $item;
        $this->orderLine = $orderLine;
        $this->quantityRemaining = $quantityRemaining;
        $this->options = $options;
        $this->customFieldList = $customFieldList;
    }
}?>